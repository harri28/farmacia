<?php
// ============================================================
// ARCHIVO: farmacia/config/nubefact.php
// DESCRIPCIÓN: Integración con Nubefact (facturación electrónica SUNAT)
// ⚠️  No compartas este archivo públicamente — contiene credenciales.
// ============================================================

define('NUBEFACT_URL',            'https://api.nubefact.com/api/v1/cb728691-016d-4e0c-bd73-0ff4eb198223');
define('NUBEFACT_TOKEN',          '42443a7f2f894b99834aed6ff79fb42ff84d75448bc349e390ce9f35f6c20cb5');
define('NUBEFACT_SERIE_BOLETA',   'B001');
define('NUBEFACT_SERIE_FACTURA',  'F001');

/**
 * Envía un comprobante a Nubefact y actualiza el registro en BD.
 *
 * @param  PDO        $db               Conexión activa
 * @param  array      $comp             Datos del comprobante (id, tipo, serie, numero, numero_completo)
 * @param  array|null $cliente          Datos del cliente o null para cliente general
 * @param  array      $items            Líneas de la venta enriquecidas con nombre y codigo
 * @param  array      $venta            Datos de la venta (numero_venta, tipo_pago)
 * @return array                        Resultado para devolver al frontend
 */
function enviar_nubefact(PDO $db, array $comp, ?array $cliente, array $items, array $venta): array {

    $tipo_nubefact = $comp['tipo'] === 'factura' ? 1 : 2;

    // ---- Datos del cliente ----
    if ($cliente && $comp['tipo'] === 'factura' && !empty($cliente['ruc'])) {
        $cli_tipo  = 6;   // RUC
        $cli_doc   = trim($cliente['ruc']);
        $cli_nom   = strtoupper(trim(($cliente['nombres'] ?? '') . ' ' . ($cliente['apellidos'] ?? '')));
        $cli_dir   = trim($cliente['direccion'] ?? '');
        $cli_email = trim($cliente['email'] ?? '');
    } elseif ($cliente && !empty($cliente['dni'])) {
        $cli_tipo  = 1;   // DNI
        $cli_doc   = trim($cliente['dni']);
        $cli_nom   = strtoupper(trim(($cliente['nombres'] ?? '') . ' ' . ($cliente['apellidos'] ?? '')));
        $cli_dir   = trim($cliente['direccion'] ?? '');
        $cli_email = trim($cliente['email'] ?? '');
    } else {
        $cli_tipo  = 0;   // Sin documento
        $cli_doc   = '-';
        $cli_nom   = 'CLIENTE GENERAL';
        $cli_dir   = '';
        $cli_email = '';
    }

    // ---- Calcular totales e ítems (precios CON IGV incluido) ----
    $total_gravada = 0.0;
    $total_igv     = 0.0;
    $total_venta   = 0.0;
    $nf_items      = [];

    foreach ($items as $item) {
        $cantidad   = intval($item['cantidad']);
        $precio_inc = round(floatval($item['precio'] ?? $item['precio_unitario'] ?? 0), 2); // con IGV
        $valor_unit = $precio_inc / 1.18;                              // sin IGV (alta precisión)
        $sub_base   = round($valor_unit * $cantidad, 2);               // base gravada
        $total_item = round($precio_inc * $cantidad, 2);               // total con IGV
        $igv_item   = round($total_item - $sub_base, 2);               // IGV real (evita redondeo)

        $total_gravada += $sub_base;
        $total_igv     += $igv_item;
        $total_venta   += $total_item;

        $nf_items[] = [
            'unidad_de_medida'           => 'NIU',
            'codigo'                     => $item['codigo'] ?? (string)($item['producto_id'] ?? ''),
            'descripcion'                => $item['nombre'] ?? 'Producto',
            'cantidad'                   => $cantidad,
            'valor_unitario'             => round($valor_unit, 6),
            'precio_unitario'            => $precio_inc,
            'descuento'                  => '',
            'subtotal'                   => $sub_base,
            'tipo_de_igv'                => 1,    // Gravado — Operación Onerosa
            'igv'                        => $igv_item,
            'total'                      => $total_item,
            'anticipo_regularizacion'    => false,
            'anticipo_documento_serie'   => '',
            'anticipo_documento_numero'  => '',
        ];
    }

    $total_gravada = round($total_gravada, 2);
    $total_igv     = round($total_igv, 2);
    $total_venta   = round($total_venta, 2);

    // ---- Construir payload ----
    $medio = ucfirst(strtolower($venta['tipo_pago'] ?? 'efectivo'));

    $payload = [
        'operacion'                             => 'generar_comprobante',
        'tipo_de_comprobante'                   => $tipo_nubefact,
        'serie'                                 => $comp['serie'],
        'numero'                                => $comp['numero'],
        'sunat_transaction'                     => 1,
        'cliente_tipo_de_documento'             => $cli_tipo,
        'cliente_numero_de_documento'           => $cli_doc,
        'cliente_denominacion'                  => $cli_nom,
        'cliente_direccion'                     => $cli_dir,
        'cliente_email'                         => $cli_email,
        'cliente_email_1'                       => '',
        'cliente_email_2'                       => '',
        'fecha_de_emision'                      => date('d/m/Y'),
        'fecha_de_vencimiento'                  => '',
        'moneda'                                => 1,    // PEN (soles)
        'tipo_de_cambio'                        => '',
        'porcentaje_de_igv'                     => 18.0,
        'descuento_global'                      => 0,
        'total_descuento'                       => 0,
        'total_anticipo'                        => 0,
        'total_gravada'                         => $total_gravada,
        'total_inafecta'                        => 0,
        'total_exonerada'                       => 0,
        'total_igv'                             => $total_igv,
        'total_gratuita'                        => 0,
        'total_otros_cargos'                    => 0,
        'total'                                 => $total_venta,
        'percepciones_retencion_o_detracciones' => 0,
        'total_perception'                      => 0,
        'enviar_automaticamente_a_la_sunat'     => true,
        'enviar_automaticamente_al_cliente'     => false,
        'codigo_unico'                          => '',
        'condiciones_de_pago'                   => 'Contado',
        'medio_de_pago'                         => $medio,
        'placa_vehiculo'                        => '',
        'orden_compra_servicio'                 => $venta['numero_venta'] ?? '',
        'table_impuestos'                       => '1',
        'formato_de_pdf'                        => '',
        'items'                                 => $nf_items,
    ];

    // ---- Llamada cURL ----
    $ch = curl_init(NUBEFACT_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Token ' . NUBEFACT_TOKEN,
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $raw        = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // ---- Error de red ----
    if ($curl_error) {
        $db->prepare("UPDATE comprobantes_electronicos SET estado_sunat = :e WHERE id = :id")
           ->execute([':e' => 'Error de conexión', ':id' => $comp['id']]);
        return [
            'numero_completo' => $comp['numero_completo'],
            'tipo'            => $comp['tipo'],
            'estado_sunat'    => 'Error de conexión',
            'error_nubefact'  => true,
            'mensaje'         => 'No se pudo conectar con Nubefact: ' . $curl_error,
        ];
    }

    $resp    = json_decode($raw, true) ?? [];
    $ok      = $http_code === 200 && !empty($resp['enlace_del_pdf']);
    $estado  = $ok
        ? ($resp['sunat_description'] ?? 'Aceptado por SUNAT')
        : ($resp['errors'][0] ?? $resp['message'] ?? "Error HTTP $http_code");

    // ---- Guardar respuesta ----
    $db->prepare("
        UPDATE comprobantes_electronicos SET
            estado_sunat          = :estado,
            enlace_del_pdf        = :pdf,
            enlace_del_xml        = :xml,
            enlace_del_cdr        = :cdr,
            cadena_para_codigo_qr = :qr,
            nubefact_response     = :resp
        WHERE id = :id
    ")->execute([
        ':estado' => $estado,
        ':pdf'    => $resp['enlace_del_pdf']        ?? '',
        ':xml'    => $resp['enlace_del_xml']        ?? '',
        ':cdr'    => $resp['enlace_del_cdr']        ?? '',
        ':qr'     => $resp['cadena_para_codigo_qr'] ?? '',
        ':resp'   => json_encode($resp, JSON_UNESCAPED_UNICODE),
        ':id'     => $comp['id'],
    ]);

    return [
        'numero_completo' => $comp['numero_completo'],
        'tipo'            => $comp['tipo'],
        'estado_sunat'    => $estado,
        'enlace_del_pdf'  => $resp['enlace_del_pdf']        ?? null,
        'enlace_del_xml'  => $resp['enlace_del_xml']        ?? null,
        'enlace_del_cdr'  => $resp['enlace_del_cdr']        ?? null,
        'cadena_qr'       => $resp['cadena_para_codigo_qr'] ?? null,
        'error_nubefact'  => !$ok,
        'mensaje'         => $ok ? null : $estado,
    ];
}
