<?php
require_once __DIR__ . '/../datos/ConexionBD.php';

class Proveedor
{
  const NOMBRE_TABLA = "proveedores";

  /**
   * Crea un proveedor nuevo asociado al usuario autenticado
   *
   * @param int   $idUsuario   ID del usuario autenticado
   * @param array $datos       Datos del proveedor 
   *
   * @return array             Mensaje para la vista
   */
  public static function crear($idUsuario, $datos)
  {
    if (!isset($datos['nombre'])) {
      throw new ExcepcionApi(4, "El campo 'nombre' es obligatorio", 422);
    }

    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $query = "INSERT INTO proveedores (idUsuario, nombre, contacto, telefono)
                VALUES (?, ?, ?, ?)";

      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $query->bindParam(2, $datos['nombre']);
      $query->bindParam(3, $datos['contacto']);
      $query->bindParam(4, $datos['telefono']);
      $query->execute();

      return [
        "estado" => 1,
        "mensaje" => "Proveedor registrado correctamente",
        "id" => $_conexion->lastInsertId()
      ];
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al registrar proveedor: " . $e->getMessage(), 500);
    }
  }

  /**
   * Devuelve todos los proveedores registrados por el usuario autenticado
   *
   * @param int $idUsuario  ID del usuario autenticado
   *
   * @return array          Lista de proveedores
   */
  public static function obtenerTodos($idUsuario)
  {
    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $query = "SELECT idProveedor, nombre, contacto, telefono
                FROM proveedores WHERE idUsuario = ?";

      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $query->execute();

      $proveedores = $query->fetchAll(PDO::FETCH_ASSOC);

      return [
        "estado" => 1,
        "mensaje" => "Proveedores recuperados correctamente",
        "datos" => $proveedores
      ];
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al consultar proveedores: " . $e->getMessage(), 500);
    }
  }

  /**
   * Devuelve un proveedor específico del usuario autenticado
   *
   * @param int $idUsuario     ID del usuario autenticado
   * @param int $idProveedor   ID del proveedor a consultar
   *
   * @return array             Datos del proveedor o error
   */
  public static function obtenerPorId($idUsuario, $idProveedor)
  {
    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $query = "SELECT idProveedor, nombre, contacto, telefono
                FROM proveedores
                WHERE idUsuario = ? AND idProveedor = ?";

      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idUsuario, PDO::PARAM_INT);
      $query->bindParam(2, $idProveedor, PDO::PARAM_INT);
      $query->execute();

      $proveedor = $query->fetch(PDO::FETCH_ASSOC);

      if (!$proveedor) {
        throw new ExcepcionApi(5, "El proveedor no existe o no pertenece al usuario", 404);
      }

      return [
        "estado" => 1,
        "mensaje" => "Proveedor recuperado correctamente",
        "datos" => $proveedor
      ];
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al consultar proveedor: " . $e->getMessage(), 500);
    }
  }

  /**
   * Actualiza un proveedor del usuario autenticado
   *
   * @param int   $idUsuario     ID del usuario autenticado
   * @param int   $idProveedor   ID del proveedor a actualizar
   * @param array $datos         Nuevos datos del proveedor
   *
   * @return array
   * @throws ExcepcionApi
   */
  public static function actualizar($idUsuario, $idProveedor, $datos)
  {
    if (!isset($datos['nombre'])) {
      throw new ExcepcionApi(4, "El campo 'nombre' es obligatorio", 422);
    }

    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $query = "UPDATE proveedores
                SET nombre = ?, contacto = ?, telefono = ?
                WHERE idProveedor = ? AND idUsuario = ?";

      $query = $_conexion->prepare($query);
      $query->bindParam(1, $datos['nombre']);
      $query->bindParam(2, $datos['contacto']);
      $query->bindParam(3, $datos['telefono']);
      $query->bindParam(4, $idProveedor, PDO::PARAM_INT);
      $query->bindParam(5, $idUsuario, PDO::PARAM_INT);
      $query->execute();

      if ($query->rowCount() > 0) {
        return [
          "estado" => 1,
          "mensaje" => "Proveedor actualizado correctamente"
        ];
      } else {
        throw new ExcepcionApi(5, "El proveedor no existe o no pertenece al usuario", 404);
      }
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al actualizar proveedor: " . $e->getMessage(), 500);
    }
  }

  /**
   * Elimina un proveedor del usuario autenticado
   *
   * @param int $idUsuario     ID del usuario autenticado
   * @param int $idProveedor   ID del proveedor a eliminar
   *
   * @return array
   * @throws ExcepcionApi
   */
  public static function eliminar($idUsuario, $idProveedor)
  {
    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $query = "DELETE FROM proveedores
                WHERE idProveedor = ? AND idUsuario = ?";

      $query = $_conexion->prepare($query);
      $query->bindParam(1, $idProveedor, PDO::PARAM_INT);
      $query->bindParam(2, $idUsuario, PDO::PARAM_INT);
      $query->execute();

      if ($query->rowCount() > 0) {
        return [
          "estado" => 1,
          "mensaje" => "Proveedor eliminado correctamente"
        ];
      } else {
        throw new ExcepcionApi(5, "El proveedor no existe o no pertenece al usuario", 404);
      }
    } catch (PDOException $e) {
      throw new ExcepcionApi(7, "Error al eliminar proveedor: " . $e->getMessage(), 500);
    }
  }
}
