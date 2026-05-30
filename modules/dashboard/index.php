<?php
// ============================================================
// ARCHIVO: farmacia/modules/dashboard/index.php
// DESCRIPCIÓN: Dashboard principal — resumen ejecutivo
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$current_module = 'dashboard';
$current_page   = 'inicio';
$page_title     = 'Dashboard — FarmaSystem';
$breadcrumb     = '<strong>Dashboard</strong>';

include '../../includes/header.php';
?>

<style>
/* ---- Layout ---- */
.dash-wrap     { padding: 24px; display: flex; flex-direction: column; gap: 24px; }
.section-title { font-size: .75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }

/* ---- KPI cards ---- */
.kpi-grid      { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 16px; }
.kpi-card      {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
    transition: box-shadow var(--transition);
}
.kpi-card:hover { box-shadow: var(--shadow-md); }
.kpi-card .accent {
    position: absolute; top: 0; left: 0;
    width: 4px; height: 100%;
    border-radius: var(--radius-lg) 0 0 var(--radius-lg);
}
.kpi-icon {
    width: 40px; height: 40px;
    border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin-bottom: 12px;
}
.kpi-label { font-size: .78rem; color: var(--text-muted); font-weight: 500; margin-bottom: 4px; }
.kpi-value { font-size: 1.6rem; font-weight: 700; color: var(--text); line-height: 1; }
.kpi-sub   { font-size: .73rem; color: var(--text-light); margin-top: 6px; }
.kpi-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 20px;
    font-size: .68rem; font-weight: 700;
}

/* ---- Two-column layout ---- */
.dash-cols   { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.dash-cols-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
@media (max-width: 1100px) {
    .dash-cols   { grid-template-columns: 1fr; }
    .dash-cols-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 680px) {
    .dash-cols-3 { grid-template-columns: 1fr; }
    .kpi-grid    { grid-template-columns: 1fr 1fr; }
}

/* ---- Card ---- */
.d-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}
.d-card-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-light);
    display: flex; align-items: center; justify-content: space-between;
}
.d-card-head h3 { font-size: .92rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.d-card-body    { padding: 20px; }

/* ---- Table ---- */
.dash-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.dash-table th {
    padding: 8px 12px; text-align: left;
    font-size: .72rem; font-weight: 600; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .06em;
    border-bottom: 1px solid var(--border);
}
.dash-table td  { padding: 10px 12px; border-bottom: 1px solid var(--border-light); vertical-align: middle; }
.dash-table tr:last-child td { border-bottom: none; }
.dash-table tr:hover td { background: var(--surface-2); }

/* ---- Stock badge ---- */
.badge-danger  { background: var(--danger-light);  color: var(--danger);  }
.badge-warning { background: var(--warning-light); color: var(--warning); }
.badge-success { background: var(--success-light); color: var(--success); }
.badge-info    { background: var(--info-light);    color: var(--info);    }
.badge-muted   { background: var(--surface-2);     color: var(--text-muted); }

/* ---- Caja status ---- */
.caja-status {
    display: flex; flex-direction: column; gap: 10px;
}
.caja-row {
    display: flex; justify-content: space-between; align-items: center;
    font-size: .85rem;
}
.caja-row .label { color: var(--text-muted); }
.caja-row .val   { font-weight: 600; }
.caja-closed {
    text-align: center; padding: 20px 0;
    color: var(--text-muted); font-size: .88rem;
}
.caja-closed i { font-size: 2rem; display: block; margin-bottom: 8px; color: var(--border); }

/* ---- Método pago list ---- */
.metodo-list { display: flex; flex-direction: column; gap: 10px; }
.metodo-row  { display: flex; align-items: center; gap: 10px; }
.metodo-bar-bg { flex: 1; height: 6px; background: var(--surface-2); border-radius: 99px; overflow: hidden; }
.metodo-bar    { height: 100%; border-radius: 99px; background: var(--primary); transition: width .5s ease; }
.metodo-label  { font-size: .78rem; min-width: 80px; font-weight: 500; }
.metodo-val    { font-size: .78rem; color: var(--text-muted); min-width: 70px; text-align: right; }

/* ---- Skeleton loader ---- */
.skel { background: linear-gradient(90deg, var(--surface-2) 25%, var(--border-light) 50%, var(--surface-2) 75%); background-size: 400% 100%; animation: skel-shine 1.4s ease infinite; border-radius: 6px; }
@keyframes skel-shine { 0%{background-position:100% 50%} 100%{background-position:0% 50%} }

/* ---- Chart container ---- */
.chart-wrap { position: relative; height: 220px; }
</style>

<div class="dash-wrap">

    <!-- KPI Row -->
    <div>
        <p class="section-title">Resumen del día · <span id="fecha-hoy"></span></p>
        <div class="kpi-grid" id="kpi-grid">
            <!-- Skeleton placeholders -->
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="kpi-card">
                <div class="accent skel" style="background:transparent"></div>
                <div class="skel" style="width:40px;height:40px;margin-bottom:12px"></div>
                <div class="skel" style="width:60%;height:.78rem;margin-bottom:6px"></div>
                <div class="skel" style="width:80%;height:1.6rem"></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Charts + Caja row -->
    <div class="dash-cols">
        <!-- Ventas últimos 7 días -->
        <div class="d-card">
            <div class="d-card-head">
                <h3><i class="fas fa-chart-line" style="color:var(--primary)"></i> Ventas últimos 7 días</h3>
                <select id="sel-periodo" onchange="loadChart()" style="font-size:.78rem;border:1px solid var(--border);border-radius:var(--radius-sm);padding:4px 8px;background:var(--surface);color:var(--text)">
                    <option value="7">7 días</option>
                    <option value="14">14 días</option>
                    <option value="30">30 días</option>
                </select>
            </div>
            <div class="d-card-body">
                <div class="chart-wrap"><canvas id="chart-ventas"></canvas></div>
            </div>
        </div>

        <!-- Caja actual -->
        <div class="d-card">
            <div class="d-card-head">
                <h3><i class="fas fa-cash-register" style="color:var(--success)"></i> Estado de Caja</h3>
                <span id="caja-badge" class="kpi-badge badge-muted">Cargando…</span>
            </div>
            <div class="d-card-body" id="caja-body">
                <div class="caja-closed"><i class="fas fa-lock"></i>Cargando…</div>
            </div>
        </div>
    </div>

    <!-- Métodos de pago + Top vendidos + Alertas stock -->
    <div class="dash-cols-3">

        <!-- Métodos de pago -->
        <div class="d-card">
            <div class="d-card-head">
                <h3><i class="fas fa-wallet" style="color:var(--info)"></i> Métodos de Pago</h3>
                <span style="font-size:.72rem;color:var(--text-muted)">Hoy</span>
            </div>
            <div class="d-card-body" id="metodo-body">
                <div class="skel" style="height:140px"></div>
            </div>
        </div>

        <!-- Top 5 vendidos -->
        <div class="d-card">
            <div class="d-card-head">
                <h3><i class="fas fa-fire" style="color:var(--warning)"></i> Top 5 Productos</h3>
                <span style="font-size:.72rem;color:var(--text-muted)">Por unidades</span>
            </div>
            <div class="d-card-body" id="top-body" style="padding:0">
                <div class="skel" style="height:200px;margin:20px"></div>
            </div>
        </div>

        <!-- Alertas de stock -->
        <div class="d-card">
            <div class="d-card-head">
                <h3><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Alertas de Stock</h3>
                <span id="alerta-count" class="kpi-badge badge-danger" style="display:none"></span>
            </div>
            <div class="d-card-body" id="alerta-body" style="padding:0">
                <div class="skel" style="height:200px;margin:20px"></div>
            </div>
        </div>
    </div>

    <!-- Últimas ventas del día -->
    <div class="d-card">
        <div class="d-card-head">
            <h3><i class="fas fa-receipt" style="color:var(--primary)"></i> Últimas ventas de hoy</h3>
            <a href="<?= $base_path ?>modules/ventas/historial.php" style="font-size:.78rem;color:var(--primary);font-weight:500">
                Ver todo <i class="fas fa-arrow-right" style="font-size:.68rem"></i>
            </a>
        </div>
        <div style="overflow-x:auto">
            <table class="dash-table" id="tbl-ventas">
                <thead>
                    <tr>
                        <th>N° Venta</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Comprobante</th>
                        <th>Hora</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted)">
                        <i class="fas fa-spinner fa-spin"></i> Cargando…
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
const API = '../../modules/dashboard/api.php';
let chartVentas = null;

// ---- Helpers ----
const fmt = (n) => 'S/ ' + parseFloat(n).toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2});
const fmtN = (n) => parseFloat(n).toLocaleString('es-PE');

const METODO_LABEL = { efectivo:'Efectivo', yape:'Yape', plin:'Plin', tarjeta:'Tarjeta', transferencia:'Transferencia' };
const METODO_COLOR = { efectivo:'var(--success)', yape:'#8b5cf6', plin:'#06b6d4', tarjeta:'var(--primary)', transferencia:'var(--warning)' };

function setDate() {
    const d = new Date();
    const opts = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
    document.getElementById('fecha-hoy').textContent = d.toLocaleDateString('es-PE', opts);
}

// ---- Load KPI resumen ----
async function loadResumen() {
    const res = await fetch(API + '?action=resumen');
    const d   = await res.json();

    const v   = d.ventas;
    const inv = d.inventario;
    const alm = d.almacen;
    const caja = d.caja;

    const kpiData = [
        {
            label: 'Ingresos del día',
            value: fmt(v.ingresos),
            sub:   v.total_ventas + ' ventas realizadas',
            icon:  'fas fa-coins', iconBg: 'var(--primary-light)', iconColor: 'var(--primary)',
            accent: 'var(--primary)',
        },
        {
            label: 'Ticket Promedio',
            value: fmt(v.ticket_promedio),
            sub:   v.anuladas + ' venta(s) anulada(s)',
            icon:  'fas fa-receipt', iconBg: 'var(--success-light)', iconColor: 'var(--success)',
            accent: 'var(--success)',
        },
        {
            label: 'Valor de Inventario',
            value: fmt(inv.valor_inventario),
            sub:   inv.total_activos + ' productos activos',
            icon:  'fas fa-boxes', iconBg: 'var(--info-light)', iconColor: 'var(--info)',
            accent: 'var(--info)',
        },
        {
            label: 'Stock Bajo',
            value: inv.stock_bajo,
            sub:   inv.agotados + ' producto(s) agotado(s)',
            icon:  'fas fa-exclamation-circle',
            iconBg: inv.stock_bajo > 0 ? 'var(--danger-light)' : 'var(--success-light)',
            iconColor: inv.stock_bajo > 0 ? 'var(--danger)' : 'var(--success)',
            accent: inv.stock_bajo > 0 ? 'var(--danger)' : 'var(--success)',
        },
        {
            label: 'Ingresos al Almacén',
            value: alm.ingresos_mes,
            sub:   'Este mes · ' + fmt(alm.valor_mes),
            icon:  'fas fa-truck-loading', iconBg: 'var(--warning-light)', iconColor: 'var(--warning)',
            accent: 'var(--warning)',
        },
        {
            label: 'Proveedores Activos',
            value: alm.proveedores_activos,
            sub:   'Proveedores habilitados',
            icon:  'fas fa-truck', iconBg: '#f5f3ff', iconColor: '#7c3aed',
            accent: '#7c3aed',
        },
    ];

    const grid = document.getElementById('kpi-grid');
    grid.innerHTML = kpiData.map(k => `
        <div class="kpi-card">
            <div class="accent" style="background:${k.accent}"></div>
            <div class="kpi-icon" style="background:${k.iconBg};color:${k.iconColor}">
                <i class="${k.icon}"></i>
            </div>
            <div class="kpi-label">${k.label}</div>
            <div class="kpi-value">${k.value}</div>
            <div class="kpi-sub">${k.sub}</div>
        </div>
    `).join('');

    // Caja
    renderCaja(caja);
}

function renderCaja(caja) {
    const badge = document.getElementById('caja-badge');
    const body  = document.getElementById('caja-body');

    if (!caja.abierta) {
        badge.textContent = 'Cerrada';
        badge.className   = 'kpi-badge badge-danger';
        body.innerHTML    = `<div class="caja-closed"><i class="fas fa-lock"></i>No hay caja abierta</div>`;
        return;
    }

    badge.textContent = 'Abierta';
    badge.className   = 'kpi-badge badge-success';

    const hora = caja.apertura_at
        ? new Date(caja.apertura_at).toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'})
        : '—';

    body.innerHTML = `
        <div class="caja-status">
            <div class="caja-row">
                <span class="label">Responsable</span>
                <span class="val">${caja.responsable || '—'}</span>
            </div>
            <div class="caja-row">
                <span class="label">Apertura</span>
                <span class="val">${hora}</span>
            </div>
            <div class="caja-row">
                <span class="label">Saldo inicial</span>
                <span class="val">${fmt(caja.saldo_inicial)}</span>
            </div>
            <div class="caja-row">
                <span class="label">Ventas del turno</span>
                <span class="val">${caja.ventas_count} · ${fmt(caja.ventas_total)}</span>
            </div>
            <hr style="border:none;border-top:1px solid var(--border-light);margin:4px 0">
            <div class="caja-row">
                <span class="label" style="font-weight:600">Saldo esperado</span>
                <span class="val" style="color:var(--success);font-size:1.05rem">${fmt(caja.saldo_esperado)}</span>
            </div>
        </div>
    `;
}

// ---- Ventas chart ----
async function loadChart() {
    const dias = document.getElementById('sel-periodo').value;
    const res  = await fetch(API + '?action=ventas_semana&dias=' + dias);
    const data = await res.json();

    const labels  = data.map(d => {
        const parts = d.fecha.split('-');
        return parts[2] + '/' + parts[1];
    });
    const ingresos = data.map(d => parseFloat(d.ingresos));
    const ventas   = data.map(d => parseInt(d.total_ventas));

    const ctx = document.getElementById('chart-ventas').getContext('2d');

    if (chartVentas) chartVentas.destroy();

    chartVentas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Ingresos (S/)',
                    data: ingresos,
                    backgroundColor: 'rgba(37,99,235,.15)',
                    borderColor: 'rgba(37,99,235,.8)',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'N° Ventas',
                    data: ventas,
                    type: 'line',
                    borderColor: '#16a34a',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointBackgroundColor: '#16a34a',
                    pointRadius: 4,
                    tension: 0.3,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, padding: 12 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.datasetIndex === 0
                            ? ' ' + fmt(ctx.parsed.y)
                            : ' ' + ctx.parsed.y + ' ventas'
                    }
                }
            },
            scales: {
                y:  { position: 'left',  ticks: { callback: v => 'S/ ' + v, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.04)' } },
                y2: { position: 'right', ticks: { stepSize: 1, font: { size: 11 } }, grid: { display: false } },
                x:  { ticks: { font: { size: 11 } }, grid: { display: false } },
            }
        }
    });
}

// ---- Métodos de pago ----
async function loadMetodos() {
    const res  = await fetch(API + '?action=ventas_metodo_pago&periodo=hoy');
    const data = await res.json();
    const body = document.getElementById('metodo-body');

    if (!data.length) {
        body.innerHTML = '<p style="color:var(--text-muted);font-size:.83rem;text-align:center;padding:20px 0">Sin ventas hoy</p>';
        return;
    }

    const max = Math.max(...data.map(d => parseFloat(d.monto)));
    body.innerHTML = `<div class="metodo-list">${
        data.map(d => {
            const pct  = max > 0 ? (parseFloat(d.monto) / max * 100).toFixed(1) : 0;
            const lbl  = METODO_LABEL[d.tipo_pago] ?? d.tipo_pago;
            const col  = METODO_COLOR[d.tipo_pago]  ?? 'var(--primary)';
            return `<div class="metodo-row">
                <span class="metodo-label">${lbl}</span>
                <div class="metodo-bar-bg"><div class="metodo-bar" style="width:${pct}%;background:${col}"></div></div>
                <span class="metodo-val">${fmt(d.monto)}</span>
            </div>`;
        }).join('')
    }</div>`;
}

// ---- Top vendidos ----
async function loadTop() {
    const res  = await fetch(API + '?action=top_vendidos');
    const data = await res.json();
    const body = document.getElementById('top-body');

    if (!data.length) {
        body.innerHTML = '<p style="color:var(--text-muted);font-size:.83rem;text-align:center;padding:30px">Sin datos</p>';
        return;
    }

    const max = data[0].total_vendido;
    body.innerHTML = `<table class="dash-table">
        <thead><tr><th>#</th><th>Producto</th><th style="text-align:right">Uds.</th></tr></thead>
        <tbody>${data.map((p, i) => {
            const pct = max > 0 ? (p.total_vendido / max * 100).toFixed(0) : 0;
            return `<tr>
                <td style="color:var(--text-muted);font-weight:700">${i+1}</td>
                <td>
                    <div style="font-weight:500;font-size:.83rem">${p.nombre}</div>
                    <div style="font-size:.72rem;color:var(--text-light)">${p.laboratorio ?? '—'} · ${p.categoria ?? '—'}</div>
                    <div style="margin-top:4px;height:4px;background:var(--surface-2);border-radius:99px;overflow:hidden">
                        <div style="height:100%;width:${pct}%;background:var(--warning);border-radius:99px"></div>
                    </div>
                </td>
                <td style="text-align:right;font-weight:700">${fmtN(p.total_vendido)}</td>
            </tr>`;
        }).join('')}</tbody>
    </table>`;
}

// ---- Alertas de stock ----
async function loadAlertas() {
    const res  = await fetch(API + '?action=stock_alertas');
    const data = await res.json();
    const body = document.getElementById('alerta-body');
    const cnt  = document.getElementById('alerta-count');

    if (!data.length) {
        cnt.style.display = 'none';
        body.innerHTML = `<p style="color:var(--success);font-size:.83rem;text-align:center;padding:30px">
            <i class="fas fa-check-circle" style="display:block;font-size:1.6rem;margin-bottom:8px"></i>
            Stock en orden
        </p>`;
        return;
    }

    cnt.style.display = 'inline-flex';
    cnt.textContent   = data.length + ' alerta' + (data.length > 1 ? 's' : '');

    body.innerHTML = `<table class="dash-table">
        <thead><tr><th>Producto</th><th style="text-align:right">Stock</th><th style="text-align:right">Mín</th></tr></thead>
        <tbody>${data.map(p => {
            const es = p.alerta === 'agotado';
            const cls = es ? 'badge-danger' : 'badge-warning';
            const lbl = es ? 'Agotado' : 'Bajo';
            return `<tr>
                <td>
                    <div style="font-weight:500;font-size:.82rem">${p.nombre}</div>
                    <div style="font-size:.7rem;color:var(--text-light)">${p.categoria ?? '—'}</div>
                </td>
                <td style="text-align:right">
                    <span class="kpi-badge ${cls}">${p.stock}</span>
                </td>
                <td style="text-align:right;color:var(--text-muted)">${p.stock_minimo}</td>
            </tr>`;
        }).join('')}</tbody>
    </table>`;
}

// ---- Últimas ventas ----
async function loadUltimasVentas() {
    const res  = await fetch(API + '?action=ultimas_ventas');
    const data = await res.json();
    const tbody = document.querySelector('#tbl-ventas tbody');

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted)">Sin ventas registradas hoy</td></tr>';
        return;
    }

    const COMP_LABEL = { ticket:'Ticket', boleta:'Boleta', factura:'Factura' };
    const PAGO_ICONS = { efectivo:'💵', yape:'📱', plin:'📱', tarjeta:'💳', transferencia:'🏦' };

    tbody.innerHTML = data.map(v => {
        const hora  = new Date(v.created_at).toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'});
        const icon  = PAGO_ICONS[v.tipo_pago] ?? '';
        const comp  = COMP_LABEL[v.tipo_comprobante] ?? v.tipo_comprobante;
        const esCan = v.estado === 'anulada';
        const badge = esCan
            ? '<span class="kpi-badge badge-danger">Anulada</span>'
            : '<span class="kpi-badge badge-success">Completada</span>';
        return `<tr style="${esCan ? 'opacity:.55' : ''}">
            <td><span style="font-family:monospace;font-size:.8rem">${v.numero_venta}</span></td>
            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${v.cliente}</td>
            <td>${icon} ${METODO_LABEL[v.tipo_pago] ?? v.tipo_pago}</td>
            <td>${comp}</td>
            <td style="color:var(--text-muted)">${hora}</td>
            <td style="font-weight:600">${fmt(v.total)}</td>
            <td>${badge}</td>
        </tr>`;
    }).join('');
}

// ---- Init ----
setDate();
Promise.all([
    loadResumen(),
    loadChart(),
    loadMetodos(),
    loadTop(),
    loadAlertas(),
    loadUltimasVentas(),
]);
</script>

<?php include '../../includes/footer.php'; ?>
