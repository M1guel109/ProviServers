<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/perfil.php';
require_once __DIR__ . '/../../config/database.php';

function mostrarPerfilAdmin($id)
{
    try {
        $db = new Conexion();
        $conexion = $db->getConexion();

        $sql = "SELECT
                    u.id,
                    u.email AS correo,
                    u.rol,
                    a.nombres,
                    a.apellidos,
                    a.telefono,
                    a.ubicacion AS direccion,
                    a.foto
                FROM usuarios u
                INNER JOIN admins a ON u.id = a.usuario_id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: [
            'nombres'   => 'Admin',
            'apellidos' => '',
            'telefono'  => '',
            'correo'    => '',
            'direccion' => '',
            'rol'       => 'admin',
            'foto'      => 'default_user.png',
        ];
    } catch (PDOException $e) {
        error_log("Error en mostrarPerfilAdmin: " . $e->getMessage());
        return [
            'nombres'   => 'Admin',
            'apellidos' => '',
            'telefono'  => '',
            'correo'    => '',
            'direccion' => '',
            'rol'       => 'admin',
            'foto'      => 'default_user.png',
        ];
    }
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

function cambiarContrasenaUsuario(string $redirectPath)
{
    if (session_status() == PHP_SESSION_NONE) session_start();
    $id_usuario = (int)($_SESSION['user']['id'] ?? 0);

    if ($id_usuario <= 0) {
        mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión.', BASE_URL . '/login');
        exit();
    }

    $clave_actual    = $_POST['clave_actual'] ?? '';
    $clave_nueva     = $_POST['clave_nueva'] ?? '';
    $clave_confirmar = $_POST['clave_confirmar'] ?? '';

    if (empty($clave_actual) || empty($clave_nueva) || empty($clave_confirmar)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos.', BASE_URL . $redirectPath);
        exit();
    }

    if ($clave_nueva !== $clave_confirmar) {
        mostrarSweetAlert('error', 'Error', 'Las nuevas contraseñas no coinciden.', BASE_URL . $redirectPath);
        exit();
    }

    $objPerfil = new Perfil();
    $resultado = $objPerfil->actualizarClave($id_usuario, $clave_actual, $clave_nueva);

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Éxito', 'Tu contraseña ha sido actualizada correctamente.', BASE_URL . $redirectPath);
    } else {
        mostrarSweetAlert('error', 'Error', (string)$resultado, BASE_URL . $redirectPath);
    }
    exit();
}


function mostrarPerfilCliente($id)
{
    try {
        $db = new Conexion();
        $conexion = $db->getConexion();

        $sql = "SELECT 
                    u.id,
                    u.email AS correo,
                    u.rol,
                    c.nombres,
                    c.apellidos,
                    c.telefono,
                    c.ubicacion AS direccion,
                    c.foto
                FROM usuarios u
                INNER JOIN clientes c ON u.id = c.usuario_id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: [
            'nombres'   => 'Cliente',
            'apellidos' => '',
            'telefono'  => '',
            'correo'    => '',
            'direccion' => '',
            'rol'       => 'cliente',
            'foto'      => 'default_user.png',
        ];
    } catch (PDOException $e) {
        error_log("Error en mostrarPerfilCliente: " . $e->getMessage());
        return [
            'nombres'   => 'Cliente',
            'apellidos' => '',
            'telefono'  => '',
            'correo'    => '',
            'direccion' => '',
            'rol'       => 'cliente',
            'foto'      => 'default_user.png',
        ];
    }
}
