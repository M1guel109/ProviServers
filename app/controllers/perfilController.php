<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/perfil.php';

function mostrarPerfilAdmin($id)
{

    $objPerfil = new Perfil();
    $usuario = $objPerfil->mostrarPerfilAdmin($id);

    return $usuario;
}

