<?php
// ============================================================
// ARCHIVO: farmacia/modules/caja/index.php
// MÓDULO:  Caja
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$current_module = 'caja';
$current_page   = 'caja';
$page_title     = 'Caja — FarmaSystem';
$breadcrumb     = '<strong>Caja</strong>';

$db = getDB();

// Estado actual de la caja (server-side para renderizado inicial)
$caja = $db->query("
    SELECT * FROM cajas WHERE estado = 'abierta'
    ORDER BY apertura_at DESC LIMIT 1
")->fetch();

// Usuarios previos para datalist
$usuarios_previos = $db->query("
    SELECT DISTINCT usuario_apertura FROM cajas
    WHERE usuario_apertura IS NOT NULL AND usuario_apertura != ''
    ORDER BY usuario_apertura
")->fetchAll(PDO::FETCH_COLUMN);

include '../../includes/header.php';
?>

<?php if (!$caja): ?>
<!-- ============================================================
     ESTADO: CAJA CERRADA
     ============================================================ -->

<div class="page-header">
    <div>
        <div class="page-title"><i class="fas fa-cash-register" style="color:var(--primary);margin-right:8px"></i>Caja</div>
        <div class="page-subtitle">Apertura y cierre de turno</div>
    </div>
</div>

<!-- Formulario de apertura centrado -->
<div style="display:flex;justify-content:center;margin:20px 0 32px">
    <div class="card" style="max-width:440px;width:100%;text-align:center">

        <div style="padding:32px 32px 24px">
            <div style="width:72px;height:72px;background:var(--surface-2);border-radius:50%;
                        display:flex;align-items:center;justify-content:center;margin:0 auto 20px;
                        border:2px solid var(--border)">
                <i class="fas fa-lock" style="font-size:1.8rem;color:var(--text-light)"></i>
            </div>
            <div style="font-size:1.15rem;font-weight:700;margin-bottom:6px">Caja Cerrada</div>
            <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:28px">
                Apertura la caja para comenzar a registrar ventas
            </div>

            <div class="form-group" style="text-align:left">
                <label class="form-label">Usuario que apertura <span style="color:var(--danger)">*</span></label>
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="ap-usuario" class="form-control"
                        placeholder="Nombre del cajero" autocomplete="off"
                        list="usuarios-list" style="padding-left:36px">
                </div>
                <datalist id="usuarios-list">
                    <?php foreach ($usuarios_previos as $u): ?>
                    <option value="<?= htmlspecialchars($u) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group" style="text-align:left">
                <label class="form-label">Monto inicial en caja</label>
                <div class="input-group">
                    <span class="input-group-icon" style="font-weight:700;font-size:.85rem;color:var(--text)">S/</span>
                    <input type="number" id="ap-monto" class="form-control"
                        value="0.00" min="0" step="0.50" placeholder="0.00"
                        style="font-size:1.2rem;font-weight:700;text-align:right">
                </div>
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap" id="montos-rapidos">
                    <?php foreach ([0, 50, 100, 200, 500] as $m): ?>
                    <button type="button" class="btn btn-outline btn-sm"
                        onclick="document.getElementById('ap-monto').value='<?= $m ?>.00'">
                        S/ <?= $m ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="btn btn-success w-100 btn-lg" onclick="aperturarCaja()" id="btn-aperturar"
                style="margin-top:8px;font-size:1rem">
                <i class="fas fa-unlock"></i> Aperturar Caja
            </button>
        </div>
    </div>
</div>

<?php else:
    // Calcular resumen del turno
    $ventas_stmt = $db->prepare("
        SELECT COUNT(*) AS count, COALESCE(SUM(total),0) AS total
        FROM ventas WHERE caja_id = :id AND estado = 'completada'
    ");
    $ventas_stmt->execute([':id' => $caja['id']]);
    $ventas = $ventas_stmt->fetch();

    $movs_stmt = $db->prepare("
        SELECT tipo, COALESCE(SUM(monto),0) AS total
        FROM caja_movimientos WHERE caja_id = :id GROUP BY tipo
    ");
    $movs_stmt->execute([':id' => $caja['id']]);
    $ingresos_adic = 0; $egresos = 0;
    foreach ($movs_stmt->fetchAll() as $r) {
        if ($r['tipo'] === 'ingreso') $ingresos_adic = floatval($r['total']);
        else                          $egresos        = floatval($r['total']);
    }
    $saldo_esperado = floatval($caja['saldo_inicial']) + floatval($ventas['total']) + $ingresos_adic - $egresos;
    $apertura_dt    = new DateTime($caja['apertura_at']);
?>

<!-- ============================================================
     ESTADO: CAJA ABIERTA
     ============================================================ -->

<div class="page-header">
    <div>
        <div class="page-title">
            <i class="fas fa-cash-register" style="color:var(--primary);margin-right:8px"></i>Caja
        </div>
        <div class="page-subtitle">
            <span style="color:var(--success);font-weight:600">
                <i class="fas fa-circle" style="font-size:.55rem;vertical-align:middle;margin-right:5px"></i>Abierta
            </span>
            &nbsp;·&nbsp; Usuario: <strong><?= htmlspecialchars($caja['usuario_apertura']) ?></strong>
            &nbsp;·&nbsp; Desde: <?= $apertura_dt->format('d/m/Y H:i') ?>
        </div>
    </div>
    <div class="page-actions">
        <button class="btn btn-danger" onclick="openCerrarModal()">
            <i class="fas fa-lock"></i> Cerrar Caja
        </button>
    </div>
</div>

<!-- Stats del turno -->
<div class="stat-cards" style="margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-cash-register"></i></div>
        <div>
            <div class="stat-value"><?= intval($ventas['count']) ?></div>
            <div class="stat-label">Ventas del turno</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div>
            <div class="stat-value">S/ <?= number_format(floatval($ventas['total']), 2) ?></div>
            <div class="stat-label">Ingresos por ventas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-hand-holding-usd"></i></div>
        <div>
            <div class="stat-value">S/ <?= number_format($saldo_esperado, 2) ?></div>
            <div class="stat-label">Total esperado en caja</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $egresos > 0 ? 'red' : 'blue' ?>"><i class="fas fa-exchange-alt"></i></div>
        <div>
            <div class="stat-value"><?= $ingresos_adic + $egresos > 0 ? ($ingresos_adic + $egresos) : '0' ?></div>
            <div class="stat-label">Movimientos adicionales</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

    <!-- Columna izquierda: movimientos -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Movimientos del turno</div>
                <span id="movs-count" style="font-size:.82rem;color:var(--text-muted)"></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Usuario</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody id="movs-body">
                        <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-light)">
                            <i class="fas fa-spinner fa-spin"></i>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Columna derecha: resumen + nuevo movimiento -->
    <div style="display:flex;flex-direction:column;gap:16px">

        <!-- Resumen financiero -->
        <div class="card">
            <div class="card-header" style="padding-bottom:0">
                <div class="card-title" style="font-size:.9rem">Resumen financiero</div>
            </div>
            <div style="padding:0 16px 16px;font-size:.88rem">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-muted)">Monto inicial</span>
                    <span>S/ <?= number_format(floatval($caja['saldo_inicial']), 2) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-muted)">Ventas (<?= intval($ventas['count']) ?>)</span>
                    <span style="color:var(--success)">+ S/ <?= number_format(floatval($ventas['total']), 2) ?></span>
                </div>
                <?php if ($ingresos_adic > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-muted)">Ingresos adicionales</span>
                    <span style="color:var(--success)">+ S/ <?= number_format($ingresos_adic, 2) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($egresos > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-muted)">Egresos</span>
                    <span style="color:var(--danger)">- S/ <?= number_format($egresos, 2) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;padding:10px 0 0;font-weight:700;font-size:1rem">
                    <span>Total esperado</span>
                    <span style="color:var(--success)">S/ <?= number_format($saldo_esperado, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Nuevo movimiento -->
        <div class="card">
            <div class="card-header" style="padding-bottom:0">
                <div class="card-title" style="font-size:.9rem">Registrar movimiento</div>
            </div>
            <div style="padding:0 16px 16px">
                <div style="display:flex;gap:8px;margin-bottom:10px">
                    <label style="flex:1;display:flex;align-items:center;gap:8px;padding:9px 12px;
                                  border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;font-size:.83rem"
                           id="label-ingreso">
                        <input type="radio" name="mov-tipo" value="ingreso" id="mov-ingreso" checked
                               style="accent-color:var(--success)" onchange="updateTipoStyle()">
                        <span><i class="fas fa-arrow-down" style="color:var(--success)"></i> Ingreso</span>
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:8px;padding:9px 12px;
                                  border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;font-size:.83rem"
                           id="label-egreso">
                        <input type="radio" name="mov-tipo" value="egreso" id="mov-egreso"
                               style="accent-color:var(--danger)" onchange="updateTipoStyle()">
                        <span><i class="fas fa-arrow-up" style="color:var(--danger)"></i> Egreso</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size:.8rem">Concepto <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="mov-concepto" class="form-control" style="font-size:.85rem"
                        placeholder="Ej: Gasto de limpieza, Fondo de cambio...">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.8rem">Monto (S/) <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="mov-monto" class="form-control" placeholder="0.00"
                        min="0.01" step="0.01" style="font-size:.85rem">
                </div>

                <button class="btn btn-primary w-100" onclick="registrarMovimiento()" id="btn-mov" style="font-size:.85rem">
                    <i class="fas fa-plus"></i> Registrar
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ---- MODAL: Cerrar Caja ---- -->
<div class="modal-overlay" id="modal-cerrar">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-lock" style="color:var(--danger);margin-right:8px"></i>Cerrar Caja
            </h3>
            <button class="modal-close" onclick="closeModal('modal-cerrar')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:var(--surface-2);border-radius:var(--radius);padding:14px 16px;margin-bottom:16px;font-size:.87rem">
                <div style="display:flex;justify-content:space-between;padding:4px 0">
                    <span style="color:var(--text-muted)">Monto inicial</span>
                    <span>S/ <?= number_format(floatval($caja['saldo_inicial']), 2) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:4px 0">
                    <span style="color:var(--text-muted)">Ventas del turno</span>
                    <span style="color:var(--success)">+ S/ <?= number_format(floatval($ventas['total']), 2) ?></span>
                </div>
                <?php if ($ingresos_adic > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:4px 0">
                    <span style="color:var(--text-muted)">Ingresos adicionales</span>
                    <span style="color:var(--success)">+ S/ <?= number_format($ingresos_adic, 2) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($egresos > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:4px 0">
                    <span style="color:var(--text-muted)">Egresos</span>
                    <span style="color:var(--danger)">- S/ <?= number_format($egresos, 2) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0 0;border-top:1px solid var(--border);
                            margin-top:6px;font-weight:700">
                    <span>Total esperado</span>
                    <span style="color:var(--success)">S/ <?= number_format($saldo_esperado, 2) ?></span>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:8px">
                <label class="form-label">Monto contado físicamente (S/)</label>
                <div class="input-group">
                    <span class="input-group-icon" style="font-weight:700;font-size:.85rem">S/</span>
                    <input type="number" id="cierre-monto-real" class="form-control"
                        value="<?= number_format($saldo_esperado, 2) ?>"
                        min="0" step="0.01" style="font-size:1.1rem;font-weight:700"
                        oninput="calcularDiferencia()">
                </div>
            </div>
            <div id="diferencia-info" style="font-size:.85rem;text-align:right;min-height:20px"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-cerrar')">Cancelar</button>
            <button class="btn btn-danger" id="btn-confirmar-cierre" onclick="confirmarCierre()">
                <i class="fas fa-lock"></i> Confirmar Cierre
            </button>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- ======== HISTORIAL DE SESIONES (siempre visible abajo) ======== -->
<div class="card" style="margin-top:28px">
    <div class="card-header">
        <div class="card-title">Historial de sesiones</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Apertura</th>
                    <th>Cierre</th>
                    <th class="text-right">Monto Inicial</th>
                    <th class="text-right">Ventas</th>
                    <th class="text-right">Monto Final</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="historial-body">
                <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-light)">
                    <i class="fas fa-spinner fa-spin"></i>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
const BASE    = '../../';
const CAJA_ID = <?= $caja ? intval($caja['id']) : 'null' ?>;
const USUARIO = <?= $caja ? json_encode(htmlspecialchars($caja['usuario_apertura'])) : 'null' ?>;

document.addEventListener('DOMContentLoaded', () => {
    loadHistorial();
    <?php if ($caja): ?>
    loadMovimientos();
    updateTipoStyle();
    <?php endif; ?>
});

// ---- Apertura ----
function aperturarCaja() {
    const usuario = document.getElementById('ap-usuario').value.trim();
    const monto   = parseFloat(document.getElementById('ap-monto').value) || 0;

    if (!usuario) { showToast('Ingresa el nombre del usuario', 'error'); return; }

    const btn = document.getElementById('btn-aperturar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aperturando...';

    fetch(BASE + 'modules/caja/api.php?action=aperturar', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ usuario, monto_inicial: monto }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-unlock"></i> Aperturar Caja';
            showToast(d.message, 'error');
            return;
        }
        showToast('¡Caja aperturada correctamente!', 'success');
        setTimeout(() => location.reload(), 800);
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-unlock"></i> Aperturar Caja';
        showToast('Error al aperturar caja', 'error');
    });
}

// ---- Cierre ----
function openCerrarModal() {
    calcularDiferencia();
    openModal('modal-cerrar');
    setTimeout(() => document.getElementById('cierre-monto-real').select(), 100);
}

function calcularDiferencia() {
    const esperado = <?= $caja ? $saldo_esperado : 0 ?>;
    const real     = parseFloat(document.getElementById('cierre-monto-real')?.value) || 0;
    const diff     = real - esperado;
    const el       = document.getElementById('diferencia-info');
    if (!el) return;
    if (diff === 0) {
        el.innerHTML = '<span style="color:var(--success)"><i class="fas fa-check"></i> Sin diferencia</span>';
    } else {
        const signo = diff > 0 ? '+' : '';
        const color = diff > 0 ? 'var(--success)' : 'var(--danger)';
        el.innerHTML = `<span style="color:${color}">Diferencia: ${signo}S/ ${Math.abs(diff).toFixed(2)}</span>`;
    }
}

function confirmarCierre() {
    const monto_real = parseFloat(document.getElementById('cierre-monto-real').value) || 0;
    const btn = document.getElementById('btn-confirmar-cierre');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cerrando...';

    fetch(BASE + 'modules/caja/api.php?action=cerrar', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ caja_id: CAJA_ID, monto_real }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Confirmar Cierre';
            showToast(d.message, 'error');
            return;
        }
        showToast('Caja cerrada correctamente', 'success');
        setTimeout(() => location.reload(), 800);
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Confirmar Cierre';
        showToast('Error al cerrar caja', 'error');
    });
}

// ---- Movimientos ----
function loadMovimientos() {
    if (!CAJA_ID) return;
    fetch(BASE + `modules/caja/api.php?action=movimientos&caja_id=${CAJA_ID}`)
        .then(r => r.json())
        .then(data => {
            const count = document.getElementById('movs-count');
            if (count) count.textContent = data.length + ' movimiento(s)';
            const tbody = document.getElementById('movs-body');
            if (!tbody) return;
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-light)">Sin movimientos adicionales registrados</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(m => {
                const dt    = new Date(m.created_at);
                const hora  = dt.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                const color = m.tipo === 'ingreso' ? 'var(--success)' : 'var(--danger)';
                const icono = m.tipo === 'ingreso' ? 'arrow-down' : 'arrow-up';
                const signo = m.tipo === 'ingreso' ? '+' : '-';
                return `<tr>
                    <td style="font-size:.82rem;color:var(--text-muted)">${hora}</td>
                    <td>
                        <span class="badge" style="background:${m.tipo === 'ingreso' ? 'var(--success-light,#dcfce7)' : '#fee2e2'};
                              color:${color};font-size:.75rem">
                            <i class="fas fa-${icono}"></i> ${m.tipo}
                        </span>
                    </td>
                    <td style="font-size:.85rem">${m.concepto}</td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${m.usuario}</td>
                    <td class="text-right" style="font-weight:700;color:${color}">
                        ${signo} S/ ${parseFloat(m.monto).toFixed(2)}
                    </td>
                </tr>`;
            }).join('');
        });
}

function updateTipoStyle() {
    const esIngreso = document.getElementById('mov-ingreso')?.checked;
    const lbI = document.getElementById('label-ingreso');
    const lbE = document.getElementById('label-egreso');
    if (lbI) lbI.style.borderColor = esIngreso  ? 'var(--success)' : 'var(--border)';
    if (lbE) lbE.style.borderColor = !esIngreso ? 'var(--danger)'  : 'var(--border)';
}

function registrarMovimiento() {
    const tipo     = document.querySelector('input[name="mov-tipo"]:checked')?.value;
    const concepto = document.getElementById('mov-concepto').value.trim();
    const monto    = parseFloat(document.getElementById('mov-monto').value);

    if (!concepto)           { showToast('El concepto es requerido', 'error'); return; }
    if (!monto || monto <= 0){ showToast('Ingresa un monto válido', 'error'); return; }

    const btn = document.getElementById('btn-mov');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(BASE + 'modules/caja/api.php?action=registrar_movimiento', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ caja_id: CAJA_ID, tipo, monto, concepto, usuario: USUARIO }),
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i> Registrar';
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Movimiento registrado', 'success');
        document.getElementById('mov-concepto').value = '';
        document.getElementById('mov-monto').value    = '';
        loadMovimientos();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i> Registrar';
        showToast('Error al registrar movimiento', 'error');
    });
}

// ---- Historial de sesiones ----
function loadHistorial() {
    fetch(BASE + 'modules/caja/api.php?action=historial')
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('historial-body');
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-light)">Sin sesiones registradas</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(c => {
                const apertura = c.apertura_at ? new Date(c.apertura_at).toLocaleString('es-PE', {dateStyle:'short',timeStyle:'short'}) : '—';
                const cierre   = c.cierre_at   ? new Date(c.cierre_at).toLocaleString('es-PE',   {dateStyle:'short',timeStyle:'short'}) : '—';
                const badge    = (c.estado === 'abierta')
                    ? '<span class="badge badge-success">Abierta</span>'
                    : '<span class="badge badge-gray">Cerrada</span>';
                return `<tr>
                    <td style="font-weight:500">${c.usuario_apertura || '—'}</td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${apertura}</td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${cierre}</td>
                    <td class="text-right">S/ ${parseFloat(c.saldo_inicial).toFixed(2)}</td>
                    <td class="text-right" style="color:var(--success)">${c.total_ventas} venta(s)</td>
                    <td class="text-right" style="font-weight:700">S/ ${parseFloat(c.saldo_actual).toFixed(2)}</td>
                    <td>${badge}</td>
                </tr>`;
            }).join('');
        });
}

// ---- Helpers ----
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type = 'info') {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>

<?php include '../../includes/footer.php'; ?>
