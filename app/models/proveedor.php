<?php
require_once __DIR__ . '/../../config/database.php';

class Servicio
{
    private $conexion;

    public function __construct()
    {
        $db = new  Conexion();
        $this->conexion = $db->getConexion();
    }

    public function registrar($data){
        
    }
}