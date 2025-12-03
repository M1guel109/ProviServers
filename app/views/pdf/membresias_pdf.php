<?php
// Función de ayuda necesaria para la vista de la tabla de membresías
// Se incluye aquí para que la vista del PDF sea lo más auto-contenida posible.
if (!function_exists('toYesNo')) {
    function toYesNo($value) {
        return ($value == 1) ? 'Sí' : 'No';
    }
}

// NOTA IMPORTANTE: Esta vista espera que la variable $membresias (o $datos,
// según el controlador de tu aplicación) esté definida y contenga el array
// de datos de las membresías. Usaremos $membresias para este ejemplo.
// Si tu controlador usa $datos, cambia $membresias por $datos en el <tbody>.
$datos_reporte = isset($membresias) ? $membresias : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Membresías - Proviservers</title>
    <style>
        /* CRÍTICO PARA DOMPDF: Estilos CSS Inclusos */
        body {
            font-family: "Poppins", sans-serif;
            margin: 40px; 
            padding: 0;
        }

        /* Colores y Tipografía */
        .header-title {
            color: #0066ff; /* Color de título solicitado */
            text-align: center;
            font-size: 24px;
            margin-top: 20px;
        }
        .description-paragraph {
            color: #000; /* Color de párrafo solicitado */
            margin-bottom: 30px;
            line-height: 1.5;
            font-size: 12px;
        }

        /* Logo Centrado */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }

        /* Estilo de Tabla */
        .table-reporte {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 9px; /* Tamaño de fuente reducido para acomodar más columnas */
        }
        .table-reporte th, .table-reporte td {
            border: 1px solid #ddd;
            padding: 6px 8px; /* Padding ajustado */
            text-align: left;
        }
        .table-reporte th {
            background-color: #f2f2f2;
            color: #333;
            text-transform: uppercase;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            line-height: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #eee;
        }

        /* Clases de estado para mejor visualización */
        .estado-activo { color: green; font-weight: bold; }
        .estado-inactivo { color: red; }

    </style>
</head>
<body>

    <!-- CABECERA: LOGO y TÍTULO -->
    <div class="logo-container">
        <!-- NOTA CRÍTICA: La ruta DEBE SER ABSOLUTA en el servidor para dompdf. -->
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers">
    </div>

    <h1 class="header-title">Reporte Detallado de Planes de Membresía</h1>

    <!-- PÁRRAFO DE DESCRIPCIÓN -->
    <p class="description-paragraph">
        Este documento presenta un listado completo de todos los planes de membresía configurados en la plataforma Proviservers. 
        Incluye detalles como el tipo de plan, costo, duración, estado actual y las características clave que ofrece cada membresía.
    </p>

    <!-- TABLA DE MEMBRESÍAS -->
    <table class="table-reporte">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Costo</th>
                <th>Duración (Días)</th>
                <th>Estado</th>
                <th>Destacado</th>
                <th>Máx. Servicios</th>
                <th>Estadísticas Pro</th>
                <th>Videos</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($datos_reporte)) : ?>

                <?php foreach ($datos_reporte as $membresia) : ?>
                    <tr>
                        <td><?= htmlspecialchars($membresia['tipo'] ?? '') ?></td>
                        <td><?= htmlspecialchars($membresia['descripcion'] ?? '') ?></td>
                        <td><?= htmlspecialchars($membresia['costo'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($membresia['duracion_dias'] ?? 'N/A') ?></td>
                        <td class="<?= ($membresia['estado'] ?? '') === 'Activo' ? 'estado-activo' : 'estado-inactivo' ?>">
                            <?= htmlspecialchars($membresia['estado'] ?? 'N/A') ?>
                        </td>
                        <td><?= toYesNo($membresia['es_destacado'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($membresia['max_servicios_activos'] ?? 'Ilimitado') ?></td>
                        <td><?= toYesNo($membresia['acceso_estadisticas_pro'] ?? 0) ?></td>
                        <td><?= toYesNo($membresia['permite_videos'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center;">
                        <h4 style="color: #0066ff; padding: 10px;">No hay planes de membresía registrados para generar el reporte.</h4>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        &copy; Proviservers - <?= date('Y') ?> - Reporte de Membresías
    </div>

</body>
</html>