<?php
// ============================================================
// ARCHIVO: farmacia/modules/facturacion/notas_credito.php
// MODULO:  Facturacion -> Notas de credito
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin', 'gerente'];
$current_module = 'facturacion';
$current_page   = 'notas_credito';
$page_title     = 'Notas de crédito — FarmaSystem';
$breadcrumb     = '<strong>Facturación</strong> / Notas de crédito';

include '../../includes/header.php';
?>

<link rel="stylesheet" href="<?= $base_path ?>assets/vendor/datatables/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/vendor/select2/select2.min.css">

<style>
.select2-container { width:100% !important; }
.select2-container--default .select2-selection--single {
    height:38px;
    border:1px solid var(--border);
    border-radius:10px;
    display:flex;
    align-items:center;
    background:var(--surface);
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height:36px;
    padding-left:12px;
    padding-right:28px;
    font-size:.84rem;
    color:var(--text-primary);
}
.select2-container--open { z-index:1065; }
.select2-dropdown {
    border:1px solid var(--border);
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 16px 40px rgba(15, 23, 42, .14);
}
.select2-search--dropdown { padding:10px; }
.select2-search__field {
    border:1px solid var(--border) !important;
    border-radius:8px !important;
    padding:8px 10px !important;
    font-size:.84rem;
}
.fact-table thead th { white-space:nowrap; }
.fact-table tbody td { vertical-align:middle; }
.table-wrap {
    overflow-x:auto;
    width:100%;
    -webkit-overflow-scrolling:touch;
}
.table-wrap table.dataTable {
    width:100% !important;
    min-width:1120px;
}
.dt-toolbar,
.dt-footer {
    display:flex;
    gap:12px;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
    padding:14px 16px 0;
    width:100%;
    box-sizing:border-box;
}
.dt-toolbar {
    border-bottom:1px solid var(--border);
    padding-bottom:12px;
    margin-bottom:8px;
}
.dt-footer {
    border-top:1px solid var(--border);
    margin-top:8px;
    padding-top:12px;
}
.dataTables_filter input,
.dataTables_length select {
    border:1px solid var(--border);
    border-radius:10px;
    background:#fff;
    min-height:36px;
    padding:0 12px;
    font:inherit;
}
.dataTables_wrapper .dataTables_paginate .page-link {
    border:1px solid var(--border) !important;
    border-radius:8px !important;
    background:#fff !important;
    padding:5px 10px !important;
    min-width:34px;
    font-size:.8rem !important;
}
.dataTables_wrapper .dataTables_paginate ul.pagination {
    list-style:none !important;
    padding-left:0 !important;
    margin:0 !important;
    display:flex;
    gap:6px;
    align-items:center;
    flex-wrap:wrap;
}
.dataTables_wrapper .dataTables_paginate ul.pagination li {
    list-style:none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    margin:0 !important;
    padding:0 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button a {
    border:1px solid var(--border) !important;
    border-radius:8px !important;
    background:#fff !important;
    color:var(--text-primary) !important;
    padding:5px 10px !important;
    min-width:34px;
    font-size:.8rem !important;
    text-decoration:none !important;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.active a,
.dataTables_wrapper .dataTables_paginate .paginate_button.current a {
    background:var(--primary) !important;
    color:#fff !important;
    border-color:var(--primary) !important;
}
.dataTables_wrapper .dataTables_paginate ul.pagination {
    list-style:none !important;
    padding-left:0 !important;
    margin:0 !important;
    display:flex;
    gap:6px;
    align-items:center;
    flex-wrap:wrap;
}
.dataTables_wrapper .dataTables_paginate ul.pagination li {
    list-style:none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    margin:0 !important;
    padding:0 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button a {
    border:1px solid var(--border) !important;
    border-radius:8px !important;
    background:#fff !important;
    color:var(--text-primary) !important;
    padding:5px 10px !important;
    min-width:34px;
    font-size:.8rem !important;
    text-decoration:none !important;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.active a,
.dataTables_wrapper .dataTables_paginate .paginate_button.current a {
    background:var(--primary) !important;
    color:#fff !important;
    border-color:var(--primary) !important;
}
.nota-resumen {
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:12px;
    margin-bottom:14px;
}
.nota-resumen .box {
    background:var(--surface-2);
    border:1px solid var(--border);
    border-radius:12px;
    padding:12px 14px;
}
.nota-resumen .label {
    font-size:.7rem;
    color:var(--text-muted);
    text-transform:uppercase;
    letter-spacing:.04em;
}
.nota-resumen .value {
    margin-top:4px;
    font-size:.9rem;
    font-weight:700;
    color:var(--text-primary);
}
.nota-grid {
    display:grid;
    grid-template-columns:1.2fr .8fr;
    gap:14px;
}
.nota-preview {
    background:var(--surface-2);
    border:1px dashed var(--border);
    border-radius:12px;
    padding:12px 14px;
}
.nota-preview .title {
    font-size:.72rem;
    color:var(--text-muted);
    text-transform:uppercase;
    letter-spacing:.04em;
}
.nota-preview .main {
    font-size:.95rem;
    font-weight:700;
    color:var(--text-primary);
    margin-top:6px;
}
.nota-preview .sub {
    margin-top:3px;
    font-size:.8rem;
    color:var(--text-muted);
}
.icon-action-btn {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    background: #f8fbff;
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: .18s ease;
}
.icon-action-btn:hover {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.actions-inline {
    display:flex;
    gap:8px;
    justify-content:center;
    align-items:center;
}
@media (max-width: 900px) {
    .nota-resumen,
    .nota-grid { grid-template-columns:1fr; }
}
</style>

<div class="page-header">
    <div>
        <div class="page-title">
            <i class="fas fa-receipt" style="color:var(--primary);margin-right:8px"></i>Notas de crédito
        </div>
        <div class="page-subtitle">Emite y reenvía notas de crédito electrónicas vinculadas a boletas y facturas</div>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= $base_path ?>modules/facturacion/index.php">
            <i class="fas fa-arrow-left"></i> Volver al reporte
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-filter" style="color:var(--primary)"></i> Filtros</div>
        <button class="btn btn-ghost btn-sm" onclick="resetFiltrosNotas()" style="margin-left:auto">
            <i class="fas fa-undo"></i> Limpiar
        </button>
    </div>
    <div style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:150px">
            <label class="form-label">Desde</label>
            <input type="date" id="f-nota-desde" class="form-control" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:150px">
            <label class="form-label">Hasta</label>
            <input type="date" id="f-nota-hasta" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:150px">
            <label class="form-label">Estado SUNAT</label>
            <select class="form-control" id="f-nota-estado">
                <option value="">Todos</option>
                <option value="Aceptado">Aceptado</option>
                <option value="Observado">Observado</option>
                <option value="Pendiente">Pendiente</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:2;min-width:220px">
            <label class="form-label">Buscar</label>
            <div class="input-group">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-nota-q" class="form-control" placeholder="Serie, cliente, referencia...">
            </div>
        </div>
        <button class="btn btn-primary" onclick="cargarNotasCredito()">
            <i class="fas fa-search"></i> Buscar
        </button>
    </div>
</div>

<div class="nota-resumen">
    <div class="box">
        <div class="label">Notas emitidas</div>
        <div class="value" id="nota-total-emitidas">-</div>
    </div>
    <div class="box">
        <div class="label">Total referenciado</div>
        <div class="value" id="nota-total-monto">S/ 0.00</div>
    </div>
    <div class="box">
        <div class="label">Envío SUNAT</div>
        <div class="value" id="nota-total-sunat">-</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Detalle de notas de crédito</div>
        <div style="display:flex;align-items:center;gap:12px;margin-left:auto">
            <span style="font-size:.82rem;color:var(--text-muted)" id="nota-result-count">—</span>
        </div>
    </div>
    <div class="table-wrap">
        <table id="notas-table" class="display fact-table" style="width:100%">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Documento</th>
                    <th>Cliente</th>
                    <th>Motivo</th>
                    <th>Referencia</th>
                    <th class="text-right">Total</th>
                    <th>XML</th>
                    <th>CDR</th>
                    <th>SUNAT</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="notas-body">
                <tr><td colspan="11" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modal-nota-credito">
    <div class="modal" style="max-width:980px;width:min(980px,calc(100vw - 32px))">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-file-circle-plus" style="color:var(--primary);margin-right:8px"></i>Nueva nota de crédito</h3>
            <button class="modal-close" onclick="closeModal('modal-nota-credito')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding:20px 24px;max-height:74vh;overflow-y:auto">
            <div class="nota-grid">
                <div>
                    <div class="form-group" style="margin-bottom:12px">
                        <label class="form-label">Comprobante origen *</label>
                        <select id="nota-origen" style="width:100%"></select>
                        <div class="cliente-help">Busca boletas o facturas ya enviadas a SUNAT.</div>
                    </div>
                    <div class="nota-resumen" style="grid-template-columns:1fr 1fr 1fr;margin-bottom:0">
                        <div class="box">
                            <div class="label">Documento</div>
                            <div class="value" id="nota-origen-doc">-</div>
                        </div>
                        <div class="box">
                            <div class="label">Cliente</div>
                            <div class="value" id="nota-origen-cliente">-</div>
                        </div>
                        <div class="box">
                            <div class="label">Total</div>
                            <div class="value" id="nota-origen-total">S/ 0.00</div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="form-group" style="margin-bottom:12px">
                        <label class="form-label">Motivo *</label>
                        <select id="nota-motivo" class="form-control"></select>
                    </div>
                    <div class="form-group" style="margin-bottom:12px">
                        <label class="form-label">Descripción *</label>
                        <textarea id="nota-descripcion" class="form-control" rows="5" placeholder="Describe brevemente el motivo de la nota de crédito"></textarea>
                    </div>
                    <div class="nota-preview">
                        <div class="title">Importante</div>
                        <div class="main">La nota de crédito se emitirá por el total del comprobante seleccionado.</div>
                        <div class="sub">Si luego necesitas una devolución parcial, lo afinamos como siguiente mejora.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-nota-credito')">Cancelar</button>
            <button class="btn btn-primary" id="btn-crear-nota" onclick="crearNotaCredito()">
                <i class="fas fa-paper-plane"></i> Emitir nota de crédito
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script src="<?= $base_path ?>assets/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="<?= $base_path ?>assets/vendor/select2/select2.min.js"></script>
<script src="<?= $base_path ?>assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= $base_path ?>assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
const BASE = '../../';
const EMPRESA_NOMBRE  = <?= json_encode(sesionTenantNombre()) ?>;
const EMPRESA_RUC     = <?= json_encode(getTenantConfig()['ruc'] ?? '') ?>;
const EMPRESA_TEL     = <?= json_encode(getTenantConfig()['telefono'] ?? '') ?>;
const EMPRESA_DIR     = <?= json_encode(getTenantConfig()['direccion'] ?? '') ?>;
const SUCURSAL_NOMBRE = <?= json_encode(sesionSucursal()) ?>;
const VENDEDOR_NOMBRE = <?= json_encode(sesionNombre()) ?>;
let notasTable = null;
let origenesNotas = [];
let motivosNotas = [];
let origenActual = null;
let notasCreditoMap = {};
const origenInicialId = new URLSearchParams(window.location.search).get('origen');

function showToast(msg, type = 'info') {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

function esc(s) {
    return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function actionIconButton(icon, title, href = null, onclick = null) {
    const attrs = href
        ? `href="${href}" target="_blank" rel="noopener"`
        : `href="javascript:void(0)" onclick="${onclick}"`;
    return `<a class="icon-action-btn" ${attrs} title="${title}"><i class="fas fa-${icon}"></i></a>`;
}

function openPrintWindow(title, contentHtml) {
    const w = window.open('', '_blank', 'width=420,height=700');
    if (!w) return;
    w.document.open();
    w.document.write(`<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>${esc(title)}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New',Courier,monospace; font-size:12px; color:#000; width:80mm; margin:0 auto; padding:4mm 3mm; }
        @media print { @page { size:80mm auto; margin:4mm 3mm; } body { padding:0; } }
    </style>
</head>
<body>${contentHtml}
<script>
    setTimeout(() => { window.print(); }, 250);
<\/script>
</body>
</html>`);
    w.document.close();
}

function buildNotaCreditoTicketHTML(row) {
    const dt = new Date(row.created_at || Date.now());
    const fecha = dt.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric' });
    const hora  = dt.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    const total = parseFloat(row.total || 0);
    const cliente = row.cliente || 'Cliente General';
    const clienteDoc = row.dni || row.ruc || '';
    const ref = row.referencia_numero_completo || row.documento_modificado_numero_completo || '-';
    const motivo = row.motivo_nota_credito || row.motivo_descripcion || row.motivo || '';
    const tipo = row.codigo_tipo_nota_credito || row.tipo_codigo || '';
    const numero = row.numero_completo || row.serie || 'NC';

    return `
        <div id="pos-ticket" style="font-family:'Courier New',Courier,monospace;color:#000;font-size:12px;line-height:1.5;width:100%">
            <div style="text-align:center;margin-bottom:10px">
                <div style="font-size:15px;font-weight:900;letter-spacing:1px;text-transform:uppercase">${esc(EMPRESA_NOMBRE || 'FARMACIA')}</div>
                ${EMPRESA_RUC ? `<div style="font-size:11px">RUC: ${esc(EMPRESA_RUC)}</div>` : ''}
                ${SUCURSAL_NOMBRE ? `<div style="font-size:11px;font-weight:600">${esc(SUCURSAL_NOMBRE)}</div>` : ''}
                ${EMPRESA_DIR ? `<div style="font-size:10px">${esc(EMPRESA_DIR)}</div>` : ''}
                ${EMPRESA_TEL ? `<div style="font-size:10px">Tel: ${esc(EMPRESA_TEL)}</div>` : ''}
            </div>

            <div style="border-top:2px solid #000;border-bottom:2px solid #000;text-align:center;padding:5px 0;margin-bottom:8px">
                <div style="font-size:11px;font-weight:700">NOTA DE CRÉDITO</div>
                <div style="font-size:11px">${esc(numero)}</div>
            </div>

            <div style="font-size:11px;margin-bottom:6px">
                <div>Fecha   : ${fecha} ${hora}</div>
                <div>Cajero  : ${esc(VENDEDOR_NOMBRE)}</div>
                <div>Cliente : ${esc(cliente)}</div>
                ${clienteDoc ? `<div>${row.ruc ? 'RUC' : 'DNI'}     : ${esc(clienteDoc)}</div>` : ''}
                <div>Ref.    : ${esc(ref)}</div>
            </div>

            <div style="border-top:1px dashed #000;margin:6px 0"></div>
            <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:700;margin-bottom:3px">
                <span>DESCRIPCIÓN</span><span>TOTAL</span>
            </div>
            <div style="border-top:1px dashed #000;margin:6px 0"></div>

            <div style="font-size:11px;margin:4px 0">
                <div style="display:flex;justify-content:space-between;gap:10px"><span>MOTIVO:</span><span>${esc(motivo || 'Anulación de la operación')}</span></div>
                <div style="display:flex;justify-content:space-between;gap:10px"><span>TIPO:</span><span>${esc(tipo)}</span></div>
            </div>

            <div style="border-top:2px solid #000;margin:6px 0"></div>
            <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:800;line-height:1.6">
                <span>TOTAL:</span><span>S/ ${total.toFixed(2)}</span>
            </div>
            <div style="border-top:2px solid #000;margin:6px 0"></div>

            <div style="text-align:center;font-size:11px;padding:4px 0">
                <div>¡Gracias por su compra!</div>
                <div>Vuelva pronto</div>
            </div>
        </div>
    `;
}

function imprimirTicketNotaCredito(notaId) {
    const row = notasCreditoMap[String(notaId)];
    if (!row) {
        showToast('No se encontro la nota para imprimir.', 'error');
        return;
    }
    const html = buildNotaCreditoTicketHTML(row);
    openPrintWindow(`NC ${row.numero_completo || row.serie || ''}`, html);
}

function sunatBadge(rawStatus, responseCode = null) {
    const txt = String(rawStatus || '').trim();
    const code = String(responseCode ?? '').trim();
    const lowered = txt.toLowerCase();
    if (code === '0' || lowered.includes('acept')) return { label: 'Aceptado', className: 'badge-success', title: txt || 'CDR aceptado' };
    if (code === '1' || lowered.includes('observ')) return { label: 'Observado', className: 'badge-danger', title: txt || 'CDR observado' };
    if (!txt) return { label: 'Pendiente', className: 'badge-warning', title: 'Pendiente de envío' };
    return { label: txt, className: 'badge-gray', title: txt };
}

function destroyNotasTable() {
    if (notasTable) {
        notasTable.destroy();
        notasTable = null;
    }
}

function initNotasTable() {
    const table = document.getElementById('notas-table');
    if (!table || typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') return;
    destroyNotasTable();
    notasTable = jQuery(table).DataTable({
        pageLength: 10,
        lengthMenu: [10, 15, 25, 50],
        autoWidth: false,
        order: [[0, 'desc']],
        pagingType: 'simple_numbers',
        dom: "<'dt-toolbar'lf>t<'dt-footer'ip>",
        language: {
            search: 'Buscar en resultados:',
            searchPlaceholder: 'Serie, cliente, referencia...',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Sin registros',
            infoFiltered: '(filtrado de _MAX_ registros)',
            zeroRecords: 'No se encontraron notas de crédito',
            emptyTable: 'No hay notas de crédito para mostrar',
            paginate: { first: 'Primero', last: 'Ultimo', next: 'Siguiente', previous: 'Anterior' }
        },
        columnDefs: [
            { targets: [6,7,8,10], orderable: false, searchable: false },
            { targets: 5, className: 'dt-body-right' },
            { targets: [6,7,8,9,10], className: 'dt-body-center' }
        ]
    });
}

function getNotasParams() {
    return {
        desde: document.getElementById('f-nota-desde').value,
        hasta: document.getElementById('f-nota-hasta').value,
        estado: document.getElementById('f-nota-estado').value,
        q: document.getElementById('f-nota-q').value,
    };
}

function buildNotasQuery(extra = {}) {
    return new URLSearchParams({ ...getNotasParams(), ...extra }).toString();
}

async function cargarMotivosNotaCredito() {
    const r = await fetch(BASE + 'modules/facturacion/api.php?action=tipos_nota_credito');
    motivosNotas = await r.json();
    const sel = document.getElementById('nota-motivo');
    sel.innerHTML = '<option value="">Selecciona un motivo</option>' + motivosNotas.map(m => `<option value="${m.id}">${esc(m.codigo)} - ${esc(m.descripcion)}</option>`).join('');
    const defaultMotivo = motivosNotas.find(m => String(m.codigo || '').trim() === '01') || motivosNotas[0];
    if (window.jQuery && window.jQuery.fn.select2) {
        if (window.jQuery(sel).data('select2')) {
            window.jQuery(sel).select2('destroy');
        }
        window.jQuery(sel).select2({
            width: '100%',
            dropdownParent: window.jQuery('#modal-nota-credito'),
            minimumResultsForSearch: Infinity
        });
    }
    if (defaultMotivo) {
        sel.value = String(defaultMotivo.id);
        if (window.jQuery && window.jQuery.fn.select2 && window.jQuery(sel).data('select2')) {
            window.jQuery(sel).val(String(defaultMotivo.id)).trigger('change');
        }
    }
}

async function cargarOrigenesNotaCredito() {
    const r = await fetch(BASE + 'modules/facturacion/api.php?action=documentos_origen_nota_credito');
    origenesNotas = await r.json();
    const $select = window.jQuery('#nota-origen');
    if ($select.data('select2')) {
        $select.select2('destroy');
    }
    $select.empty().append('<option value=""></option>');
    origenesNotas.forEach(item => {
        const label = `${item.numero_completo} — ${item.cliente} — S/ ${parseFloat(item.total || 0).toFixed(2)}`;
        const opt = new Option(label, item.id, false, false);
        opt.dataset.cliente = item.cliente || '';
        opt.dataset.total = item.total || 0;
        opt.dataset.numero = item.numero_completo || '';
        $select.append(opt);
    });
    $select.select2({
        placeholder: 'Buscar boleta o factura',
        allowClear: true,
        dropdownParent: window.jQuery('#modal-nota-credito'),
        width: '100%'
    });
    $select.off('change').on('change', () => {
        const id = $select.val();
        const item = origenesNotas.find(row => String(row.id) === String(id));
        origenActual = item || null;
        renderOrigenSeleccionado();
    });

    if (origenInicialId) {
        const origen = origenesNotas.find(row => String(row.id) === String(origenInicialId));
        if (origen) {
            origenActual = origen;
            renderOrigenSeleccionado();
            $select.val(String(origenInicialId)).trigger('change');
            openModal('modal-nota-credito');
            setTimeout(() => {
                if (window.jQuery && window.jQuery('#nota-origen').data('select2')) {
                    window.jQuery('#nota-origen').select2('open');
                }
            }, 180);
        }
    }
}

function renderOrigenSeleccionado() {
    const doc = document.getElementById('nota-origen-doc');
    const cli = document.getElementById('nota-origen-cliente');
    const total = document.getElementById('nota-origen-total');
    if (!origenActual) {
        doc.textContent = '-';
        cli.textContent = '-';
        total.textContent = 'S/ 0.00';
        return;
    }
    doc.textContent = origenActual.numero_completo || '-';
    cli.textContent = origenActual.cliente || '-';
    total.textContent = `S/ ${parseFloat(origenActual.total || 0).toFixed(2)}`;
}

function abrirModalNotaCredito() {
    const motivoSelect = document.getElementById('nota-motivo');
    const defaultMotivo = Array.isArray(motivosNotas)
        ? (motivosNotas.find(m => String(m.codigo || '').trim() === '01') || motivosNotas[0] || null)
        : null;
    document.getElementById('nota-descripcion').value = 'Anulación de la operación';
    if (motivoSelect) {
        if (window.jQuery && window.jQuery.fn.select2 && window.jQuery(motivoSelect).data('select2')) {
            window.jQuery(motivoSelect).val(defaultMotivo ? String(defaultMotivo.id) : '').trigger('change');
        } else {
            motivoSelect.value = defaultMotivo ? String(defaultMotivo.id) : '';
        }
    }
    origenActual = null;
    renderOrigenSeleccionado();
    openModal('modal-nota-credito');
    setTimeout(() => {
        if (window.jQuery && window.jQuery('#nota-origen').data('select2')) {
            window.jQuery('#nota-origen').val(null).trigger('change');
            window.jQuery('#nota-origen').select2('open');
        }
    }, 120);
}

function resetFiltrosNotas() {
    const hoy = new Date();
    document.getElementById('f-nota-desde').value = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().slice(0,10);
    document.getElementById('f-nota-hasta').value = hoy.toISOString().slice(0,10);
    document.getElementById('f-nota-estado').value = '';
    document.getElementById('f-nota-q').value = '';
    cargarNotasCredito();
}

function cargarNotasCredito() {
    destroyNotasTable();
    document.getElementById('notas-body').innerHTML = '<tr><td colspan="11" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
    document.getElementById('nota-result-count').textContent = '...';

    fetch(BASE + 'modules/facturacion/api.php?' + buildNotasQuery({ action: 'notas_reporte' }))
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                document.getElementById('notas-body').innerHTML = `<tr><td colspan="11" style="text-align:center;padding:30px;color:#dc2626">${esc(data.message)}</td></tr>`;
                document.getElementById('nota-result-count').textContent = 'Error';
                return;
            }

            document.getElementById('nota-result-count').textContent = `${data.length} resultado(s)`;
            if (!data.length) {
                document.getElementById('notas-body').innerHTML = '<tr><td colspan="11" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-inbox"></i><br><br>Sin notas de crédito para mostrar</td></tr>';
                document.getElementById('nota-total-emitidas').textContent = '0';
                document.getElementById('nota-total-monto').textContent = 'S/ 0.00';
                document.getElementById('nota-total-sunat').textContent = '-';
                return;
            }

            const totalMonto = data.reduce((sum, row) => sum + parseFloat(row.total || 0), 0);
            const aceptadas = data.filter(row => String(row.enlace_del_cdr || '').trim() !== '').length;
            document.getElementById('nota-total-emitidas').textContent = String(data.length);
            document.getElementById('nota-total-monto').textContent = `S/ ${totalMonto.toFixed(2)}`;
            document.getElementById('nota-total-sunat').textContent = `${aceptadas} con CDR`;

            notasCreditoMap = {};
            document.getElementById('notas-body').innerHTML = data.map(row => {
                notasCreditoMap[String(row.id)] = row;
                const dt = new Date(row.created_at);
                const fechaOrden = dt.getTime();
                const fecha = dt.toLocaleDateString('es-PE');
                const hora = dt.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                const respCode = row.nubefact_response
                    ? (() => {
                        try {
                            const parsed = typeof row.nubefact_response === 'string' ? JSON.parse(row.nubefact_response) : row.nubefact_response;
                            return parsed?.cdr?.response_code ?? parsed?.cdr?.responseCode ?? null;
                        } catch (_) { return null; }
                    })()
                    : null;
                const sunat = sunatBadge(row.estado_sunat, respCode);
                const pdf = row.enlace_del_pdf ? actionIconButton('file-pdf', 'Ver PDF', row.enlace_del_pdf) : '';
                const xml = row.enlace_del_xml ? actionIconButton('file-code', 'Ver XML', row.enlace_del_xml) : '';
                const cdr = row.enlace_del_cdr ? actionIconButton('receipt', 'Ver CDR', row.enlace_del_cdr) : '';
                const reenviar = actionIconButton(
                    'paper-plane',
                    row.enlace_del_cdr ? 'Ya tiene CDR' : 'Reenviar a SUNAT',
                    null,
                    `reenviarNotaSunat(${parseInt(row.id,10)}, ${row.enlace_del_cdr ? 'true' : 'false'})`
                );
                const estadoClass = row.enlace_del_cdr ? 'badge-success' : 'badge-warning';
                const clienteDoc = row.ruc || row.dni || '';
                return `
                    <tr>
                        <td data-order="${fechaOrden}">
                            <div style="display:flex;flex-direction:column">
                                <strong>${fecha}</strong>
                                <span style="font-size:.78rem;color:var(--text-muted)">${hora}</span>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column">
                                <strong style="font-size:.88rem">${esc(row.numero_completo)}</strong>
                                <span style="font-size:.75rem;color:var(--text-muted)">NOTA DE CREDITO</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:.83rem">
                                <div style="font-weight:600">${esc(row.cliente)}</div>
                                ${clienteDoc ? `<div style="font-size:.74rem;color:var(--text-muted)">${esc(clienteDoc)}</div>` : ''}
                            </div>
                        </td>
                        <td style="font-size:.82rem">
                            <div style="font-weight:600">${esc(row.codigo_tipo_nota_credito || '-')}</div>
                            <div style="font-size:.74rem;color:var(--text-muted)">${esc(row.motivo_nota_credito || '')}</div>
                        </td>
                        <td style="font-size:.82rem">
                            <div style="font-weight:600">${esc(row.referencia_numero_completo || row.documento_modificado_numero_completo || '-')}</div>
                        </td>
                        <td class="text-right" data-order="${parseFloat(row.total || 0)}"><span class="total-pill">S/ ${parseFloat(row.total || 0).toFixed(2)}</span></td>
                        <td>${xml}</td>
                        <td>${cdr}</td>
                        <td><span class="badge ${sunat.className}" title="${sunat.title}">${sunat.label}</span></td>
                        <td><span class="badge ${estadoClass}">${row.enlace_del_cdr ? 'Vigente' : 'Pendiente'}</span></td>
                        <td>
                            <div class="actions-inline">
                                ${actionIconButton('print', 'Imprimir ticket', null, `imprimirTicketNotaCredito(${parseInt(row.id,10)})`)}
                                ${pdf}
                                ${reenviar}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            initNotasTable();
        })
        .catch(() => {
            document.getElementById('notas-body').innerHTML = '<tr><td colspan="11" style="text-align:center;padding:30px;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> Error al cargar notas de crédito</td></tr>';
            document.getElementById('nota-result-count').textContent = 'Error';
        });
}

function crearNotaCredito() {
    const origenId = document.getElementById('nota-origen').value;
    const tipoId = document.getElementById('nota-motivo').value;
    const descripcion = document.getElementById('nota-descripcion').value.trim();
    if (!origenId) {
        showToast('Selecciona el comprobante origen', 'error');
        return;
    }
    if (!tipoId) {
        showToast('Selecciona el motivo de la nota', 'error');
        return;
    }
    if (!descripcion) {
        showToast('Escribe una descripcion', 'error');
        return;
    }

    const btn = document.getElementById('btn-crear-nota');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Emitiendo...';

    fetch(BASE + 'modules/facturacion/api.php?action=crear_nota_credito', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            comprobante_origen_id: origenId,
            tipo_nota_credito_id: tipoId,
            descripcion
        })
    })
    .then(async r => {
        const data = await r.json();
        if (!r.ok || data.error) throw new Error(data.message || 'No se pudo emitir la nota de credito.');
        closeModal('modal-nota-credito');
        showToast(data.message || 'Nota de credito emitida correctamente.', 'success');
        cargarNotasCredito();
    })
    .catch(err => showToast(err.message || 'Error al emitir nota de credito', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Emitir nota de crédito';
    });
}

function reenviarNotaSunat(comprobanteId, tieneCdr = false) {
    if (tieneCdr) {
        showToast('Esta nota ya tiene CDR y no se puede reenviar.', 'error');
        return;
    }

    fetch(BASE + 'modules/facturacion/api.php?action=reenviar_sunat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comprobante_id: comprobanteId })
    })
    .then(async r => {
        const data = await r.json();
        if (!r.ok || data.error) throw new Error(data.message || 'No se pudo reenviar la nota');
        showToast(data.message || 'Nota reenviada a SUNAT.', 'success');
        cargarNotasCredito();
    })
    .catch(err => showToast(err.message || 'Error al reenviar a SUNAT', 'error'));
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function openModal(id) {
    document.getElementById(id).classList.add('open');
}

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal(overlay.id);
    });
});

document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([cargarMotivosNotaCredito(), cargarOrigenesNotaCredito()]);
    cargarNotasCredito();
    document.getElementById('f-nota-q').addEventListener('keyup', e => {
        if (e.key === 'Enter') cargarNotasCredito();
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
