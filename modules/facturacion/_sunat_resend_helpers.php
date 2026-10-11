<?php
// ============================================================
// ARCHIVO: farmacia/modules/facturacion/_sunat_resend_helpers.php
// DESCRIPCION: Helpers compartidos entre el endpoint reenviar_sunat del
//              api.php y el worker scripts/reenviar_sunat_pendientes.php.
//              Reune en un solo lugar la logica de "tomar un comprobante
//              existente y reenviarlo a SUNAT".
//
//              NO contiene auth: el api.php lo gatea con requireApiAuth,
//              el worker corre bajo cron (sin usuario).
// ============================================================

require_once __DIR__ . '/../../config/sunat.php';
require_once __DIR__ . '/_data_helpers.php';

/**
 * Reenvia un comprobante existente a SUNAT.
 * Devuelve array con:
 *   - ok (bool)       : true si enviar_sunat completo sin error_nubefact
 *   - response_code   : si SUNAT devolvio CDR, el codigo (0 aceptado, 1 observado, otro rechazo)
 *   - mensaje         : estado/mensaje devuelto por enviar_sunat
 *   - enlace_del_cdr  : URL al CDR si fue aceptado
 *   - raw             : respuesta completa de enviar_sunat (para auditoria)
 */
function reenviarSunatComprobante(PDO $db, int $comprobanteId): array
{
    $comp = facturacionCargarComprobanteVenta($db, $comprobanteId);

    $cliente = facturacionCargarClienteVenta($db, !empty($comp['cliente_id']) ? (int) $comp['cliente_id'] : null);
    $items   = facturacionCargarItemsVenta($db, (int) $comp['venta_id']);

    $result = enviar_sunat(
        $db,
        [
            'id'                   => (int) $comp['id'],
            'tipo'                 => (string) $comp['tipo'],
            'serie'                => (string) $comp['serie'],
            'numero'               => (int) $comp['numero'],
            'numero_completo'      => (string) $comp['numero_completo'],
            'codigo_tipo_documento'=> (string) $comp['codigo_tipo_documento'],
        ],
        $cliente,
        $items,
        [
            'numero_venta'      => (string) $comp['numero_venta'],
            'tipo_pago'         => (string) $comp['tipo_pago'],
            'sunat_forma_pago'  => (string) ($comp['sunat_forma_pago'] ?: 'Contado'),
            'fecha_emision'     => (string) $comp['fecha_emision'],
            'hora_emision'      => (string) $comp['hora_emision'],
            'moneda'            => (string) ($comp['moneda_codigo'] ?: 'PEN'),
            'totales'           => ['total' => (float) $comp['total']],
            'documento_modificado_tipo_documento_codigo' => (string) ($comp['documento_modificado_tipo_documento_codigo'] ?? ''),
            'documento_modificado_numero_completo'       => (string) ($comp['documento_modificado_numero_completo'] ?? ''),
            'motivo_codigo'      => (string) ($comp['codigo_tipo_nota_credito'] ?? ''),
            'motivo_descripcion' => (string) ($comp['motivo_nota_credito'] ?? ''),
        ]
    );

    $ok = empty($result['error_nubefact']) && !empty($result['enlace_del_cdr']);

    return [
        'ok'             => $ok,
        'response_code'  => $result['response_code'] ?? null,
        'mensaje'        => (string) ($result['estado_sunat'] ?? $result['mensaje'] ?? ''),
        'enlace_del_cdr' => $result['enlace_del_cdr'] ?? null,
        'raw'            => $result,
    ];
}
