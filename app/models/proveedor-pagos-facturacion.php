<?php
// app/models/ProveedorPagosFacturacion.php

require_once __DIR__ . '/../../config/database.php';

class ProveedorPagosFacturacion
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene la configuración de pagos/facturación a partir del usuario (tabla usuarios).
     */
    public function obtenerPorUsuario(int $usuarioId): ?array
    {
        try {
            $sql = "
                SELECT pf.*
                FROM proveedores_pagos_facturacion pf
                INNER JOIN proveedores p ON pf.proveedor_id = p.id
                WHERE p.usuario_id = :usuario_id
                LIMIT 1
            ";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProveedorPagosFacturacion::obtenerPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Inserta/actualiza los datos de pagos/facturación del proveedor.
     */
    public function guardarDesdeFormulario(int $usuarioId, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener proveedor_id
            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
            if (!$proveedorId) {
                throw new Exception("No se encontró proveedor asociado al usuario.");
            }

            // 2. Normalizar datos
            $tipoDocumento        = $data['tipo_documento']        ?? '';
            $numeroDocumento      = $data['numero_documento']      ?? '';
            $razonSocial          = $data['razon_social']          ?? '';
            $regimenFiscal        = $data['regimen_fiscal']        ?? null;
            $direccionFacturacion = $data['direccion_facturacion'] ?? '';
            $ciudadFacturacion    = $data['ciudad_facturacion']    ?? '';
            $paisFacturacion      = $data['pais_facturacion']      ?? 'Colombia';
            $correoFacturacion    = $data['correo_facturacion']    ?? '';
            $telefonoFacturacion  = $data['telefono_facturacion']  ?? null;

            $banco                = $data['banco']                 ?? null;
            $tipoCuenta           = $data['tipo_cuenta']           ?? null;
            $numeroCuenta         = $data['numero_cuenta']         ?? null;
            $titularCuenta        = $data['titular_cuenta']        ?? null;
            $identificacionTitular= $data['identificacion_titular']?? null;
            $metodoPagoPreferido  = $data['metodo_pago_preferido'] ?? null;
            $notaMetodoPago       = $data['nota_metodo_pago']      ?? null;

            $frecuenciaLiquidacion = $data['frecuencia_liquidacion'] ?? null;
            $montoMinimoRetiro     = $data['monto_minimo_retiro']     ?? null;
            $aceptaFacturaElectronica = !empty($data['acepta_factura_electronica']) ? 1 : 0;

            // Normalizar monto mínimo
            if ($montoMinimoRetiro === '' || !is_numeric($montoMinimoRetiro)) {
                $montoMinimoRetiro = null;
            }

            // 3. Ver si ya tiene registro
            $sqlExiste = "SELECT id FROM proveedores_pagos_facturacion WHERE proveedor_id = :proveedor_id LIMIT 1";
            $stmtExiste = $this->conexion->prepare($sqlExiste);
            $stmtExiste->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmtExiste->execute();
            $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                // UPDATE
                $sql = "
                    UPDATE proveedores_pagos_facturacion
                    SET tipo_documento        = :tipo_documento,
                        numero_documento      = :numero_documento,
                        razon_social          = :razon_social,
                        regimen_fiscal        = :regimen_fiscal,
                        direccion_facturacion = :direccion_facturacion,
                        ciudad_facturacion    = :ciudad_facturacion,
                        pais_facturacion      = :pais_facturacion,
                        correo_facturacion    = :correo_facturacion,
                        telefono_facturacion  = :telefono_facturacion,
                        banco                 = :banco,
                        tipo_cuenta           = :tipo_cuenta,
                        numero_cuenta         = :numero_cuenta,
                        titular_cuenta        = :titular_cuenta,
                        identificacion_titular= :identificacion_titular,
                        metodo_pago_preferido = :metodo_pago_preferido,
                        nota_metodo_pago      = :nota_metodo_pago,
                        frecuencia_liquidacion= :frecuencia_liquidacion,
                        monto_minimo_retiro   = :monto_minimo_retiro,
                        acepta_factura_electronica = :acepta_factura_electronica
                    WHERE proveedor_id = :proveedor_id
                    LIMIT 1
                ";
            } else {
                // INSERT
                $sql = "
                    INSERT INTO proveedores_pagos_facturacion (
                        proveedor_id,
                        tipo_documento,
                        numero_documento,
                        razon_social,
                        regimen_fiscal,
                        direccion_facturacion,
                        ciudad_facturacion,
                        pais_facturacion,
                        correo_facturacion,
                        telefono_facturacion,
                        banco,
                        tipo_cuenta,
                        numero_cuenta,
                        titular_cuenta,
                        identificacion_titular,
                        metodo_pago_preferido,
                        nota_metodo_pago,
                        frecuencia_liquidacion,
                        monto_minimo_retiro,
                        acepta_factura_electronica
                    ) VALUES (
                        :proveedor_id,
                        :tipo_documento,
                        :numero_documento,
                        :razon_social,
                        :regimen_fiscal,
                        :direccion_facturacion,
                        :ciudad_facturacion,
                        :pais_facturacion,
                        :correo_facturacion,
                        :telefono_facturacion,
                        :banco,
                        :tipo_cuenta,
                        :numero_cuenta,
                        :titular_cuenta,
                        :identificacion_titular,
                        :metodo_pago_preferido,
                        :nota_metodo_pago,
                        :frecuencia_liquidacion,
                        :monto_minimo_retiro,
                        :acepta_factura_electronica
                    )
                ";
            }

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_documento', $tipoDocumento);
            $stmt->bindParam(':numero_documento', $numeroDocumento);
            $stmt->bindParam(':razon_social', $razonSocial);
            $stmt->bindParam(':regimen_fiscal', $regimenFiscal);
            $stmt->bindParam(':direccion_facturacion', $direccionFacturacion);
            $stmt->bindParam(':ciudad_facturacion', $ciudadFacturacion);
            $stmt->bindParam(':pais_facturacion', $paisFacturacion);
            $stmt->bindParam(':correo_facturacion', $correoFacturacion);
            $stmt->bindParam(':telefono_facturacion', $telefonoFacturacion);
            $stmt->bindParam(':banco', $banco);
            $stmt->bindParam(':tipo_cuenta', $tipoCuenta);
            $stmt->bindParam(':numero_cuenta', $numeroCuenta);
            $stmt->bindParam(':titular_cuenta', $titularCuenta);
            $stmt->bindParam(':identificacion_titular', $identificacionTitular);
            $stmt->bindParam(':metodo_pago_preferido', $metodoPagoPreferido);
            $stmt->bindParam(':nota_metodo_pago', $notaMetodoPago);
            $stmt->bindParam(':frecuencia_liquidacion', $frecuenciaLiquidacion);
            $stmt->bindParam(':monto_minimo_retiro', $montoMinimoRetiro);
            $stmt->bindParam(':acepta_factura_electronica', $aceptaFacturaElectronica, PDO::PARAM_INT);

            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error en ProveedorPagosFacturacion::guardarDesdeFormulario -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Devuelve el id del proveedor (tabla proveedores) a partir del usuario_id.
     */
    private function obtenerProveedorIdPorUsuario(int $usuarioId): ?int
    {
        $sql = "SELECT id FROM proveedores WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila['id'] ?? null;
    }
}
