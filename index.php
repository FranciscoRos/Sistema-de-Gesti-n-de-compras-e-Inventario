<?php

// Cargar controladores (agrega más según avances)
require_once __DIR__ .'/controladores/usuarios.php';
require_once __DIR__ .'/controladores/productos.php';
require_once __DIR__ . '/controladores/proveedores.php';

// Cargar vistas
require_once __DIR__ .'/vistas/VistaJson.php';
require_once __DIR__ .'/utilidades/ExcepcionApi.php';

// Encabezados CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Authorization, Content-Type");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    exit(0);
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Definir códigos internos
// Códigos internos de error según convención oficial
const ESTADO_ERROR_LOGICO = 2;
const ESTADO_AUTENTICACION = 3;
const ESTADO_ERROR_PARAMETROS = 4;
const ESTADO_NO_ENCONTRADO = 5;
const ESTADO_BLOQUEADO = 6;
const ESTADO_ERROR_BD = 7;
const ESTADO_ERROR_DESCONOCIDO = 99;

// Inicializar vista JSON (formato por defecto)
$vista = new VistaJson();

// Manejador global de excepciones
set_exception_handler(function ($exception) use ($vista) {
    $respuesta = [
        "estado" => $exception->estado,
        "mensaje" => $exception->getMessage()
    ];

    $vista->estado = $exception->getCode() ?: 500;
    $vista->imprimir($respuesta);
});

// Obtener recurso de la URL
if (!isset($_GET['PATH_INFO'])) {
    throw new ExcepcionApi(ESTADO_ERROR_PARAMETROS, "No se reconoció la petición");
}

$peticion = explode('/', $_GET['PATH_INFO']);
$recurso = array_shift($peticion);

// Lista de recursos disponibles
$recursosPermitidos = ['usuarios', 'productos', 'proveedores', 'compras'];

// Validar recurso
if (!in_array($recurso, $recursosPermitidos)) {
    throw new ExcepcionApi(ESTADO_NO_ENCONTRADO, "Recurso no válido");
}

// Detectar método HTTP
$metodo = strtolower($_SERVER['REQUEST_METHOD']);

// Ejecutar controlador
if (method_exists($recurso, $metodo)) {
    $respuesta = call_user_func([$recurso, $metodo], $peticion);
    $vista->imprimir($respuesta);
} else {
    throw new ExcepcionApi(ESTADO_BLOQUEADO, "Método HTTP no permitido", 405);
}
