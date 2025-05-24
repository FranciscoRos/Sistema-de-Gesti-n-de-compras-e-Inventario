<?php
require_once __DIR__ . '/../datos/ConexionBD.php';
require_once __DIR__ . '/../librerias/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    $userInfo = self::correoUsuario($idUsuario);
    $emailUser = $userInfo['datos']['correo'];
    $userName = $userInfo['datos']['nombreUsuario'];
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

      $idProducto = $_conexion->lastInsertId();

      // Enviar correo de aviso
      try {
        $mail = new PHPMailer(true);
        // Configuración básica
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'falsofrancisco804@gmail.com';
        $mail->Password   = 'uplsgkhtgboubegh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('falsofrancisco804@gmail.com', 'Administrador');
        $mail->addAddress($emailUser, $userName); // Cambia por el destinatario real
        $mail->isHTML(true);
        $mail->Subject = '¡Se ha creado un nuevo producto!';
        $mail->Body    = 
          '<b>Hola '. htmlspecialchars($userName) . '</b><br>'.
          '<b>Usted ha creado un nuevo producto con las siguientes caracteristicas:</b><br>' .
          'Nombre: ' . htmlspecialchars($datos['nombre']) . '<br>' .
          'Precio de compra: ' . htmlspecialchars($datos['precioCompra']) . '<br>' .
          'Precio de venta: ' . htmlspecialchars($datos['precioVenta']) . '<br>' .
          'Stock: ' . htmlspecialchars($datos['stock']) . '<br>' .
          'ID: ' . $idProducto. '<br>'.
          'Gracias por confiar en nosotros. Tenga un buen dia';
        $mail->AltBody = 'Usted ha creado un nuevo producto con las siguientes caracteristicas: ' .
          'Nombre: ' . $datos['nombre'] . ', ' .
          'Precio de compra: ' . $datos['precioCompra'] . ', ' .
          'Precio de venta: ' . $datos['precioVenta'] . ', ' .
          'Stock: ' . $datos['stock'] . ', ' .
          'ID: ' . $idProducto;
        $mail->send();
      } catch (Exception $e) {
        // Manejo de errores al enviar el correo
        throw new ExcepcionApi(8, "Error al enviar el correo: " . $mail->ErrorInfo, 500);
      } 

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
      $query->bindParam(2, $idProducto, PDO::PARAM_INT);
      $query->execute();
      $productoInfo = $query->fetch(PDO::FETCH_ASSOC);
      
      if (!$productoInfo) {
        throw new ExcepcionApi(5, "El producto no existe o no pertenece al usuario", 404);
      }

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
        $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

        $sql = "UPDATE productos SET nombre = ?, precioCompra = ?, precioVenta = ?, stock = ?
                WHERE idProducto = ? AND idUsuario = ?";

        $sentencia = $_conexion->prepare($sql);
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
        $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

        $sql = "DELETE FROM productos WHERE idProducto = ? AND idUsuario = ?";
        $sentencia = $_conexion->prepare($sql);
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

public static function filtrarBajoStock ($cant) {
  try {
    $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();

    $sql = "SELECT idProducto, idUsuario, nombre, precioCompra, precioVenta, stock FROM productos WHERE stock <= ?";
    $sentencia = $_conexion->prepare($sql);
    $sentencia->bindParam(1, $cant, PDO::PARAM_INT);
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
//Filtrado de producto por id del provedor(función es devolver productos).
public static function filtrarPorProveedor($idProveedor)
{
    try {
        $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
        $sql = "SELECT idProducto, idUsuario, nombre, precioCompra, precioVenta, stock 
                FROM productos 
                WHERE idProveedor = ?";
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
        throw new ExcepcionApi(7, "Rango inválido: " . $e->getMessage(), 500);
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
?>
