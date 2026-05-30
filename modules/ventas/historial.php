<?php
// ============================================================
// ARCHIVO: farmacia/modules/ventas/historial.php
// MÓDULO:  Ventas → Historial de Ventas
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$current_module = 'ventas';
$current_page   = 'historial';
$page_title     = 'Historial de Ventas — FarmaSystem';
$breadcrumb     = '<strong>Ventas</strong> / Historial de Ventas';

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><i class="fas fa-history" style="color:var(--primary);margin-right:8px"></i>Historial de Ventas</div>
        <div class="page-subtitle">Consulta y gestiona todas las ventas registradas</div>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Venta
        </a>
    </div>
</div>

<!-- Stats del día -->
<div class="stat-cards" id="stats-container">
    <?php foreach (['blue','green','yellow','red'] as $c): ?>
    <div class="stat-card"><div class="stat-icon <?= $c ?>"><i class="fas fa-spinner fa-spin"></i></div><div><div class="stat-value">—</div><div class="stat-label">...</div></div></div>
    <?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="card" style="margin-bottom:20px">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:160px">
            <label class="form-label">Desde</label>
            <input type="date" id="f-desde" class="form-control" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:160px">
            <label class="form-label">Hasta</label>
            <input type="date" id="f-hasta" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:130px">
            <label class="form-label">Estado</label>
            <select class="form-control" id="f-estado">
                <option value="">Todos</option>
                <option value="completada">Completada</option>
                <option value="anulada">Anulada</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:2;min-width:200px">
            <label class="form-label">Buscar</label>
            <div class="input-group">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-q" class="form-control" placeholder="N° venta o cliente...">
            </div>
        </div>
        <button class="btn btn-primary" onclick="loadHistorial()"><i class="fas fa-search"></i> Buscar</button>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Ventas registradas</div>
        <span style="font-size:.82rem;color:var(--text-muted)" id="result-count">Cargando...</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N° Venta</th>
                    <th>Fecha / Hora</th>
                    <th>Cliente</th>
                    <th>Items</th>
                    <th>Pago</th>
                    <th>Comp.</th>
                    <th class="text-right">Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Detalle de venta -->
<div class="modal-overlay" id="modal-detalle">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="detalle-titulo">Detalle de Venta</h3>
            <button class="modal-close" onclick="closeModal('modal-detalle')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detalle-body"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-detalle')">Cerrar</button>
            <button class="btn btn-danger" id="btn-anular" style="display:none" onclick="anularVenta()">
                <i class="fas fa-ban"></i> Anular Venta
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
const BASE = '../../';
let currentVentaId = null;

document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadHistorial();
    document.getElementById('f-q').addEventListener('keyup', e => { if(e.key==='Enter') loadHistorial(); });
});

function loadStats() {
    fetch(BASE + 'modules/ventas/api.php?action=stats_dia')
        .then(r => r.json())
        .then(d => {
            const cfg = [
                { icon:'cash-register', color:'blue',   val: d.total_ventas,    label:'Ventas hoy' },
                { icon:'dollar-sign',   color:'green',  val: 'S/ '+parseFloat(d.ingresos).toFixed(2), label:'Ingresos hoy' },
                { icon:'chart-line',    color:'yellow', val: 'S/ '+parseFloat(d.ticket_promedio).toFixed(2), label:'Ticket promedio' },
                { icon:'ban',           color:'red',    val: d.anuladas,        label:'Anuladas hoy' },
            ];
            document.getElementById('stats-container').innerHTML = cfg.map(c => `
                <div class="stat-card">
                    <div class="stat-icon ${c.color}"><i class="fas fa-${c.icon}"></i></div>
                    <div><div class="stat-value">${c.val}</div><div class="stat-label">${c.label}</div></div>
                </div>`).join('');
        });
}

function loadHistorial() {
    const params = new URLSearchParams({
        action: 'historial',
        desde:  document.getElementById('f-desde').value,
        hasta:  document.getElementById('f-hasta').value,
        estado: document.getElementById('f-estado').value,
        q:      document.getElementById('f-q').value,
    });
    document.getElementById('tabla-body').innerHTML =
        '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i> Buscando...</td></tr>';

    fetch(BASE + 'modules/ventas/api.php?' + params)
        .then(r => r.json())
        .then(data => {
            document.getElementById('result-count').textContent = data.length + ' resultado(s)';
            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-inbox" style="font-size:1.3rem"></i><br><br>No se encontraron ventas</td></tr>';
                return;
            }
            const pagoIcon = { efectivo:'💵', yape:`<img src="${BASE}assets/img/yape_plin.jpg" alt="Yape" style="height:18px;vertical-align:middle;border-radius:3px">`, plin:`<img src="${BASE}assets/img/yape_plin.jpg" alt="Plin" style="height:18px;vertical-align:middle;border-radius:3px">`, tarjeta:'💳', transferencia:'🏦' };
            document.getElementById('tabla-body').innerHTML = data.map(v => {
                const esCls = v.estado === 'completada' ? 'badge-success' : 'badge-danger';
                const dt = new Date(v.created_at);
                const fechaStr = dt.toLocaleDateString('es-PE') + ' ' + dt.toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit'});
                return `<tr>
                    <td><span style="font-weight:700;color:var(--primary)">${v.numero_venta}</span></td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${fechaStr}</td>
                    <td style="font-size:.85rem">${v.cliente}</td>
                    <td><span class="badge badge-gray">${v.num_items} items</span></td>
                    <td style="font-size:.82rem">${pagoIcon[v.tipo_pago]||''} ${v.tipo_pago}</td>
                    <td><span class="badge badge-primary" style="text-transform:capitalize">${v.tipo_comprobante}</span></td>
                    <td class="text-right"><strong>S/ ${parseFloat(v.total).toFixed(2)}</strong></td>
                    <td><span class="badge ${esCls}">${v.estado}</span></td>
                    <td>
                        <button class="btn btn-ghost btn-sm" onclick="verDetalle(${v.id},'${v.numero_venta}','${v.estado}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');
        })
        .catch(() => showToast('Error al cargar historial','error'));
}

function verDetalle(id, numero, estado) {
    currentVentaId = id;
    document.getElementById('detalle-titulo').textContent = 'Detalle: ' + numero;
    document.getElementById('btn-anular').style.display = estado === 'completada' ? 'inline-flex' : 'none';
    document.getElementById('detalle-body').innerHTML =
        '<div style="text-align:center;padding:30px"><i class="fas fa-spinner fa-spin" style="font-size:1.5rem;color:var(--text-light)"></i></div>';
    openModal('modal-detalle');

    fetch(BASE + `modules/ventas/api.php?action=detalle_venta&id=${id}`)
        .then(r => r.json())
        .then(items => {
            if (!items.length) {
                document.getElementById('detalle-body').innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">Sin detalles disponibles</p>';
                return;
            }
            const total = items.reduce((s,i) => s + parseFloat(i.subtotal), 0);
            document.getElementById('detalle-body').innerHTML = `
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Código</th><th>Producto</th><th class="text-right">Cant.</th><th class="text-right">P. Unit.</th><th class="text-right">Subtotal</th></tr>
                        </thead>
                        <tbody>
                            ${items.map(i => `
                            <tr>
                                <td style="font-size:.8rem;color:var(--text-muted)">${i.codigo}</td>
                                <td style="font-size:.85rem;font-weight:500">${i.producto_nombre}</td>
                                <td class="text-right">${i.cantidad}</td>
                                <td class="text-right">S/ ${parseFloat(i.precio_unitario).toFixed(2)}</td>
                                <td class="text-right"><strong>S/ ${parseFloat(i.subtotal).toFixed(2)}</strong></td>
                            </tr>`).join('')}
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--surface-2)">
                                <td colspan="4" style="padding:10px 14px;font-weight:700;text-align:right">TOTAL</td>
                                <td style="padding:10px 14px;font-weight:700;text-align:right;color:var(--success);font-size:1rem">S/ ${total.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>`;
        });
}

function anularVenta() {
    if (!currentVentaId || !confirm('¿Anular esta venta? El stock será repuesto.')) return;

    fetch(BASE + 'modules/ventas/api.php?action=anular_venta', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id: currentVentaId })
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) { showToast(d.message,'error'); return; }
        showToast('Venta anulada correctamente','success');
        closeModal('modal-detalle');
        loadHistorial(); loadStats();
    })
    .catch(() => showToast('Error al anular la venta','error'));
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type='info') {
    const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>

<?php include '../../includes/footer.php'; ?>