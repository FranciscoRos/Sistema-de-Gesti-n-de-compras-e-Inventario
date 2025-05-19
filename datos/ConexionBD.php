<?php

class ConexionBD
{
    private static $instancia = null;
    private $pdo;

    private function __construct()
    {
        try {
            $host = "localhost";
            $bd = "sistema_compras";
            $usuario = "root";
            $contrasena = ""; 
            $puerto = "3306"; 

            $this->pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8", $usuario, $contrasena);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error en la conexión: " . $e->getMessage());
        }
    }

    public static function obtenerInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function obtenerBD()
    {
        return $this->pdo;
    }
}
