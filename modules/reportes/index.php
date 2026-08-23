<?php
// ============================================================
// ARCHIVO: farmacia/modules/reportes/index.php
// MÓDULO:  Reportes (tabs: Costos de Compras | Ventas | Inventario | Caja)
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin', 'gerente'];
$current_module = 'reportes';
$current_page   = 'reportes';
$page_title     = 'Reportes — FarmaSystem';
$breadcrumb     = '<strong>Reportes</strong>';

include '../../includes/header.php';
?>

<style>
.rep-tabs {
    display: flex;
    gap: 6px;
    margin-bottom: 20px;
    background: var(--surface-2);
    border-radius: var(--radius);
    padding: 5px;
    width: fit-content;
    flex-wrap: wrap;
}
.rep-tab {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 8px 20px;
    border: none;
    border-radius: calc(var(--radius) - 2px);
    background: transparent;
    color: var(--text-muted);
    font-size: .88rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, color .15s;
    white-space: nowrap;
}
.rep-tab:hover { background: var(--surface); color: var(--text); }
.rep-tab.active { background: var(--primary); color: #fff; }
.rep-pane { }
@media (max-width: 640px) {
    .rep-tabs { width: 100%; }
}
</style>

<div class="page-header">
    <div>
        <div class="page-title"><i class="fas fa-chart-pie" style="color:var(--primary);margin-right:8px"></i>Reportes</div>
        <div class="page-subtitle">Costos, ventas, inventario y caja — filtra y exporta</div>
    </div>
</div>

<div class="rep-tabs">
    <button class="rep-tab active" id="rep-tab-btn-costos" onclick="repSwitchTab('costos')">
        <i class="fas fa-truck-loading"></i> Costos de Compras
    </button>
    <button class="rep-tab" id="rep-tab-btn-ventas" onclick="repSwitchTab('ventas')">
        <i class="fas fa-cash-register"></i> Ventas
    </button>
    <button class="rep-tab" id="rep-tab-btn-inventario" onclick="repSwitchTab('inventario')">
        <i class="fas fa-boxes"></i> Inventario
    </button>
    <button class="rep-tab" id="rep-tab-btn-caja" onclick="repSwitchTab('caja')">
        <i class="fas fa-cash-register"></i> Caja
    </button>
</div>

<!-- ============================================================
     PANE: COSTOS DE COMPRAS
     ============================================================ -->
<div id="rep-pane-costos" class="rep-pane">

    <div class="card" style="margin-bottom:20px">
        <div style="padding:10px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Desde</label>
                <input type="date" id="cc-desde" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Hasta</label>
                <input type="date" id="cc-hasta" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:180px">
                <label class="form-label">Proveedor</label>
                <select id="cc-proveedor" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Estado</label>
                <select id="cc-estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="borrador">Borrador</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="recibida">Recibida</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="ccBuscar()" style="margin-bottom:0">
                <i class="fas fa-search"></i> Buscar
            </button>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:0">
                <span style="font-size:.78rem;color:var(--text-muted)">Rápido:</span>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cc','hoy')">Hoy</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cc','semana')">Esta semana</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cc','mes')">Este mes</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cc','mes_ant')">Mes anterior</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="cc-stats">
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div><div><div class="stat-value" id="cc-st-ordenes">—</div><div class="stat-label">Órdenes</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-coins"></i></div><div><div class="stat-value" id="cc-st-subtotal">—</div><div class="stat-label">Subtotal</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-shipping-fast"></i></div><div><div class="stat-value" id="cc-st-envio">—</div><div class="stat-label">Costo de envío</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-receipt"></i></div><div><div class="stat-value" id="cc-st-total">—</div><div class="stat-label">Total general</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Detalle de órdenes de compra</div>
            <div style="display:flex;align-items:center;gap:12px;margin-left:auto">
                <span style="font-size:.82rem;color:var(--text-muted)" id="cc-result-count">—</span>
                <button class="btn btn-success btn-sm" onclick="ccExportar()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>N° Orden</th><th>Fecha</th><th>Proveedor</th><th>Estado</th>
                    <th class="text-right">Subtotal</th><th class="text-right">IGV</th>
                    <th class="text-right">Envío</th><th class="text-right">Total</th>
                </tr></thead>
                <tbody id="cc-tabla-body">
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /rep-pane-costos -->


<!-- ============================================================
     PANE: VENTAS
     ============================================================ -->
<div id="rep-pane-ventas" class="rep-pane" style="display:none">

    <div class="card" style="margin-bottom:20px">
        <div style="padding:10px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Desde</label>
                <input type="date" id="vt-desde" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Hasta</label>
                <input type="date" id="vt-hasta" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Agrupar por</label>
                <select id="vt-agrupar" class="form-control">
                    <option value="vendedor">Vendedor</option>
                    <option value="dia">Día</option>
                    <option value="comprobante">Tipo de comprobante</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="vtBuscar()" style="margin-bottom:0">
                <i class="fas fa-search"></i> Buscar
            </button>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:0">
                <span style="font-size:.78rem;color:var(--text-muted)">Rápido:</span>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('vt','hoy')">Hoy</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('vt','semana')">Esta semana</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('vt','mes')">Este mes</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('vt','mes_ant')">Mes anterior</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="vt-stats">
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon blue"><i class="fas fa-receipt"></i></div><div><div class="stat-value" id="vt-st-ventas">—</div><div class="stat-label">Ventas</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-sack-dollar"></i></div><div><div class="stat-value" id="vt-st-ingresos">—</div><div class="stat-label">Ingresos</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-tag"></i></div><div><div class="stat-value" id="vt-st-ticket">—</div><div class="stat-label">Ticket promedio</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-ban"></i></div><div><div class="stat-value" id="vt-st-anuladas">—</div><div class="stat-label">Anuladas</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title" id="vt-tabla-titulo">Ventas por vendedor</div>
            <div style="display:flex;align-items:center;gap:12px;margin-left:auto">
                <button class="btn btn-success btn-sm" onclick="vtExportar()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th id="vt-th-etiqueta">Vendedor</th>
                    <th class="text-right">N° Ventas</th><th class="text-right">Ingresos</th>
                    <th class="text-right">IGV</th><th class="text-right">Ticket promedio</th>
                    <th class="text-right">Anuladas</th>
                </tr></thead>
                <tbody id="vt-tabla-body">
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /rep-pane-ventas -->


<!-- ============================================================
     PANE: INVENTARIO (Valorización)
     ============================================================ -->
<div id="rep-pane-inventario" class="rep-pane" style="display:none">

    <div class="card" style="margin-bottom:20px">
        <div style="padding:10px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:180px">
                <label class="form-label">Buscar</label>
                <input type="text" id="inv-q" class="form-control" placeholder="Nombre o código...">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Categoría</label>
                <select id="inv-categoria" class="form-control">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Estado</label>
                <select id="inv-solo-activos" class="form-control">
                    <option value="1">Solo activos</option>
                    <option value="0">Todos</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="invBuscar()" style="margin-bottom:0">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4" id="inv-stats">
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon blue"><i class="fas fa-boxes"></i></div><div><div class="stat-value" id="inv-st-productos">—</div><div class="stat-label">Productos</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-coins"></i></div><div><div class="stat-value" id="inv-st-valor-compra">—</div><div class="stat-label">Valor (costo)</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-sack-dollar"></i></div><div><div class="stat-value" id="inv-st-valor-venta">—</div><div class="stat-label">Valor (venta)</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div><div><div class="stat-value" id="inv-st-alertas">—</div><div class="stat-label">Agotados / stock bajo</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Valorización por producto</div>
            <div style="display:flex;align-items:center;gap:12px;margin-left:auto">
                <span style="font-size:.82rem;color:var(--text-muted)" id="inv-result-count">—</span>
                <button class="btn btn-success btn-sm" onclick="invExportar()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Código</th><th>Producto</th><th>Categoría</th>
                    <th class="text-right">Stock</th><th class="text-right">P. Compra</th>
                    <th class="text-right">P. Venta</th><th class="text-right">Valor Inventario</th>
                </tr></thead>
                <tbody id="inv-tabla-body">
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /rep-pane-inventario -->


<!-- ============================================================
     PANE: CAJA (Movimientos)
     ============================================================ -->
<div id="rep-pane-caja" class="rep-pane" style="display:none">

    <div class="card" style="margin-bottom:20px">
        <div style="padding:10px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Desde</label>
                <input type="date" id="cj-desde" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Hasta</label>
                <input type="date" id="cj-hasta" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:150px">
                <label class="form-label">Tipo</label>
                <select id="cj-tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="egreso">Egreso</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="cjBuscar()" style="margin-bottom:0">
                <i class="fas fa-search"></i> Buscar
            </button>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:0">
                <span style="font-size:.78rem;color:var(--text-muted)">Rápido:</span>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cj','hoy')">Hoy</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cj','semana')">Esta semana</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cj','mes')">Este mes</button>
                <button class="btn btn-ghost btn-sm" onclick="repSetPeriodo('cj','mes_ant')">Mes anterior</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="cj-stats">
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon blue"><i class="fas fa-exchange-alt"></i></div><div><div class="stat-value" id="cj-st-movimientos">—</div><div class="stat-label">Movimientos</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-arrow-down"></i></div><div><div class="stat-value" id="cj-st-ingresos">—</div><div class="stat-label">Ingresos</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-arrow-up"></i></div><div><div class="stat-value" id="cj-st-egresos">—</div><div class="stat-label">Egresos</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-balance-scale"></i></div><div><div class="stat-value" id="cj-st-neto">—</div><div class="stat-label">Neto</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Movimientos de caja</div>
            <div style="display:flex;align-items:center;gap:12px;margin-left:auto">
                <button class="btn btn-success btn-sm" onclick="cjExportar()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Fecha</th><th>Caja</th><th>Tipo</th><th>Concepto</th>
                    <th>Usuario</th><th class="text-right">Monto</th>
                </tr></thead>
                <tbody id="cj-tabla-body">
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /rep-pane-caja -->

<div class="app-toast-container" id="toast-container"></div>

<script>
const BASE = '../../';
const API  = BASE + 'modules/reportes/api.php';
const _repTabLoaded = {};

function money(n) { return 'S/ ' + (parseFloat(n) || 0).toFixed(2); }
function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

function repSwitchTab(tab) {
    document.querySelectorAll('.rep-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('rep-tab-btn-' + tab).classList.add('active');
    document.querySelectorAll('.rep-pane').forEach(p => p.style.display = 'none');
    document.getElementById('rep-pane-' + tab).style.display = '';

    if (!_repTabLoaded[tab]) {
        _repTabLoaded[tab] = true;
        if (tab === 'costos')     { ccCargarProveedores(); ccBuscar(); }
        if (tab === 'ventas')     { vtBuscar(); }
        if (tab === 'inventario') { invCargarCategorias(); invBuscar(); }
        if (tab === 'caja')       { cjBuscar(); }
    }
}

function repSetPeriodo(prefix, p) {
    const hoy = new Date(), fmt = d => d.toISOString().slice(0,10);
    let desde, hasta;
    switch (p) {
        case 'hoy':  desde = hasta = fmt(hoy); break;
        case 'semana': { const l = new Date(hoy); l.setDate(hoy.getDate()-((hoy.getDay()+6)%7)); desde=fmt(l); hasta=fmt(hoy); break; }
        case 'mes':     desde = fmt(new Date(hoy.getFullYear(),hoy.getMonth(),1)); hasta = fmt(hoy); break;
        case 'mes_ant': { const f=new Date(hoy.getFullYear(),hoy.getMonth()-1,1),l=new Date(hoy.getFullYear(),hoy.getMonth(),0); desde=fmt(f); hasta=fmt(l); break; }
    }
    document.getElementById(prefix + '-desde').value = desde;
    document.getElementById(prefix + '-hasta').value = hasta;
    if (prefix === 'cc') ccBuscar();
    if (prefix === 'vt') vtBuscar();
    if (prefix === 'cj') cjBuscar();
}

function repDownload(url) {
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link); link.click(); document.body.removeChild(link);
    showToast('Descargando archivo Excel...', 'success');
}

// ================================================================
// COSTOS DE COMPRAS
// ================================================================

function ccCargarProveedores() {
    fetch(API + '?action=proveedores_lista')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('cc-proveedor');
            (data || []).forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id; opt.textContent = p.razon_social;
                sel.appendChild(opt);
            });
        })
        .catch(() => {});
}

function ccParams(extra = {}) {
    return new URLSearchParams({
        desde: document.getElementById('cc-desde').value,
        hasta: document.getElementById('cc-hasta').value,
        proveedor_id: document.getElementById('cc-proveedor').value,
        estado: document.getElementById('cc-estado').value,
        ...extra
    }).toString();
}

function ccBuscar() {
    document.getElementById('cc-tabla-body').innerHTML =
        '<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(API + '?action=costos_compras_stats&' + ccParams())
        .then(r => r.json())
        .then(s => {
            document.getElementById('cc-st-ordenes').textContent  = s.total_ordenes ?? 0;
            document.getElementById('cc-st-subtotal').textContent = money(s.total_subtotal);
            document.getElementById('cc-st-envio').textContent    = money(s.total_envio);
            document.getElementById('cc-st-total').textContent    = money(s.total_general);
        })
        .catch(() => {});

    fetch(API + '?action=costos_compras&' + ccParams())
        .then(r => r.json())
        .then(data => {
            const rows = Array.isArray(data) ? data : [];
            document.getElementById('cc-result-count').textContent = rows.length + ' orden(es)';
            if (!rows.length) {
                document.getElementById('cc-tabla-body').innerHTML =
                    '<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-light)">Sin resultados</td></tr>';
                return;
            }
            const badgeEstado = { borrador:'badge-gray', pendiente:'badge-warning', aprobada:'badge-primary', recibida:'badge-success', cancelada:'badge-danger' };
            document.getElementById('cc-tabla-body').innerHTML = rows.map(o => {
                const dt = new Date(o.created_at);
                return `<tr>
                    <td><strong>${esc(o.numero_orden)}</strong></td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${dt.toLocaleDateString('es-PE')}</td>
                    <td>${esc(o.proveedor)}</td>
                    <td><span class="badge ${badgeEstado[o.estado]||'badge-gray'}" style="text-transform:capitalize">${esc(o.estado)}</span></td>
                    <td class="text-right">${money(o.subtotal)}</td>
                    <td class="text-right">${money(o.igv)}</td>
                    <td class="text-right">${money(o.costo_envio)}</td>
                    <td class="text-right"><strong>${money(o.total)}</strong></td>
                </tr>`;
            }).join('');
        })
        .catch(() => showToast('Error al cargar el reporte de compras', 'error'));
}

function ccExportar() { repDownload(API + '?action=costos_compras_exportar&' + ccParams()); }

// ================================================================
// VENTAS
// ================================================================

function vtParams(extra = {}) {
    return new URLSearchParams({
        desde: document.getElementById('vt-desde').value,
        hasta: document.getElementById('vt-hasta').value,
        agrupar: document.getElementById('vt-agrupar').value,
        ...extra
    }).toString();
}

function vtBuscar() {
    const agrupar = document.getElementById('vt-agrupar').value;
    const labels = { vendedor: 'Vendedor', dia: 'Día', comprobante: 'Tipo de comprobante' };
    document.getElementById('vt-th-etiqueta').textContent = labels[agrupar] || 'Vendedor';
    document.getElementById('vt-tabla-titulo').textContent = 'Ventas por ' + (labels[agrupar] || 'Vendedor').toLowerCase();

    document.getElementById('vt-tabla-body').innerHTML =
        '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(API + '?action=ventas_reporte&' + vtParams())
        .then(r => r.json())
        .then(data => {
            const rows = Array.isArray(data) ? data : [];

            const totVentas = rows.reduce((a,r) => a + parseInt(r.total_ventas||0), 0);
            const totIngresos = rows.reduce((a,r) => a + parseFloat(r.total_ingresos||0), 0);
            const totAnuladas = rows.reduce((a,r) => a + parseInt(r.total_anuladas||0), 0);
            document.getElementById('vt-st-ventas').textContent    = totVentas;
            document.getElementById('vt-st-ingresos').textContent  = money(totIngresos);
            document.getElementById('vt-st-ticket').textContent    = money(totVentas ? totIngresos/totVentas : 0);
            document.getElementById('vt-st-anuladas').textContent  = totAnuladas;

            if (!rows.length) {
                document.getElementById('vt-tabla-body').innerHTML =
                    '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-light)">Sin resultados</td></tr>';
                return;
            }
            document.getElementById('vt-tabla-body').innerHTML = rows.map(r => `<tr>
                <td style="text-transform:capitalize">${esc(r.etiqueta)}</td>
                <td class="text-right">${r.total_ventas}</td>
                <td class="text-right"><strong>${money(r.total_ingresos)}</strong></td>
                <td class="text-right">${money(r.total_igv)}</td>
                <td class="text-right">${money(r.ticket_promedio)}</td>
                <td class="text-right">${r.total_anuladas}</td>
            </tr>`).join('');
        })
        .catch(() => showToast('Error al cargar el reporte de ventas', 'error'));
}

function vtExportar() { repDownload(API + '?action=ventas_exportar&' + vtParams()); }

// ================================================================
// INVENTARIO (Valorización)
// ================================================================

function invCargarCategorias() {
    fetch(API + '?action=categorias_lista')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('inv-categoria');
            (data || []).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id; opt.textContent = c.nombre;
                sel.appendChild(opt);
            });
        })
        .catch(() => {});
}

function invParams(extra = {}) {
    return new URLSearchParams({
        q: document.getElementById('inv-q').value,
        categoria_id: document.getElementById('inv-categoria').value,
        solo_activos: document.getElementById('inv-solo-activos').value,
        ...extra
    }).toString();
}

function invBuscar() {
    document.getElementById('inv-tabla-body').innerHTML =
        '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(API + '?action=inventario_valorizacion_stats&' + invParams())
        .then(r => r.json())
        .then(s => {
            document.getElementById('inv-st-productos').textContent   = s.total_productos ?? 0;
            document.getElementById('inv-st-valor-compra').textContent = money(s.valor_total_compra);
            document.getElementById('inv-st-valor-venta').textContent  = money(s.valor_total_venta);
            document.getElementById('inv-st-alertas').textContent      = `${s.agotados ?? 0} / ${s.stock_bajo ?? 0}`;
        })
        .catch(() => {});

    fetch(API + '?action=inventario_valorizacion&' + invParams())
        .then(r => r.json())
        .then(data => {
            const rows = Array.isArray(data) ? data : [];
            document.getElementById('inv-result-count').textContent = rows.length + ' producto(s)';
            if (!rows.length) {
                document.getElementById('inv-tabla-body').innerHTML =
                    '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-light)">Sin resultados</td></tr>';
                return;
            }
            document.getElementById('inv-tabla-body').innerHTML = rows.map(p => `<tr>
                <td style="font-size:.82rem;color:var(--text-muted)">${esc(p.codigo)}</td>
                <td>${esc(p.nombre)}</td>
                <td>${esc(p.categoria)}</td>
                <td class="text-right">${p.stock}</td>
                <td class="text-right">${money(p.precio_compra)}</td>
                <td class="text-right">${money(p.precio_venta)}</td>
                <td class="text-right"><strong>${money(p.valor_inventario)}</strong></td>
            </tr>`).join('');
        })
        .catch(() => showToast('Error al cargar la valorización de inventario', 'error'));
}

function invExportar() { repDownload(API + '?action=inventario_valorizacion_exportar&' + invParams()); }

// ================================================================
// CAJA (Movimientos)
// ================================================================

function cjParams(extra = {}) {
    return new URLSearchParams({
        desde: document.getElementById('cj-desde').value,
        hasta: document.getElementById('cj-hasta').value,
        tipo: document.getElementById('cj-tipo').value,
        ...extra
    }).toString();
}

function cjBuscar() {
    document.getElementById('cj-tabla-body').innerHTML =
        '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(API + '?action=caja_movimientos_stats&' + cjParams())
        .then(r => r.json())
        .then(s => {
            document.getElementById('cj-st-movimientos').textContent = s.total_movimientos ?? 0;
            document.getElementById('cj-st-ingresos').textContent    = money(s.total_ingresos);
            document.getElementById('cj-st-egresos').textContent     = money(s.total_egresos);
            document.getElementById('cj-st-neto').textContent        = money(s.neto);
        })
        .catch(() => {});

    fetch(API + '?action=caja_movimientos&' + cjParams())
        .then(r => r.json())
        .then(data => {
            const rows = Array.isArray(data) ? data : [];
            if (!rows.length) {
                document.getElementById('cj-tabla-body').innerHTML =
                    '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-light)">Sin resultados</td></tr>';
                return;
            }
            document.getElementById('cj-tabla-body').innerHTML = rows.map(m => {
                const dt = new Date(m.created_at);
                const fechaStr = dt.toLocaleDateString('es-PE') + ' ' + dt.toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit'});
                return `<tr>
                    <td style="font-size:.82rem;color:var(--text-muted)">${fechaStr}</td>
                    <td>${esc(m.caja_nombre)}</td>
                    <td><span class="badge ${m.tipo === 'ingreso' ? 'badge-success' : 'badge-danger'}" style="text-transform:capitalize">${esc(m.tipo)}</span></td>
                    <td>${esc(m.concepto)}</td>
                    <td style="font-size:.85rem">${esc(m.usuario)}</td>
                    <td class="text-right"><strong>${money(m.monto)}</strong></td>
                </tr>`;
            }).join('');
        })
        .catch(() => showToast('Error al cargar los movimientos de caja', 'error'));
}

function cjExportar() { repDownload(API + '?action=caja_movimientos_exportar&' + cjParams()); }

// ================================================================
// TOASTS
// ================================================================
function showToast(msg, type = 'info') {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const toast = document.createElement('div');
    toast.className = `app-toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ================================================================
// INIT
// ================================================================
ccCargarProveedores();
ccBuscar();
_repTabLoaded.costos = true;
</script>

<?php include '../../includes/footer.php'; ?>
