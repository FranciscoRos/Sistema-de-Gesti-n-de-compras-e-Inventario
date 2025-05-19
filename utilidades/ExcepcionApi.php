<?php

class ExcepcionApi extends Exception
{
    public $estado;

    /**
     * 
     *
     * @param int    $estado    Código interno  ( 1: OK, 2: error lógico, 3: error de autenticación, 4: Error de parámetros, 5: recurso no encontrado, 6: bloqueado, 7: error de DB, 99: error no canalizado
     *   
     * @param string $mensaje   Mensaje para el cliente
     * @param int    $codigo    Código HTTP (200, 400, 401, etc.)
     */
    public function __construct($estado, $mensaje, $codigo = 400)
    {
        $this->estado = $estado;
        parent::__construct($mensaje, $codigo);
    }
}
