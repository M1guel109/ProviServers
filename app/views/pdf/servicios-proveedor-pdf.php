<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Servicios - Proviservers</title>
    <style>
        /* CRÍTICO PARA DOMPDF: Estilos CSS Inclusos */
        body {
            font-family: "Poppins", sans-serif;
            /* Aplicar márgenes internos al contenido del body */
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
            font-size: 10px; /* Tamaño de fuente más pequeño para tablas */
        }
        .table-reporte th, .table-reporte td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .table-reporte th {
            background-color: #f2f2f2;
            color: #333;
            text-transform: uppercase;
        }
        
        /* Estilos de Imagen en la tabla */
        .service-photo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
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

        /* Clase para centrar el contenido de la celda de la foto */
        .center-cell {
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- CABECERA: LOGO y TÍTULO -->
    <div class="logo-container">
        <!-- Igual que en usuarios_pdf: ruta accesible para dompdf -->
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers">
    </div>

    <h1 class="header-title">Reporte Detallado de Servicios del Proveedor</h1>

    <!-- PÁRRAFO DE DESCRIPCIÓN -->
    <p class="description-paragraph">
        Este documento presenta un listado detallado de los servicios registrados por el proveedor en la plataforma Proviservers, 
        incluyendo información sobre categoría, descripción, disponibilidad y fecha de creación. 
        Este reporte facilita la gestión y seguimiento de la oferta de servicios publicada en el sistema.
    </p>

    <!-- TABLA DE SERVICIOS -->
    <table class="table-reporte">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre del servicio</th>
                <th>Categoría</th>
                <th>Descripción</th>
                <th>Disponibilidad</th>
                <th>Fecha de creación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($servicios)) : ?>
                <?php foreach ($servicios as $servicio) : ?>
                    <?php
                        $nombreCategoria = $mapCategorias[$servicio['id_categoria']] ?? 'Sin categoría';
                        $estado = $servicio['disponibilidad'] ? 'Disponible' : 'No disponible';
                    ?>
                    <tr>
                        <td class="center-cell">
                            <?php if (!empty($servicio['imagen'])): ?>
                                <img class="service-photo"
                                     src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($servicio['imagen']) ?>"
                                     alt="Imagen servicio">
                            <?php else: ?>
                                <span style="font-size: 9px; color: #777;">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                        <td><?= htmlspecialchars($nombreCategoria) ?></td>
                        <td><?= htmlspecialchars($servicio['descripcion'] ?? 'Sin descripción') ?></td>
                        <td><?= htmlspecialchars($estado) ?></td>
                        <td><?= htmlspecialchars($servicio['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">
                        <h4 style="color: #0066ff;">No hay servicios registrados para generar el reporte.</h4>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        &copy; Proviservers - <?= date('Y') ?>
    </div>

</body>
</html>
