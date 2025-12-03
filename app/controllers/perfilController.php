<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/perfil.php';
require_once __DIR__ . '/../../config/database.php';

function mostrarPerfilAdmin($id)
{

    $objPerfil = new Perfil();
    $usuario = $objPerfil->mostrarPerfilAdmin($id);

    return $usuario;
}

function mostrarPerfilProveedor($id)
{
    try {
        $db = new Conexion();                // Usa tu clase de conexión
        $conexion = $db->getConexion();

        $sql = "SELECT 
                    u.id,
                    u.email,
                    u.rol,
                    p.nombres,
                    p.apellidos,
                    p.telefono,
                    p.ubicacion,
                    p.foto
                FROM usuarios u
                INNER JOIN proveedores p ON u.id = p.usuario_id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: [
            'nombres' => 'Proveedor',
            'rol'     => 'proveedor',
            'foto'    => 'default_user.png',
        ];
    } catch (PDOException $e) {
        error_log("Error en mostrarPerfilProveedor: " . $e->getMessage());
        return [
            'nombres' => 'Proveedor',
            'rol'     => 'proveedor',
            'foto'    => 'default_user.png',
        ];
    }
}

function cambiarContrasenaUsuario()
{
    // 1. Verificar sesión
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $id_usuario = $_SESSION['user']['id'];

    // 2. Capturar datos
    $clave_actual = $_POST['clave_actual'] ?? '';
    $clave_nueva = $_POST['clave_nueva'] ?? '';
    $clave_confirmar = $_POST['clave_confirmar'] ?? '';

    // 3. Validaciones básicas
    if (empty($clave_actual) || empty($clave_nueva) || empty($clave_confirmar)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos.', BASE_URL . '/admin/perfil');
        exit();
    }

    if ($clave_nueva !== $clave_confirmar) {
        mostrarSweetAlert('error', 'Error', 'Las nuevas contraseñas no coinciden.', BASE_URL . '/admin/perfil');
        exit();
    }

    // 4. Llamar al Modelo
    $objPerfil = new Perfil();

    // Esta función del modelo debe devolver: true (éxito) o un mensaje de error (string)
    $resultado = $objPerfil->actualizarClave($id_usuario, $clave_actual, $clave_nueva);

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Éxito', 'Tu contraseña ha sido actualizada correctamente.', BASE_URL . '/admin/perfil');
    } else {
        // Si devuelve un string, es el mensaje de error (ej: "La clave actual es incorrecta")
        mostrarSweetAlert('error', 'Error', $resultado, BASE_URL . '/admin/perfil');
    }
    exit();
}
