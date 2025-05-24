<?php
require_once __DIR__ . '/../datos/ConexionBD.php';
require_once __DIR__ . '/../librerias/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Usuario
{
  const NOMBRE_TABLA = "usuarios";

  /**
   * Registra un nuevo usuario
   * 
   * @param string $contrasena  Contraseña plana
   *
   * @return array  Arreghlo mensaje para vista
   */
  public static function crear($nombre, $correo, $contrasena)
  {
    $claveApi = self::generarClaveApi();
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = "INSERT INTO " . self::NOMBRE_TABLA . " (nombre, correo, contrasena, claveApi) VALUES (?, ?, ?, ?)";

      $query = $_conexion->prepare($query);
      $query->bindParam(1, $nombre);
      $query->bindParam(2, $correo);
      $query->bindParam(3, $hash);
      $query->bindParam(4, $claveApi);
      $query->execute();

      try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'falsofrancisco804@gmail.com';
        $mail->Password   = 'uplsgkhtgboubegh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('falsofrancisco804@gmail.com', 'Administrador');
        $mail->addAddress($correo, $contrasena);
        $mail->isHTML(true);
        $mail->Subject = '¡Se ha creado un nuevo producto!';
        $mail->Body = 'Hola ' . $nombre . 
        ',<br><br>Tu cuenta asociada a: '. $correo . ' sido creada exitosamente. Tu clave API es: <strong>' . $claveApi . 
        '</strong>.<br><br>Saludos.<br>Equipo de Soporte';
        $mail->send();

      } catch (PDOException $e) {
        throw new ExcepcionApi(4, "Error al enviar el correo al usuario: " . $e->getMessage(), 400);
      }
      return [
        "estado" => 1,
        "mensaje" => "Usuario registrado correctamente",
        "claveApi" => $claveApi
      ];
    } catch (PDOException $exception) {
      throw new ExcepcionApi(2, "Error al registrar: " . $exception->getMessage(), 400);
    }
  }

  /**
   * Autentica a un usuario
   *
   * @param string $contrasena  Contraseña plana
   *
   * @return array  Arreghlo mensaje para vista
   */
  public static function autenticar($correo, $contrasena)
  {
    $query = "SELECT idUsuario, nombre, correo, contrasena, claveApi FROM " . self::NOMBRE_TABLA . " WHERE correo = ?";

    try {
      $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = $_conexion->prepare($query);
      $query->bindParam(1, $correo);
      $query->execute();
      //FETCH_ASSOC nos da arreglo asociativo [clave: valor]
      $usuario = $query->fetch(PDO::FETCH_ASSOC);

      if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
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
      } else {
        throw new ExcepcionApi(3, "Credenciales incorrectas", 401);
      }
    } catch (PDOException $exception) {
      throw new ExcepcionApi(2, "Error en autenticación: " . $exception->getMessage(), 400);
    }

  }

  /**
   * Valida una clave API y devuelve el ID del usuario 
   *
   * @param string $clave  Clave API enviada en encabezado Authorization
   *
   * @return int|null  ID del usuario si es válida, null si no existe
   */
  public static function validarClaveApi($clave)
  {
    $query = "SELECT idUsuario FROM " . self::NOMBRE_TABLA . " WHERE claveApi = ?";

    $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
    $query = $_conexion->prepare($query);
    $query->bindParam(1, $clave);
    $query->execute();

    $fila = $query->fetch();
    return $fila ? $fila['idUsuario'] : null;
  }

  /**
   * Genera clave API 
   *
   * @return string  Clave API 
   */
  private static function generarClaveApi()
  {
    return bin2hex(random_bytes(20));
  }
  //Metodo get para regresar un usuario
  public static function obtenerUsuario($idUsuario)
    {
        $conexion = ConexionBD::obtenerInstancia()->obtenerBD();
        $sql = "SELECT idUsuario, nombre, correo FROM usuarios WHERE idUsuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
