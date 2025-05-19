<?php
require_once __DIR__ . '/../datos/ConexionBD.php';

class Compra
{
  const TABLA_COMPRA = "compras";
  const TABLA_DETALLE = "detalle_compra";

  /**
   * Crea una nueva compra con sus detalles y actualiza stock
   *
   * @param int   $idUsuario   ID del usuario autenticado
   * @param array $datos       Datos JSON: idProveedor, productos[{idProducto, cantidad, precioUnitario}]
   *
   * @return array
   * @throws ExcepcionApi
   */
  public static function crear($idUsuario, $datos)
  {
    if (!isset($datos['idProveedor'], $datos['productos']) || !is_array($datos['productos']) || count($datos['productos']) === 0) {
      throw new ExcepcionApi(4, "Datos incompletos para registrar compra", 422);
    }


    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $_conexion->beginTransaction(); // iniciar transacción

      //Primero se inserta en la tabla compras
      $sqlCompra = "INSERT INTO " . self::TABLA_COMPRA . " (idUsuario, idProveedor, fecha, total) VALUES (?, ?, NOW(), 0)";
      $sentenciaCompra = $_conexion->prepare($sqlCompra);
      $sentenciaCompra->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $sentenciaCompra->bindParam(2, $datos['idProveedor'], PDO::PARAM_INT);
      $sentenciaCompra->execute();
      $idCompra = $_conexion->lastInsertId();

      $total = 0;

      // Insertar productos en detalle_compra y acumular total
      foreach ($datos['productos'] as $detalle) {
        if (!isset($detalle['idProducto'], $detalle['cantidad'], $detalle['precioUnitario'])) {
          throw new ExcepcionApi(4, "Faltan campos en uno de los productos", 422);
        }

        $subtotal = $detalle['cantidad'] * $detalle['precioUnitario'];
        $total += $subtotal;

        // Insertar detalle
        $sqlDetalle = "INSERT INTO " . self::TABLA_DETALLE . " (idCompra, idProducto, cantidad, precioUnitario) VALUES (?, ?, ?, ?)";
        $insercionDetalle = $_conexion->prepare($sqlDetalle);
        $insercionDetalle->bindParam(1, $idCompra, PDO::PARAM_INT);
        $insercionDetalle->bindParam(2, $detalle['idProducto'], PDO::PARAM_INT);
        $insercionDetalle->bindParam(3, $detalle['cantidad'], PDO::PARAM_INT);
        $insercionDetalle->bindParam(4, $detalle['precioUnitario']);
        $insercionDetalle->execute();

        // Actualizar stock del producto
        $sqlStock = "UPDATE productos SET stock = stock + ? WHERE idProducto = ? AND idUsuario = ?";
        $stmtStock = $_conexion->prepare($sqlStock);
        $stmtStock->bindParam(1, $detalle['cantidad'], PDO::PARAM_INT);
        $stmtStock->bindParam(2, $detalle['idProducto'], PDO::PARAM_INT);
        $stmtStock->bindParam(3, $idUsuario, PDO::PARAM_INT);
        $stmtStock->execute();
      }

      //  Actualizar total de la compra
      $sqlUpdateTotal = "UPDATE " . self::TABLA_COMPRA . " SET total = ? WHERE idCompra = ?";
      $insercionTotal = $_conexion->prepare($sqlUpdateTotal);
      $insercionTotal->bindParam(1, $total);
      $insercionTotal->bindParam(2, $idCompra, PDO::PARAM_INT);
      $insercionTotal->execute();

      $_conexion->commit(); // confirmar transacción

      return [
        "estado" => 1,
        "mensaje" => "Compra registrada correctamente",
        "idCompra" => $idCompra,
        "total" => $total
      ];
    } catch (PDOException $e) {
      $_conexion->rollBack(); // deshace todo
      throw new ExcepcionApi(7, "Error al registrar compra: " . $e->getMessage(), 500);
    }
  }

  // pendientes obtenerTodos, obtenerPorId
}
