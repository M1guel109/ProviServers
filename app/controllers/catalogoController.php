<?php
// app/controllers/catalogoController.php

require_once __DIR__ . '/../models/Publicacion.php';
require_once __DIR__ . '/../models/categoria.php'; // si quieres filtros por categoría

/**
 * Muestra el catálogo público de servicios (publicaciones activas).
 */
function catalogo_index()
{
    // Capturar filtros básicos (opcional)
    $categoriaId = $_GET['categoria_id'] ?? null;
    $texto       = $_GET['q'] ?? null;

    $filtros = [];
    if (!empty($categoriaId)) {
        $filtros['categoria_id'] = (int)$categoriaId;
    }
    if (!empty($texto)) {
        $filtros['texto'] = trim($texto);
    }

    $publicacionModel = new Publicacion();
    $publicaciones    = $publicacionModel->listarPublicasActivas($filtros);

    // Categorías para filtros (opcional)
    $categoriaModel = new Categoria();
    $categorias     = $categoriaModel->mostrar();

    // Incluimos la vista del catálogo
    require BASE_PATH . '/app/views/clientes/catalogo_publico.php';
}

/**
 * Muestra el detalle de una publicación en concreto.
 */
function catalogo_detalle()
{
    $id = $_GET['id'] ?? null;

    if (empty($id)) {
        // Podrías redirigir al catálogo con un mensaje de error
        header('Location: ' . BASE_URL . '/servicios');
        exit();
    }

    $publicacionModel = new Publicacion();
    $detalle = $publicacionModel->obtenerDetallePublicoPorId((int)$id);

    if (!$detalle) {
        // Si no existe o no está activa, redirigimos al catálogo
        header('Location: ' . BASE_URL . '/servicios');
        exit();
    }

    require BASE_PATH . '/app/views/clientes/detalle_publicacion.php';
}
