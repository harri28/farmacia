<?php
// ============================================================
// ARCHIVO: farmacia/modules/almacen/index.php
// MÓDULO:  Almacén → Ingresos de Stock
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin'];
$current_module = 'almacen';
$current_page   = 'ingresos';
$page_title     = 'Ingresos de Stock — FarmaSystem';
$breadcrumb     = '<strong>Almacén</strong> / Ingresos de Stock';

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><i class="fas fa-truck-loading" style="color:var(--primary);margin-right:8px"></i>Ingresos de Stock</div>
        <div class="page-subtitle">Registra la recepción de mercadería de proveedores</div>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openNuevoIngreso()">
            <i class="fas fa-plus"></i> Nuevo Ingreso
        </button>
    </div>
</div>

<!-- Stats -->
<div class="stat-cards" id="stats-container">
    <?php foreach (['blue','green','yellow','red'] as $c): ?>
    <div class="stat-card">
        <div class="stat-icon <?= $c ?>"><i class="fas fa-spinner fa-spin"></i></div>
        <div><div class="stat-value">—</div><div class="stat-label">...</div></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="card" style="margin-bottom:20px">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
            <label class="form-label">Desde</label>
            <input type="date" id="f-desde" class="form-control" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
            <label class="form-label">Hasta</label>
            <input type="date" id="f-hasta" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
            <label class="form-label">Estado</label>
            <select class="form-control" id="f-estado">
                <option value="">Todos</option>
                <option value="completado">Completado</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:2;min-width:180px">
            <label class="form-label">Buscar</label>
            <div class="input-group">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-q" class="form-control" placeholder="N° ingreso o proveedor...">
            </div>
        </div>
        <button class="btn btn-primary" onclick="loadIngresos()"><i class="fas fa-search"></i> Buscar</button>
    </div>
</div>

<!-- Tabla de ingresos -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Historial de ingresos</div>
        <span style="font-size:.82rem;color:var(--text-muted)" id="result-count">Cargando...</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N° Ingreso</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>N° Factura</th>
                    <th>Items</th>
                    <th class="text-right">Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-light)">
                    <i class="fas fa-spinner fa-spin"></i>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== MODAL: Detalle de Ingreso ===================== -->
<div class="modal-overlay" id="modal-detalle">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="detalle-titulo">Detalle de Ingreso</h3>
            <button class="modal-close" onclick="closeModal('modal-detalle')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detalle-body"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-detalle')">Cerrar</button>
            <button class="btn btn-danger" id="btn-anular" style="display:none" onclick="anularIngreso()">
                <i class="fas fa-ban"></i> Anular Ingreso
            </button>
        </div>
    </div>
</div>

<!-- ===================== MODAL: Nuevo Ingreso ===================== -->
<div class="modal-overlay" id="modal-nuevo">
    <div class="modal" style="max-width:900px;width:96%">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-truck-loading" style="color:var(--primary);margin-right:8px"></i>Nuevo Ingreso de Stock
            </h3>
            <button class="modal-close" onclick="closeModal('modal-nuevo')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="max-height:75vh;overflow-y:auto">

            <!-- Cabecera del ingreso -->
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:14px;margin-bottom:18px">
                <div class="form-group" style="margin:0">
                    <label class="form-label">Proveedor</label>
                    <select id="n-proveedor" class="form-control">
                        <option value="">— Sin proveedor —</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label class="form-label">N° Factura / Guía</label>
                    <input type="text" id="n-factura" class="form-control" placeholder="F001-00123">
                </div>
                <div class="form-group" style="margin:0">
                    <label class="form-label">Fecha Factura</label>
                    <input type="date" id="n-fecha" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <!-- Buscador de productos -->
            <div style="margin-bottom:12px">
                <label class="form-label">
                    <i class="fas fa-barcode" style="color:var(--primary);margin-right:5px"></i>
                    Agregar producto — escaneá o escribí nombre / código
                </label>
                <div style="position:relative">
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-search"></i></span>
                        <input type="text" id="n-producto-search" class="form-control"
                            placeholder="Nombre o código de barras..." autocomplete="off">
                    </div>
                    <div id="n-producto-dropdown"
                        style="display:none;position:absolute;top:100%;left:0;right:0;background:var(--surface);
                               border:1px solid var(--border);border-radius:var(--radius);z-index:200;
                               box-shadow:0 4px 16px rgba(0,0,0,.12);max-height:220px;overflow-y:auto">
                    </div>
                </div>
            </div>

            <!-- Tabla de líneas -->
            <div class="table-wrap" style="margin-bottom:14px">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-right" style="width:90px">Cantidad</th>
                            <th class="text-right" style="width:120px">P. Compra (S/)</th>
                            <th class="text-right" style="width:100px">Subtotal</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="n-lineas-body">
                        <tr id="n-lineas-empty">
                            <td colspan="6" style="text-align:center;padding:24px;color:var(--text-light)">
                                <i class="fas fa-inbox" style="font-size:1.3rem"></i>
                                <p style="margin:8px 0 0">Agrega productos escaneando o buscando arriba</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totales -->
            <div style="display:flex;justify-content:flex-end;margin-bottom:14px">
                <div style="min-width:260px;background:var(--surface-2);border-radius:var(--radius);padding:12px 16px;font-size:.88rem">
                    <div style="display:flex;justify-content:space-between;padding:3px 0">
                        <span style="color:var(--text-muted)">Subtotal</span>
                        <span id="n-subtotal">S/ 0.00</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:3px 0">
                        <span style="color:var(--text-muted)">IGV (18%)</span>
                        <span id="n-igv">S/ 0.00</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:6px 0 0;border-top:1px solid var(--border);margin-top:6px;font-weight:700;font-size:1rem">
                        <span>TOTAL</span>
                        <span id="n-total" style="color:var(--success)">S/ 0.00</span>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="form-group" style="margin:0">
                <label class="form-label">Observaciones <span style="font-size:.8rem;color:var(--text-muted)">(opcional)</span></label>
                <input type="text" id="n-obs" class="form-control" placeholder="Notas sobre el ingreso...">
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-nuevo')">Cancelar</button>
            <button class="btn btn-primary btn-lg" id="btn-guardar-ingreso" onclick="saveIngreso()">
                <i class="fas fa-save"></i> Registrar Ingreso
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
// ============================================================
// Almacén — Ingresos JavaScript
// ============================================================

const BASE = '../../';
let currentIngresoId = null;
let lines = [];   // { producto_id, codigo, nombre, cantidad, precio_unitario }

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadIngresos();
    loadProveedoresSelect();
    setupProductoSearch();
    setupBarcodeScanner();
    document.getElementById('f-q').addEventListener('keyup', e => { if (e.key === 'Enter') loadIngresos(); });
});

// ---- Stats ----
function loadStats() {
    fetch(BASE + 'modules/almacen/api.php?action=stats_almacen')
        .then(r => r.json())
        .then(d => {
            const cfg = [
                { icon: 'truck-loading', color: 'blue',   val: d.ingresos_mes,        label: 'Ingresos este mes' },
                { icon: 'dollar-sign',   color: 'green',  val: 'S/ ' + parseFloat(d.valor_mes).toFixed(2), label: 'Valor comprado' },
                { icon: 'truck',         color: 'yellow', val: d.proveedores_activos,  label: 'Proveedores activos' },
                { icon: 'ban',           color: 'red',    val: d.anulados_mes,         label: 'Anulados este mes' },
            ];
            document.getElementById('stats-container').innerHTML = cfg.map(c => `
                <div class="stat-card">
                    <div class="stat-icon ${c.color}"><i class="fas fa-${c.icon}"></i></div>
                    <div><div class="stat-value">${c.val}</div><div class="stat-label">${c.label}</div></div>
                </div>`).join('');
        });
}

// ---- Cargar historial ----
function loadIngresos() {
    const params = new URLSearchParams({
        action: 'ingresos_listar',
        desde:  document.getElementById('f-desde').value,
        hasta:  document.getElementById('f-hasta').value,
        estado: document.getElementById('f-estado').value,
        q:      document.getElementById('f-q').value,
    });
    document.getElementById('tabla-body').innerHTML =
        '<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(BASE + 'modules/almacen/api.php?' + params)
        .then(r => r.json())
        .then(data => {
            document.getElementById('result-count').textContent = data.length + ' registro(s)';
            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-inbox" style="font-size:1.3rem"></i><br><br>No se encontraron ingresos</td></tr>';
                return;
            }
            document.getElementById('tabla-body').innerHTML = data.map(i => {
                const esCls = i.estado === 'completado' ? 'badge-success' : 'badge-danger';
                const dt    = new Date(i.created_at);
                const fecha = dt.toLocaleDateString('es-PE') + ' ' + dt.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                return `<tr>
                    <td><span style="font-weight:700;color:var(--primary)">${i.numero_ingreso}</span></td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${fecha}</td>
                    <td style="font-size:.85rem">${i.proveedor}</td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${i.numero_factura || '—'}</td>
                    <td><span class="badge badge-gray">${i.num_items} items</span></td>
                    <td class="text-right"><strong>S/ ${parseFloat(i.total).toFixed(2)}</strong></td>
                    <td><span class="badge ${esCls}">${i.estado}</span></td>
                    <td>
                        <button class="btn btn-ghost btn-sm" onclick="verDetalle(${i.id},'${i.numero_ingreso}','${i.estado}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');
        })
        .catch(() => showToast('Error al cargar ingresos', 'error'));
}

// ---- Detalle ----
function verDetalle(id, numero, estado) {
    currentIngresoId = id;
    document.getElementById('detalle-titulo').innerHTML =
        '<i class="fas fa-truck-loading" style="color:var(--primary);margin-right:8px"></i>Detalle: ' + numero;
    document.getElementById('btn-anular').style.display = estado === 'completado' ? 'inline-flex' : 'none';
    document.getElementById('detalle-body').innerHTML =
        '<div style="text-align:center;padding:30px"><i class="fas fa-spinner fa-spin" style="font-size:1.5rem;color:var(--text-light)"></i></div>';
    openModal('modal-detalle');

    fetch(BASE + `modules/almacen/api.php?action=ingreso_detalle&id=${id}`)
        .then(r => r.json())
        .then(items => {
            if (!items.length) {
                document.getElementById('detalle-body').innerHTML =
                    '<p style="text-align:center;padding:20px;color:var(--text-muted)">Sin detalles disponibles</p>';
                return;
            }
            const total = items.reduce((s, i) => s + parseFloat(i.subtotal), 0);
            document.getElementById('detalle-body').innerHTML = `
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Código</th><th>Producto</th>
                            <th class="text-right">Cant.</th>
                            <th class="text-right">P. Compra</th>
                            <th class="text-right">Subtotal</th>
                        </tr></thead>
                        <tbody>${items.map(i => `
                            <tr>
                                <td style="font-size:.8rem;color:var(--text-muted)">${i.codigo}</td>
                                <td style="font-weight:500">${i.producto_nombre}</td>
                                <td class="text-right">${i.cantidad}</td>
                                <td class="text-right">S/ ${parseFloat(i.precio_unitario).toFixed(2)}</td>
                                <td class="text-right"><strong>S/ ${parseFloat(i.subtotal).toFixed(2)}</strong></td>
                            </tr>`).join('')}
                        </tbody>
                        <tfoot><tr style="background:var(--surface-2)">
                            <td colspan="4" style="padding:10px 14px;font-weight:700;text-align:right">TOTAL</td>
                            <td style="padding:10px 14px;font-weight:700;text-align:right;color:var(--success);font-size:1rem">
                                S/ ${total.toFixed(2)}
                            </td>
                        </tr></tfoot>
                    </table>
                </div>`;
        });
}

function anularIngreso() {
    if (!currentIngresoId || !confirm('¿Anular este ingreso? El stock de todos los productos será revertido.')) return;
    fetch(BASE + 'modules/almacen/api.php?action=anular_ingreso', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentIngresoId }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Ingreso anulado correctamente', 'success');
        closeModal('modal-detalle');
        loadIngresos();
        loadStats();
    })
    .catch(() => showToast('Error al anular', 'error'));
}

// ---- Nuevo Ingreso ----
function loadProveedoresSelect() {
    fetch(BASE + 'modules/almacen/api.php?action=proveedores_listar&activos=1&q=')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('n-proveedor');
            data.forEach(p => {
                sel.innerHTML += `<option value="${p.id}">${p.nombre_comercial || p.razon_social}</option>`;
            });
        });
}

function openNuevoIngreso() {
    lines = [];
    renderLineas();
    document.getElementById('n-proveedor').value = '';
    document.getElementById('n-factura').value   = '';
    document.getElementById('n-fecha').value     = '<?= date('Y-m-d') ?>';
    document.getElementById('n-obs').value       = '';
    document.getElementById('n-producto-search').value = '';
    document.getElementById('n-producto-dropdown').style.display = 'none';
    calcularTotales();
    openModal('modal-nuevo');
    setTimeout(() => document.getElementById('n-producto-search').focus(), 100);
}

// ---- Buscador de productos (autocomplete) ----
function setupProductoSearch() {
    let timer;
    const input    = document.getElementById('n-producto-search');
    const dropdown = document.getElementById('n-producto-dropdown');

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (!q) { dropdown.style.display = 'none'; return; }
        timer = setTimeout(() => buscarProducto(q), 200);
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Escape') { dropdown.style.display = 'none'; input.value = ''; }
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function buscarProducto(q) {
    const dropdown = document.getElementById('n-producto-dropdown');
    fetch(BASE + `modules/almacen/api.php?action=buscar_producto&q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                dropdown.innerHTML = '<div style="padding:12px 16px;color:var(--text-muted);font-size:.85rem">Sin resultados</div>';
                dropdown.style.display = 'block';
                return;
            }
            dropdown.innerHTML = data.map(p => `
                <div onclick='seleccionarProducto(${JSON.stringify(p)})'
                    style="padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--border);
                           display:flex;justify-content:space-between;align-items:center;font-size:.85rem"
                    onmouseover="this.style.background='var(--primary-light)'"
                    onmouseout="this.style.background=''">
                    <div>
                        <span style="font-weight:600">${p.nombre}</span>
                        <span style="color:var(--text-muted);margin-left:8px;font-size:.78rem">${p.codigo}</span>
                    </div>
                    <div style="text-align:right;white-space:nowrap">
                        <div style="color:var(--text-muted);font-size:.78rem">Stock: ${p.stock}</div>
                        <div style="color:var(--primary);font-weight:600">S/ ${parseFloat(p.precio_compra).toFixed(2)}</div>
                    </div>
                </div>`).join('');
            dropdown.style.display = 'block';
        });
}

function seleccionarProducto(p) {
    document.getElementById('n-producto-dropdown').style.display = 'none';
    document.getElementById('n-producto-search').value = '';
    addLinea(p);
    document.getElementById('n-producto-search').focus();
}

function addLinea(p) {
    const pid = parseInt(p.id);
    const existing = lines.findIndex(l => l.producto_id === pid);
    if (existing >= 0) {
        lines[existing].cantidad++;
        lines[existing].subtotal = lines[existing].cantidad * lines[existing].precio_unitario;
    } else {
        lines.push({
            producto_id:    pid,
            codigo:         p.codigo,
            nombre:         p.nombre,
            cantidad:       1,
            precio_unitario: parseFloat(p.precio_compra) || 0,
        });
    }
    renderLineas();
    calcularTotales();
}

function updateLinea(idx, field, value) {
    if (field === 'cantidad')       lines[idx].cantidad        = Math.max(1, parseInt(value) || 1);
    if (field === 'precio_unitario') lines[idx].precio_unitario = parseFloat(value) || 0;
    renderLineas();
    calcularTotales();
}

function removeLinea(idx) {
    lines.splice(idx, 1);
    renderLineas();
    calcularTotales();
}

function renderLineas() {
    const tbody = document.getElementById('n-lineas-body');
    const empty = document.getElementById('n-lineas-empty');

    // Quitar filas previas (excepto la fila vacía)
    tbody.querySelectorAll('.linea-row').forEach(r => r.remove());

    if (!lines.length) {
        empty.style.display = '';
        return;
    }
    empty.style.display = 'none';

    lines.forEach((l, idx) => {
        const sub = l.cantidad * l.precio_unitario;
        const tr  = document.createElement('tr');
        tr.className = 'linea-row';
        tr.innerHTML = `
            <td style="font-size:.8rem;color:var(--text-muted);font-family:monospace">${l.codigo}</td>
            <td style="font-weight:500;font-size:.88rem">${l.nombre}</td>
            <td class="text-right">
                <input type="number" value="${l.cantidad}" min="1"
                    style="width:72px;text-align:right;padding:5px 7px;border:1px solid var(--border);
                           border-radius:var(--radius-sm);font-size:.88rem;background:var(--surface)"
                    oninput="updateLinea(${idx},'cantidad',this.value)">
            </td>
            <td class="text-right">
                <input type="number" value="${l.precio_unitario.toFixed(2)}" min="0" step="0.01"
                    style="width:90px;text-align:right;padding:5px 7px;border:1px solid var(--border);
                           border-radius:var(--radius-sm);font-size:.88rem;background:var(--surface)"
                    oninput="updateLinea(${idx},'precio_unitario',this.value)">
            </td>
            <td class="text-right" style="font-weight:600">S/ ${sub.toFixed(2)}</td>
            <td>
                <button onclick="removeLinea(${idx})"
                    style="background:none;border:none;color:var(--danger);cursor:pointer;padding:4px 6px;font-size:.9rem">
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;
        tbody.insertBefore(tr, empty);
    });
}

function calcularTotales() {
    const subtotal = lines.reduce((s, l) => s + l.cantidad * l.precio_unitario, 0);
    const igv      = subtotal * 0.18;
    document.getElementById('n-subtotal').textContent = 'S/ ' + subtotal.toFixed(2);
    document.getElementById('n-igv').textContent      = 'S/ ' + igv.toFixed(2);
    document.getElementById('n-total').textContent    = 'S/ ' + subtotal.toFixed(2);
}

function saveIngreso() {
    if (!lines.length) { showToast('Agrega al menos un producto', 'error'); return; }

    for (const l of lines) {
        if (l.cantidad <= 0)        { showToast('La cantidad debe ser mayor a 0',         'error'); return; }
        if (l.precio_unitario <= 0) { showToast('El precio de compra debe ser mayor a 0', 'error'); return; }
    }

    const payload = {
        proveedor_id:    document.getElementById('n-proveedor').value || null,
        numero_factura:  document.getElementById('n-factura').value,
        fecha_factura:   document.getElementById('n-fecha').value,
        observaciones:   document.getElementById('n-obs').value,
        items: lines.map(l => ({
            producto_id:    l.producto_id,
            cantidad:       l.cantidad,
            precio_unitario: l.precio_unitario,
        })),
    };

    const btn = document.getElementById('btn-guardar-ingreso');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    fetch(BASE + 'modules/almacen/api.php?action=registrar_ingreso', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Registrar Ingreso';
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Ingreso ' + d.numero_ingreso + ' registrado correctamente', 'success');
        closeModal('modal-nuevo');
        loadIngresos();
        loadStats();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Registrar Ingreso';
        showToast('Error al registrar', 'error');
    });
}

// ---- Scanner de código de barras ----
function setupBarcodeScanner() {
    document.addEventListener('barcodescan', function(e) {
        const code      = e.detail.code.trim();
        const modalOpen = document.getElementById('modal-nuevo').classList.contains('open');
        if (!modalOpen) return;

        fetch(BASE + `modules/almacen/api.php?action=buscar_producto&q=${encodeURIComponent(code)}`)
            .then(r => r.json())
            .then(data => {
                const exact = data.find(p => p.codigo.toUpperCase() === code.toUpperCase());
                const prod  = exact || data[0];
                if (prod) {
                    addLinea(prod);
                    showToast('<i class="fas fa-barcode"></i> ' + prod.nombre, 'success');
                } else {
                    showToast('Código <strong>' + code + '</strong> no encontrado en inventario', 'error');
                }
            });
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
