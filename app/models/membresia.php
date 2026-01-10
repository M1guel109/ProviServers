<?php
require_once __DIR__ . '/../../config/database.php';

class Membresia
{
    private $conexion;

    public function __construct()
    {
        $db = new  Conexion();
        $this->conexion = $db->getConexion();
    }

    public function registrar($data)
    {
        try {
            // Inicia una transacción para asegurar la atomicidad (aunque es una sola inserción, es buena práctica)
            $this->conexion->beginTransaction();

            // Consulta SQL para insertar los datos del plan en la tabla 'membresias'
            $insertar = "INSERT INTO membresias (tipo, 
                            costo, 
                            duracion_dias, 
                            descripcion, 
                            max_servicios_activos, 
                            orden_visual, 
                            acceso_estadisticas_pro, 
                            permite_videos, 
                            es_destacado, 
                            estado
                        ) VALUES (
                            :tipo, 
                            :costo, 
                            :duracion_dias, 
                            :descripcion, 
                            :max_servicios_activos, 
                            :orden_visual, 
                            :acceso_estadisticas_pro, 
                            :permite_videos, 
                            :es_destacado, 
                            :estado
                        )";

            $resultado = $this->conexion->prepare($insertar);

            // 1. Asignación de Parámetros
            $resultado->bindParam(':tipo', $data['tipo']);
            $resultado->bindParam(':costo', $data['costo']);
            $resultado->bindParam(':duracion_dias', $data['duracion_dias']);
            $resultado->bindParam(':descripcion', $data['descripcion']);
            $resultado->bindParam(':max_servicios_activos', $data['max_servicios_activos']);

            // Si orden_visual es null, se debe enviar PDO::PARAM_NULL si el campo en DB lo permite
            $ordenVisual = $data['orden_visual'];
            $resultado->bindParam(':orden_visual', $ordenVisual, is_null($ordenVisual) ? PDO::PARAM_NULL : PDO::PARAM_INT);

            $resultado->bindParam(':acceso_estadisticas_pro', $data['acceso_estadisticas_pro']);
            $resultado->bindParam(':permite_videos', $data['permite_videos']);
            $resultado->bindParam(':es_destacado', $data['es_destacado']);
            $resultado->bindParam(':estado', $data['estado']);

            // 2. Ejecución de la Consulta
            $resultado->execute();

            // 3. Confirmar la transacción
            $this->conexion->commit();

            return true;
        } catch (PDOException $e) {
            // Si algo falla, se revierte la transacción
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            // Registrar el error para fines de depuración
            error_log("Error en Membresia::registrar -> " . $e->getMessage());

            // Devolver false para indicar que el registro falló
            return false;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error general en Membresia::registrar -> " . $e->getMessage());
            return false;
        }
    }

    public function mostrar()
    {
        try {
            // VARIABLE QUE ALMACENA LA SENTENCIA DE SQL A EJECUTAR
            $consultar = "SELECT * FROM membresias";

            // PREPARAR LO NECESARIO PARA EJECUTAR LA CONSULTA 
            $resultado = $this->conexion->prepare($consultar);
            // EJECUTAR LA CONSULTA
            $resultado->execute();

            return $resultado->fetchAll(); // Usamos FETCH_ASSOC para devolver un array asociativo
        } catch (PDOException $e) {
            // Registrar el error en el log del sistema
            error_log("Error en MembresiaModel::mostrarMembresias -> " . $e->getMessage());
            // Devolver un array vacío para manejo seguro en la aplicación
            return [];
        }
    }

    public function mostrarId($id)
    {
        try {
            $consultar = "SELECT * FROM membresias WHERE id = :id";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            return $resultado->fetch();
        } catch (PDOException $e) {
            error_log("Error en Membresia::mostrar->" . $e->getMessage());
            return [];
        }
    }

    public function actualizar($data)
    {
        // Iniciamos la transacción para asegurar la integridad de la operación (aunque es una sola consulta)
        $this->conexion->beginTransaction();

        try {
            // Consulta SQL para actualizar todos los campos de la tabla 'membresias'
            $actualizar = "UPDATE membresias 
                            SET 
                                tipo = :tipo,
                                descripcion = :descripcion,
                                costo = :costo,
                                duracion_dias = :duracion_dias,
                                estado = :estado,
                                es_destacado = :es_destacado,
                                orden_visual = :orden_visual,
                                max_servicios_activos = :max_servicios_activos,
                                acceso_estadisticas_pro = :acceso_estadisticas_pro,
                                permite_videos = :permite_videos
                            WHERE id = :id";

            $resultado = $this->conexion->prepare($actualizar);

            // BINDING DE PARÁMETROS:
            $resultado->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $resultado->bindParam(':tipo', $data['tipo']);
            $resultado->bindParam(':costo', $data['costo']);
            $resultado->bindParam(':duracion_dias', $data['duracion_dias'], PDO::PARAM_INT);
            $resultado->bindParam(':descripcion', $data['descripcion']);
            $resultado->bindParam(':max_servicios_activos', $data['max_servicios_activos'], PDO::PARAM_INT);

            // Manejo de 'orden_visual': Puede ser NULL
            $orden_visual = $data['orden_visual'] !== null ? $data['orden_visual'] : null;
            $tipo_orden = $orden_visual === null ? PDO::PARAM_NULL : PDO::PARAM_INT;
            $resultado->bindParam(':orden_visual', $orden_visual, $tipo_orden);


            // Los campos booleanos/enteros se asumen como enteros (1 o 0)
            $resultado->bindParam(':acceso_estadisticas_pro', $data['acceso_estadisticas_pro'], PDO::PARAM_INT);
            $resultado->bindParam(':permite_videos', $data['permite_videos'], PDO::PARAM_INT);
            $resultado->bindParam(':es_destacado', $data['es_destacado'], PDO::PARAM_INT);
            $resultado->bindParam(':estado', $data['estado']);

            // Ejecutar la consulta
            $resultado->execute();

            // Si todo fue exitoso, hacemos commit
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            // Si algo falla, hacemos rollback y registramos el error
            $this->conexion->rollBack();
            error_log("Error en Membresia::actualizar -> " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id)
    {
        try {
            $eliminar = "DELETE FROM membresias WHERE id = :id";
            $resultado = $this->conexion->prepare($eliminar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Error en Membresia::eliminar->" . $e->getMessage());
            return false;
        }
    }
}
