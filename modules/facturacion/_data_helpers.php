<?php
// ============================================================
// ARCHIVO: farmacia/modules/facturacion/_data_helpers.php
// DESCRIPCION: Helpers de lectura compartidos entre el api.php del
//              modulo y el worker scripts/reenviar_sunat_pendientes.php.
//              Separados aqui para evitar duplicacion.
// ============================================================

function facturacionNormalizarAfectacionTipo(?string $tipo, ?string $codigo): string
{
    $tipo = strtoupper(trim((string) $tipo));
    if ($tipo !== '') {
        return $tipo;
    }

    switch (trim((string) $codigo)) {
        case '20': case '21': return 'EXO';
        case '30': case '31': case '32': case '33': case '34': case '35': case '36': return 'INA';
        case '40': return 'EXP';
        default:   return 'GRAV';
    }
}

function facturacionCargarComprobanteVenta(PDO $db, int $comprobanteId): array
{
    $stmt = $db->prepare("
        SELECT
            ce.id,
            ce.venta_id,
            ce.tipo,
            ce.serie,
            ce.numero,
            ce.numero_completo,
            ce.codigo_tipo_documento,
            ce.estado_sunat,
            ce.enlace_del_pdf,
            ce.enlace_del_xml,
            ce.enlace_del_cdr,
            ce.nubefact_response,
            ce.referencia_comprobante_id,
            ce.tipo_nota_credito_id,
            ce.codigo_tipo_nota_credito,
            ce.motivo_nota_credito,
            ce.descripcion_nota_credito,
            ce.documento_modificado_tipo_documento_codigo,
            ce.documento_modificado_serie,
            ce.documento_modificado_numero,
            ce.documento_modificado_numero_completo,
            ce.documento_modificado_fecha,
            v.numero_venta,
            v.tipo_pago,
            v.sunat_forma_pago,
            v.fecha_emision,
            v.hora_emision,
            v.moneda_codigo,
            v.total,
            v.cliente_id
        FROM comprobantes_electronicos ce
        INNER JOIN ventas v ON v.id = ce.venta_id
        WHERE ce.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $comprobanteId]);
    $row = $stmt->fetch();

    if (!$row) {
        throw new RuntimeException('No se encontro el comprobante seleccionado.');
    }

    return $row;
}

function facturacionCargarClienteVenta(PDO $db, ?int $clienteId): ?array
{
    if (!$clienteId) {
        return null;
    }

    $stmt = $db->prepare("
        SELECT
            id, nombres, apellidos, dni, ruc, telefono, email, direccion,
            tipo_documento_id, tipo_documento_codigo, numero_documento,
            nombre_completo, razon_social, ubigeo
        FROM clientes
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $clienteId]);

    return $stmt->fetch() ?: null;
}

function facturacionCargarItemsVenta(PDO $db, int $ventaId): array
{
    $stmt = $db->prepare("
        SELECT
            vd.producto_id,
            COALESCE(vd.descripcion, p.nombre, 'Producto') AS nombre,
            COALESCE(vd.codigo_interno, p.codigo_interno, p.codigo, '') AS codigo,
            vd.codigo_sunat,
            vd.cantidad,
            vd.precio_unitario,
            vd.valor_unitario,
            vd.valor_total,
            vd.precio_total,
            vd.igv,
            vd.unidad_codigo,
            vd.afectacion_igv_codigo,
            COALESCE(a.tipo, '') AS afectacion_tipo,
            CASE
                WHEN COALESCE(vd.igv, 0) > 0 AND COALESCE(vd.valor_total, 0) > 0
                    THEN ROUND((vd.igv / NULLIF(vd.valor_total, 0)) * 100, 2)
                ELSE 0
            END AS porcentaje_igv
        FROM venta_detalles vd
        LEFT JOIN productos p ON p.id = vd.producto_id
        LEFT JOIN public.fe_tipos_afectacion_igv a ON a.id = vd.afectacion_igv_id
        WHERE vd.venta_id = :venta_id
        ORDER BY vd.id
    ");
    $stmt->execute([':venta_id' => $ventaId]);

    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $row['afectacion_tipo'] = facturacionNormalizarAfectacionTipo(
            $row['afectacion_tipo'] ?? null,
            $row['afectacion_igv_codigo'] ?? null
        );
        $items[] = $row;
    }

    if (!$items) {
        throw new RuntimeException('La venta no tiene detalle para reenviar a SUNAT.');
    }

    return $items;
}

function facturacionExtraerResponseCode(?string $json): ?string
{
    $json = trim((string) $json);
    if ($json === '') {
        return null;
    }

    $data = json_decode($json, true);
    if (!is_array($data)) {
        return null;
    }

    return isset($data['cdr']['response_code'])
        ? trim((string) $data['cdr']['response_code'])
        : (isset($data['cdr']['responseCode']) ? trim((string) $data['cdr']['responseCode']) : null);
}
