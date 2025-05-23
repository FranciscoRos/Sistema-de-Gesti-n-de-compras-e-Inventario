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

    } elseif ($peticion[0] === "filtrarBajoStock" && isset($peticion[1])) {
      $cant = $peticion[1];
      return Producto::filtrarBajoStock($cant);
      
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
  //Filtrar producto por id de prooveedor
  public static function filtrarPorProveedor($idProveedor) {
    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

      $sql = "SELECT idProducto, idUsuario, nombre, precioCompra, precioVenta, stock FROM productos WHERE idProveedor = ?";
      $sentencia = $_conexion->prepare($sql);
      $sentencia->bindParam(1, $idProveedor, PDO::PARAM_INT);
      $sentencia->execute();
      $datos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
      
      return [
          "estado" => 1,
          "mensaje" => "Productos encontrados correctamente",
          "datos" => $datos
        ];

    } catch (PDOException $e){
      throw new ExcepcionApi(7, "Rango inválido: ". $e->getMessage(), 500);
    }
  }
}
