<?php
require_once "../modelos/Usuario.php";

class usuarios
{

  /**
   * Maneja peticiones POST al recurso /usuarios
   *
   * Redirige internamente a /usuarios/registro o /usuarios/login
   *
   * @param array $peticion  Segmentos de la URL después de /usuarios/
   *
   * @return array  Arreglo mensaje para vista
   */
  public static function post($peticion)
  {
    if (!isset($peticion[0])) {
      throw new ExcepcionApi(2, "Falta accion a ejecutar: registro | login", 400);
    }

    switch ($peticion[0]) {
      case 'registro':
        return self::registro();
      case 'login':
        return self::login();

    }
  }

  /**
   * Registra nuevos usuarios
   *
   * Lee los datos JSON del cuerpo de la petición, valida los campos y llama al modelo
   *
   * @return array  Arreglo mensaje para vista
   */
  private static function registro()
  {
    $body = file_get_contents('php://input');
    $datos = json_decode($body, true);

    if (!isset($datos['nombre'], $datos['correo'], $datos['contrasena'])) {
      throw new ExcepcionApi(4, "Datos incompletos para registro", 422);
    }

    return Usuario::crear($datos['nombre'], $datos['correo'], $datos['contrasena']);
  }

  /**
   * Ejecuta el flujo delogin
   *
   * Lee correo y contraseña del cuerpo JSON, y devuelve los datos del usuario si son válidos.
   *
   * @return array  Datos del usuario autenticado y su clave API
   */
  private static function login()
  {
    $body = file_get_contents('php://input');
    $datos = json_decode($body, true);

    if (!isset($datos['correo'], $datos['contrasena'])) {
      throw new ExcepcionApi(4, "Faltan campos para login", 422);
    }

    return Usuario::autenticar($datos['correo'], $datos['contrasena']);
  }

  /**
   * Valida la clave API enviada en los encabezados HTTP
   *
   * Debe usarse en recursos protegidos para verificar que el usuario está autenticado
   *
   * @return int  ID del usuario autenticado
   * @throws ExcepcionApi Si la clave falta o no es válida
   */
  public static function autorizar()
  {
    $cabeceras = apache_request_headers();

    if (!isset($cabeceras["Authorization"])) {
      throw new ExcepcionApi(5, "Falta clave API", 403);
    }

    $clave = $cabeceras["Authorization"];
    $id = Usuario::validarClaveApi($clave);

    if (!$id) {
      throw new ExcepcionApi(6, "Clave API inválida", 403);
    }

    return $id;
  }
}
?>