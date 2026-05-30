<?php

require_once __DIR__ . '/../../vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generarPDF($html, $filename = "documento.pdf", $download = false)
{
    // Limpiar cualquier output previo (notices, warnings de DomPDF en PHP 8.x)
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $prev = error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);

    // Cargar el HTML recibido
    $dompdf->loadHtml($html);

    // Opcional: tamaño y orientación
    $dompdf->setPaper('A4', 'portrait');

    // Renderizar
    $dompdf->render();

    // Descargar o mostrar
    $dompdf->stream($filename, [
        "Attachment" => $download ? 1 : 0
    ]);

    error_reporting($prev);
}