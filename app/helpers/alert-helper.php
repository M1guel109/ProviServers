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
                    /* Fondo degradado usando los colores del proyecto */
                    background: linear-gradient(135deg, $COLOR_SECUNDARIO, $COLOR_PRINCIPAL); 
                    font-family: 'Montserrat', sans-serif;
                    color: $COLOR_FONDO;
                }

                .swal2-popup {
                    font-family: 'Montserrat', sans-serif !important;
                    background-color: $COLOR_FONDO !important; /* Fondo del cuadro de alerta */
                    color: $COLOR_SECUNDARIO !important; /* Texto general del mensaje */
                }

                .swal2-title {
                    color: $COLOR_SECUNDARIO !important; /* Color del Título */
                    font-weight: 600 !important;
                }

                .swal2-styled.swal2-confirm {
                    background-color: $COLOR_PRINCIPAL !important; /* Botón Aceptar: Principal */
                    border: none !important;
                }

                .swal2-styled.swal2-confirm:hover {
                    background-color: #004cbf !important; /* Un tono más oscuro de azul para el hover */
                }

                .swal2-styled.swal2-cancel {
                    background-color: $COLOR_SECUNDARIO !important; /* Botón Cancelar: Secundario */
                    color: $COLOR_FONDO !important;
                }
            </style>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: '$tipo',
                    title: '$titulo',
                    text: '$mensaje',
                    confirmButtonText: 'Aceptar',
                    // Sobreescribe el color de confirmación en la configuración de SweetAlert
                    confirmButtonColor: '$COLOR_PRINCIPAL', 
                    background: '$COLOR_FONDO',
                    color: '$COLOR_SECUNDARIO' // Color del texto del mensaje
                }).then((result) => {
                    " . ($redirect ? "window.location.href = '$redirect';" : "window.history.back();") . "
                });
            </script>
        </body>
    </html>";
}