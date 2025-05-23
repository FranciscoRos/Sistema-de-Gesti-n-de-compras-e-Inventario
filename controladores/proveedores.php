<?php
require_once __DIR__ . '/../modelos/Proveedor.php';
require_once __DIR__ . '/../controladores/usuarios.php';

class proveedores
{
  
  public static function get($peticion)
  {
    $idUsuario = usuarios::autorizar();

    if (!isset($peticion[0]) || trim($peticion[0]) === "") {
      return Proveedor::obtenerTodos($idUsuario);
    } else {
      $idProveedor = $peticion[0];
      return Proveedor::obtenerPorId($idUsuario, $idProveedor);
    }
  }

  
  public static function post($peticion)
  {
    $idUsuario = usuarios::autorizar();

    $body = file_get_contents("php://input");
    $datos = json_decode($body, true);

    return Proveedor::crear($idUsuario, $datos);
  }
  
  
  public static function put($peticion)
  {
    $idUsuario = usuarios::autorizar();

    if (!isset($peticion[0])) {
      throw new ExcepcionApi(4, "Falta ID del proveedor", 422);
    }

    $idProveedor = $peticion[0];
    $body = file_get_contents("php://input");
    $datos = json_decode($body, true);

    return Proveedor::actualizar($idUsuario, $idProveedor, $datos);
  }

  
  public static function delete($peticion)
  {
    $idUsuario = usuarios::autorizar();

    if (!isset($peticion[0])) {
      throw new ExcepcionApi(4, "Falta ID del proveedor", 422);
    }

    $idProveedor = $peticion[0];
    return Proveedor::eliminar($idUsuario, $idProveedor);
  }
}
