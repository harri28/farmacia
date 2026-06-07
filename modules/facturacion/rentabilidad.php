<?php
// ============================================================
// ARCHIVO: farmacia/modules/facturacion/rentabilidad.php
// MÓDULO:  Facturación → Rentabilidad
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin', 'gerente', 'cajero'];
$current_module = 'facturacion';
$current_page   = 'rentabilidad';
$page_title     = 'Rentabilidad — FarmaSystem';
$breadcrumb     = '<strong>Facturación</strong> / Rentabilidad';

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">
            <i class="fas fa-chart-pie" style="color:var(--primary);margin-right:8px"></i>Rentabilidad
        </div>
        <div class="page-subtitle">Análisis de márgenes, ganancia bruta y ROI por período</div>
    </div>
    <div class="page-actions">
        <div style="background:#fefce8;border:1px solid #fde68a;border-radius:var(--radius);padding:6px 12px;font-size:.78rem;color:#92400e;display:flex;align-items:center;gap:6px">
            <i class="fas fa-info-circle"></i>
            Costos calculados con el precio de compra actual del producto
        </div>
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
        <div class="form-group" style="margin:0;flex:1;min-width:150px">
            <label class="form-label">Categoría</label>
            <select class="form-control" id="f-categoria">
                <option value="0">Todas</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
            <label class="form-label">Vendedor</label>
            <select class="form-control" id="f-vendedor">
                <option value="">Todos</option>
            </select>
        </div>
        <button class="btn btn-primary" onclick="buscar()">
            <i class="fas fa-search"></i> Calcular
        </button>

    </div>
    <div style="padding:0 20px 16px;display:flex;gap:8px;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--text-muted);align-self:center">Período:</span>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('hoy')">Hoy</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('semana')">Esta semana</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('mes')">Este mes</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('mes_ant')">Mes anterior</button>
        <button class="btn btn-ghost btn-sm" onclick="setPeriodo('trimestre')">Trimestre</button>
    </div>
</div>

<!-- ======================================================
     STATS CARDS
     ====================================================== -->
<div class="stat-cards" id="stats-container">
    <?php foreach (range(0,4) as $_): ?>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-spinner fa-spin"></i></div>
        <div><div class="stat-value">—</div><div class="stat-label">Cargando...</div></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ======================================================
     CATEGORÍAS + TOP PRODUCTOS
     ====================================================== -->
<div style="display:grid;grid-template-columns:3fr 2fr;gap:20px;margin-bottom:20px">

    <!-- Por categoría -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-tags" style="color:var(--primary)"></i> Rentabilidad por categoría
            </div>
        </div>
        <div id="tabla-categorias">
            <div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>

    <!-- Top 10 productos -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-trophy" style="color:#f59e0b"></i> Top 10 más rentables
            </div>
        </div>
        <div id="tabla-top">
            <div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>

</div>

<!-- ======================================================
     TENDENCIA DIARIA
     ====================================================== -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-chart-area" style="color:var(--primary)"></i> Tendencia de ganancia
        </div>
        <div style="display:flex;gap:12px;align-items:center;margin-left:auto;font-size:.78rem">
            <span style="display:flex;align-items:center;gap:5px"><span style="width:10px;height:10px;border-radius:2px;background:#16a34a;display:inline-block"></span>Ganancia</span>
            <span style="display:flex;align-items:center;gap:5px"><span style="width:10px;height:10px;border-radius:2px;background:#3b82f6;display:inline-block"></span>Ingresos</span>
            <span style="display:flex;align-items:center;gap:5px"><span style="width:10px;height:10px;border-radius:2px;background:#f59e0b;display:inline-block"></span>Costo</span>
        </div>
    </div>
    <div id="chart-wrap" style="padding:0 16px 16px">
        <div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
</div>

<!-- ======================================================
     TABLA COMPLETA DE PRODUCTOS
     ====================================================== -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-table" style="color:var(--primary)"></i> Todos los productos
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin-left:auto">
            <div class="input-group" style="width:220px">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-prod-q" class="form-control" placeholder="Buscar producto..."
                    oninput="filtrarProductos()">
            </div>
            <span style="font-size:.82rem;color:var(--text-muted)" id="prod-count">—</span>
        </div>
    </div>
    <div class="table-wrap">
        <table id="tabla-productos">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th class="sortable" data-col="producto" onclick="sortTabla('producto')">Producto <i class="fas fa-sort" style="font-size:.7rem;color:var(--text-muted)"></i></th>
                    <th class="sortable" data-col="categoria" onclick="sortTabla('categoria')">Categoría <i class="fas fa-sort" style="font-size:.7rem;color:var(--text-muted)"></i></th>
                    <th class="sortable text-right" data-col="unidades" onclick="sortTabla('unidades')">Uds <i class="fas fa-sort" style="font-size:.7rem;color:var(--text-muted)"></i></th>
                    <th class="sortable text-right" data-col="ingresos" onclick="sortTabla('ingresos')">Ingresos <i class="fas fa-sort" style="font-size:.7rem;color:var(--text-muted)"></i></th>
                    <th class="sortable text-right" data-col="costo" onclick="sortTabla('costo')">Costo <i class="fas fa-sort" style="font-size:.7rem;color:var(--text-muted)"></i></th>
                    <th class="sortable text-right" data-col="ganancia" onclick="sortTabla('ganancia')">Ganancia <i class="fas fa-sort" style="font-size:.7rem;color:var(--text-muted)"></i></th>
                    <th class="sortable text-right" data-col="margen_pct" onclick="sortTabla('margen_pct')">Margen % <i class="fas fa-sort" style="font-size:.7rem;color:var(--text-muted)"></i></th>
                    <th style="min-width:100px">Indicador</th>
                </tr>
            </thead>
            <tbody id="prod-body">
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)">
                    <i class="fas fa-spinner fa-spin"></i>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
const BASE = '../../';
let allProductos = [];
let sortCol      = 'ganancia';
let sortDir      = -1; // -1 = DESC, 1 = ASC

// ---- Filtros ----
function getParams() {
    return {
        desde:        document.getElementById('f-desde').value,
        hasta:        document.getElementById('f-hasta').value,
        categoria_id: document.getElementById('f-categoria').value,
        vendedor:     document.getElementById('f-vendedor').value,
    };
}
function buildQuery(extra = {}) {
    return new URLSearchParams({ ...getParams(), ...extra }).toString();
}

function setPeriodo(p) {
    const hoy = new Date();
    const fmt  = d => d.toISOString().slice(0, 10);
    let desde, hasta;
    switch (p) {
        case 'hoy':      desde = hasta = fmt(hoy); break;
        case 'semana': {
            const lun = new Date(hoy);
            lun.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
            desde = fmt(lun); hasta = fmt(hoy); break;
        }
        case 'mes':
            desde = fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            hasta = fmt(hoy); break;
        case 'mes_ant': {
            desde = fmt(new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1));
            hasta = fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 0)); break;
        }
        case 'trimestre': {
            const q = Math.floor(hoy.getMonth() / 3);
            desde = fmt(new Date(hoy.getFullYear(), q * 3, 1));
            hasta = fmt(hoy); break;
        }
    }
    document.getElementById('f-desde').value = desde;
    document.getElementById('f-hasta').value = hasta;
    buscar();
}

function resetFiltros() {
    const hoy = new Date();
    document.getElementById('f-desde').value    = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().slice(0,10);
    document.getElementById('f-hasta').value    = hoy.toISOString().slice(0,10);
    document.getElementById('f-categoria').value = '0';
    document.getElementById('f-vendedor').value  = '';
    document.getElementById('f-prod-q').value    = '';
    buscar();
}

// ---- Inicialización de filtros ----
function loadFiltros() {
    fetch(BASE + 'modules/facturacion/api.php?action=categorias_lista')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('f-categoria');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id; opt.textContent = c.nombre;
                sel.appendChild(opt);
            });
        }).catch(() => {});

    fetch(BASE + 'modules/facturacion/api.php?action=usuarios_lista')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('f-vendedor');
            data.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.vendedor; opt.textContent = u.vendedor;
                sel.appendChild(opt);
            });
        }).catch(() => {});
}

// ---- Buscar ----
function buscar() {
    loadStats();
    loadCategorias();
    loadTopProductos();
    loadTendencia();
}

// ---- Stats ----
function loadStats() {
    document.getElementById('stats-container').innerHTML = [0,1,2,3,4].map(() =>
        `<div class="stat-card"><div class="stat-icon blue"><i class="fas fa-spinner fa-spin"></i></div>
         <div><div class="stat-value">—</div><div class="stat-label">Cargando...</div></div></div>`
    ).join('');

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'rentabilidad_stats' }))
        .then(r => r.json())
        .then(d => {
            if (!d || d.error) { showToast('Error al cargar estadísticas', 'error'); return; }
            const n = v => parseFloat(v || 0);
            const m = v => 'S/ ' + n(v).toFixed(2);
            const margenColor = n(d.margen_pct) >= 20 ? 'green' : (n(d.margen_pct) >= 10 ? 'yellow' : 'red');
            const roiColor    = n(d.roi_pct)    >= 25 ? 'green' : (n(d.roi_pct)    >= 10 ? 'yellow' : 'red');
            const cards = [
                { icon: 'dollar-sign',    color: 'blue',       val: m(d.total_ingresos), label: 'Ingresos (ventas)' },
                { icon: 'shopping-bag',   color: 'red',        val: m(d.total_costo),    label: 'Costo estimado' },
                { icon: 'chart-line',     color: 'green',      val: m(d.ganancia_bruta), label: 'Ganancia bruta' },
                { icon: 'percentage',     color: margenColor,  val: n(d.margen_pct).toFixed(1) + '%', label: 'Margen bruto' },
                { icon: 'redo',           color: roiColor,     val: n(d.roi_pct).toFixed(1) + '%',    label: 'ROI estimado' },
            ];
            document.getElementById('stats-container').innerHTML = cards.map(c => `
                <div class="stat-card">
                    <div class="stat-icon ${c.color}"><i class="fas fa-${c.icon}"></i></div>
                    <div><div class="stat-value">${c.val}</div><div class="stat-label">${c.label}</div></div>
                </div>`).join('');
        })
        .catch(() => showToast('Error al cargar estadísticas', 'error'));
}

// ---- Por categoría ----
function loadCategorias() {
    document.getElementById('tabla-categorias').innerHTML =
        '<div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>';

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'rentabilidad_categorias' }))
        .then(r => r.json())
        .then(data => {
            if (!data || data.error || !data.length) {
                document.getElementById('tabla-categorias').innerHTML =
                    '<p style="padding:16px;text-align:center;color:var(--text-muted);font-size:.85rem">Sin datos</p>';
                return;
            }
            const maxGan = Math.max(...data.map(d => parseFloat(d.ganancia)));
            const rows = data.map(d => {
                const gan    = parseFloat(d.ganancia);
                const pct    = maxGan > 0 ? gan / maxGan * 100 : 0;
                const mCls   = parseFloat(d.margen_pct) >= 20 ? 'color:var(--success)' :
                               (parseFloat(d.margen_pct) >= 10 ? 'color:var(--warning,#f59e0b)' : 'color:var(--danger)');
                return `<tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:10px 14px;font-weight:600;font-size:.85rem">${d.categoria}</td>
                    <td style="padding:10px 14px;text-align:right;font-size:.82rem;color:var(--text-muted)">${d.num_ventas}</td>
                    <td style="padding:10px 14px;text-align:right;font-size:.82rem">S/ ${parseFloat(d.ingresos).toFixed(2)}</td>
                    <td style="padding:10px 14px;text-align:right;font-size:.82rem;color:var(--text-muted)">S/ ${parseFloat(d.costo).toFixed(2)}</td>
                    <td style="padding:10px 14px;text-align:right;font-size:.85rem;font-weight:700;color:${gan >= 0 ? 'var(--success)' : 'var(--danger)'}">S/ ${gan.toFixed(2)}</td>
                    <td style="padding:10px 14px">
                        <div style="display:flex;align-items:center;gap:6px">
                            <div style="flex:1;background:#f1f5f9;border-radius:3px;height:6px;overflow:hidden;min-width:40px">
                                <div style="width:${Math.max(pct,0)}%;height:100%;background:${gan >= 0 ? '#16a34a' : '#dc2626'};border-radius:3px"></div>
                            </div>
                            <span style="font-size:.78rem;min-width:38px;text-align:right;font-weight:600;${mCls}">${parseFloat(d.margen_pct).toFixed(1)}%</span>
                        </div>
                    </td>
                </tr>`;
            }).join('');

            document.getElementById('tabla-categorias').innerHTML = `
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr style="background:var(--surface-2)">
                        <th style="padding:8px 14px;text-align:left;font-size:.75rem;color:var(--text-muted);font-weight:600">Categoría</th>
                        <th style="padding:8px 14px;text-align:right;font-size:.75rem;color:var(--text-muted);font-weight:600">Ventas</th>
                        <th style="padding:8px 14px;text-align:right;font-size:.75rem;color:var(--text-muted);font-weight:600">Ingresos</th>
                        <th style="padding:8px 14px;text-align:right;font-size:.75rem;color:var(--text-muted);font-weight:600">Costo</th>
                        <th style="padding:8px 14px;text-align:right;font-size:.75rem;color:var(--text-muted);font-weight:600">Ganancia</th>
                        <th style="padding:8px 14px;font-size:.75rem;color:var(--text-muted);font-weight:600;min-width:120px">Margen</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>`;
        })
        .catch(() => showToast('Error al cargar categorías', 'error'));
}

// ---- Top 10 productos + tabla completa (misma petición) ----
function loadTopProductos() {
    document.getElementById('tabla-top').innerHTML =
        '<div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>';
    document.getElementById('prod-body').innerHTML =
        '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';
    document.getElementById('prod-count').textContent = '—';

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'rentabilidad_productos' }))
        .then(r => r.json())
        .then(data => {
            allProductos = Array.isArray(data) && !data.error ? data : [];
            renderTablaProductos();

            if (!allProductos.length) {
                document.getElementById('tabla-top').innerHTML =
                    '<p style="padding:16px;text-align:center;color:var(--text-muted);font-size:.85rem">Sin datos</p>';
                return;
            }
            const top10  = allProductos.slice(0, 10);
            const maxGan = parseFloat(top10[0]?.ganancia || 0);
            const medals = ['🥇','🥈','🥉'];
            const rows = top10.map((p, i) => {
                const gan = parseFloat(p.ganancia);
                return `<div style="display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--border)">
                    <span style="width:20px;text-align:center;font-size:.9rem;flex-shrink:0">${medals[i] || `<span style="font-size:.75rem;color:var(--text-muted);font-weight:700">${i+1}</span>`}</span>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.84rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${p.producto}">${p.producto}</div>
                        <div style="font-size:.72rem;color:var(--text-muted)">${p.categoria} · ${p.unidades} uds</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:.88rem;font-weight:700;color:${gan >= 0 ? 'var(--success)' : 'var(--danger)'}">S/ ${gan.toFixed(2)}</div>
                        <div style="font-size:.72rem;color:var(--text-muted)">${parseFloat(p.margen_pct).toFixed(1)}% margen</div>
                    </div>
                </div>`;
            }).join('');
            document.getElementById('tabla-top').innerHTML = rows;
        })
        .catch(() => showToast('Error al cargar productos', 'error'));
}

// ---- Tendencia diaria ----
function loadTendencia() {
    document.getElementById('chart-wrap').innerHTML =
        '<div style="padding:24px;text-align:center;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></div>';

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'rentabilidad_tendencia' }))
        .then(r => r.json())
        .then(data => {
            if (!data || data.error || !data.length) {
                document.getElementById('chart-wrap').innerHTML =
                    '<p style="text-align:center;color:var(--text-muted);padding:24px;font-size:.85rem">Sin datos para el período</p>';
                return;
            }
            renderTendencia(data);
        })
        .catch(() => {
            document.getElementById('chart-wrap').innerHTML =
                '<p style="text-align:center;color:var(--text-muted);padding:24px;font-size:.85rem">Error al cargar tendencia</p>';
        });
}

function renderTendencia(data) {
    const maxIng = Math.max(...data.map(d => parseFloat(d.ingresos)));
    const maxAbs = Math.max(maxIng, 1);

    const labels = data.length <= 14
        ? data.map(d => { const f = new Date(d.fecha + 'T00:00:00'); return f.toLocaleDateString('es-PE', {day:'2-digit',month:'short'}); })
        : data.map(d => new Date(d.fecha + 'T00:00:00').getDate().toString().padStart(2,'0'));

    const barGroups = data.map((d, i) => {
        const ing  = parseFloat(d.ingresos);
        const cos  = parseFloat(d.costo);
        const gan  = parseFloat(d.ganancia);
        const hIng = Math.round(ing / maxAbs * 100);
        const hCos = Math.round(cos / maxAbs * 100);
        const hGan = Math.round(Math.abs(gan) / maxAbs * 100);
        const ganColor = gan >= 0 ? '#16a34a' : '#dc2626';
        const tip = `${d.fecha}\nIngresos: S/ ${ing.toFixed(2)}\nCosto: S/ ${cos.toFixed(2)}\nGanancia: S/ ${gan.toFixed(2)}`;
        return `<div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:0;min-width:0" title="${tip}">
            <div style="width:100%;display:flex;gap:1px;align-items:flex-end;height:120px">
                <div style="flex:1;height:${hIng}%;background:#3b82f6;border-radius:2px 2px 0 0;min-height:2px;transition:.3s;opacity:.75"></div>
                <div style="flex:1;height:${hCos}%;background:#f59e0b;border-radius:2px 2px 0 0;min-height:2px;transition:.3s;opacity:.75"></div>
                <div style="flex:1;height:${hGan}%;background:${ganColor};border-radius:2px 2px 0 0;min-height:2px;transition:.3s"></div>
            </div>
        </div>`;
    }).join('');

    const labelRow = labels.map(l =>
        `<div style="flex:1;text-align:center;font-size:.62rem;color:var(--text-muted);overflow:hidden;white-space:nowrap">${l}</div>`
    ).join('');

    // Calcular totales del período
    const totIng = data.reduce((s, d) => s + parseFloat(d.ingresos), 0);
    const totCos = data.reduce((s, d) => s + parseFloat(d.costo), 0);
    const totGan = data.reduce((s, d) => s + parseFloat(d.ganancia), 0);
    const ganColor = totGan >= 0 ? 'var(--success)' : 'var(--danger)';

    document.getElementById('chart-wrap').innerHTML = `
        <div style="display:flex;align-items:flex-end;gap:4px;border-bottom:2px solid var(--border);padding-bottom:0">
            ${barGroups}
        </div>
        <div style="display:flex;gap:4px;margin-top:5px;margin-bottom:16px">
            ${labelRow}
        </div>
        <div style="display:flex;gap:24px;justify-content:center;padding:8px 0;background:var(--surface-2);border-radius:var(--radius);font-size:.82rem">
            <span>Ingresos: <strong style="color:#3b82f6">S/ ${totIng.toFixed(2)}</strong></span>
            <span>Costo: <strong style="color:#f59e0b">S/ ${totCos.toFixed(2)}</strong></span>
            <span>Ganancia: <strong style="color:${ganColor}">S/ ${totGan.toFixed(2)}</strong></span>
            <span style="color:var(--text-muted)">Período: <strong>${data.length} punto(s)</strong></span>
        </div>`;
}

function renderTablaProductos() {
    const q   = document.getElementById('f-prod-q').value.toLowerCase();
    const data = allProductos.filter(p =>
        !q || p.producto.toLowerCase().includes(q) || p.categoria.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q)
    );

    // Sort
    data.sort((a, b) => {
        const av = isNaN(a[sortCol]) ? (a[sortCol] || '').toLowerCase() : parseFloat(a[sortCol]);
        const bv = isNaN(b[sortCol]) ? (b[sortCol] || '').toLowerCase() : parseFloat(b[sortCol]);
        return av < bv ? sortDir : av > bv ? -sortDir : 0;
    });

    document.getElementById('prod-count').textContent = data.length + ' producto(s)';

    // Update sort icons
    document.querySelectorAll('.sortable').forEach(th => {
        const col = th.dataset.col;
        const ico = th.querySelector('i');
        if (ico) ico.className = col === sortCol
            ? (sortDir === -1 ? 'fas fa-sort-down' : 'fas fa-sort-up')
            : 'fas fa-sort';
        ico && (ico.style.color = col === sortCol ? 'var(--primary)' : 'var(--text-muted)');
    });

    if (!data.length) {
        document.getElementById('prod-body').innerHTML =
            '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-box-open" style="font-size:1.3rem"></i><br><br>Sin productos para los filtros</td></tr>';
        return;
    }

    const maxGan = Math.max(...data.map(d => Math.abs(parseFloat(d.ganancia))));

    document.getElementById('prod-body').innerHTML = data.map((p, i) => {
        const gan    = parseFloat(p.ganancia);
        const mar    = parseFloat(p.margen_pct);
        const pct    = maxGan > 0 ? Math.abs(gan) / maxGan * 100 : 0;
        const ganClr = gan >= 0 ? 'var(--success)' : 'var(--danger)';
        const marClr = mar >= 20 ? 'color:var(--success)' : (mar >= 10 ? 'color:var(--warning,#f59e0b)' : 'color:var(--danger)');
        const barClr = gan >= 0 ? '#16a34a' : '#dc2626';
        return `<tr>
            <td style="font-size:.78rem;color:var(--text-muted);text-align:center">${i + 1}</td>
            <td style="font-weight:500;font-size:.85rem">
                ${p.producto}
                <div style="font-size:.72rem;color:var(--text-muted);font-family:monospace">${p.codigo}</div>
            </td>
            <td style="font-size:.8rem;color:var(--text-muted)">${p.categoria}</td>
            <td class="text-right" style="font-size:.85rem">${p.unidades}</td>
            <td class="text-right" style="font-size:.85rem">S/ ${parseFloat(p.ingresos).toFixed(2)}</td>
            <td class="text-right" style="font-size:.85rem;color:var(--text-muted)">S/ ${parseFloat(p.costo).toFixed(2)}</td>
            <td class="text-right" style="font-size:.9rem;font-weight:700;color:${ganClr}">S/ ${gan.toFixed(2)}</td>
            <td class="text-right" style="font-size:.85rem;font-weight:600;${marClr}">${mar.toFixed(1)}%</td>
            <td>
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="flex:1;background:#f1f5f9;border-radius:3px;height:7px;overflow:hidden">
                        <div style="width:${pct.toFixed(1)}%;height:100%;background:${barClr};border-radius:3px;transition:.3s"></div>
                    </div>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filtrarProductos() { renderTablaProductos(); }

function sortTabla(col) {
    if (sortCol === col) { sortDir *= -1; }
    else { sortCol = col; sortDir = -1; }
    renderTablaProductos();
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
    loadFiltros();
    buscar();
});
</script>

<style>
.stat-icon.red    { background:#fef2f2; color:#dc2626; }
.stat-icon.purple { background:#f5f3ff; color:#7c3aed; }
.stat-icon.teal   { background:#f0fdfa; color:#0d9488; }
.sortable { cursor:pointer; user-select:none; }
.sortable:hover { background:var(--surface-2); }
</style>

<?php include '../../includes/footer.php'; ?>
