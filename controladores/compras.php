<?php
require_once __DIR__ . '../modelos/Compra.php';
require_once __DIR__ . '../controladores/usuarios.php';

class compras {
      /**
     * Maneja GET /compras y /compras/id
     * Devuelve todas las compras o una específica del usuario autenticado
     *
     * @param array $peticion
     * @return array
     */
    public static function get($peticion) {
        $idUsuario = usuarios::autorizar();

        if (!isset($peticion[0]) || trim($peticion[0]) === "") {
            return Compra::obtenerTodos($idUsuario);

        } else {
            $idCompra = $peticion[0];
            return Compra::obtenerPorId($idUsuario, $idCompra);
        }
    }
    /**
     * Maneja POST /Compras
     * Crea un nueva compra para el usuario autenticado
     *
     * @param array $peticion
     * @return array
     */
    public static function post($peticionNOUSADO_ANY) {
        $idUsuario = usuarios::autorizar();

          $body = json_decode(file_get_contents('php://input'), true);
        return Compra::crear($idUsuario, $body);
    }

// ????

// //Consulta para todas las compras para usuario autorizado
//     public static function get($peticion)
//     {
//         Auth::verificarAutenticacion();
//         $idUsuario = Auth::obtenerIdUsuario();

//         $compras = Compra::obtenerComprasPorUsuario($idUsuario);

//         return [
//             "estado" => "ok",
//             "compras" => $compras
//         ];
//     }
}


