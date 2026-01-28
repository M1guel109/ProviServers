<?php
require_once __DIR__ . '/../helpers/session_proveedor.php';
require_once __DIR__ . '/../models/Valoracion.php';

function mostrarResenasProveedor()
{
    // 1. Obtener ID del usuario logueado
    $usuarioId = $_SESSION['user']['id'];
    
    // 2. Pedir las reseñas a la Base de Datos (Modelo)
    $modelo = new Valoracion();
    $resenas = $modelo->obtenerResenasPorProveedor($usuarioId);

    // 3. --- ZONA MATEMÁTICA (Aquí arreglamos el error de variables vacías) ---
    $totalResenas = count($resenas);
    $promedio = 0;
    
    // Preparamos el array de conteo para evitar errores "undefined"
    // Clave = Estrellas (5,4,3...), Valor = Cantidad de votos
    $conteoEstrellas = [
        5 => 0, 
        4 => 0, 
        3 => 0, 
        2 => 0, 
        1 => 0
    ];

    if ($totalResenas > 0) {
        $sumaEstrellas = 0;
        
        foreach ($resenas as $r) {
            // Aseguramos que sea un número entero
            $calif = (int)$r['calificacion'];
            $sumaEstrellas += $calif;
            
            // Contamos: "¡Uno más votó 5 estrellas!"
            if (isset($conteoEstrellas[$calif])) {
                $conteoEstrellas[$calif]++;
            }
        }

        // Calculamos el promedio (ej: 4.8)
        $promedio = round($sumaEstrellas / $totalResenas, 1);
    }

    // 4. Calcular Porcentajes para las Barras (Regla de 3)
    $porcentajes = [];
    foreach ($conteoEstrellas as $estrella => $cantidad) {
        // Si hay reseñas, calculamos %; si no, es 0%
        $porcentajes[$estrella] = ($totalResenas > 0) 
            ? round(($cantidad / $totalResenas) * 100) 
            : 0;
    }

    // 5. CARGAR LA VISTA
    // Al hacer require aquí, las variables $resenas, $promedio, etc.
    // pasan automáticamente a la vista.
    require BASE_PATH . '/app/views/dashboard/proveedor/resenas.php';
}



function guardarRespuestaProveedor()
{
    // Verificar que sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/proveedor/resenas');
        exit;
    }

    $idResena = $_POST['id_valoracion'] ?? null;
    $textoRespuesta = $_POST['texto_respuesta'] ?? '';
    $usuarioId = $_SESSION['user']['id'];

    if ($idResena && !empty($textoRespuesta)) {
        $modelo = new Valoracion();
        $exito = $modelo->responderResena($idResena, $usuarioId, $textoRespuesta);

        if ($exito) {
            // Redirigir con éxito (puedes usar una variable de sesión para mostrar alerta luego)
            header('Location: ' . BASE_URL . '/proveedor/resenas?status=success');
        } else {
            header('Location: ' . BASE_URL . '/proveedor/resenas?status=error');
        }
    } else {
        header('Location: ' . BASE_URL . '/proveedor/resenas?status=empty');
    }
}
?>