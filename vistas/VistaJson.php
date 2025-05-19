<?php
require_once 'VistaApi.php';

class VistaJson extends VistaApi
{
    /**
     * Imprime un arreglo como JSON con el código HTTP correspondiente
     *
     * @param mixed $cuerpo Array de respuesta
     */
    public function imprimir($cuerpo)
    {
        if ($this->estado) {
            http_response_code($this->estado);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cuerpo, JSON_PRETTY_PRINT);
        exit;
    }
}
