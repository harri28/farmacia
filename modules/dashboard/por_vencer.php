<?php
// ============================================================
// ARCHIVO: farmacia/modules/dashboard/por_vencer.php
// DESCRIPCIÓN: Reporte imprimible de productos por vencer.
//   Lista completa (stock > 0, vencidos o que vencen en <= 90 días),
//   filtrable por categoría y estado, con selección por producto e
//   impresión / guardado en PDF vía el diálogo de impresión del navegador.
// ============================================================

require_once '../../config/database.php';

$required_roles = ['admin', 'gerente'];
$base_path      = '../../';
$current_module = 'dashboard';
$current_page   = 'inicio';
$page_title     = 'Productos por vencer — FarmaSystem';
$breadcrumb     = '<a href="' . $base_path . 'modules/dashboard/index.php">Dashboard</a> / <strong>Productos por vencer</strong>';

include '../../includes/header.php';

$nombreSistema = sesionTenantNombre();
$nombreSuc     = sesionSucursal();
?>

<style>
.pv-wrap    { padding: 24px; display: flex; flex-direction: column; gap: 18px; }
.pv-toolbar { background: var(--surface); border: 1px solid var(--border); border-radius: 12px;
              padding: 16px 18px; display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
.pv-field label { display: block; font-size: .72rem; font-weight: 600; color: var(--text-muted);
                  text-transform: uppercase; letter-spacing: .04em; margin-bottom: 5px; }
.pv-field select { padding: 8px 10px; border: 1.5px solid var(--border); border-radius: 8px;
                   font-size: .85rem; background: var(--surface); color: var(--text-primary); min-width: 180px; }
.pv-spacer  { flex: 1 1 auto; }
.pv-count   { font-size: .8rem; color: var(--text-muted); align-self: center; }

.pv-card    { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.pv-table   { width: 100%; border-collapse: collapse; }
.pv-table th { padding: 11px 12px; font-size: .72rem; font-weight: 700; text-transform: uppercase;
               letter-spacing: .03em; color: var(--text-muted); text-align: left;
               background: var(--surface-2); border-bottom: 1px solid var(--border); white-space: nowrap; }
.pv-table td { padding: 10px 12px; font-size: .85rem; color: var(--text-primary); border-bottom: 1px solid var(--border); }
.pv-table tbody tr:hover { background: var(--surface-2); }
.pv-table .col-chk { width: 36px; text-align: center; }
.pv-table .col-num { text-align: right; }
.pv-empty   { padding: 40px; text-align: center; color: var(--text-muted); font-size: .9rem; }

.kpi-badge  { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: .72rem; font-weight: 600; white-space: nowrap; }
.badge-danger  { background: var(--danger-light);  color: var(--danger); }
.badge-warning { background: var(--warning-light); color: var(--warning); }
.badge-info    { background: var(--info-light);    color: var(--info); }

/* ---- Área imprimible (oculta en pantalla, visible solo al imprimir) ---- */
#reporte-print { display: none; }

@media print {
    /* Ocultar toda la UI (sidebar, topbar, toolbar) y mostrar solo el reporte */
    body * { visibility: hidden; }
    /* Neutralizar el layout: sin sidebar/topbar ni sus márgenes, para que el
       reporte no quede desplazado ni recortado al imprimir. */
    .sidebar, .topbar, .sidebar-overlay { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    #reporte-print, #reporte-print * { visibility: visible; }
    #reporte-print { display: block; position: absolute; left: 0; top: 0; width: 100%; padding: 0 10px; }

    #reporte-print .rp-head { margin-bottom: 14px; border-bottom: 2px solid #000; padding-bottom: 10px; }
    #reporte-print .rp-title { font-size: 18px; font-weight: 700; margin: 0; }
    #reporte-print .rp-sub   { font-size: 12px; color: #333; margin: 3px 0 0; }
    #reporte-print table { width: 100%; border-collapse: collapse; font-size: 11px; }
    #reporte-print th, #reporte-print td { border: 1px solid #999; padding: 5px 7px; text-align: left; }
    #reporte-print th { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    #reporte-print .col-num { text-align: right; }
    #reporte-print tr { page-break-inside: avoid; }
    @page { margin: 14mm; }
}
</style>

<div class="pv-wrap">
    <div class="pv-toolbar">
        <div class="pv-field">
            <label>Categoría</label>
            <select id="fCategoria" onchange="renderTabla()">
                <option value="">Todas las categorías</option>
            </select>
        </div>
        <div class="pv-field">
            <label>Estado</label>
            <select id="fEstado" onchange="renderTabla()">
                <option value="">Todos</option>
                <option value="vencido">Vencidos</option>
                <option value="d30">Vencen en 30 días</option>
                <option value="d60">En 31 a 60 días</option>
                <option value="d90">En 61 a 90 días</option>
            </select>
        </div>
        <div class="pv-spacer"></div>
        <div class="pv-count" id="pvCount">—</div>
        <button class="btn btn-primary" onclick="imprimir()">
            <i class="fas fa-print"></i> Imprimir / Guardar PDF
        </button>
    </div>

    <div class="pv-card">
        <table class="pv-table">
            <thead>
                <tr>
                    <th class="col-chk"><input type="checkbox" id="chkAll" onclick="toggleTodos(this.checked)"></th>
                    <th>Producto</th>
                    <th>Código</th>
                    <th>Categoría</th>
                    <th>Laboratorio</th>
                    <th>Presentación</th>
                    <th class="col-num">Stock</th>
                    <th>Vence</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="pvBody">
                <tr><td colspan="9" class="pv-empty">Cargando…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Área imprimible (se llena al presionar Imprimir) -->
<div id="reporte-print"></div>

<script>
const API = '../../modules/dashboard/api.php';
const NOMBRE_SISTEMA = <?= json_encode($nombreSistema ?: 'FarmaSystem') ?>;
const NOMBRE_SUC     = <?= json_encode($nombreSuc) ?>;

let _todos = [];                 // lista completa del backend
const _sel = new Set();          // ids seleccionados (persisten entre filtros)

const esc  = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmtN = (n) => parseFloat(n).toLocaleString('es-PE');
const fmtFecha = (f) => f ? f.split('-').reverse().join('/') : '—';

function estadoDe(dias) {
    const d = parseInt(dias, 10);
    if (d < 0)       return { key: 'vencido', cls: 'badge-danger',  txt: 'Vencido hace ' + Math.abs(d) + ' d' };
    if (d === 0)     return { key: 'd30',     cls: 'badge-warning', txt: 'Vence hoy' };
    if (d <= 30)     return { key: 'd30',     cls: 'badge-warning', txt: 'En ' + d + ' días' };
    if (d <= 60)     return { key: 'd60',     cls: 'badge-info',    txt: 'En ' + d + ' días' };
    return { key: 'd90', cls: 'badge-info', txt: 'En ' + d + ' días' };
}

async function cargar() {
    try {
        const res = await fetch(API + '?action=por_vencer_lista');
        const data = await res.json();
        _todos = Array.isArray(data) ? data : [];
    } catch { _todos = []; }

    // Poblar filtro de categorías con las presentes en los datos
    const cats = [...new Set(_todos.map(p => p.categoria || 'Sin categoría'))].sort((a, b) => a.localeCompare(b, 'es'));
    const sel = document.getElementById('fCategoria');
    cats.forEach(c => {
        const o = document.createElement('option');
        o.value = c; o.textContent = c;
        sel.appendChild(o);
    });
    renderTabla();
}

function filtrados() {
    const cat = document.getElementById('fCategoria').value;
    const est = document.getElementById('fEstado').value;
    return _todos.filter(p => {
        if (cat && (p.categoria || 'Sin categoría') !== cat) return false;
        if (est && estadoDe(p.dias).key !== est) return false;
        return true;
    });
}

function renderTabla() {
    const lista = filtrados();
    const body = document.getElementById('pvBody');

    if (!lista.length) {
        body.innerHTML = '<tr><td colspan="9" class="pv-empty"><i class="fas fa-check-circle" style="color:var(--success)"></i> No hay productos por vencer con estos filtros</td></tr>';
    } else {
        body.innerHTML = lista.map(p => {
            const e = estadoDe(p.dias);
            const chk = _sel.has(p.id) ? 'checked' : '';
            return `<tr>
                <td class="col-chk"><input type="checkbox" data-id="${p.id}" ${chk} onclick="toggleUno(${p.id}, this.checked)"></td>
                <td style="font-weight:500">${esc(p.nombre)}</td>
                <td style="color:var(--text-muted);font-family:monospace;font-size:.8rem">${esc(p.codigo || '—')}</td>
                <td>${esc(p.categoria || 'Sin categoría')}</td>
                <td style="color:var(--text-muted)">${esc(p.laboratorio || '—')}</td>
                <td style="color:var(--text-muted)">${esc(p.presentacion || '—')}</td>
                <td class="col-num">${fmtN(p.stock)}</td>
                <td>${fmtFecha(p.fecha_vencimiento)}</td>
                <td><span class="kpi-badge ${e.cls}">${esc(e.txt)}</span></td>
            </tr>`;
        }).join('');
    }

    // Estado del "seleccionar todo" según lo visible
    const visIds = lista.map(p => p.id);
    const chkAll = document.getElementById('chkAll');
    chkAll.checked = visIds.length > 0 && visIds.every(id => _sel.has(id));

    const nSel = visIds.filter(id => _sel.has(id)).length;
    document.getElementById('pvCount').textContent =
        `${lista.length} producto(s)` + (nSel ? ` · ${nSel} seleccionado(s)` : '');
}

function toggleUno(id, checked) {
    if (checked) _sel.add(id); else _sel.delete(id);
    renderTabla();
}

function toggleTodos(checked) {
    filtrados().forEach(p => { if (checked) _sel.add(p.id); else _sel.delete(p.id); });
    renderTabla();
}

function imprimir() {
    // Si hay selección, imprime los seleccionados (y visibles según filtro);
    // si no hay ninguno marcado, imprime todos los filtrados.
    const lista = filtrados();
    const nSel  = lista.filter(p => _sel.has(p.id)).length;
    const aImprimir = nSel ? lista.filter(p => _sel.has(p.id)) : lista;

    if (!aImprimir.length) {
        alert('No hay productos para imprimir con los filtros actuales.');
        return;
    }

    const cat = document.getElementById('fCategoria').value || 'Todas';
    const estTxt = document.getElementById('fEstado').selectedOptions[0].textContent;
    const ahora = new Date().toLocaleString('es-PE', { dateStyle: 'long', timeStyle: 'short' });

    const filas = aImprimir.map(p => {
        const e = estadoDe(p.dias);
        return `<tr>
            <td>${esc(p.nombre)}</td>
            <td>${esc(p.codigo || '')}</td>
            <td>${esc(p.categoria || 'Sin categoría')}</td>
            <td>${esc(p.laboratorio || '')}</td>
            <td>${esc(p.presentacion || '')}</td>
            <td class="col-num">${fmtN(p.stock)}</td>
            <td>${fmtFecha(p.fecha_vencimiento)}</td>
            <td>${esc(e.txt)}</td>
        </tr>`;
    }).join('');

    document.getElementById('reporte-print').innerHTML = `
        <div class="rp-head">
            <p class="rp-title">Productos por vencer</p>
            <p class="rp-sub">${esc(NOMBRE_SISTEMA)}${NOMBRE_SUC ? ' — ' + esc(NOMBRE_SUC) : ''}</p>
            <p class="rp-sub">Categoría: ${esc(cat)} · Estado: ${esc(estTxt)} · ${aImprimir.length} producto(s)</p>
            <p class="rp-sub">Generado: ${esc(ahora)}</p>
        </div>
        <table>
            <thead><tr>
                <th>Producto</th><th>Código</th><th>Categoría</th><th>Laboratorio</th>
                <th>Presentación</th><th class="col-num">Stock</th><th>Vence</th><th>Estado</th>
            </tr></thead>
            <tbody>${filas}</tbody>
        </table>`;

    window.print();
}

cargar();
</script>

<?php include '../../includes/footer.php'; ?>
