<?php
require_once __DIR__ . '/../datos/ConexionBD.php';

class Reporte {
    const TABLA_PROVEEDORES = "proveedores";
    const TABLA_PRODCUTOS = "productos";
    const TABLA_USUARIOS = "usuarios";

    public static function getAllSuppliers () {
        try {
        $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
        $query = "SELECT idProveedor, idUsuario, nombre, contacto, telefono FROM ". self::TABLA_PROVEEDORES ;
        $query = $_conexion->prepare($query);
        $query->execute();
        $proveedores = $query->fetchAll(PDO::FETCH_ASSOC);

        return [
            "estado" => 1,
            "mensaje" => "Proveedores obtenidos exitosamente",
            "datos" => $proveedores

        ];
        } catch (PDOException $e) {
            throw new ExcepcionApi(7, "Error al consultar los proveedores: " . $e->getMessage(), 500);
        }
    }

    public static function getAllProducts() {
        try {
        $_conexion = ConexionBD::obtenerInstancia()->obtenerBD();
        $query = "SELECT idProducto, idUsuario, nombre, precioCompra, precioVenta, stock FROM ". self::TABLA_PRODCUTOS ;
        $query = $_conexion->prepare($query);
        $query->execute();
        $productos = $query->fetchAll(PDO::FETCH_ASSOC);

        return [
            "estado" => 1,
            "mensaje" => "Productos obtenidos exitosamente",
            "datos" => $productos

        ];
        } catch (PDOException $e) {
            throw new ExcepcionApi(7, "Error al consultar los productos: " . $e->getMessage(), 500);
        }
    }
    

}
?>