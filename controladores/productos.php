<?php
require_once __DIR__ . '/../modelos/Producto.php';
require_once __DIR__ . '/../controladores/usuarios.php';

class productos
{
  /**
   * Maneja POST /productos
   * Crea un nuevo producto para el usuario autenticado
   *
   * @param array $peticion
   * @return array
   */
  public static function post($peticionNOUSADO_ANY)
  {
    $idUsuario = usuarios::autorizar();
    $body = json_decode(file_get_contents('php://input'), true);
    return Producto::crear($idUsuario, $body);
  }

  /**
   * Maneja GET /productos y /productos/id
   * Devuelve todos los productos o uno específico del usuario autenticado
   *
   * @param array $peticion
   * @return array
   */
  public static function get($peticion)
  {
    $idUsuario = usuarios::autorizar();

    if (!isset($peticion[0]) || trim($peticion[0]) === "") {
      return Producto::obtenerTodos($idUsuario);
    } else {
      $idProducto = $peticion[0];
      return Producto::obtenerPorId($idUsuario, $idProducto);
    }
  }

  /**
   * Maneja PUT /productos/id
   * Actualiza un producto del usuario
   *
   * @param array $peticion
   * @return array
   */
  public static function put($peticion)
  {
    if (!isset($peticion[0])) {
      throw new ExcepcionApi(4, "Falta id del producto", 422);
    }

    $idUsuario = usuarios::autorizar();
    $idProducto = $peticion[0];
    $body = json_decode(file_get_contents('php://input'), true);

    return Producto::actualizar($idUsuario, $idProducto, $body);
  }

  /**
   * Maneja DELETE /productos/id
   * Elimina un producto del usuario
   *
   * @param array $peticion
   * @return array
   */
  public static function delete($peticion)
  {
    if (!isset($peticion[0])) {
      throw new ExcepcionApi(4, "Falta id del producto", 422);
    }

    $idUsuario = usuarios::autorizar();
    $idProducto = $peticion[0];

    return Producto::eliminar($idUsuario, $idProducto);
  }
}
