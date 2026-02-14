<?php
// Asegúrate de que la ruta a tu conexión sea la correcta
require_once __DIR__ . '/../../config/database.php';

class Moderacion
{
    private $conexion;

    public function __construct()
    {
        // Ajusta esto según cómo llames a tu clase de conexión (Conexion o Database)
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /* ====================================================================
       MÉTODO APROBAR (Sistema de Reputación)
       --------------------------------------------------------------------
       Recibe: $servicio_id (El ID del servicio, no de la publicación)
       1. Busca la publicación correcta usando servicio_id.
       2. Cambia estado a 'aprobado'.
       3. Suma +1 al contador del proveedor.
       4. Si llega a 3, activa la 'auto_aprobacion_activa'.
    ==================================================================== */
    public function aprobar($servicio_id)
    {
        try {
            $this->conexion->beginTransaction();

            // 1. OBTENER EL ID REAL DE LA PUBLICACIÓN Y EL PROVEEDOR
            // Usamos servicio_id para encontrar la fila correcta en 'publicaciones'
            $sqlInfo = "SELECT id AS publicacion_id, proveedor_id 
                        FROM publicaciones 
                        WHERE servicio_id = :sid 
                        LIMIT 1";
            $stmtInfo = $this->conexion->prepare($sqlInfo);
            $stmtInfo->bindParam(":sid", $servicio_id, PDO::PARAM_INT);
            $stmtInfo->execute();
            $datos = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            // Si no se encuentra, cancelamos
            if (!$datos) {
                $this->conexion->rollBack();
                return false;
            }

            $pubId = $datos['publicacion_id'];
            $provId = $datos['proveedor_id'];

            // 2. APROBAR LA PUBLICACIÓN
            $sqlUpd = "UPDATE publicaciones 
                       SET estado = 'aprobado', motivo_rechazo = NULL, modified_at = NOW() 
                       WHERE id = :pid";
            $stmtUpd = $this->conexion->prepare($sqlUpd);
            $stmtUpd->bindParam(":pid", $pubId, PDO::PARAM_INT);
            $stmtUpd->execute();

            // 3. SUMAR REPUTACIÓN AL PROVEEDOR (+1)
            $sqlProv = "UPDATE proveedores 
                        SET publicaciones_aprobadas_count = publicaciones_aprobadas_count + 1 
                        WHERE id = :prid";
            $stmtProv = $this->conexion->prepare($sqlProv);
            $stmtProv->bindParam(":prid", $provId, PDO::PARAM_INT);
            $stmtProv->execute();

            // 4. VERIFICAR VIP (AUTO-APROBACIÓN)
            // Si el contador llega a 3, activamos el flag.
            $sqlVip = "UPDATE proveedores 
                       SET auto_aprobacion_activa = 1 
                       WHERE id = :prid AND publicaciones_aprobadas_count >= 3";
            $stmtVip = $this->conexion->prepare($sqlVip);
            $stmtVip->bindParam(":prid", $provId, PDO::PARAM_INT);
            $stmtVip->execute();

            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Moderacion::aprobar -> " . $e->getMessage());
            return false;
        }
    }

    /* ====================================================================
       MÉTODO RECHAZAR (Sistema de Castigo)
       --------------------------------------------------------------------
       Recibe: $servicio_id, $motivo
       1. Busca la publicación correcta.
       2. Cambia estado a 'rechazado'.
       3. CASTIGO: Reinicia el contador a 0 y quita el permiso VIP.
    ==================================================================== */
    public function rechazar($servicio_id, $motivo)
    {
        try {
            $this->conexion->beginTransaction();

            // 1. OBTENER DATOS
            $sqlInfo = "SELECT id AS publicacion_id, proveedor_id 
                        FROM publicaciones 
                        WHERE servicio_id = :sid 
                        LIMIT 1";
            $stmtInfo = $this->conexion->prepare($sqlInfo);
            $stmtInfo->bindParam(":sid", $servicio_id, PDO::PARAM_INT);
            $stmtInfo->execute();
            $datos = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            if (!$datos) {
                $this->conexion->rollBack();
                return false;
            }

            $pubId = $datos['publicacion_id'];
            $provId = $datos['proveedor_id'];

            // 2. RECHAZAR PUBLICACIÓN
            $sqlUpd = "UPDATE publicaciones 
                       SET estado = 'rechazado', motivo_rechazo = :mot, modified_at = NOW() 
                       WHERE id = :pid";
            $stmtUpd = $this->conexion->prepare($sqlUpd);
            $stmtUpd->bindParam(":pid", $pubId, PDO::PARAM_INT);
            $stmtUpd->bindParam(":mot", $motivo, PDO::PARAM_STR);
            $stmtUpd->execute();

            // 3. CASTIGO: QUITAR VIP Y RESETEAR CONTADOR A 0
            // Si rechazamos, el proveedor debe volver a ganar confianza.
            $sqlCastigo = "UPDATE proveedores 
                           SET auto_aprobacion_activa = 0, 
                               publicaciones_aprobadas_count = 0 
                           WHERE id = :prid";
            $stmtCastigo = $this->conexion->prepare($sqlCastigo);
            $stmtCastigo->bindParam(":prid", $provId, PDO::PARAM_INT);
            $stmtCastigo->execute();

            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Moderacion::rechazar -> " . $e->getMessage());
            return false;
        }
    }
}