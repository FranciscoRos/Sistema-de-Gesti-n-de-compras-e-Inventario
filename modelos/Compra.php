<?php
require_once __DIR__ . '/../datos/ConexionBD.php';
require_once __DIR__ . '/../modelos/detalleCompra.php';
require_once __DIR__ . '/../librerias/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    if (!isset($datos['idProveedor']) || count($datos['productos']) === 0) {
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
      $productosResumen = '';
      // Insertar productos en detalle_compra y acumular total
      foreach ($datos['productos'] as $detalle) {
        if (!isset($detalle['idProducto'], $detalle['cantidad'], $detalle['precioUnitario'])) {
          throw new ExcepcionApi(4, "Faltan campos en uno de los productos", 422);
        }

        $subtotal = $detalle['cantidad'] * $detalle['precioUnitario']; //2 vasos * 20.00 = 40 , 3 manzabas * 10.00 = 300
        $total += $subtotal; //340.00

        // Insertar detalle
        $sqlDetalle = "INSERT INTO " . self::TABLA_DETALLE . " (idCompra, idProducto, cantidad, precioUnitario) VALUES (?, ?, ?, ?)";
        $insercionDetalle = $_conexion->prepare($sqlDetalle);
        $insercionDetalle->bindParam(1, $idCompra, PDO::PARAM_INT);
        $insercionDetalle->bindParam(2, $detalle['idProducto'], PDO::PARAM_INT);
        $insercionDetalle->bindParam(3, $detalle['cantidad'], PDO::PARAM_INT);
        $insercionDetalle->bindParam(4, $detalle['precioUnitario']);
        $insercionDetalle->execute();

        // Acumular información de productos para el correo
        $productosResumen .=
          'Producto: ' . htmlspecialchars($detalle['idProducto']) . '<br>' .
          'Cantidad: ' . htmlspecialchars($detalle['cantidad']) . '<br>' .
          'Precio unitario: ' . htmlspecialchars($detalle['precioUnitario']) . '<br>' .
          'Subtotal: ' . htmlspecialchars($subtotal) . '<br><br>';

        // Actualizar stock del producto
        $sqlStock = "UPDATE productos SET stock = stock + ? WHERE idProducto = ? AND idUsuario = ?";
        $stmtStock = $_conexion->prepare($sqlStock);
        $stmtStock->bindParam(1, $detalle['cantidad'], PDO::PARAM_INT);
        $stmtStock->bindParam(2, $detalle['idProducto'], PDO::PARAM_INT);
        $stmtStock->bindParam(3, $idUsuario, PDO::PARAM_INT);
        $stmtStock->execute();
        // Después de actualizar stock, también actualizamos el precioCompra del producto
        $sqlPrecio = "UPDATE productos SET precioCompra = ? WHERE idProducto = ? AND idUsuario = ?";
        $stmtPrecio = $_conexion->prepare($sqlPrecio);
        $stmtPrecio->bindParam(1, $detalle['precioUnitario']);
        $stmtPrecio->bindParam(2, $detalle['idProducto'], PDO::PARAM_INT);
        $stmtPrecio->bindParam(3, $idUsuario, PDO::PARAM_INT);
        $stmtPrecio->execute();
      }

      // Enviar correo de ticket de compra con el resumen de todos los productos
      try {
        $userData = self::correoUsuario($idUsuario);
        $emailUser = $userData['datos']['correo'];
        $userName = $userData['datos']['nombreUsuario'];

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'falsofrancisco804@gmail.com';
        $mail->Password   = 'uplsgkhtgboubegh';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('falsofrancisco804@gmail.com', 'Administrador');
        $mail->addAddress($emailUser, $userName);
        $mail->isHTML(true);
        $mail->Subject = '¡Ticket de compra generado!';
        $mail->Body    =
          '<b>Hola ' . htmlspecialchars($userName) . '</b><br>' .
          '<b>Se ha creado su ticket de compra: </b><br>' .
          $productosResumen .
          'ID de compra: ' . $idCompra . '<br>' .
          'Total: ' . $total . '<br>' .
          'Gracias por confiar en nosotros. Tenga un buen día';
        $mail->AltBody = 'Usted ha realizado una compra. ID de compra: ' . $idCompra . ', Total: ' . $total;
        $mail->send();
      } catch (\Exception $e) {
        // Puedes registrar el error si lo deseas, pero no interrumpas la compra
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

  /**
   * Obtiene todas las compras hechas por un usuario
   * 
   * @param int $idUsuario ID del usuario autenticado
   * @return array Arreglo de compras pertenecientes al usuario
   */

  public static function obtenerTodos($idUsuario)
  {
    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = "SELECT idCompra, idUsuario, idProveedor, fecha, total FROM " . self::TABLA_COMPRA . " WHERE idUsuario = ?";
      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $query->execute();
      $compras = $query->fetchAll(PDO::FETCH_ASSOC);

      return [
        "estado" => 1,
        "mensaje" => "Compras recuperados correctamente",
        "datos" => $compras

      ];
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al consultar compras: " . $e->getMessage(), 500);
    }
  }
  /**
   * Obtiene una compra específica del usuario
   *
   * @param int $idUsuario ID del usuario autenticado
   * @param int $idCompra  ID de la compra
   * @return array Detalles de la compra
   */
  public static function obtenerPorId($idUsuario, $idCompra)
  {
    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $sql = "SELECT idCompra, idUsuario, idProveedor, fecha, total FROM " . self::TABLA_COMPRA . " WHERE idCompra = ?";

      $query = $_conexion->prepare($sql);
      $query->bindParam(1, $idCompra, PDO::PARAM_INT);
      $query->execute();
      $infoCompra = $query->fetch(PDO::FETCH_ASSOC);

      if (!$infoCompra) {
        throw new ExcepcionApi(8, "La compra no existe", 404);
      }

      // Verifica que el usuario autenticado sea el dueño
      if ($infoCompra["idUsuario"] !== $idUsuario) {
        throw new ExcepcionApi(9, "No tienes permisos para ver esta compra", 403);
      }

      return [
        "estado" => 1,
        "mensaje" => "Información de la compra recuperada correctamente",
        "datos" => $infoCompra
      ];

    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al consultar compra: " . $e->getMessage(), 500);
    }
  }

  // Consultas de todas las compras realizadas
  public static function obtenerComprasPorUsuario($idUsuario)
  {
    try {
      $conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $sql = "SELECT c.idCompra, c.fecha, c.total, 
                           IFNULL(p.nombre, 'Proveedor eliminado') AS proveedor
                    FROM compras c
                    LEFT JOIN proveedores p ON c.idProveedor = p.idProveedor
                    WHERE c.idUsuario = ?
                    ORDER BY c.fecha DESC";

      $stmt = $conexion->prepare($sql);
      $stmt->execute([$idUsuario]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al consultar compras: " . $e->getMessage(), 500);
    }
  }

  public static function correoUsuario($idUsuario) {
    try {
        $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

        $sql = "SELECT u.correo,
        u.nombre AS nombreUsuario,
        p.nombre AS nombreProducto,
        p.precioCompra,
        p.precioVenta,
        p.stock
        FROM usuarios u JOIN productos p ON u.idusuario = p.idUsuario
        WHERE u.idUsuario = ?";
        $sentencia = $_conexion->prepare($sql);
        $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);
        $sentencia->execute();
        $datos = $sentencia->fetch(PDO::FETCH_ASSOC);

        return [
          "estado" => 1,
          "mensaje" => "Datos encontrados correctamente",
          "datos" => $datos
        ];
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al encontrar el correo del usuario: " . $e->getMessage(), 500);
    }
  }
}
