<?php
require_once __DIR__ . '/../datos/ConexionBD.php';

class Usuario
{
  const NOMBRE_TABLA = "usuarios";

  public static function crear($nombre, $correo, $contrasena)
  {
    $claveApi = self::generarClaveApi();
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
      $conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = "INSERT INTO " . self::NOMBRE_TABLA . " (nombre, correo, contrasena, claveApi) VALUES (?, ?, ?, ?)";

      $query = $conexion->prepare($query);
      $query->bindParam(1, $nombre);
      $query->bindParam(2, $correo);
      $query->bindParam(3, $hash);
      $query->bindParam(4, $claveApi);
      $query->execute();
      return [
        "estado" => 1,
        "mensaje" => "Usuario registrado correctamente",
        "claveApi" => $claveApi
      ];
    } catch (PDOException $exception) {
      throw new ExcepcionApi(2, "Error al registrar: " . $exception->getMessage(), 400);
    }
  }

  public static function autenticar($correo, $contrasena)
  {
    $query = "SELECT idUsuario, nombre, correo, contrasena, claveApi FROM " . self::NOMBRE_TABLA . " WHERE correo = ?";
    
    try{
       $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $query = $pdo->prepare($query);
            $query->bindParam(1, $correo);
            $query->execute();
            //FETCH_ASSOC nos da arreglo asociativo [clave: valor]
            $usuario = $query->fetch(PDO::FETCH_ASSOC);

            if($usuario && password_verify($contrasena,$usuario['contrasena'])){
              return [
                    "estado" => 1,
                    "mensaje" => "Autenticación exitosa",
                    "usuario" => [
                        "id" => $usuario["idUsuario"],
                        "nombre" => $usuario["nombre"],
                        "correo" => $usuario["correo"],
                        "claveApi" => $usuario["claveApi"]
                    ]
                ];
            }else{
              throw new ExcepcionApi(3, "Credenciales incorrectas", 401);
            }
    }catch (PDOException $exception){
      throw new ExcepcionApi(2, "Error en autenticación: " .$exception->getMessage(),400)
    }
  
  }

  public static function validarClaveApi($clave)
    {
        $sql = "SELECT idUsuario FROM " . self::NOMBRE_TABLA . " WHERE claveApi = ?";

        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $sentencia = $pdo->prepare($sql);
        $sentencia->bindParam(1, $clave);
        $sentencia->execute();

        $fila = $sentencia->fetch();
        return $fila ? $fila['idUsuario'] : null;
    }

  private static function generarClaveApi()
  {
    return bin2hex(random_bytes(20));
  }
}
