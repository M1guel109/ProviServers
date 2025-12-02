<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios - Proviservers</title>
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
        }
        .table-reporte th {
            background-color: #f2f2f2;
            color: #333;
            text-transform: uppercase;
        }
        
        /* Estilos de Imagen en la tabla */
        .user-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
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
        <!-- NOTA CRÍTICA: En dompdf, la ruta DEBE SER ABSOLUTA en el servidor, 
             o debes configurar la opción 'chroot' y 'isRemoteEnabled' para cargarla. 
             Asegúrate de que esta ruta sea accesible por PHP. -->
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers">
    </div>

    <h1 class="header-title">Reporte Detallado de Usuarios del Sistema</h1>

    <!-- PÁRRAFO DE DESCRIPCIÓN -->
    <p class="description-paragraph">
        Este documento presenta un listado completo de todos los usuarios registrados en la plataforma Proviservers al momento de la generación del reporte. 
        Se incluye información de contacto, rol asignado y ubicación, siendo una herramienta vital para la gestión administrativa y el control de accesos.
    </p>

    <!-- TABLA DE USUARIOS -->
    <table class="table-reporte">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nombre Completo</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Ubicación</th>
                <th>Rol</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)) : ?>

                <?php foreach ($usuarios as $usuario) : ?>
                    <tr>
                        <td class="center-cell">
                            <!-- De nuevo, esta ruta debe ser accesible para dompdf -->
                            <img class="user-photo" 
                                src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($usuario['foto']) ?>" 
                                alt="Foto"
                            >
                        </td>
                        <td><?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                        <td><?= htmlspecialchars($usuario['ubicacion']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($usuario['rol'])) ?></td>
                        <td><?= htmlspecialchars(ucfirst($usuario['estado'])) ?></td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">
                        <h4 style="color: #0066ff;">No hay usuarios registrados para generar el reporte.</h4>
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