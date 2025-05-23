<?php
require_once __DIR__ . '/../datos/ConexionBD.php';

class DetalleCompra 
{
    const TABLA_DETALLE_COMPRA = "detalle_compra";
    const TABLA_COMPRA = "compras";
    const TABLA_PRODUCTOS = "productos";
    const TABLA_USUARIOS = "usuarios";

    /**
     * Crea un nuevo detalle de compra
     *
     * @param int   $idCompra   ID de la compra
     * @param array $datos      Datos JSON: idProducto, cantidad, precioUnitario
     *
     * @return array
     * @throws ExcepcionApi
     */

    public static function obtenerTodos($idUsuario) {
        try {
            $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
            $sql = "SELECT u.nombre AS nombreUsuario, 
            c.idCompra, 
            c.fecha, 
            p.nombre AS producto, 
            dc.cantidad, 
            dc.precioUnitario, 
            dc.subtotal
            FROM ". self::TABLA_USUARIOS ." u 
            JOIN ". self::TABLA_COMPRA. " c ON u.idUsuario = c.idUsuario
            JOIN ". self::TABLA_DETALLE_COMPRA. " dc ON c.idCompra = dc.idCompra
            JOIN ".  self::TABLA_PRODUCTOS. " p ON dc.idProducto = p.idProducto
            WHERE u.idUsuario = ?
            ORDER BY c.fecha DESC";
            $sentencia = $_conexion->prepare($sql);
            $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);
            $sentencia->execute();
            $detalles = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return [
                "estado" => 1,
                "mensaje" => "Detalles de compra obtenidos correctamente",
                "datos" => $detalles
            ];


        } catch (PDOException $e) {
            throw new ExcepcionApi(7, "Error al obtener la informacion de las compras". $e->getMessage(), 500);
        }
    }

    public static function obtenerPorId($idUsuario, $idCompra){
        try {
            $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
            $sql = "SELECT u.nombre AS nombreUsuario, 
            c.idCompra, 
            c.fecha, 
            p.nombre AS producto, 
            dc.cantidad, 
            dc.precioUnitario, 
            dc.subtotal
            FROM ". self::TABLA_USUARIOS ." u 
            JOIN ". self::TABLA_COMPRA. " c ON u.idUsuario = c.idUsuario
            JOIN ". self::TABLA_DETALLE_COMPRA. " dc ON c.idCompra = dc.idCompra
            JOIN ".  self::TABLA_PRODUCTOS. " p ON dc.idProducto = p.idProducto
            WHERE u.idUsuario = ? AND c.idCompra = ?
            ORDER BY c.fecha DESC";
            $sentencia = $_conexion->prepare($sql);
            $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);
            $sentencia->bindParam(2, $idCompra, PDO::PARAM_INT);
            $sentencia->execute();
            $detalles = $sentencia->fetch(PDO::FETCH_ASSOC);

            return [
                "estado" => 1,
                "mensaje" => "Detalles de la compra obtenidos correctamente",
                "datos" => $detalles
            ];
        } catch (PDOException $e) {
            throw new ExcepcionApi(7, "Error al obtener la informacion de las compra". $e->getMessage(), 500);
        }
    }
}
?>