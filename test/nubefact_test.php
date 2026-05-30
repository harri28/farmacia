<?php
// ============================================================
// ARCHIVO: farmacia/test/nubefact_test.php
// DESCRIPCIÓN: Prueba de conexión y envío a la API de Nubefact
// ============================================================

// Valores por defecto desde config (si existe)
$cfg_url   = '';
$cfg_token = '';
if (file_exists(__DIR__ . '/../config/nubefact.php')) {
    // Capturar las constantes sin contaminar el namespace global con define()
    // si ya fueron definidas en otra ejecución
    if (!defined('NUBEFACT_URL')) {
        require_once __DIR__ . '/../config/nubefact.php';
    }
    $cfg_url   = defined('NUBEFACT_URL')   ? NUBEFACT_URL   : '';
    $cfg_token = defined('NUBEFACT_TOKEN') ? NUBEFACT_TOKEN : '';
}

// ---- Payload de prueba por defecto (boleta mínima) ----
$payload_default = json_encode([
    'operacion'                             => 'generar_comprobante',
    'tipo_de_comprobante'                   => 2,
    'serie'                                 => 'B001',
    'numero'                                => 1,
    'sunat_transaction'                     => 1,
    'cliente_tipo_de_documento'             => 0,
    'cliente_numero_de_documento'           => '-',
    'cliente_denominacion'                  => 'CLIENTE GENERAL',
    'cliente_direccion'                     => '',
    'cliente_email'                         => '',
    'cliente_email_1'                       => '',
    'cliente_email_2'                       => '',
    'fecha_de_emision'                      => date('d/m/Y'),
    'fecha_de_vencimiento'                  => '',
    'moneda'                                => 1,
    'tipo_de_cambio'                        => '',
    'porcentaje_de_igv'                     => 18.0,
    'descuento_global'                      => 0,
    'total_descuento'                       => 0,
    'total_anticipo'                        => 0,
    'total_gravada'                         => 8.47,
    'total_inafecta'                        => 0,
    'total_exonerada'                       => 0,
    'total_igv'                             => 1.53,
    'total_gratuita'                        => 0,
    'total_otros_cargos'                    => 0,
    'total'                                 => 10.00,
    'percepciones_retencion_o_detracciones' => 0,
    'total_perception'                      => 0,
    'enviar_automaticamente_a_la_sunat'     => true,
    'enviar_automaticamente_al_cliente'     => false,
    'codigo_unico'                          => '',
    'condiciones_de_pago'                   => 'Contado',
    'medio_de_pago'                         => 'Efectivo',
    'placa_vehiculo'                        => '',
    'orden_compra_servicio'                 => 'TEST-001',
    'table_impuestos'                       => '1',
    'formato_de_pdf'                        => '',
    'items'                                 => [[
        'unidad_de_medida'          => 'NIU',
        'codigo'                    => 'PROD001',
        'descripcion'               => 'Producto de prueba',
        'cantidad'                  => 1,
        'valor_unitario'            => 8.474576,
        'precio_unitario'           => 10.00,
        'descuento'                 => '',
        'subtotal'                  => 8.47,
        'tipo_de_igv'               => 1,
        'igv'                       => 1.53,
        'total'                     => 10.00,
        'anticipo_regularizacion'   => false,
        'anticipo_documento_serie'  => '',
        'anticipo_documento_numero' => '',
    ]],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ---- Procesar envío ----
$resultado   = null;
$error_msg   = '';
$url_usada   = '';
$token_usado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url_usada   = trim($_POST['nubefact_url']   ?? '');
    $token_usado = trim($_POST['nubefact_token'] ?? '');
    $payload_raw = trim($_POST['payload']        ?? '');

    if (!$url_usada) {
        $error_msg = 'La URL de Nubefact es obligatoria.';
    } elseif (!$token_usado) {
        $error_msg = 'El token es obligatorio.';
    } elseif (!$payload_raw) {
        $error_msg = 'El payload no puede estar vacío.';
    } else {
        // Validar JSON
        $payload_decoded = json_decode($payload_raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_msg = 'El payload no es JSON válido: ' . json_last_error_msg();
        } else {
            // Enviar a Nubefact
            $ch = curl_init($url_usada);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload_decoded, JSON_UNESCAPED_UNICODE),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Token ' . $token_usado,
                ],
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $raw        = curl_exec($ch);
            $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            curl_close($ch);

            $resultado = [
                'http_code'  => $http_code,
                'curl_error' => $curl_error,
                'total_time' => round($total_time * 1000),
                'raw'        => $raw,
                'data'       => json_decode($raw, true) ?? [],
            ];
        }
    }
} else {
    $url_usada   = $cfg_url;
    $token_usado = $cfg_token;
}

$payload_enviado = !empty($_POST['payload']) ? $_POST['payload'] : $payload_default;

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Nubefact API — FarmaSystem</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            padding: 32px 16px;
        }
        .container { max-width: 900px; margin: 0 auto; }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border-radius: 14px;
            padding: 24px 28px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .page-header i { font-size: 2rem; opacity: .9; }
        .page-header h1 { font-size: 1.3rem; font-weight: 700; }
        .page-header p  { font-size: .85rem; opacity: .8; margin-top: 3px; }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            font-size: .9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-body { padding: 20px; }

        /* Form */
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .04rem;
        }
        .form-group input[type="text"],
        .form-group input[type="url"] {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 9px;
            font-size: .92rem;
            font-family: 'Segoe UI', system-ui, sans-serif;
            outline: none;
            transition: border-color .15s;
        }
        .form-group input:focus { border-color: #0ea5e9; }

        .token-wrap { position: relative; }
        .token-wrap input { padding-right: 44px; font-family: monospace; font-size: .85rem; }
        .toggle-token {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: .9rem;
            padding: 4px;
        }
        .toggle-token:hover { color: #475569; }

        .hint {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 5px;
        }
        .hint code {
            background: #f1f5f9;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: .8rem;
            color: #475569;
        }

        .credentials-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 600px) { .credentials-grid { grid-template-columns: 1fr; } }

        /* Textarea payload */
        textarea {
            width: 100%;
            min-height: 320px;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 9px;
            font-family: 'Cascadia Code', 'Consolas', monospace;
            font-size: .78rem;
            line-height: 1.55;
            resize: vertical;
            outline: none;
            transition: border-color .15s;
            color: #1e293b;
        }
        textarea:focus { border-color: #0ea5e9; }

        /* Botones */
        .btn-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 4px; }
        .btn {
            padding: 11px 22px;
            border: none;
            border-radius: 9px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: .15s;
        }
        .btn-primary { background: #0ea5e9; color: white; }
        .btn-primary:hover { background: #0284c7; }
        .btn-secondary {
            background: white;
            color: #475569;
            border: 2px solid #e2e8f0;
        }
        .btn-secondary:hover { border-color: #94a3b8; }
        .btn-danger { background: #fef2f2; color: #dc2626; border: 2px solid #fecaca; }
        .btn-danger:hover { background: #fee2e2; }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 9px;
            font-size: .87rem;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert i { margin-top: 1px; flex-shrink: 0; }
        .alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .alert-warn    { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .alert-info    { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }

        /* Resultado */
        .result-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .http-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 700;
        }
        .http-200 { background: #dcfce7; color: #15803d; }
        .http-4xx { background: #fef2f2; color: #dc2626; }
        .http-5xx { background: #fef3c7; color: #b45309; }
        .http-err { background: #f1f5f9; color: #475569; }
        .time-badge {
            font-size: .78rem;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 3px 10px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }
        @media (max-width: 600px) { .data-grid { grid-template-columns: 1fr; } }
        .data-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .data-item.full { grid-column: 1 / -1; }
        .data-label { font-size: .72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .04rem; margin-bottom: 3px; }
        .data-value { font-size: .92rem; font-weight: 600; color: #1e293b; word-break: break-all; }
        .data-value.success { color: #16a34a; }
        .data-value.danger  { color: #dc2626; }
        .data-value a { color: #0284c7; text-decoration: none; }
        .data-value a:hover { text-decoration: underline; }

        details { margin-top: 4px; }
        summary {
            cursor: pointer;
            font-size: .8rem;
            color: #0ea5e9;
            font-weight: 600;
            padding: 6px 0;
            user-select: none;
        }
        summary:hover { color: #0284c7; }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            border-radius: 9px;
            padding: 14px;
            font-size: .75rem;
            overflow-x: auto;
            margin-top: 8px;
            line-height: 1.55;
            tab-size: 2;
        }

        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 16px 0; }

        .from-config-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .72rem;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 2px 8px;
            margin-left: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="page-header">
        <i class="fas fa-plug"></i>
        <div>
            <h1>Test Nubefact API</h1>
            <p>Prueba la conexión y el envío de comprobantes a la API de Nubefact</p>
        </div>
    </div>

    <?php if ($error_msg): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" id="form-test">

        <!-- Credenciales -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-key" style="color:#0ea5e9"></i>
                Credenciales
                <?php if ($cfg_url): ?>
                <span class="from-config-badge"><i class="fas fa-check"></i> pre-cargado desde config/nubefact.php</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="credentials-grid">
                    <div class="form-group">
                        <label for="nubefact_url">URL del endpoint</label>
                        <input type="url" id="nubefact_url" name="nubefact_url"
                            value="<?= htmlspecialchars($url_usada) ?>"
                            placeholder="https://api.nubefact.com/api/v1/..."
                            autocomplete="off" spellcheck="false">
                        <div class="hint">URL completa incluyendo el identificador de empresa.</div>
                    </div>
                    <div class="form-group">
                        <label for="nubefact_token">Token de autorización</label>
                        <div class="token-wrap">
                            <input type="password" id="nubefact_token" name="nubefact_token"
                                value="<?= htmlspecialchars($token_usado) ?>"
                                placeholder="Token de la cuenta Nubefact"
                                autocomplete="off" spellcheck="false">
                            <button type="button" class="toggle-token" onclick="toggleToken()" title="Mostrar/ocultar token">
                                <i class="fas fa-eye" id="icon-token"></i>
                            </button>
                        </div>
                        <div class="hint">Se enviará como <code>Authorization: Token &lt;valor&gt;</code></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payload -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-code" style="color:#0ea5e9"></i>
                Payload JSON
                <span style="margin-left:auto;display:flex;gap:8px">
                    <button type="button" class="btn btn-secondary" style="padding:6px 14px;font-size:.8rem"
                        onclick="resetPayload()">
                        <i class="fas fa-undo"></i> Restaurar ejemplo
                    </button>
                    <button type="button" class="btn btn-secondary" style="padding:6px 14px;font-size:.8rem"
                        onclick="formatPayload()">
                        <i class="fas fa-magic"></i> Formatear
                    </button>
                </span>
            </div>
            <div class="card-body">
                <div class="alert alert-info" style="margin-bottom:16px">
                    <i class="fas fa-info-circle"></i>
                    <span>El payload de abajo es una boleta de prueba. Puedes editarlo libremente antes de enviar.
                    Cambia <strong>serie</strong> y <strong>numero</strong> para evitar duplicados en Nubefact.</span>
                </div>
                <textarea id="payload" name="payload" spellcheck="false"><?= htmlspecialchars($payload_enviado) ?></textarea>
            </div>
        </div>

        <!-- Acciones -->
        <div class="btn-row" style="margin-bottom:24px">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Enviar a Nubefact
            </button>
            <button type="button" class="btn btn-secondary" onclick="validarJSON()">
                <i class="fas fa-check-square"></i> Validar JSON
            </button>
            <button type="reset" class="btn btn-danger" onclick="return confirm('¿Limpiar el formulario?')">
                <i class="fas fa-trash"></i> Limpiar
            </button>
        </div>

    </form>

    <!-- ======================================================
         RESULTADO
         ====================================================== -->
    <?php if ($resultado !== null): ?>
    <?php
        $ok       = $resultado['http_code'] === 200 && !empty($resultado['data']['enlace_del_pdf']);
        $data     = $resultado['data'];
        $http     = $resultado['http_code'];
        $httpClass = $http === 200 ? 'http-200' : ($http >= 500 ? 'http-5xx' : ($http >= 400 ? 'http-4xx' : 'http-err'));
    ?>
    <div class="card">
        <div class="card-header" style="background:<?= $ok ? '#f0fdf4' : '#fef2f2' ?>">
            <i class="fas <?= $ok ? 'fa-check-circle' : 'fa-times-circle' ?>"
               style="color:<?= $ok ? '#16a34a' : '#dc2626' ?>"></i>
            Respuesta de Nubefact
        </div>
        <div class="card-body">

            <!-- Meta -->
            <div class="result-meta">
                <?php if ($resultado['curl_error']): ?>
                <span class="http-badge http-err"><i class="fas fa-wifi-slash"></i> Error de red</span>
                <?php else: ?>
                <span class="http-badge <?= $httpClass ?>">HTTP <?= $http ?></span>
                <?php endif; ?>
                <span class="time-badge"><i class="fas fa-clock"></i> <?= $resultado['total_time'] ?> ms</span>
                <span class="time-badge"><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i:s') ?></span>
            </div>

            <?php if ($resultado['curl_error']): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><strong>Error de conexión cURL:</strong> <?= htmlspecialchars($resultado['curl_error']) ?></span>
            </div>

            <?php elseif ($ok): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><strong>Comprobante aceptado.</strong>
                <?= htmlspecialchars($data['sunat_description'] ?? 'La operación fue procesada correctamente por Nubefact y SUNAT.') ?></span>
            </div>
            <div class="data-grid">
                <?php if (!empty($data['numero_de_comprobante'])): ?>
                <div class="data-item">
                    <div class="data-label">Número de comprobante</div>
                    <div class="data-value"><?= htmlspecialchars($data['numero_de_comprobante']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['aceptada_por_sunat'])): ?>
                <div class="data-item">
                    <div class="data-label">Aceptada por SUNAT</div>
                    <div class="data-value success"><?= htmlspecialchars($data['aceptada_por_sunat']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['enlace_del_pdf'])): ?>
                <div class="data-item full">
                    <div class="data-label">PDF</div>
                    <div class="data-value">
                        <a href="<?= htmlspecialchars($data['enlace_del_pdf']) ?>" target="_blank">
                            <i class="fas fa-file-pdf"></i> <?= htmlspecialchars($data['enlace_del_pdf']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['enlace_del_xml'])): ?>
                <div class="data-item full">
                    <div class="data-label">XML</div>
                    <div class="data-value">
                        <a href="<?= htmlspecialchars($data['enlace_del_xml']) ?>" target="_blank">
                            <i class="fas fa-file-code"></i> <?= htmlspecialchars($data['enlace_del_xml']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['cadena_para_codigo_qr'])): ?>
                <div class="data-item full">
                    <div class="data-label">Cadena QR</div>
                    <div class="data-value" style="font-size:.78rem;font-family:monospace">
                        <?= htmlspecialchars($data['cadena_para_codigo_qr']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <?php
                $msg = '';
                if (!empty($data['errors']) && is_array($data['errors'])) {
                    $msg = implode(' / ', $data['errors']);
                } elseif (!empty($data['message'])) {
                    $msg = $data['message'];
                } elseif (!empty($resultado['raw'])) {
                    $msg = $resultado['raw'];
                } else {
                    $msg = "Error HTTP $http sin cuerpo de respuesta.";
                }
                $isAuth = $http === 401 || $http === 403;
            ?>
            <div class="alert <?= $isAuth ? 'alert-warn' : 'alert-error' ?>">
                <i class="fas <?= $isAuth ? 'fa-lock' : 'fa-times-circle' ?>"></i>
                <div>
                    <?php if ($isAuth): ?>
                    <strong>Error de autenticación (HTTP <?= $http ?>):</strong> Verifica que el token sea correcto y que la URL incluya el identificador de empresa.
                    <?php else: ?>
                    <strong>Error:</strong> <?= htmlspecialchars($msg) ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <hr class="divider">

            <details <?= !$ok ? 'open' : '' ?>>
                <summary><i class="fas fa-code"></i> Ver respuesta JSON completa</summary>
                <pre><?= htmlspecialchars(
                    json_encode(
                        !empty($data) ? $data : ['raw_response' => $resultado['raw']],
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    )
                ) ?></pre>
            </details>
        </div>
    </div>
    <?php endif; ?>

    <!-- Info -->
    <div class="card" style="background:#f8fafc">
        <div class="card-body" style="font-size:.8rem;color:#64748b">
            <strong style="display:block;margin-bottom:8px;color:#475569">
                <i class="fas fa-info-circle"></i> Notas
            </strong>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:6px">
                <li><i class="fas fa-dot-circle" style="font-size:.5rem;vertical-align:middle;margin-right:6px"></i>
                    Los precios en el payload deben ser <strong>con IGV incluido</strong>. El <code>valor_unitario</code> = <code>precio / 1.18</code>.</li>
                <li><i class="fas fa-dot-circle" style="font-size:.5rem;vertical-align:middle;margin-right:6px"></i>
                    Cambia <strong>numero</strong> en cada prueba para evitar errores de correlativo duplicado.</li>
                <li><i class="fas fa-dot-circle" style="font-size:.5rem;vertical-align:middle;margin-right:6px"></i>
                    <code>tipo_de_comprobante: 2</code> = Boleta, <code>1</code> = Factura.</li>
                <li><i class="fas fa-shield-alt" style="margin-right:6px"></i>
                    Esta página es solo para pruebas internas. No expongas las credenciales.</li>
            </ul>
        </div>
    </div>

</div>

<script>
const payloadDefault = <?= json_encode($payload_default) ?>;

function toggleToken() {
    const input = document.getElementById('nubefact_token');
    const icon  = document.getElementById('icon-token');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function resetPayload() {
    if (confirm('¿Restaurar el payload de ejemplo? Se perderán los cambios actuales.')) {
        document.getElementById('payload').value = payloadDefault;
    }
}

function formatPayload() {
    const ta = document.getElementById('payload');
    try {
        const obj = JSON.parse(ta.value);
        ta.value = JSON.stringify(obj, null, 2);
    } catch (e) {
        alert('JSON inválido: ' + e.message);
    }
}

function validarJSON() {
    const ta = document.getElementById('payload');
    try {
        JSON.parse(ta.value);
        alert('JSON válido.');
    } catch (e) {
        alert('JSON inválido: ' + e.message);
    }
}

// Tab key inserta 2 espacios en el textarea
document.getElementById('payload').addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        const end   = this.selectionEnd;
        this.value  = this.value.substring(0, start) + '  ' + this.value.substring(end);
        this.selectionStart = this.selectionEnd = start + 2;
    }
});
</script>
</body>
</html>
