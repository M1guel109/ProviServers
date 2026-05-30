<?php

/**
 * Función para imprimir SweetAlert dinámico con los colores personalizados del proyecto.
 * Paleta: Principal (#0066ff), Secundario (#0e1116), Fondo (#fff).
 */
function mostrarSweetAlert($tipo, $titulo, $mensaje, $redirect = null)
{
    // Colores del proyecto:
    $COLOR_PRINCIPAL = '#0066ff'; // Azul brillante (Botón Aceptar)
    $COLOR_SECUNDARIO = '#0e1116'; // Casi Negro (Botón Cancelar / Título)
    $COLOR_FONDO = '#fff'; // Blanco (Fondo del SweetAlert)

    // json_encode escapa caracteres especiales para contexto JS (previene XSS y open redirect)
    $jsTipo     = json_encode((string) $tipo);
    $jsTitulo   = json_encode((string) $titulo);
    $jsMensaje  = json_encode((string) $mensaje);
    $jsRedirect = $redirect
        ? 'window.location.href = ' . json_encode((string) $redirect) . ';'
        : 'window.history.back();';

    echo "
    <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');

                body {
                    margin: 0;
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, $COLOR_SECUNDARIO, $COLOR_PRINCIPAL);
                    font-family: 'Montserrat', sans-serif;
                    color: $COLOR_FONDO;
                }

                .swal2-popup {
                    font-family: 'Montserrat', sans-serif !important;
                    background-color: $COLOR_FONDO !important;
                    color: $COLOR_SECUNDARIO !important;
                }

                .swal2-title {
                    color: $COLOR_SECUNDARIO !important;
                    font-weight: 600 !important;
                }

                .swal2-styled.swal2-confirm {
                    background-color: $COLOR_PRINCIPAL !important;
                    border: none !important;
                }

                .swal2-styled.swal2-confirm:hover {
                    background-color: #004cbf !important;
                }

                .swal2-styled.swal2-cancel {
                    background-color: $COLOR_SECUNDARIO !important;
                    color: $COLOR_FONDO !important;
                }
            </style>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: $jsTipo,
                    title: $jsTitulo,
                    text: $jsMensaje,
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '$COLOR_PRINCIPAL',
                    background: '$COLOR_FONDO',
                    color: '$COLOR_SECUNDARIO'
                }).then((result) => {
                    $jsRedirect
                });
            </script>
        </body>
    </html>";
}
