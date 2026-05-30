<?php
// ============================================================
// ARCHIVO: farmacia/modules/facturacion/index.php
// MÓDULO:  Facturación → Reporte de Ventas
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin'];
$current_module = 'facturacion';
$current_page   = 'reporte';
$page_title     = 'Reporte de Ventas — FarmaSystem';
$breadcrumb     = '<strong>Facturación</strong> / Reporte de Ventas';

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">
            <i class="fas fa-chart-bar" style="color:var(--primary);margin-right:8px"></i>Reporte de Ventas
        </div>
        <div class="page-subtitle">Consulta, filtra y exporta las ventas del período</div>
    </div>
    <div class="page-actions">
        <button class="btn btn-success" id="btn-exportar" onclick="exportarExcel()">
            <i class="fas fa-file-excel"></i> Descargar Excel
        </button>
    </div>
</div>

<!-- ======================================================
     FILTROS
     ====================================================== -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-filter" style="color:var(--primary)"></i> Filtros</div>
        <button class="btn btn-ghost btn-sm" onclick="resetFiltros()" style="margin-left:auto">
            <i class="fas fa-undo"></i> Limpiar
        </button>
    </div>
    <div style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">

        <div class="form-group" style="margin:0;flex:1;min-width:150px">
            <label class="form-label">Desde</label>
            <input type="date" id="f-desde" class="form-control" value="<?= date('Y-m-01') ?>">
        </div>

        <div class="form-group" style="margin:0;flex:1;min-width:150px">
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

        <div class="form-group" style="margin:0;flex:1;min-width:130px">
            <label class="form-label">Comprobante</label>
            <select class="form-control" id="f-tipo-comp">
                <option value="">Todos</option>
                <option value="ticket">Ticket</option>
                <option value="boleta">Boleta</option>
                <option value="factura">Factura</option>
            </select>
        </div>

        <div class="form-group" style="margin:0;flex:1;min-width:130px">
            <label class="form-label">Método de pago</label>
            <select class="form-control" id="f-tipo-pago">
                <option value="">Todos</option>
                <option value="efectivo">Efectivo</option>
                <option value="yape">Yape</option>
                <option value="plin">Plin</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </div>

        <div class="form-group" style="margin:0;flex:1;min-width:130px">
            <label class="form-label">Vendedor</label>
            <select class="form-control" id="f-vendedor">
                <option value="">Todos</option>
            </select>
        </div>

        <div class="form-group" style="margin:0;flex:2;min-width:200px">
            <label class="form-label">Buscar</label>
            <div class="input-group">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-q" class="form-control"
                    placeholder="N° venta, cliente, vendedor...">
            </div>
        </div>

        <button class="btn btn-primary" onclick="buscar()">
            <i class="fas fa-search"></i> Buscar
        </button>
    </div>

    <!-- Atajos de período -->
    <div style="padding:0 20px 16px;display:flex;gap:8px;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--text-muted);align-self:center">Período rápido:</span>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('hoy')">Hoy</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('ayer')">Ayer</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('semana')">Esta semana</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('mes')">Este mes</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('mes_ant')">Mes anterior</button>
    </div>
</div>

<!-- ======================================================
     STATS CARDS
     ====================================================== -->
<div class="stat-cards" id="stats-container">
    <?php foreach (range(0,5) as $_): ?>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-spinner fa-spin"></i></div>
        <div><div class="stat-value">—</div><div class="stat-label">Cargando...</div></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ======================================================
     RESUMEN POR MÉTODO DE PAGO / COMPROBANTE
     ====================================================== -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px" id="resumen-wrap">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-wallet" style="color:var(--primary)"></i> Por método de pago</div>
        </div>
        <div style="padding:0 8px 8px" id="resumen-pago">
            <div style="padding:20px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-file-invoice" style="color:var(--primary)"></i> Por tipo de comprobante</div>
        </div>
        <div style="padding:0 8px 8px" id="resumen-comp">
            <div style="padding:20px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>
</div>

<!-- ======================================================
     COMPARATIVA POR VENDEDOR
     ====================================================== -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-users" style="color:var(--primary)"></i> Comparativa por vendedor
        </div>
    </div>
    <div id="resumen-usuarios" style="min-height:60px">
        <div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
</div>

<!-- ======================================================
     TABLA DE VENTAS
     ====================================================== -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Detalle de ventas</div>
        <div style="display:flex;align-items:center;gap:12px;margin-left:auto">
            <span style="font-size:.82rem;color:var(--text-muted)" id="result-count">—</span>
            <button class="btn btn-success btn-sm" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N° Venta</th>
                    <th>Fecha / Hora</th>
                    <th>Vendedor</th>
                    <th>Cliente</th>
                    <th>Items</th>
                    <th>Pago</th>
                    <th>Comprobante</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">IGV</th>
                    <th class="text-right">Total</th>
                    <th>Estado</th>
                    <th>SUNAT</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                <tr><td colspan="12" style="text-align:center;padding:30px;color:var(--text-light)">
                    <i class="fas fa-spinner fa-spin"></i>
                </td></tr>
            </tbody>
            <tfoot id="tabla-foot"></tfoot>
        </table>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
const BASE = '../../';

// ---- Estado de filtros ----
function getParams() {
    return {
        desde:     document.getElementById('f-desde').value,
        hasta:     document.getElementById('f-hasta').value,
        estado:    document.getElementById('f-estado').value,
        tipo_comp: document.getElementById('f-tipo-comp').value,
        tipo_pago: document.getElementById('f-tipo-pago').value,
        vendedor:  document.getElementById('f-vendedor').value,
        q:         document.getElementById('f-q').value,
    };
}

function buildQuery(extra = {}) {
    return new URLSearchParams({ ...getParams(), ...extra }).toString();
}

// ---- Períodos rápidos ----
function setPeriodo(p) {
    const hoy = new Date();
    const fmt  = d => d.toISOString().slice(0, 10);
    let desde, hasta;
    switch (p) {
        case 'hoy':
            desde = hasta = fmt(hoy); break;
        case 'ayer': {
            const a = new Date(hoy); a.setDate(a.getDate() - 1);
            desde = hasta = fmt(a); break;
        }
        case 'semana': {
            const lun = new Date(hoy);
            lun.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
            desde = fmt(lun); hasta = fmt(hoy); break;
        }
        case 'mes':
            desde = fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            hasta = fmt(hoy); break;
        case 'mes_ant': {
            const f = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
            const l = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
            desde = fmt(f); hasta = fmt(l); break;
        }
    }
    document.getElementById('f-desde').value = desde;
    document.getElementById('f-hasta').value = hasta;
    buscar();
}

function resetFiltros() {
    const hoy  = new Date();
    const desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().slice(0,10);
    document.getElementById('f-desde').value     = desde;
    document.getElementById('f-hasta').value     = hoy.toISOString().slice(0,10);
    document.getElementById('f-estado').value    = '';
    document.getElementById('f-tipo-comp').value = '';
    document.getElementById('f-tipo-pago').value = '';
    document.getElementById('f-vendedor').value  = '';
    document.getElementById('f-q').value         = '';
    buscar();
}

// ---- Carga stats + reporte ----
function buscar() {
    loadStats();
    loadReporte();
}

function loadStats() {
    document.getElementById('stats-container').innerHTML = [0,1,2,3,4,5].map(() =>
        `<div class="stat-card"><div class="stat-icon blue"><i class="fas fa-spinner fa-spin"></i></div>
         <div><div class="stat-value">—</div><div class="stat-label">Cargando...</div></div></div>`
    ).join('');
    document.getElementById('resumen-pago').innerHTML =
        '<div style="padding:20px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>';
    document.getElementById('resumen-comp').innerHTML =
        '<div style="padding:20px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>';
    document.getElementById('resumen-usuarios').innerHTML =
        '<div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>';

    loadStatsUsuario();

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'stats' }))
        .then(r => r.json())
        .then(d => {
            const n  = v => parseFloat(v || 0);
            const m  = v => 'S/ ' + n(v).toFixed(2);

            const cards = [
                { icon: 'shopping-cart',       color: 'blue',   val: d.total_completadas, label: 'Ventas completadas' },
                { icon: 'dollar-sign',          color: 'green',  val: m(d.total_ingresos),  label: 'Ingresos del período' },
                { icon: 'receipt',              color: 'yellow', val: m(d.total_igv),        label: 'IGV total' },
                { icon: 'tag',                  color: 'purple', val: m(d.total_descuentos), label: 'Descuentos otorgados' },
                { icon: 'chart-line',           color: 'teal',   val: m(d.ticket_promedio),  label: 'Ticket promedio' },
                { icon: 'ban',                  color: 'red',    val: d.total_anuladas,      label: 'Ventas anuladas' },
            ];
            document.getElementById('stats-container').innerHTML = cards.map(c => `
                <div class="stat-card">
                    <div class="stat-icon ${c.color}"><i class="fas fa-${c.icon}"></i></div>
                    <div><div class="stat-value">${c.val}</div><div class="stat-label">${c.label}</div></div>
                </div>`).join('');

            // Resumen por método de pago
            const pagos = [
                { label: 'Efectivo',      key: 'pago_efectivo',      icon: 'money-bill-wave', color: '#16a34a' },
                { label: 'Yape',          key: 'pago_yape',          icon: 'mobile-alt',      color: '#7c3aed' },
                { label: 'Plin',          key: 'pago_plin',          icon: 'mobile-alt',      color: '#2563eb' },
                { label: 'Tarjeta',       key: 'pago_tarjeta',       icon: 'credit-card',     color: '#0891b2' },
                { label: 'Transferencia', key: 'pago_transferencia', icon: 'university',      color: '#d97706' },
            ];
            const totalPagos = pagos.reduce((s, p) => s + n(d[p.key]), 0);
            document.getElementById('resumen-pago').innerHTML = pagos
                .filter(p => n(d[p.key]) > 0)
                .map(p => {
                    const pct = totalPagos > 0 ? (n(d[p.key]) / totalPagos * 100).toFixed(1) : 0;
                    return `<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid var(--border)">
                        <i class="fas fa-${p.icon}" style="color:${p.color};width:16px;text-align:center"></i>
                        <span style="flex:1;font-size:.85rem;font-weight:500">${p.label}</span>
                        <div style="width:80px;background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden">
                            <div style="width:${pct}%;height:100%;background:${p.color};border-radius:4px"></div>
                        </div>
                        <span style="font-size:.78rem;color:var(--text-muted);width:38px;text-align:right">${pct}%</span>
                        <strong style="font-size:.88rem;min-width:80px;text-align:right">S/ ${n(d[p.key]).toFixed(2)}</strong>
                    </div>`;
                }).join('') || '<p style="padding:16px;color:var(--text-muted);text-align:center;font-size:.85rem">Sin datos para el período</p>';

            // Resumen por comprobante
            const comps = [
                { label: 'Ticket',  key: 'comp_ticket',  icon: 'receipt',      color: '#64748b' },
                { label: 'Boleta',  key: 'comp_boleta',  icon: 'file-alt',     color: '#2563eb' },
                { label: 'Factura', key: 'comp_factura', icon: 'file-invoice', color: '#7c3aed' },
            ];
            const totalComps = comps.reduce((s, c) => s + parseInt(d[c.key] || 0), 0);
            document.getElementById('resumen-comp').innerHTML = comps
                .filter(c => parseInt(d[c.key] || 0) > 0)
                .map(c => {
                    const cnt = parseInt(d[c.key] || 0);
                    const pct = totalComps > 0 ? (cnt / totalComps * 100).toFixed(1) : 0;
                    return `<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid var(--border)">
                        <i class="fas fa-${c.icon}" style="color:${c.color};width:16px;text-align:center"></i>
                        <span style="flex:1;font-size:.85rem;font-weight:500">${c.label}</span>
                        <div style="width:80px;background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden">
                            <div style="width:${pct}%;height:100%;background:${c.color};border-radius:4px"></div>
                        </div>
                        <span style="font-size:.78rem;color:var(--text-muted);width:38px;text-align:right">${pct}%</span>
                        <strong style="font-size:.88rem;min-width:60px;text-align:right">${cnt} ventas</strong>
                    </div>`;
                }).join('') || '<p style="padding:16px;color:var(--text-muted);text-align:center;font-size:.85rem">Sin datos para el período</p>';
        })
        .catch(() => showToast('Error al cargar estadísticas', 'error'));
}

// ---- Comparativa por vendedor ----
function loadStatsUsuario() {
    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'stats_usuario' }))
        .then(r => r.json())
        .then(data => {
            if (!data || data.error || !data.length) {
                document.getElementById('resumen-usuarios').innerHTML =
                    '<p style="padding:16px;color:var(--text-muted);text-align:center;font-size:.85rem">Sin datos para el período</p>';
                return;
            }

            const totalGlobal = data.reduce((s, u) => s + parseFloat(u.total_ingresos), 0);
            const colores = ['#4f46e5','#0891b2','#16a34a','#d97706','#7c3aed','#dc2626','#0f766e','#c2410c'];

            const thead = `<table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:var(--surface-2)">
                        <th style="padding:10px 16px;text-align:left;font-size:.78rem;color:var(--text-muted);font-weight:600">Vendedor</th>
                        <th style="padding:10px 16px;text-align:right;font-size:.78rem;color:var(--text-muted);font-weight:600">Ventas</th>
                        <th style="padding:10px 16px;text-align:right;font-size:.78rem;color:var(--text-muted);font-weight:600">Ingresos</th>
                        <th style="padding:10px 16px;text-align:right;font-size:.78rem;color:var(--text-muted);font-weight:600">Ticket prom.</th>
                        <th style="padding:10px 16px;text-align:right;font-size:.78rem;color:var(--text-muted);font-weight:600">Anuladas</th>
                        <th style="padding:10px 16px;font-size:.78rem;color:var(--text-muted);font-weight:600;min-width:180px">Participación</th>
                    </tr>
                </thead><tbody>`;

            const rows = data.map((u, i) => {
                const color = colores[i % colores.length];
                const pct   = totalGlobal > 0 ? (parseFloat(u.total_ingresos) / totalGlobal * 100).toFixed(1) : 0;
                const ini   = (u.vendedor || '?').charAt(0).toUpperCase();
                const isTop = i === 0 && parseFloat(u.total_ingresos) > 0;
                return `<tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:32px;height:32px;border-radius:50%;background:${color}22;color:${color};display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0">${ini}</div>
                            <div>
                                <div style="font-size:.88rem;font-weight:600">${u.vendedor}</div>
                                ${isTop ? '<span style="font-size:.7rem;background:#dcfce7;color:#15803d;padding:1px 6px;border-radius:10px;font-weight:600">Top vendedor</span>' : ''}
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:.9rem;font-weight:700">${u.total_ventas}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:.95rem;font-weight:700;color:var(--success)">S/ ${parseFloat(u.total_ingresos).toFixed(2)}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:.82rem;color:var(--text-muted)">S/ ${parseFloat(u.ticket_promedio).toFixed(2)}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:.85rem;color:${parseInt(u.total_anuladas) > 0 ? 'var(--danger)' : 'var(--text-muted)'};font-weight:${parseInt(u.total_anuladas) > 0 ? '600' : '400'}">${u.total_anuladas}</td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="flex:1;background:#f1f5f9;border-radius:4px;height:10px;overflow:hidden">
                                <div style="width:${pct}%;height:100%;background:${color};border-radius:4px;transition:.4s"></div>
                            </div>
                            <span style="font-size:.82rem;color:var(--text-muted);min-width:40px;text-align:right;font-weight:600">${pct}%</span>
                        </div>
                    </td>
                </tr>`;
            }).join('');

            // Fila de totales
            const totVentas   = data.reduce((s, u) => s + parseInt(u.total_ventas), 0);
            const totAnuladas = data.reduce((s, u) => s + parseInt(u.total_anuladas), 0);
            const totIngresos = totalGlobal;
            const totTicket   = totVentas > 0 ? totIngresos / totVentas : 0;
            const totals = `<tr style="background:var(--surface-2);font-weight:700">
                <td style="padding:10px 16px;font-size:.82rem;color:var(--text-muted)">Total (${data.length} vendedor${data.length !== 1 ? 'es' : ''})</td>
                <td style="padding:10px 16px;text-align:right">${totVentas}</td>
                <td style="padding:10px 16px;text-align:right;color:var(--success)">S/ ${totIngresos.toFixed(2)}</td>
                <td style="padding:10px 16px;text-align:right;font-size:.82rem;color:var(--text-muted)">S/ ${totTicket.toFixed(2)}</td>
                <td style="padding:10px 16px;text-align:right;color:${totAnuladas > 0 ? 'var(--danger)' : 'var(--text-muted)'}">${totAnuladas}</td>
                <td></td>
            </tr>`;

            document.getElementById('resumen-usuarios').innerHTML = thead + rows + totals + '</tbody></table>';
        })
        .catch(() => {
            document.getElementById('resumen-usuarios').innerHTML =
                '<p style="padding:16px;color:var(--text-muted);text-align:center;font-size:.85rem">Error al cargar datos</p>';
        });
}

function loadReporte() {
    document.getElementById('tabla-body').innerHTML =
        '<tr><td colspan="12" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
    document.getElementById('tabla-foot').innerHTML = '';
    document.getElementById('result-count').textContent = '—';

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'reporte' }))
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                document.getElementById('tabla-body').innerHTML =
                    `<tr><td colspan="12" style="text-align:center;padding:30px;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> ${data.message}</td></tr>`;
                document.getElementById('result-count').textContent = 'Error';
                return;
            }

            document.getElementById('result-count').textContent = data.length + ' resultado(s)';

            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="12" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-inbox" style="font-size:1.3rem"></i><br><br>Sin ventas para los filtros aplicados</td></tr>';
                return;
            }

            const pagoIcon   = { efectivo:'💵', yape:`<img src="${BASE}assets/img/yape_plin.jpg" alt="Yape" style="height:18px;vertical-align:middle;border-radius:3px">`, plin:`<img src="${BASE}assets/img/yape_plin.jpg" alt="Plin" style="height:18px;vertical-align:middle;border-radius:3px">`, tarjeta:'💳', transferencia:'🏦' };
            const coloresUser = ['#4f46e5','#0891b2','#16a34a','#d97706','#7c3aed','#dc2626','#0f766e','#c2410c'];
            const vendColorMap = {};
            let vendIdx = 0;

            document.getElementById('tabla-body').innerHTML = data.map(v => {
                const dt       = new Date(v.created_at);
                const fechaStr = dt.toLocaleDateString('es-PE') + ' ' + dt.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' });
                const esCls    = v.estado === 'completada' ? 'badge-success' : 'badge-danger';
                const sfx      = v.estado === 'anulada' ? 'style="opacity:.5"' : '';

                // Color único por vendedor
                const vend = v.vendedor || '—';
                if (vend !== '—' && !vendColorMap[vend]) {
                    vendColorMap[vend] = coloresUser[vendIdx++ % coloresUser.length];
                }
                const vendColor = vendColorMap[vend] || '#94a3b8';
                const vendBadge = `<span style="display:inline-flex;align-items:center;gap:4px;font-size:.78rem;color:${vendColor};font-weight:600">`
                    + `<span style="width:7px;height:7px;border-radius:50%;background:${vendColor};display:inline-block"></span>${vend}</span>`;

                const compBadge = v.comprobante_numero
                    ? `<span class="badge badge-primary" style="font-size:.7rem">${v.comprobante_numero}</span>`
                    : `<span class="badge badge-gray" style="text-transform:capitalize;font-size:.75rem">${v.tipo_comprobante}</span>`;
                const sunatBadge = v.comprobante_numero
                    ? (() => {
                        const txt  = v.estado_sunat || 'pendiente';
                        const ok   = txt.toLowerCase().includes('aceptado') || txt.toLowerCase().includes('sunat');
                        const warn = txt === 'pendiente';
                        const cls  = ok ? 'badge-success' : (warn ? 'badge-warning' : 'badge-danger');
                        const pdf  = v.enlace_pdf
                            ? ` <a href="${v.enlace_pdf}" target="_blank" title="Ver PDF" style="margin-left:4px"><i class="fas fa-file-pdf" style="color:#dc2626"></i></a>`
                            : '';
                        return `<span class="badge ${cls}" style="font-size:.7rem;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle" title="${txt}">${txt}</span>${pdf}`;
                    })()
                    : '<span style="color:var(--text-light);font-size:.8rem">—</span>';

                return `<tr ${sfx}>
                    <td><span style="font-weight:700;color:var(--primary);font-size:.88rem">${v.numero_venta}</span></td>
                    <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">${fechaStr}</td>
                    <td style="white-space:nowrap">${vendBadge}</td>
                    <td style="font-size:.82rem">
                        <div style="font-weight:500">${v.cliente}</div>
                        ${v.dni ? `<div style="color:var(--text-muted);font-size:.75rem">DNI: ${v.dni}</div>` : ''}
                        ${v.ruc ? `<div style="color:var(--text-muted);font-size:.75rem">RUC: ${v.ruc}</div>` : ''}
                    </td>
                    <td><span class="badge badge-gray">${v.num_items}</span></td>
                    <td style="font-size:.82rem;white-space:nowrap">${pagoIcon[v.tipo_pago]||''} ${v.tipo_pago}</td>
                    <td>${compBadge}</td>
                    <td class="text-right" style="font-size:.85rem">S/ ${parseFloat(v.subtotal).toFixed(2)}</td>
                    <td class="text-right" style="font-size:.85rem;color:var(--text-muted)">S/ ${parseFloat(v.igv).toFixed(2)}</td>
                    <td class="text-right"><strong style="font-size:.9rem">S/ ${parseFloat(v.total).toFixed(2)}</strong></td>
                    <td><span class="badge ${esCls}">${v.estado}</span></td>
                    <td style="max-width:130px">${sunatBadge}</td>
                </tr>`;
            }).join('');

            // Totales al pie (solo ventas completadas)
            const completadas = data.filter(v => v.estado === 'completada');
            const totSubtotal = completadas.reduce((s, v) => s + parseFloat(v.subtotal), 0);
            const totIgv      = completadas.reduce((s, v) => s + parseFloat(v.igv), 0);
            const totTotal    = completadas.reduce((s, v) => s + parseFloat(v.total), 0);
            document.getElementById('tabla-foot').innerHTML = `
                <tr style="background:var(--surface-2);font-weight:700">
                    <td colspan="7" style="padding:10px 14px;font-size:.82rem;color:var(--text-muted)">
                        Totales (${completadas.length} completadas de ${data.length})
                    </td>
                    <td class="text-right" style="padding:10px 14px">S/ ${totSubtotal.toFixed(2)}</td>
                    <td class="text-right" style="padding:10px 14px;color:var(--text-muted)">S/ ${totIgv.toFixed(2)}</td>
                    <td class="text-right" style="padding:10px 14px;color:var(--success);font-size:1rem">S/ ${totTotal.toFixed(2)}</td>
                    <td colspan="2"></td>
                </tr>`;
        })
        .catch(() => showToast('Error al cargar el reporte', 'error'));
}

// ---- Cargar lista de vendedores para el filtro ----
function loadUsuarios() {
    fetch(BASE + 'modules/facturacion/api.php?action=usuarios_lista')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('f-vendedor');
            data.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.vendedor;
                opt.textContent = u.vendedor;
                sel.appendChild(opt);
            });
        })
        .catch(() => {});
}

// ---- Exportar Excel (CSV) ----
function exportarExcel() {
    const url  = BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'exportar' });
    const link = document.createElement('a');
    link.href  = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('Descargando archivo Excel...', 'success');
}

// ---- Toast ----
function showToast(msg, type = 'info') {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    loadUsuarios();
    buscar();
    document.getElementById('f-q').addEventListener('keyup', e => { if (e.key === 'Enter') buscar(); });
});
</script>

<style>
.stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
.stat-icon.teal   { background: #f0fdfa; color: #0d9488; }
</style>

<?php include '../../includes/footer.php'; ?>
