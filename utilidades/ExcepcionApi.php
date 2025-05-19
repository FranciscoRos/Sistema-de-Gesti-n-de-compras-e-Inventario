<?php

class ExcepcionApi extends Exception
{
    public $estado;

    /**
     * 
     *
     * @param int    $estado    Código interno  ( 1: OK, 2: error lógico, 3: credenciales incorrectas)
     * @param string $mensaje   Mensaje para el cliente
     * @param int    $codigo    Código HTTP (200, 400, 401, etc.)
     */
    public function __construct($estado, $mensaje, $codigo = 400)
    {
        $this->estado = $estado;
        parent::__construct($mensaje, $codigo);
    }
}
