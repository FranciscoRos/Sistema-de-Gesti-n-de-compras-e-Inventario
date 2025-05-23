<?php
require_once __DIR__.'/../modelos/DetalleCompra.php';
require_once __DIR__.'/../controladores/usuarios.php';

class detalleCompras {
    /**
     * Maneja GET /detalleCompra y /detalleCompra/id
     * Devuelve todas las compras o una específica del usuario autenticado
     *
     * @param array $peticion
     * @return array
     */
    public static function get($peticion) {
        $idUsuario = usuarios::autorizar();

        if (!isset($peticion[0]) || trim($peticion[0]) === "") {
            return DetalleCompra::obtenerTodos($idUsuario);

        } else {
            $idCompra = $peticion[0];
            return DetalleCompra::obtenerPorId($idUsuario, $idCompra);
        }
    }
}
?>