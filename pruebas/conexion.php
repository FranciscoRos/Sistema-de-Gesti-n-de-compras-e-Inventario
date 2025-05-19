<?php
require_once '../datos/ConexionBD.php';

try {
    $bd = ConexionBD::obtenerInstancia()->obtenerBD();
    echo "Conexión exitosa";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
