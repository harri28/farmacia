<?php
// ============================================================
// ARCHIVO: farmacia/modules/facturacion/index.php
// MÓDULO:  Facturación → Reporte de Ventas
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin', 'gerente'];
$current_module = 'facturacion';
$current_page   = 'reporte';
$page_title     = 'Reporte de Ventas — FarmaSystem';
$breadcrumb     = '<strong>Facturación</strong> / Reporte de Ventas';

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
</style>
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
        <table id="ventas-table" class="display facturacion-table" style="width:100%">
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

<div class="modal-overlay" id="modal-nota-credito-reporte">
    <div class="modal" style="max-width:760px;width:min(760px,calc(100vw - 32px))">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-file-circle-plus" style="color:var(--primary);margin-right:8px"></i>Anular con Nota de Credito</h3>
            <button class="modal-close" onclick="closeNotaCreditoReporte()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding:20px 24px;max-height:74vh;overflow-y:auto">
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:14px;padding:14px 16px;margin-bottom:16px">
                <div style="font-size:.74rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Comprobante origen</div>
                <div style="margin-top:6px;font-size:1rem;font-weight:700;color:var(--text-primary)" id="nc-origen-numero">-</div>
                <div style="margin-top:2px;font-size:.82rem;color:var(--text-muted)" id="nc-origen-cliente">-</div>
                <div style="margin-top:4px;font-size:.9rem;font-weight:700;color:var(--primary)" id="nc-origen-total">S/ 0.00</div>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label class="form-label">Motivo SUNAT *</label>
                <select id="nc-motivo" class="form-control"></select>
            </div>

            <div class="form-group" style="margin-bottom:6px">
                <label class="form-label">Descripcion / motivo interno *</label>
                <textarea id="nc-descripcion" class="form-control" rows="4" placeholder="Describe brevemente el motivo de la nota de credito"></textarea>
            </div>

            <div style="font-size:.8rem;color:var(--text-muted)">
                La nota de credito se emitira vinculada al comprobante seleccionado.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeNotaCreditoReporte()">Cancelar</button>
            <button class="btn btn-primary" id="btn-emitir-nc" onclick="emitirNotaCreditoReporte()">
                <i class="fas fa-paper-plane"></i> Emitir nota de credito
            </button>
        </div>
    </div>
</div>

<script src="<?= $base_path ?>assets/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="<?= $base_path ?>assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= $base_path ?>assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script src="<?= $base_path ?>assets/vendor/select2/select2.min.js"></script>
<script>
const BASE = '../../';
const EMPRESA_NOMBRE  = <?= json_encode(sesionTenantNombre()) ?>;
const EMPRESA_RUC     = <?= json_encode(getTenantConfig()['ruc'] ?? '') ?>;
const EMPRESA_TEL     = <?= json_encode(getTenantConfig()['telefono'] ?? '') ?>;
const EMPRESA_DIR     = <?= json_encode(getTenantConfig()['direccion'] ?? '') ?>;
const SUCURSAL_NOMBRE = <?= json_encode(sesionSucursal()) ?>;
const VENDEDOR_NOMBRE = <?= json_encode(sesionNombre()) ?>;
let reporteTable = null;
let reporteVentasCache = [];
let reporteVentasMap = {};
let notaCreditoReporteOrigen = null;
let notaCreditoReporteMotivos = [];

function destroyReporteTable() {
    if (reporteTable) {
        reporteTable.destroy();
        reporteTable = null;
    }
}

function initReporteTable() {
    const table = document.getElementById('ventas-table');
    if (!table || typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
        return;
    }

    destroyReporteTable();
    renderSalesTableHeader();

    reporteTable = jQuery(table).DataTable({
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50, 100],
        order: [[0, 'desc']],
        autoWidth: false,
        responsive: false,
        pagingType: 'simple_numbers',
        dom: "<'dt-toolbar'lf>t<'dt-footer'ip>",
        language: {
            search: 'Buscar en resultados:',
            searchPlaceholder: 'Cliente o comprobante...',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Sin registros disponibles',
            infoFiltered: '(filtrado de _MAX_ registros)',
            zeroRecords: 'No se encontraron ventas con ese criterio',
            emptyTable: 'No hay ventas para mostrar',
            paginate: {
                first: 'Primero',
                last: 'Ultimo',
                next: 'Siguiente',
                previous: 'Anterior'
            }
        },
        columnDefs: [
            { targets: [3], className: 'dt-body-right' },
            { targets: [4, 5, 6, 7, 8], className: 'dt-body-center' },
            { targets: [4, 5, 8], orderable: false, searchable: false },
            { targets: 6, width: '88px' },
            { targets: 7, width: '88px' },
            { targets: 8, width: '72px' }
        ]
    });
}

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

function renderSalesTableHeader() {
    const table = document.getElementById('ventas-table');
    if (!table || !table.tHead) {
        return;
    }

    table.tHead.innerHTML = `
        <tr>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Comprobante</th>
            <th class="text-right">Total</th>
            <th>XML</th>
            <th>CDR</th>
            <th>SUNAT</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    `;
}

function renderSummaryCard(totalDocs, totalAmount) {
    let summary = document.getElementById('tabla-summary');
    if (!summary) {
        summary = document.createElement('div');
        summary.id = 'tabla-summary';
        summary.className = 'tabla-summary';
        document.querySelector('.table-wrap')?.insertAdjacentElement('afterend', summary);
    }

    summary.innerHTML = `
        <div class="summary-pill">
            <span class="summary-label">Comprobantes</span>
            <strong>${totalDocs}</strong>
        </div>
        <div class="summary-pill">
            <span class="summary-label">Total emitido</span>
            <strong>S/ ${Number(totalAmount || 0).toFixed(2)}</strong>
        </div>
    `;
}

function shortSunatStatus(rawStatus, responseCode = null) {
    const txt = String(rawStatus || '').trim();
    const code = String(responseCode ?? '').trim();
    const lowered = txt.toLowerCase();

    if (code === '0') {
        return { label: 'Aceptado', className: 'badge-success', title: txt || 'CDR con respuesta 0' };
    }
    if (code === '1') {
        return { label: 'Observado', className: 'badge-danger', title: txt || 'CDR con observaciones' };
    }

    if (!txt) {
        return { label: '-', className: 'badge-gray', title: '' };
    }
    if (lowered.includes('aceptad')) {
        return { label: 'Aceptado', className: 'badge-success', title: txt };
    }
    if (lowered.includes('pendiente')) {
        return { label: 'Pendiente', className: 'badge-warning', title: txt };
    }

    if (lowered.includes('observ')) {
        return { label: 'Observado', className: 'badge-danger', title: txt };
    }

    return { label: txt, className: 'badge-gray', title: txt };
}

function actionIconButton(icon, title, href = null, onclick = null) {
    const attrs = href
        ? `href="${href}" target="_blank" rel="noopener"`
        : `href="javascript:void(0)" onclick="${onclick}"`;

    return `<a class="icon-action-btn" ${attrs} title="${title}"><i class="fas fa-${icon}"></i></a>`;
}

function esc(s) {
    return String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
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

function buildReporteVentaTicketHtml(data) {
    const items = Array.isArray(data.items) ? data.items : [];
    const total = parseFloat(data.total || 0);
    const subtotal = parseFloat(data.subtotal || 0);
    const descuento = parseFloat(data.descuento || 0);
    const igv = parseFloat(data.igv || 0);
    const tipo = String(data.tipo_comprobante || '').toLowerCase();
    const compLabel = tipo === 'factura'
        ? 'FACTURA DE VENTA ELECTRÓNICA'
        : (tipo === 'boleta' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'TICKET DE VENTA');
    const compNumero = data.comprobante_numero || data.numero_venta || '---';
    const dt = new Date(data.created_at || Date.now());
    const fecha = dt.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric' });
    const hora = dt.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    const clienteNombre = data.cliente || 'Cliente General';
    const clienteDoc = data.dni || data.ruc || '';
    const docLabel = data.ruc ? 'RUC' : (data.dni ? 'DNI' : '');

    const itemsHtml = items.map(it => {
        const nombre = it.nombre || it.producto_nombre || it.descripcion || 'Producto';
        const qty = parseFloat(it.cantidad || it.qty || 0) || 0;
        const pu = parseFloat(it.precio_unitario || it.precio || 0) || 0;
        const sub = parseFloat(it.precio_total || it.subtotal || 0) || (pu * qty);
        return `
            <div style="margin-bottom:5px">
                <div style="display:flex;justify-content:space-between;font-size:11px">
                    <span style="flex:1;padding-right:6px;word-break:break-word">${esc(nombre)}</span>
                    <span style="white-space:nowrap;font-weight:600">S/${sub.toFixed(2)}</span>
                </div>
                <div style="font-size:10px;color:#555;padding-left:2px">${qty} unid. x S/${pu.toFixed(2)}</div>
            </div>
        `;
    }).join('');

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
                <div style="font-size:11px;font-weight:700">${compLabel}</div>
                <div style="font-size:11px">${esc(compNumero)}</div>
            </div>

            <div style="font-size:11px;margin-bottom:6px">
                <div>Fecha   : ${fecha} ${hora}</div>
                <div>Cajero  : ${esc(data.vendedor || VENDEDOR_NOMBRE)}</div>
                ${clienteNombre ? `<div>Cliente : ${esc(clienteNombre)}</div>` : ''}
                ${docLabel && clienteDoc ? `<div>${docLabel}     : ${esc(clienteDoc)}</div>` : ''}
                ${data.direccion ? `<div>Dir.    : ${esc(data.direccion)}</div>` : ''}
            </div>

            <div style="border-top:1px dashed #000;margin:6px 0"></div>
            <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:700;margin-bottom:3px">
                <span>DESCRIPCIÓN</span><span>TOTAL</span>
            </div>
            <div style="border-top:1px dashed #000;margin:6px 0"></div>
            <div style="margin-bottom:4px">${itemsHtml}</div>
            <div style="border-top:1px dashed #000;margin:6px 0"></div>

            <div style="font-size:11px;margin:4px 0">
                <div style="display:flex;justify-content:space-between;gap:10px"><span>OP. GRAVADA:</span><span>S/ ${subtotal.toFixed(2)}</span></div>
                ${descuento > 0 ? `<div style="display:flex;justify-content:space-between;gap:10px"><span>DESCUENTO:</span><span>-S/ ${descuento.toFixed(2)}</span></div>` : ''}
                <div style="display:flex;justify-content:space-between;gap:10px"><span>IGV (18%):</span><span>S/ ${igv.toFixed(2)}</span></div>
            </div>

            <div style="border-top:2px solid #000;margin:6px 0"></div>
            <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:800;line-height:1.6">
                <span>TOTAL:</span><span>S/ ${total.toFixed(2)}</span>
            </div>
            <div style="border-top:2px solid #000;margin:6px 0"></div>

            <div style="font-size:11px;margin-top:5px">
                <div style="display:flex;justify-content:space-between;gap:10px"><span>${data.tipo_pago ? esc(data.tipo_pago.charAt(0).toUpperCase() + data.tipo_pago.slice(1)) + ':' : 'Pago:'}</span><span>S/ ${total.toFixed(2)}</span></div>
            </div>

            <div style="border-top:1px dashed #000;margin:6px 0"></div>
            <div style="text-align:center;font-size:11px;padding:4px 0">
                <div>¡Gracias por su compra!</div>
                <div>Vuelva pronto</div>
            </div>
        </div>
    `;
}

async function imprimirTicketVenta(ventaId) {
    const row = reporteVentasMap[String(ventaId)];
    if (!row) {
        showToast('No se encontro la venta para imprimir.', 'error');
        return;
    }

    try {
        const r = await fetch(BASE + 'modules/facturacion/api.php?action=detalle_venta_ticket&id=' + encodeURIComponent(ventaId));
        const data = await r.json();
        if (!r.ok || data.error) throw new Error(data.message || 'No se pudo preparar el ticket.');
        const html = buildReporteVentaTicketHtml(data);
        openPrintWindow(`Ticket ${data.numero_venta || data.comprobante_numero || ventaId}`, html);
    } catch (e) {
        console.error('imprimirTicketVenta', e);
        showToast('No se pudo preparar el ticket para imprimir.', 'error');
    }
}

function initNotaCreditoMotivoSelect() {
    const sel = document.getElementById('nc-motivo');
    if (!sel || !(window.jQuery && window.jQuery.fn.select2)) {
        return;
    }
    const $sel = window.jQuery(sel);
    if ($sel.data('select2')) {
        $sel.select2('destroy');
    }
    $sel.select2({
        width: '100%',
        dropdownParent: window.jQuery('#modal-nota-credito-reporte'),
        minimumResultsForSearch: Infinity
    });
}

async function cargarMotivosNotaCreditoReporte() {
    try {
        const r = await fetch(BASE + 'modules/facturacion/api.php?action=tipos_nota_credito');
        notaCreditoReporteMotivos = await r.json();
        const sel = document.getElementById('nc-motivo');
        if (!sel) return;
        sel.innerHTML = '<option value="">Selecciona un motivo</option>' + (notaCreditoReporteMotivos || [])
            .map(m => `<option value="${m.id}">${m.codigo} - ${m.descripcion}</option>`)
            .join('');
        const defaultMotivo = notaCreditoReporteMotivos.find(m => String(m.codigo || '').trim() === '01') || notaCreditoReporteMotivos[0] || null;
        initNotaCreditoMotivoSelect();
        if (defaultMotivo) {
            sel.value = String(defaultMotivo.id);
            if (window.jQuery && window.jQuery.fn.select2 && window.jQuery(sel).data('select2')) {
                window.jQuery(sel).val(String(defaultMotivo.id)).trigger('change');
            }
        }
    } catch (_) {
        const sel = document.getElementById('nc-motivo');
        if (sel) sel.innerHTML = '<option value="">No se pudieron cargar los motivos</option>';
    }
}

function abrirNotaCreditoReporte(comprobanteId) {
    const row = reporteVentasCache.find(v => String(v.comprobante_id || 0) === String(comprobanteId));
    if (!row || !parseInt(row.comprobante_id || 0, 10)) {
        showToast('No se encontro el comprobante para generar la nota de credito', 'error');
        return;
    }
    if (!String(row.enlace_cdr || '').trim()) {
        showToast('El comprobante debe tener CDR para emitir una nota de credito', 'error');
        return;
    }
    if (String(row.tipo_comprobante || '').toLowerCase() === 'ticket') {
        showToast('No se puede emitir nota de credito sobre ticket', 'error');
        return;
    }

    notaCreditoReporteOrigen = row;
    document.getElementById('nc-origen-numero').textContent = row.comprobante_numero || row.numero_venta || '-';
    const cliente = row.cliente ? String(row.cliente) : 'Cliente';
    const doc = [row.ruc ? `RUC: ${row.ruc}` : '', row.dni ? `DNI: ${row.dni}` : ''].filter(Boolean).join(' · ');
    document.getElementById('nc-origen-cliente').textContent = doc ? `${cliente} · ${doc}` : cliente;
    document.getElementById('nc-origen-total').textContent = `S/ ${parseFloat(row.total || 0).toFixed(2)}`;
    document.getElementById('nc-descripcion').value = 'Anulacion de la operacion';
    const motivoSelect = document.getElementById('nc-motivo');
    const defaultMotivo = Array.isArray(notaCreditoReporteMotivos)
        ? (notaCreditoReporteMotivos.find(m => String(m.codigo || '').trim() === '01') || notaCreditoReporteMotivos[0] || null)
        : null;
    if (motivoSelect) {
        motivoSelect.value = defaultMotivo ? String(defaultMotivo.id) : '';
        if (window.jQuery && window.jQuery.fn.select2 && window.jQuery(motivoSelect).data('select2')) {
            window.jQuery(motivoSelect).val(defaultMotivo ? String(defaultMotivo.id) : '').trigger('change');
        }
    }
    openNotaCreditoReporte();
}

function openNotaCreditoReporte() {
    document.getElementById('modal-nota-credito-reporte').classList.add('open');
    initNotaCreditoMotivoSelect();
    const sel = document.getElementById('nc-motivo');
    const defaultMotivo = Array.isArray(notaCreditoReporteMotivos)
        ? (notaCreditoReporteMotivos.find(m => String(m.codigo || '').trim() === '01') || notaCreditoReporteMotivos[0] || null)
        : null;
    if (sel && defaultMotivo) {
        sel.value = String(defaultMotivo.id);
        if (window.jQuery && window.jQuery.fn.select2 && window.jQuery(sel).data('select2')) {
            window.jQuery(sel).val(String(defaultMotivo.id)).trigger('change');
        }
    }
}

function closeNotaCreditoReporte() {
    document.getElementById('modal-nota-credito-reporte').classList.remove('open');
}

function emitirNotaCreditoReporte() {
    if (!notaCreditoReporteOrigen) {
        showToast('Selecciona un comprobante origen', 'error');
        return;
    }
    const tipoId = document.getElementById('nc-motivo').value;
    const descripcion = document.getElementById('nc-descripcion').value.trim();
    if (!tipoId) {
        showToast('Selecciona el motivo de la nota', 'error');
        return;
    }
    if (!descripcion) {
        showToast('Escribe una descripcion', 'error');
        return;
    }

    const btn = document.getElementById('btn-emitir-nc');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Emitiendo...';

    fetch(BASE + 'modules/facturacion/api.php?action=crear_nota_credito', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            comprobante_origen_id: notaCreditoReporteOrigen.comprobante_id,
            tipo_nota_credito_id: tipoId,
            descripcion
        })
    })
        .then(async r => {
            const data = await r.json();
            if (!r.ok || data.error) throw new Error(data.message || 'No se pudo emitir la nota de credito.');
            closeNotaCreditoReporte();
            showToast(data.message || 'Nota de credito emitida correctamente.', 'success');
            buscar();
        })
        .catch(err => showToast(err.message || 'Error al emitir nota de credito', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Emitir nota de credito';
        });
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
    loadReporteFacturacion();
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
    destroyReporteTable();
    document.getElementById('tabla-body').innerHTML =
        '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
    const tableFoot = document.getElementById('tabla-foot');
    if (tableFoot) {
        tableFoot.innerHTML = '';
        tableFoot.style.display = 'none';
    }
    document.getElementById('result-count').textContent = '—';

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'reporte' }))
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                document.getElementById('tabla-body').innerHTML =
                    `<tr><td colspan="9" style="text-align:center;padding:30px;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> ${data.message}</td></tr>`;
                document.getElementById('result-count').textContent = 'Error';
                return;
            }

            document.getElementById('result-count').textContent = data.length + ' resultado(s)';

            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-inbox" style="font-size:1.3rem"></i><br><br>Sin ventas para los filtros aplicados</td></tr>';
                renderSummaryCard(0, 0);
                return;
            }

            const pagoIcon   = { efectivo:'ðŸ’µ', yape:`<img src="${BASE}assets/img/yape_plin.jpg" alt="Yape" style="height:18px;vertical-align:middle;border-radius:3px">`, plin:`<img src="${BASE}assets/img/yape_plin.jpg" alt="Plin" style="height:18px;vertical-align:middle;border-radius:3px">`, tarjeta:'ðŸ’³', transferencia:'ðŸ¦' };
            const coloresUser = ['#4f46e5','#0891b2','#16a34a','#d97706','#7c3aed','#dc2626','#0f766e','#c2410c'];
            const vendColorMap = {};
            let vendIdx = 0;

            document.getElementById('tabla-body').innerHTML = data.map(v => {
                const dt       = new Date(v.created_at);
                const fechaStr = dt.toLocaleDateString('es-PE') + ' ' + dt.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' });
                const fechaOrden = dt.getTime();
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
                        const reenviar = parseInt(v.comprobante_id || 0, 10) > 0 && !v.enlace_cdr
                            ? `<button class="btn btn-ghost btn-sm" type="button" onclick="reenviarSunat(${parseInt(v.comprobante_id, 10)})" title="Reenviar a SUNAT" style="margin-left:6px;padding:4px 7px"><i class="fas fa-paper-plane"></i></button>`
                            : '';
                        return `<span class="badge ${cls}" style="font-size:.7rem;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle" title="${txt}">${txt}</span>${pdf}${reenviar}`;
                    })()
                    : '<span style="color:var(--text-light);font-size:.8rem">—</span>';

                return `<tr ${sfx}>
                    <td><span style="font-weight:700;color:var(--primary);font-size:.88rem">${v.numero_venta}</span></td>
                    <td data-order="${fechaOrden}" style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">${fechaStr}</td>
                    <td style="white-space:nowrap">${vendBadge}</td>
                    <td style="font-size:.82rem">
                        <div style="font-weight:500">${v.cliente}</div>
                        ${v.dni ? `<div style="color:var(--text-muted);font-size:.75rem">DNI: ${v.dni}</div>` : ''}
                        ${v.ruc ? `<div style="color:var(--text-muted);font-size:.75rem">RUC: ${v.ruc}</div>` : ''}
                    </td>
                    <td><span class="badge badge-gray">${v.num_items}</span></td>
                    <td style="font-size:.82rem;white-space:nowrap">${pagoIcon[v.tipo_pago]||''} ${v.tipo_pago}</td>
                    <td>${compBadge}</td>
                    <td data-order="${parseFloat(v.subtotal)}" class="text-right" style="font-size:.85rem">S/ ${parseFloat(v.subtotal).toFixed(2)}</td>
                    <td data-order="${parseFloat(v.igv)}" class="text-right" style="font-size:.85rem;color:var(--text-muted)">S/ ${parseFloat(v.igv).toFixed(2)}</td>
                    <td data-order="${parseFloat(v.total)}" class="text-right"><strong style="font-size:.9rem">S/ ${parseFloat(v.total).toFixed(2)}</strong></td>
                    <td><span class="badge ${esCls}">${v.estado}</span></td>
                    <td class="sunat-cell">${sunatBadge}</td>
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
            initReporteTable();
        })
        .catch(() => showToast('Error al cargar el reporte', 'error'));
}

function loadReporteFacturacion() {
    destroyReporteTable();
    renderSalesTableHeader();

    const tableFoot = document.getElementById('tabla-foot');
    if (tableFoot) {
        tableFoot.innerHTML = '';
        tableFoot.style.display = 'none';
    }

    document.getElementById('tabla-body').innerHTML =
        '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
    document.getElementById('result-count').textContent = '...';

    fetch(BASE + 'modules/facturacion/api.php?' + buildQuery({ action: 'reporte' }))
        .then(r => r.json())
        .then(data => {
            reporteVentasCache = Array.isArray(data) ? data : [];
            if (data.error) {
                document.getElementById('tabla-body').innerHTML =
                    `<tr><td colspan="9" style="text-align:center;padding:30px;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> ${data.message}</td></tr>`;
                document.getElementById('result-count').textContent = 'Error';
                renderSummaryCard(0, 0);
                return;
            }

            document.getElementById('result-count').textContent = `${data.length} resultado(s)`;

            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-inbox" style="font-size:1.3rem"></i><br><br>Sin ventas para los filtros aplicados</td></tr>';
                renderSummaryCard(0, 0);
                return;
            }

            reporteVentasMap = {};
            document.getElementById('tabla-body').innerHTML = data.map(v => {
                reporteVentasMap[String(v.id)] = v;
                const dt = new Date(v.created_at);
                const fechaOrden = dt.getTime();
                const fecha = dt.toLocaleDateString('es-PE');
                const hora = dt.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                const total = parseFloat(v.total || 0);
                const clienteDoc = v.ruc || v.dni || '';
                const responseCode = v.nubefact_response
                    ? (() => {
                        try {
                            const parsed = typeof v.nubefact_response === 'string' ? JSON.parse(v.nubefact_response) : v.nubefact_response;
                            return parsed?.cdr?.response_code ?? parsed?.cdr?.responseCode ?? null;
                        } catch (_) {
                            return null;
                        }
                    })()
                    : null;
                const sunat = shortSunatStatus(v.estado_sunat, responseCode);
                const estadoLabel = v.estado === 'completada' ? 'Vigente' : 'Anulado';
                const estadoClass = v.estado === 'completada' ? 'badge-success' : 'badge-danger';

                const clienteHtml = `
                    <div class="client-block">
                        <div class="client-name">${v.cliente}</div>
                        ${clienteDoc ? `<div class="client-doc">${clienteDoc}</div>` : ''}
                    </div>
                `;

                const comprobanteHtml = v.comprobante_numero
                    ? `<div class="voucher-block"><strong>${v.comprobante_numero}</strong><span>${String(v.tipo_comprobante || '').toUpperCase()}</span></div>`
                    : `<div class="voucher-block"><strong>${v.numero_venta}</strong><span>NOTA DE VENTA</span></div>`;

                const xmlBtn = v.enlace_xml
                    ? actionIconButton('file-code', 'Ver XML', v.enlace_xml)
                    : '';
                const cdrBtn = v.enlace_cdr
                    ? actionIconButton('receipt', 'Ver CDR', v.enlace_cdr)
                    : '';

                const acciones = `
                    <div class="actions-inline">
                        ${actionIconButton('print', 'Imprimir ticket', null, `imprimirTicketVenta(${parseInt(v.id, 10)})`)}
                        ${v.comprobante_id && v.enlace_cdr && v.tipo_comprobante !== 'ticket'
                            ? actionIconButton('file-circle-plus', 'Generar nota de credito', null, `abrirNotaCreditoReporte(${parseInt(v.comprobante_id, 10)})`)
                            : ''}
                        ${v.enlace_pdf ? actionIconButton('file-pdf', 'Ver PDF', v.enlace_pdf) : ''}
                        ${parseInt(v.comprobante_id || 0, 10) > 0 ? actionIconButton('paper-plane', 'Reenviar a SUNAT', null, `reenviarSunat(${parseInt(v.comprobante_id, 10)})`) : ''}
                    </div>
                `;

                return `
                    <tr>
                        <td data-order="${fechaOrden}">
                            <div class="date-block">
                                <strong>${fecha}</strong>
                                <span>${hora}</span>
                            </div>
                        </td>
                        <td>${clienteHtml}</td>
                        <td>${comprobanteHtml}</td>
                        <td data-order="${total}" class="text-right"><span class="total-pill">S/ ${total.toFixed(2)}</span></td>
                        <td>${xmlBtn}</td>
                        <td>${cdrBtn}</td>
                        <td><span class="badge ${sunat.className}" title="${sunat.title}">${sunat.label}</span></td>
                        <td><span class="badge ${estadoClass}">${estadoLabel}</span></td>
                        <td>${acciones}</td>
                    </tr>
                `;
            }).join('');

            const completadas = data.filter(v => v.estado === 'completada');
            const totalGeneral = completadas.reduce((sum, row) => sum + parseFloat(row.total || 0), 0);
            renderSummaryCard(completadas.length, totalGeneral);
            initReporteTable();
        })
        .catch(() => {
            document.getElementById('tabla-body').innerHTML =
                '<tr><td colspan="9" style="text-align:center;padding:30px;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> Error al cargar el reporte</td></tr>';
            document.getElementById('result-count').textContent = 'Error';
            renderSummaryCard(0, 0);
        });
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

function reenviarSunat(comprobanteId) {
    if (!comprobanteId) {
        return;
    }

    fetch(BASE + 'modules/facturacion/api.php?action=reenviar_sunat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comprobante_id: comprobanteId })
    })
        .then(async r => {
            const data = await r.json();
            if (!r.ok || data.error) {
                throw new Error(data.message || 'No se pudo reenviar el comprobante.');
            }
            showToast(data.message || 'Comprobante reenviado a SUNAT.', 'success');
            buscar();
        })
        .catch(err => showToast(err.message || 'Error al reenviar a SUNAT', 'error'));
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
    cargarMotivosNotaCreditoReporte();
    loadUsuarios();
    buscar();
    document.getElementById('f-q').addEventListener('keyup', e => { if (e.key === 'Enter') buscar(); });
});
</script>

<style>
.stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
.stat-icon.teal   { background: #f0fdfa; color: #0d9488; }
.facturacion-table thead th {
    white-space: nowrap;
}
    .facturacion-table tbody td {
        vertical-align: middle;
    }
    .facturacion-table {
        table-layout: fixed;
    }
.table-wrap {
    overflow-x: auto;
    overflow-y: hidden;
    width: 100%;
    -webkit-overflow-scrolling: touch;
}
.table-wrap table.dataTable {
    width: 100% !important;
    min-width: 1180px;
    border-collapse: separate !important;
}
.dataTables_wrapper {
    padding: 0 0 12px;
    width: 100%;
    overflow: hidden;
    box-sizing: border-box;
}
.dt-toolbar,
.dt-footer {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    padding: 14px 16px 0;
    width: 100%;
    box-sizing: border-box;
}
.dt-toolbar {
    border-bottom: 1px solid var(--border);
    padding-bottom: 12px;
    margin-bottom: 8px;
}
.dt-footer {
    border-top: 1px solid var(--border);
    margin-top: 8px;
    padding-top: 12px;
}
.dataTables_filter input,
.dataTables_length select {
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #fff;
    color: var(--text);
    min-height: 36px;
    padding: 0 12px;
    font: inherit;
}
.dataTables_filter label,
.dataTables_length label,
.dataTables_info {
    color: var(--text-muted);
    font-size: .8rem;
}
.dataTables_length,
.dataTables_filter,
.dataTables_info,
.dataTables_paginate {
    margin: 0 !important;
}
.dataTables_filter {
    margin-left: auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex: 1 1 280px;
    min-width: 220px;
}
.dataTables_paginate {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
}
.dataTables_wrapper .dataTables_paginate ul.pagination {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
    display: flex !important;
    gap: 6px;
    align-items: center;
}
.dataTables_wrapper .dataTables_paginate ul.pagination li {
    list-style: none !important;
    margin: 0 !important;
}
.dataTables_wrapper .dataTables_paginate .page-link {
    border: 1px solid var(--border) !important;
    border-radius: 8px !important;
    background: #fff !important;
    color: var(--text) !important;
    padding: 5px 10px !important;
    min-width: 34px;
    font-size: .8rem !important;
    line-height: 1.2 !important;
    text-align: center;
    box-shadow: none !important;
}
.dataTables_wrapper .dataTables_paginate .page-item.active .page-link,
.dataTables_wrapper .dataTables_paginate .page-link:hover {
    background: var(--primary) !important;
    color: #fff !important;
    border-color: var(--primary) !important;
}
.dataTables_wrapper .dataTables_paginate .page-item.disabled .page-link {
    opacity: .5;
    cursor: not-allowed !important;
    background: #fff !important;
    color: var(--text-muted) !important;
    border-color: var(--border) !important;
}
.dataTables_wrapper .dataTables_filter input {
    margin-left: 8px;
    min-width: 180px;
    max-width: 260px;
}
.dataTables_wrapper .dataTables_length select {
    margin: 0 6px;
    min-width: 74px;
}
.dataTables_info {
    padding: 0 16px 12px;
}
.tabla-summary {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 12px 16px 4px;
    flex-wrap: wrap;
}
.summary-pill {
    min-width: 150px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.summary-label {
    font-size: .72rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .03em;
}
.summary-pill strong {
    font-size: 1rem;
    color: var(--text);
}
.date-block,
.voucher-block,
.client-block {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.date-block strong,
.voucher-block strong,
.client-name {
    font-size: .9rem;
    font-weight: 600;
    color: var(--text);
}
.date-block span,
.voucher-block span,
.client-doc {
    font-size: .74rem;
    color: var(--text-muted);
}
.total-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
    padding: 8px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    font-weight: 700;
    color: var(--text);
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
    display: flex;
    gap: 8px;
    justify-content: center;
}
    .sunat-cell {
    width: 88px !important;
    min-width: 88px !important;
    max-width: 88px !important;
    white-space: nowrap;
    text-align: center;
    padding-left: 8px !important;
    padding-right: 8px !important;
}
    .sunat-cell .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 76px;
        max-width: 76px;
        padding: 6px 10px;
        font-size: .72rem !important;
        line-height: 1;
    }
.sunat-cell .icon-action-btn,
.sunat-cell .btn.btn-ghost.btn-sm {
    width: 28px;
    height: 28px;
    padding: 0 !important;
    margin-left: 4px !important;
    flex-shrink: 0;
}
.facturacion-table tbody td {
    padding-top: 14px !important;
    padding-bottom: 14px !important;
}
.dt-footer {
    padding-bottom: 10px;
}
@media (max-width: 768px) {
    .dt-toolbar,
    .dt-footer {
        padding: 12px 12px 0;
    }
    .dataTables_filter {
        margin-left: 0;
        width: 100%;
    }
    .dataTables_filter input {
        width: 100%;
        max-width: none;
    }
    .table-wrap table.dataTable {
        min-width: 1050px;
    }
    .dataTables_wrapper .dataTables_paginate .page-link {
        padding: 4px 8px !important;
        min-width: 30px;
    }
    .tabla-summary {
        justify-content: stretch;
        padding: 12px;
    }
    .summary-pill {
        width: 100%;
        min-width: 0;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>

