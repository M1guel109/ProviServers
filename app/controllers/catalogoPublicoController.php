<?php
// app/controllers/catalogoPublicoController.php

require_once __DIR__ . '/../models/Publicacion.php';
require_once __DIR__ . '/../models/categoria.php';

// Filtros desde la URL
$busqueda    = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoriaId = isset($_GET['categoria']) && $_GET['categoria'] !== ''
    ? (int) $_GET['categoria']
    : null;

// Modelo de publicaciones
$publicacionModel = new Publicacion();
$publicaciones = $publicacionModel->listarPublicasActivas(
    $busqueda !== '' ? $busqueda : null,
    $categoriaId
);

// Modelo de categorías (para los filtros de arriba)
$categoriaModel = new Categoria();
$categorias = $categoriaModel->mostrar();

// Cargamos la vista
require BASE_PATH . '/app/views/dashboard/cliente/explorarServicios.php';
