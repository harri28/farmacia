<?php
// ============================================================
// ARCHIVO: farmacia/test/facturacion_test.php
// DESCRIPCIÓN: Prueba de consulta de RUC / DNI para facturación
// Fuentes:  1) Migo.pe (con credenciales del sistema)
//           2) apis.net.pe (API pública gratuita - fallback)
// ============================================================

define('MIGO_URL',   'https://api.migo.pe/api/v1');
define('MIGO_TOKEN', 'QmnK905p0rMKp1aPyGY0UA32N3dfxByPHEjqEHiG69FdQqN3b9XxqX8y3dmq');

// ---- Funciones de consulta ----

function consultar_ruc_migo(string $ruc): array {
    $payload = json_encode([
        'ruc' => $ruc,
    ]);
    $ch = curl_init(MIGO_URL . '/ruc');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . MIGO_TOKEN,
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return [
        'http_code' => $code,
        'curl_error'=> $err,
        'raw'       => $raw,
        'data'      => json_decode($raw, true) ?? [],
    ];
}

function consultar_ruc_apisperu(string $ruc): array {
    $url = 'https://api.apis.net.pe/v2/sunat/ruc?numero=' . urlencode($ruc);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'FarmaSystem/1.0',
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return [
        'http_code' => $code,
        'curl_error'=> $err,
        'raw'       => $raw,
        'data'      => json_decode($raw, true) ?? [],
    ];
}

function consultar_dni_apisperu(string $dni): array {
    $url = 'https://api.apis.net.pe/v2/reniec/dni?numero=' . urlencode($dni);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'FarmaSystem/1.0',
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return [
        'http_code' => $code,
        'curl_error'=> $err,
        'raw'       => $raw,
        'data'      => json_decode($raw, true) ?? [],
    ];
}

// ---- Procesar formulario ----
$tipo      = $_POST['tipo']    ?? '';
$numero    = trim($_POST['numero'] ?? '');
$resultados = [];
$error_msg  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $numero) {

    if ($tipo === 'ruc') {
        if (!preg_match('/^\d{11}$/', $numero)) {
            $error_msg = 'El RUC debe tener exactamente 11 dígitos numéricos.';
        } else {
            $resultados['migo']      = consultar_ruc_migo($numero);
            $resultados['apisperu']  = consultar_ruc_apisperu($numero);
        }
    } elseif ($tipo === 'dni') {
        if (!preg_match('/^\d{8}$/', $numero)) {
            $error_msg = 'El DNI debe tener exactamente 8 dígitos numéricos.';
        } else {
            $resultados['apisperu'] = consultar_dni_apisperu($numero);
        }
    }
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Facturación — FarmaSystem</title>
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
        .container { max-width: 820px; margin: 0 auto; }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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

        /* Formulario */
        .tipo-tabs { display: flex; gap: 10px; margin-bottom: 18px; }
        .tipo-tab {
            flex: 1;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            font-size: .88rem;
            font-weight: 600;
            transition: .15s;
            background: white;
        }
        .tipo-tab.active    { border-color: #6366f1; background: #eef2ff; color: #4f46e5; }
        .tipo-tab:hover:not(.active) { border-color: #94a3b8; }

        .input-row { display: flex; gap: 10px; }
        .input-row input {
            flex: 1;
            padding: 11px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 9px;
            font-size: 1rem;
            letter-spacing: .05rem;
            outline: none;
            transition: .15s;
        }
        .input-row input:focus { border-color: #6366f1; }
        .btn-buscar {
            padding: 11px 24px;
            background: #6366f1;
            color: white;
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
        .btn-buscar:hover { background: #4f46e5; }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 9px;
            font-size: .87rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .alert-warn    { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

        /* Resultado */
        .result-section { margin-bottom: 20px; }
        .result-section h3 {
            font-size: .85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .badge-fuente {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .03rem;
        }
        .badge-nubefact { background: #dbeafe; color: #2563eb; }
        .badge-sunat    { background: #dcfce7; color: #15803d; }
        .badge-reniec   { background: #fef3c7; color: #b45309; }
        .badge-error    { background: #fee2e2; color: #dc2626; }
        .badge-ok       { background: #dcfce7; color: #15803d; }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .data-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .data-item.full { grid-column: 1 / -1; }
        .data-label { font-size: .72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .04rem; margin-bottom: 3px; }
        .data-value { font-size: .92rem; font-weight: 600; color: #1e293b; }
        .data-value.success { color: #16a34a; }
        .data-value.danger  { color: #dc2626; }
        .data-value.warn    { color: #d97706; }

        /* JSON raw */
        details { margin-top: 12px; }
        summary {
            cursor: pointer;
            font-size: .8rem;
            color: #6366f1;
            font-weight: 600;
            padding: 6px 0;
        }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            border-radius: 9px;
            padding: 14px;
            font-size: .75rem;
            overflow-x: auto;
            margin-top: 8px;
            line-height: 1.5;
        }

        /* HTTP badge */
        .http-badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 700;
        }
        .http-200 { background: #dcfce7; color: #15803d; }
        .http-4xx { background: #fef2f2; color: #dc2626; }
        .http-err { background: #fef3c7; color: #b45309; }

        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 18px 0;
        }

        /* Ejemplos */
        .ejemplos { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
        .ej-btn {
            padding: 5px 12px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: .78rem;
            cursor: pointer;
            color: #475569;
            transition: .12s;
        }
        .ej-btn:hover { background: #e2e8f0; }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="page-header">
        <i class="fas fa-file-invoice"></i>
        <div>
            <h1>Test de Facturación Electrónica</h1>
            <p>Consulta RUC (SUNAT) y DNI (RENIEC) · Migo.pe + apis.net.pe</p>
        </div>
    </div>

    <!-- Formulario -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-search" style="color:#6366f1"></i>
            Consultar RUC o DNI
        </div>
        <div class="card-body">
            <form method="POST" id="form-consulta">

                <!-- Selector de tipo -->
                <div class="tipo-tabs">
                    <label class="tipo-tab <?= ($tipo !== 'dni') ? 'active' : '' ?>" id="tab-ruc">
                        <input type="radio" name="tipo" value="ruc" style="display:none"
                            <?= ($tipo !== 'dni') ? 'checked' : '' ?> onchange="cambiarTipo('ruc')">
                        <i class="fas fa-building" style="margin-right:6px"></i>RUC
                        <div style="font-size:.72rem;font-weight:400;margin-top:3px;opacity:.7">Empresa · 11 dígitos</div>
                    </label>
                    <label class="tipo-tab <?= ($tipo === 'dni') ? 'active' : '' ?>" id="tab-dni">
                        <input type="radio" name="tipo" value="dni" style="display:none"
                            <?= ($tipo === 'dni') ? 'checked' : '' ?> onchange="cambiarTipo('dni')">
                        <i class="fas fa-id-card" style="margin-right:6px"></i>DNI
                        <div style="font-size:.72rem;font-weight:400;margin-top:3px;opacity:.7">Persona · 8 dígitos</div>
                    </label>
                </div>

                <!-- Input + botón -->
                <div class="input-row">
                    <input type="text" name="numero" id="input-numero"
                        value="<?= htmlspecialchars($numero) ?>"
                        placeholder="<?= ($tipo === 'dni') ? 'Ej: 45678901' : 'Ej: 20100055858' ?>"
                        maxlength="<?= ($tipo === 'dni') ? '8' : '11' ?>"
                        pattern="\d*" inputmode="numeric" autocomplete="off">
                    <button type="submit" class="btn-buscar">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                </div>

                <!-- Ejemplos RUC -->
                <div id="ejemplos-ruc" style="<?= ($tipo === 'dni') ? 'display:none' : '' ?>">
                    <div style="font-size:.75rem;color:#94a3b8;margin-top:10px;margin-bottom:5px">Ejemplos rápidos:</div>
                    <div class="ejemplos">
                        <button type="button" class="ej-btn" onclick="setNumero('20100055858')">20100055858 — Bayer</button>
                        <button type="button" class="ej-btn" onclick="setNumero('20100070970')">20100070970 — SUNAT</button>
                        <button type="button" class="ej-btn" onclick="setNumero('20503840703')">20503840703 — Farmindustria</button>
                        <button type="button" class="ej-btn" onclick="setNumero('10000000001')">10000000001 — Persona natural</button>
                    </div>
                </div>

                <!-- Ejemplos DNI -->
                <div id="ejemplos-dni" style="<?= ($tipo !== 'dni') ? 'display:none' : '' ?>">
                    <div style="font-size:.75rem;color:#94a3b8;margin-top:10px;margin-bottom:5px">Ejemplos rápidos:</div>
                    <div class="ejemplos">
                        <button type="button" class="ej-btn" onclick="setNumero('45678901')">45678901</button>
                        <button type="button" class="ej-btn" onclick="setNumero('32145678')">32145678</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <?php if ($error_msg): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
    </div>
    <?php endif; ?>

    <!-- ======================================================
         RESULTADOS
         ====================================================== -->
    <?php if (!empty($resultados)): ?>

    <?php
    // ---- Resultado Migo.pe (solo RUC) ----
    if (isset($resultados['migo'])):
        $nf   = $resultados['migo'];
        $nfOk = $nf['http_code'] === 200 && !empty($nf['data']);
        $nfD  = $nf['data'];
    ?>
    <div class="card result-section">
        <div class="card-header">
            <i class="fas fa-cloud" style="color:#2563eb"></i>
            Resultado Migo.pe
            <span class="badge-fuente badge-nubefact">Migo.pe API</span>
            <span class="http-badge <?= $nf['http_code'] === 200 ? 'http-200' : 'http-4xx' ?>" style="margin-left:auto">
                HTTP <?= $nf['curl_error'] ? 'ERR' : $nf['http_code'] ?>
            </span>
        </div>
        <div class="card-body">

            <?php if ($nf['curl_error']): ?>
            <div class="alert alert-error"><i class="fas fa-wifi"></i> Error de conexión: <?= htmlspecialchars($nf['curl_error']) ?></div>

            <?php elseif ($nfOk && !empty($nfD['razon_social'])): ?>
            <div class="alert alert-success" style="margin-bottom:16px">
                <i class="fas fa-check-circle"></i> Consulta exitosa
            </div>
            <div class="data-grid">
                <div class="data-item full">
                    <div class="data-label">Razón Social</div>
                    <div class="data-value" style="font-size:1.05rem"><?= htmlspecialchars($nfD['razon_social'] ?? '—') ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">RUC</div>
                    <div class="data-value"><?= htmlspecialchars($nfD['ruc'] ?? $numero) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Tipo Contribuyente</div>
                    <div class="data-value"><?= htmlspecialchars($nfD['tipo_contribuyente'] ?? '—') ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Estado</div>
                    <div class="data-value <?= strtoupper($nfD['estado'] ?? '') === 'ACTIVO' ? 'success' : 'danger' ?>">
                        <?= htmlspecialchars($nfD['estado'] ?? '—') ?>
                    </div>
                </div>
                <div class="data-item">
                    <div class="data-label">Condición</div>
                    <div class="data-value <?= strtoupper($nfD['condicion'] ?? '') === 'HABIDO' ? 'success' : 'warn' ?>">
                        <?= htmlspecialchars($nfD['condicion'] ?? '—') ?>
                    </div>
                </div>
                <?php if (!empty($nfD['direccion'])): ?>
                <div class="data-item full">
                    <div class="data-label">Dirección Fiscal</div>
                    <div class="data-value"><?= htmlspecialchars($nfD['direccion']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($nfD['ubigeo_sunat'])): ?>
                <div class="data-item">
                    <div class="data-label">Departamento / Provincia</div>
                    <div class="data-value"><?= htmlspecialchars(($nfD['departamento'] ?? '') . ' / ' . ($nfD['provincia'] ?? '')) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <div class="alert alert-warn">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($nfD['message'] ?? $nfD['errors'][0] ?? 'No se encontraron datos o el token no tiene acceso a esta consulta.') ?>
            </div>
            <?php endif; ?>

            <details>
                <summary><i class="fas fa-code"></i> Ver respuesta JSON completa</summary>
                <pre><?= htmlspecialchars(json_encode($nfD ?: ['raw' => $nf['raw']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </details>
        </div>
    </div>
    <?php endif; ?>

    <?php
    // ---- Resultado apis.net.pe (RUC o DNI) ----
    if (isset($resultados['apisperu'])):
        $ap   = $resultados['apisperu'];
        $apOk = $ap['http_code'] === 200 && !empty($ap['data']);
        $apD  = $ap['data'];
        $esDni = ($tipo === 'dni');
    ?>
    <div class="card result-section">
        <div class="card-header">
            <i class="fas fa-database" style="color:#15803d"></i>
            Resultado apis.net.pe
            <span class="badge-fuente <?= $esDni ? 'badge-reniec' : 'badge-sunat' ?>">
                <?= $esDni ? 'RENIEC' : 'SUNAT' ?> · apis.net.pe
            </span>
            <span class="http-badge <?= $ap['http_code'] === 200 ? 'http-200' : 'http-4xx' ?>" style="margin-left:auto">
                HTTP <?= $ap['curl_error'] ? 'ERR' : $ap['http_code'] ?>
            </span>
        </div>
        <div class="card-body">

            <?php if ($ap['curl_error']): ?>
            <div class="alert alert-error"><i class="fas fa-wifi"></i> Error de conexión: <?= htmlspecialchars($ap['curl_error']) ?></div>

            <?php elseif ($apOk && !$esDni && !empty($apD['razonSocial'])): ?>
            <!-- Resultado RUC -->
            <div class="alert alert-success" style="margin-bottom:16px">
                <i class="fas fa-check-circle"></i> Datos obtenidos de SUNAT
            </div>
            <div class="data-grid">
                <div class="data-item full">
                    <div class="data-label">Razón Social</div>
                    <div class="data-value" style="font-size:1.05rem"><?= htmlspecialchars($apD['razonSocial'] ?? '—') ?></div>
                </div>
                <?php if (!empty($apD['nombreComercial'])): ?>
                <div class="data-item full">
                    <div class="data-label">Nombre Comercial</div>
                    <div class="data-value"><?= htmlspecialchars($apD['nombreComercial']) ?></div>
                </div>
                <?php endif; ?>
                <div class="data-item">
                    <div class="data-label">RUC</div>
                    <div class="data-value"><?= htmlspecialchars($apD['ruc'] ?? $numero) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Tipo</div>
                    <div class="data-value"><?= htmlspecialchars($apD['tipo'] ?? '—') ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Estado</div>
                    <div class="data-value <?= strtoupper($apD['estado'] ?? '') === 'ACTIVO' ? 'success' : 'danger' ?>">
                        <i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:4px"></i>
                        <?= htmlspecialchars($apD['estado'] ?? '—') ?>
                    </div>
                </div>
                <div class="data-item">
                    <div class="data-label">Condición</div>
                    <div class="data-value <?= strtoupper($apD['condicion'] ?? '') === 'HABIDO' ? 'success' : 'warn' ?>">
                        <?= htmlspecialchars($apD['condicion'] ?? '—') ?>
                    </div>
                </div>
                <div class="data-item full">
                    <div class="data-label">Dirección Fiscal</div>
                    <div class="data-value"><?= htmlspecialchars($apD['direccion'] ?? '—') ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Distrito / Provincia</div>
                    <div class="data-value"><?= htmlspecialchars(($apD['distrito'] ?? '—') . ' / ' . ($apD['provincia'] ?? '—')) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Departamento</div>
                    <div class="data-value"><?= htmlspecialchars($apD['departamento'] ?? '—') ?></div>
                </div>
                <?php if (!empty($apD['actividadEconomica'])): ?>
                <div class="data-item full">
                    <div class="data-label">Actividad Económica</div>
                    <div class="data-value"><?= htmlspecialchars($apD['actividadEconomica']) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($apOk && $esDni && !empty($apD['nombreCompleto'])): ?>
            <!-- Resultado DNI -->
            <div class="alert alert-success" style="margin-bottom:16px">
                <i class="fas fa-check-circle"></i> Datos obtenidos de RENIEC
            </div>
            <div class="data-grid">
                <div class="data-item full">
                    <div class="data-label">Nombre Completo</div>
                    <div class="data-value" style="font-size:1.05rem"><?= htmlspecialchars($apD['nombreCompleto'] ?? '—') ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Nombres</div>
                    <div class="data-value"><?= htmlspecialchars($apD['nombres'] ?? '—') ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Apellidos</div>
                    <div class="data-value"><?= htmlspecialchars(($apD['apellidoPaterno'] ?? '') . ' ' . ($apD['apellidoMaterno'] ?? '')) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">DNI</div>
                    <div class="data-value"><?= htmlspecialchars($apD['dni'] ?? $numero) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Código de Verificación</div>
                    <div class="data-value"><?= htmlspecialchars($apD['codVerifica'] ?? '—') ?></div>
                </div>
            </div>

            <?php else: ?>
            <div class="alert alert-warn">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($apD['message'] ?? $apD['error'] ?? 'No se encontraron datos o la API requiere token para esta consulta.') ?>
            </div>
            <?php endif; ?>

            <details>
                <summary><i class="fas fa-code"></i> Ver respuesta JSON completa</summary>
                <pre><?= htmlspecialchars(json_encode($apD ?: ['raw' => $ap['raw']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </details>
        </div>
    </div>
    <?php endif; ?>

    <!-- ---- Resumen de uso ---- -->
    <?php if ($tipo === 'ruc' && !empty($resultados['apisperu']['data']['razonSocial'])): ?>
    <?php $d = $resultados['apisperu']['data']; ?>
    <div class="card">
        <div class="card-header" style="background:#f0fdf4">
            <i class="fas fa-copy" style="color:#16a34a"></i>
            Datos listos para usar en Factura
        </div>
        <div class="card-body">
            <div style="background:#f8fafc;border-radius:9px;padding:14px 16px;font-size:.88rem">
                <div style="display:grid;grid-template-columns:140px 1fr;gap:6px 12px">
                    <span style="color:#64748b">Razón Social:</span>
                    <strong><?= htmlspecialchars($d['razonSocial']) ?></strong>
                    <span style="color:#64748b">RUC:</span>
                    <strong><?= htmlspecialchars($d['ruc'] ?? $numero) ?></strong>
                    <span style="color:#64748b">Dirección:</span>
                    <strong><?= htmlspecialchars($d['direccion'] ?? '—') ?></strong>
                    <span style="color:#64748b">Estado:</span>
                    <strong class="<?= strtoupper($d['estado'] ?? '') === 'ACTIVO' ? '' : '' ?>"
                        style="color:<?= strtoupper($d['estado'] ?? '') === 'ACTIVO' ? '#16a34a' : '#dc2626' ?>">
                        <?= htmlspecialchars($d['estado'] ?? '—') ?> /
                        <?= htmlspecialchars($d['condicion'] ?? '—') ?>
                    </strong>
                </div>
            </div>
            <?php if (strtoupper($d['estado'] ?? '') !== 'ACTIVO' || strtoupper($d['condicion'] ?? '') !== 'HABIDO'): ?>
            <div class="alert alert-warn" style="margin-top:12px">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Advertencia:</strong> El RUC no tiene estado ACTIVO/HABIDO. SUNAT podría rechazar la factura.
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>

    <!-- Info panel -->
    <div class="card" style="background:#f8fafc">
        <div class="card-body" style="font-size:.8rem;color:#64748b">
            <strong style="display:block;margin-bottom:6px;color:#475569">
                <i class="fas fa-info-circle"></i> Sobre las fuentes de consulta
            </strong>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:5px">
                <li><span style="font-weight:600;color:#2563eb">Migo.pe:</span> Usa las credenciales del sistema. Consulta RUC directamente desde SUNAT.</li>
                <li><span style="font-weight:600;color:#15803d">apis.net.pe:</span> API pública gratuita. Puede requerir token para consultas en producción. Visita <code>https://apis.net.pe</code></li>
                <li><i class="fas fa-shield-alt"></i> Esta página es solo para pruebas internas. No expongas las credenciales.</li>
            </ul>
        </div>
    </div>

</div>

<script>
function cambiarTipo(tipo) {
    document.getElementById('tab-ruc').classList.toggle('active', tipo === 'ruc');
    document.getElementById('tab-dni').classList.toggle('active', tipo === 'dni');
    document.getElementById('ejemplos-ruc').style.display = tipo === 'ruc' ? '' : 'none';
    document.getElementById('ejemplos-dni').style.display = tipo === 'dni' ? '' : 'none';
    const input = document.getElementById('input-numero');
    input.placeholder = tipo === 'dni' ? 'Ej: 45678901' : 'Ej: 20100055858';
    input.maxLength   = tipo === 'dni' ? 8 : 11;
    input.value = '';
    input.focus();
}

function setNumero(n) {
    document.getElementById('input-numero').value = n;
    document.getElementById('form-consulta').submit();
}

// Solo permitir dígitos en el input
document.getElementById('input-numero').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
});
</script>
</body>
</html>
