<?php
// app/controllers/clientePublicacionesController.php

require_once __DIR__ . '/../models/Publicacion.php';

function mostrarCatalogoPublico()
{
    // Filtros desde la URL (GET)
    $busqueda    = $_GET['q']   ?? null;
    $categoriaId = isset($_GET['cat']) && $_GET['cat'] !== ''
        ? (int) $_GET['cat']
        : null;

    $publicacionModel = new Publicacion();
    $publicaciones    = $publicacionModel->listarPublicasActivas($busqueda, $categoriaId);

    // Cargamos la vista del dashboard cliente
    require BASE_PATH . '/app/views/dashboard/cliente/explorarServicios.php';
}
