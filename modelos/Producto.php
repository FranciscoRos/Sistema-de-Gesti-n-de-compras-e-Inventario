<?php
require_once __DIR__ . '/../datos/ConexionBD.php';

class Producto
{
  const NOMBRE_TABLA = "productos";

  /**
   * Inserta un nuevo producto en la base de datos
   *
   * @param int $idUsuario  ID del usuario autenticado (clave foránea)
   * @param array $datos    Datos del producto desde JSON: nombre, precioCompra, precioVenta, stock
   * @return array          Respuesta para la vista
   * @throws ExcepcionApi   Si hay error al insertar
   */
  public static function crear($idUsuario, $datos)
  {
    // Validación básica
    if (!isset($datos['nombre'], $datos['precioCompra'], $datos['precioVenta'], $datos['stock'])) {
      throw new ExcepcionApi(4, "Datos incompletos para crear producto", 422);
    }

    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = "INSERT INTO productos (idUsuario, nombre, precioCompra, precioVenta, stock)
                VALUES (?, ?, ?, ?, ?)";

      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $query->bindParam(2, $datos['nombre']);
      $query->bindParam(3, $datos['precioCompra']);
      $query->bindParam(4, $datos['precioVenta']);
      $query->bindParam(5, $datos['stock'], PDO::PARAM_INT);
      $query->execute();

      return [
        "estado" => 1,
        "mensaje" => "Producto creado correctamente",
        "id" => $_conexion->lastInsertId()
      ];
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al crear producto: " . $e->getMessage(), 500);
    }
  }


  /**
 * Obtiene todos los productos registrados por un usuario
 *
 * @param int $idUsuario  ID del usuario autenticado
 * @return array          Arreglo de productos pertenecientes al usuario
 */
  public static function obtenerTodos($idUsuario)
  {
    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = "SELECT idProducto, idUsuario, nombre, precioCompra, precioVenta, stock FROM productos WHERE idUsuario = ?";
      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $query->execute();
      $productos = $query->fetchAll(PDO::FETCH_ASSOC);

      return [
        "estado" => 1,
        "mensaje" => "Productos recuperados correctamente",
        "datos" => $productos
      ];
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al consultar productos: " . $e->getMessage(), 500);
    }
  }

  /**
 * Obtiene un producto espefícico del usuario
 *
 * @param int $idUsuario   ID del usuario autenticado
 * @param int $idProducto  ID del producto
 * @return array           producto
 */
  public static function obtenerPorId($idUsuario, $idProducto)
  {

    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = "SELECT idProducto, idUsuario, nombre, precioCompra, precioVenta, stock FROM productos WHERE idUsuario = ? && idProducto =?";
      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $query->bindParam(12, $idProducto, PDO::PARAM_INT);
      $query->execute();
      $productoInfo = $query->fetch(PDO::FETCH_ASSOC);

      return [
        "estado" => 1,
        "mensaje" => "Info de producto recuperada correctamente",
        "datos" => $productoInfo
      ];
    } catch (PDOException $exceotion) {
      throw new ExcepcionApi(7, "Error al consultar producto: " . $exceotion->getMessage(), 500);
    }

  }

  /**
 * Actualiza los datos de un producto del usuario autenticado
 *
 * @param int   $idUsuario   ID del usuario autenticado
 * @param int   $idProducto  ID del producto a modificar
 * @param array $datos       Campos editados: nombre, precioCompra, precioVenta, stock
 *
 * @return array
 * @throws ExcepcionApi
 */
public static function actualizar($idUsuario, $idProducto, $datos)
{
    if (!isset($datos['nombre'], $datos['precioCompra'], $datos['precioVenta'], $datos['stock'])) {
        throw new ExcepcionApi(4, "Datos incompletos para actualizar", 422);
    }

    try {
        $conexion = ConexionBD::obtenerInstancia()->obtenerBD();

        $sql = "UPDATE productos SET nombre = ?, precioCompra = ?, precioVenta = ?, stock = ?
                WHERE idProducto = ? AND idUsuario = ?";

        $sentencia = $conexion->prepare($sql);
        $sentencia->bindParam(1, $datos['nombre']);
        $sentencia->bindParam(2, $datos['precioCompra']);
        $sentencia->bindParam(3, $datos['precioVenta']);
        $sentencia->bindParam(4, $datos['stock']);
        $sentencia->bindParam(5, $idProducto, PDO::PARAM_INT);
        $sentencia->bindParam(6, $idUsuario, PDO::PARAM_INT);
        $sentencia->execute();

        if ($sentencia->rowCount() > 0) {
            return [
                "estado" => 1,
                "mensaje" => "Producto actualizado correctamente"
            ];
        } else {
            throw new ExcepcionApi(5, "El producto no existe o no pertenece al usuario", 404);
        }

    } catch (PDOException $e) {
        throw new ExcepcionApi(7, "Error al actualizar producto: " . $e->getMessage(), 500);
    }
}


  /**
 * Elimina un producto de la base de datos
 *
 * @param int $idUsuario    ID del usuario autenticado
 * @param int $idProducto   ID del producto a eliminar
 *
 * @return array
 * @throws ExcepcionApi
 */
public static function eliminar($idUsuario, $idProducto)
{
    try {
        $conexion = ConexionBD::obtenerInstancia()->obtenerBD();

        $sql = "DELETE FROM productos WHERE idProducto = ? AND idUsuario = ?";
        $sentencia = $conexion->prepare($sql);
        $sentencia->bindParam(1, $idProducto, PDO::PARAM_INT);
        $sentencia->bindParam(2, $idUsuario, PDO::PARAM_INT);
        $sentencia->execute();

        if ($sentencia->rowCount() > 0) {
            return [
                "estado" => 1,
                "mensaje" => "Producto eliminado correctamente"
            ];
        } else {
            throw new ExcepcionApi(5, "El producto no existe o no pertenece al usuario", 404);
        }

    } catch (PDOException $e) {
        throw new ExcepcionApi(7, "Error al eliminar producto: " . $e->getMessage(), 500);
    }
}

}
?>
