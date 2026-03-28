<?php
// ============================================================
// ARCHIVO: farmacia/modules/inventario/index.php
// MÓDULO:  Inventario → Gestión de Productos
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$current_module = 'inventario';
$current_page   = 'inventario';
$page_title     = 'Inventario — FarmaSystem';
$breadcrumb     = '<strong>Inventario</strong> / Gestión de Productos';

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><i class="fas fa-boxes" style="color:var(--primary);margin-right:8px"></i>Inventario</div>
        <div class="page-subtitle">Gestiona productos, precios y niveles de stock</div>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openProductoModal()">
            <i class="fas fa-plus"></i> Nuevo Producto
        </button>
    </div>
</div>

<!-- Stat cards -->
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
        <div class="form-group" style="margin:0;flex:3;min-width:200px">
            <label class="form-label">Buscar</label>
            <div class="input-group">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-q" class="form-control" placeholder="Nombre, código o laboratorio...">
            </div>
        </div>
        <div class="form-group" style="margin:0;flex:2;min-width:160px">
            <label class="form-label">Categoría</label>
            <select class="form-control" id="f-categoria">
                <option value="0">Todas</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:2;min-width:160px">
            <label class="form-label">Estado de stock</label>
            <select class="form-control" id="f-stock">
                <option value="">Activos</option>
                <option value="bajo">⚠ Stock bajo</option>
                <option value="agotado">🔴 Agotado</option>
                <option value="ok">✅ Stock OK</option>
                <option value="inactivo">Inactivos</option>
            </select>
        </div>
        <button class="btn btn-outline" onclick="resetFiltros()" style="margin-bottom:0">
            <i class="fas fa-times"></i> Limpiar
        </button>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Productos</div>
        <span style="font-size:.82rem;color:var(--text-muted)" id="result-count">Cargando...</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Laboratorio</th>
                    <th class="text-right">P. Compra</th>
                    <th class="text-right">P. Venta</th>
                    <th class="text-right">Stock</th>
                    <th class="text-right">Mín.</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-light)">
                    <i class="fas fa-spinner fa-spin"></i>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== MODAL: Agregar / Editar Producto ===================== -->
<div class="modal-overlay" id="modal-producto">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-producto-title">
                <i class="fas fa-box" style="color:var(--primary);margin-right:8px"></i>Nuevo Producto
            </h3>
            <button class="modal-close" onclick="closeModal('modal-producto')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

                <div class="form-group">
                    <label class="form-label">Código <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="p-codigo" class="form-control" placeholder="MED001">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select id="p-categoria" class="form-control">
                        <option value="">Sin categoría</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="p-nombre" class="form-control" placeholder="Paracetamol 500mg x 10 tab">
                </div>

                <div class="form-group">
                    <label class="form-label">Laboratorio</label>
                    <input type="text" id="p-laboratorio" class="form-control" placeholder="Bayer">
                </div>
                <div class="form-group">
                    <label class="form-label">Presentación</label>
                    <input type="text" id="p-presentacion" class="form-control" placeholder="Tabletas x 10">
                </div>

                <div class="form-group">
                    <label class="form-label">Precio de Compra (S/)</label>
                    <input type="number" id="p-precio-compra" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Precio de Venta (S/) <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="p-precio-venta" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>

                <div class="form-group" id="stock-inicial-group">
                    <label class="form-label">Stock Inicial</label>
                    <input type="number" id="p-stock" class="form-control" placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Stock Mínimo</label>
                    <input type="number" id="p-stock-minimo" class="form-control" placeholder="5" min="0">
                </div>

                <div class="form-group" style="grid-column:1/-1;display:flex;gap:24px;align-items:center;padding:10px 0">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem">
                        <input type="checkbox" id="p-receta" style="width:16px;height:16px;cursor:pointer">
                        <span>Requiere receta médica</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem">
                        <input type="checkbox" id="p-favorito" style="width:16px;height:16px;cursor:pointer">
                        <span><i class="fas fa-star" style="color:#f59e0b"></i> Producto favorito</span>
                    </label>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-producto')">Cancelar</button>
            <button class="btn btn-primary" id="btn-guardar-producto" onclick="saveProducto()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- ===================== MODAL: Ajuste de Stock ===================== -->
<div class="modal-overlay" id="modal-ajuste">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-boxes" style="color:var(--primary);margin-right:8px"></i>Ajuste de Stock
            </h3>
            <button class="modal-close" onclick="closeModal('modal-ajuste')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:var(--surface-2);border-radius:var(--radius);padding:12px 16px;margin-bottom:16px">
                <div style="font-size:.8rem;color:var(--text-muted)">Producto</div>
                <div style="font-weight:600;font-size:.95rem" id="ajuste-nombre">—</div>
                <div style="font-size:.82rem;color:var(--text-muted);margin-top:4px">
                    Stock actual: <strong id="ajuste-stock-actual" style="color:var(--primary)">—</strong>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de ajuste</label>
                <div style="display:flex;gap:10px">
                    <label style="flex:1;display:flex;align-items:center;gap:10px;padding:12px 14px;border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:.15s" id="tipo-entrada-label">
                        <input type="radio" name="tipo-ajuste" value="entrada" id="tipo-entrada" checked style="accent-color:var(--success)">
                        <span><i class="fas fa-arrow-down" style="color:var(--success)"></i> <strong>Entrada</strong><br><small style="color:var(--text-muted)">Sumar al stock</small></span>
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:10px;padding:12px 14px;border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:.15s" id="tipo-salida-label">
                        <input type="radio" name="tipo-ajuste" value="salida" id="tipo-salida" style="accent-color:var(--danger)">
                        <span><i class="fas fa-arrow-up" style="color:var(--danger)"></i> <strong>Salida</strong><br><small style="color:var(--text-muted)">Restar del stock</small></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Cantidad</label>
                <input type="number" id="ajuste-cantidad" class="form-control" placeholder="0" min="1" style="font-size:1.1rem">
            </div>
            <div class="form-group">
                <label class="form-label">Motivo <span style="font-size:.8rem;color:var(--text-muted)">(opcional)</span></label>
                <input type="text" id="ajuste-motivo" class="form-control" placeholder="Ej: Compra, Devolución, Merma...">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-ajuste')">Cancelar</button>
            <button class="btn btn-primary" id="btn-guardar-ajuste" onclick="saveAjuste()">
                <i class="fas fa-check"></i> Aplicar Ajuste
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
// ============================================================
// Inventario JavaScript
// ============================================================

const BASE = '../../';
let categorias  = [];
let editingId   = null;
let ajusteProducto = null;

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    loadCategorias().then(() => {
        loadStats();
        loadProductos();
    });
    setupSearch();
    setupTipoAjuste();
    setupBarcodeScanner();
});

// ---- Stats ----
function loadStats() {
    fetch(BASE + 'modules/inventario/api.php?action=stats')
        .then(r => r.json())
        .then(d => {
            const cfg = [
                { icon: 'boxes',          color: 'blue',   val: d.total_activos, label: 'Productos activos' },
                { icon: 'dollar-sign',    color: 'green',  val: 'S/ ' + parseFloat(d.valor_inventario).toFixed(2), label: 'Valor en inventario' },
                { icon: 'exclamation-triangle', color: 'yellow', val: d.stock_bajo,    label: 'Stock bajo' },
                { icon: 'times-circle',   color: 'red',    val: d.agotados,      label: 'Agotados' },
            ];
            document.getElementById('stats-container').innerHTML = cfg.map(c => `
                <div class="stat-card" style="cursor:pointer" onclick="filtrarPor('${c.label}')">
                    <div class="stat-icon ${c.color}"><i class="fas fa-${c.icon}"></i></div>
                    <div><div class="stat-value">${c.val}</div><div class="stat-label">${c.label}</div></div>
                </div>`).join('');
        });
}

function filtrarPor(label) {
    const map = { 'Stock bajo': 'bajo', 'Agotados': 'agotado' };
    if (map[label]) {
        document.getElementById('f-stock').value = map[label];
        loadProductos();
    }
}

// ---- Categorías ----
async function loadCategorias() {
    const data = await fetch(BASE + 'modules/inventario/api.php?action=categorias').then(r => r.json());
    categorias = data;
    const sel1 = document.getElementById('f-categoria');
    const sel2 = document.getElementById('p-categoria');
    data.forEach(c => {
        sel1.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
        sel2.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
    });
}

// ---- Listar productos ----
function loadProductos() {
    const params = new URLSearchParams({
        action:       'listar',
        q:            document.getElementById('f-q').value,
        categoria_id: document.getElementById('f-categoria').value,
        stock_status: document.getElementById('f-stock').value,
    });

    document.getElementById('tabla-body').innerHTML =
        '<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(BASE + 'modules/inventario/api.php?' + params)
        .then(r => r.json())
        .then(data => {
            document.getElementById('result-count').textContent = data.length + ' producto(s)';
            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-box-open" style="font-size:1.3rem"></i><br><br>No se encontraron productos</td></tr>';
                return;
            }

            document.getElementById('tabla-body').innerHTML = data.map(p => {
                const agotado  = parseInt(p.stock) === 0;
                const stockBajo = parseInt(p.stock) > 0 && parseInt(p.stock) <= parseInt(p.stock_minimo);
                const stockCls  = agotado ? 'color:var(--danger);font-weight:700' :
                                  (stockBajo ? 'color:var(--warning,#f59e0b);font-weight:700' : 'color:var(--success);font-weight:600');
                const stockBadge = agotado  ? '<span class="badge badge-danger" style="font-size:.72rem">Agotado</span>' :
                                   (stockBajo ? '<span class="badge badge-warning" style="font-size:.72rem;background:#fef3c7;color:#92400e">Bajo</span>' : '');
                const activoBadge = p.activo == 't' || p.activo === true
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>';
                const favIcon = (p.favorito == 't' || p.favorito === true)
                    ? '<i class="fas fa-star" style="color:#f59e0b;margin-left:4px" title="Favorito"></i>' : '';
                const recetaIcon = (p.requiere_receta == 't' || p.requiere_receta === true)
                    ? '<i class="fas fa-prescription" style="color:var(--primary);margin-left:4px" title="Requiere receta"></i>' : '';

                return `<tr>
                    <td style="font-family:monospace;font-size:.82rem;color:var(--text-muted)">${p.codigo}</td>
                    <td style="font-weight:500">${p.nombre}${favIcon}${recetaIcon}</td>
                    <td style="font-size:.82rem">${p.categoria || '<span style="color:var(--text-light)">—</span>'}</td>
                    <td style="font-size:.82rem;color:var(--text-muted)">${p.laboratorio || '—'}</td>
                    <td class="text-right" style="font-size:.85rem">S/ ${parseFloat(p.precio_compra).toFixed(2)}</td>
                    <td class="text-right" style="font-weight:600">S/ ${parseFloat(p.precio_venta).toFixed(2)}</td>
                    <td class="text-right">
                        <span style="${stockCls}">${p.stock}</span>
                        ${stockBadge}
                    </td>
                    <td class="text-right" style="font-size:.85rem;color:var(--text-muted)">${p.stock_minimo}</td>
                    <td>${activoBadge}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <button class="btn btn-ghost btn-sm" title="Ajustar stock" onclick='openAjusteModal(${JSON.stringify(p)})'>
                                <i class="fas fa-boxes"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm" title="Editar" onclick='openProductoModal(${JSON.stringify(p)})'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm" title="${p.activo == 't' || p.activo === true ? 'Desactivar' : 'Activar'}"
                                onclick="toggleActivo(${p.id})" style="color:${p.activo == 't' || p.activo === true ? 'var(--danger)' : 'var(--success)'}">
                                <i class="fas fa-${p.activo == 't' || p.activo === true ? 'toggle-on' : 'toggle-off'}"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        })
        .catch(() => showToast('Error al cargar productos', 'error'));
}

// ---- Búsqueda con debounce ----
function setupSearch() {
    let timer;
    document.getElementById('f-q').addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(loadProductos, 250);
    });
    document.getElementById('f-categoria').addEventListener('change', loadProductos);
    document.getElementById('f-stock').addEventListener('change', loadProductos);
}

function resetFiltros() {
    document.getElementById('f-q').value = '';
    document.getElementById('f-categoria').value = '0';
    document.getElementById('f-stock').value = '';
    loadProductos();
}

// ---- Modal Producto (crear / editar) ----
function openProductoModal(producto = null) {
    editingId = producto ? producto.id : null;
    const title  = document.getElementById('modal-producto-title');
    const stockGroup = document.getElementById('stock-inicial-group');

    title.innerHTML = editingId
        ? '<i class="fas fa-edit" style="color:var(--primary);margin-right:8px"></i>Editar Producto'
        : '<i class="fas fa-plus" style="color:var(--primary);margin-right:8px"></i>Nuevo Producto';

    // Campo stock solo en creación
    stockGroup.style.display = editingId ? 'none' : 'block';

    // Resetear / poblar form
    document.getElementById('p-codigo').value        = producto?.codigo        ?? '';
    document.getElementById('p-nombre').value        = producto?.nombre        ?? '';
    document.getElementById('p-categoria').value     = producto?.categoria_id  ?? '';
    document.getElementById('p-laboratorio').value   = producto?.laboratorio   ?? '';
    document.getElementById('p-presentacion').value  = producto?.presentacion  ?? '';
    document.getElementById('p-precio-compra').value = producto ? parseFloat(producto.precio_compra).toFixed(2) : '';
    document.getElementById('p-precio-venta').value  = producto ? parseFloat(producto.precio_venta).toFixed(2)  : '';
    document.getElementById('p-stock').value         = producto?.stock         ?? '';
    document.getElementById('p-stock-minimo').value  = producto?.stock_minimo  ?? '5';
    document.getElementById('p-receta').checked      = producto?.requiere_receta == 't' || producto?.requiere_receta === true;
    document.getElementById('p-favorito').checked    = producto?.favorito == 't'        || producto?.favorito === true;

    openModal('modal-producto');
    setTimeout(() => document.getElementById('p-codigo').focus(), 100);
}

function saveProducto() {
    const codigo      = document.getElementById('p-codigo').value.trim();
    const nombre      = document.getElementById('p-nombre').value.trim();
    const precio_venta = parseFloat(document.getElementById('p-precio-venta').value);

    if (!codigo)                 { showToast('El código es requerido', 'error'); return; }
    if (!nombre)                 { showToast('El nombre es requerido', 'error'); return; }
    if (!precio_venta || precio_venta <= 0) { showToast('El precio de venta debe ser mayor a 0', 'error'); return; }

    const payload = {
        id:              editingId,
        codigo,
        nombre,
        categoria_id:    document.getElementById('p-categoria').value     || null,
        laboratorio:     document.getElementById('p-laboratorio').value,
        presentacion:    document.getElementById('p-presentacion').value,
        precio_compra:   parseFloat(document.getElementById('p-precio-compra').value) || 0,
        precio_venta,
        stock:           parseInt(document.getElementById('p-stock').value)        || 0,
        stock_minimo:    parseInt(document.getElementById('p-stock-minimo').value) || 5,
        requiere_receta: document.getElementById('p-receta').checked,
        favorito:        document.getElementById('p-favorito').checked,
    };

    const action = editingId ? 'actualizar' : 'crear';
    const btn    = document.getElementById('btn-guardar-producto');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    fetch(BASE + `modules/inventario/api.php?action=${action}`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        if (data.error) { showToast(data.message, 'error'); return; }
        showToast(data.message, 'success');
        closeModal('modal-producto');
        loadProductos();
        loadStats();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        showToast('Error al guardar', 'error');
    });
}

// ---- Modal Ajuste de Stock ----
function setupTipoAjuste() {
    document.querySelectorAll('input[name="tipo-ajuste"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.getElementById('tipo-entrada-label').style.borderColor =
                document.getElementById('tipo-entrada').checked ? 'var(--success)' : 'var(--border)';
            document.getElementById('tipo-salida-label').style.borderColor =
                document.getElementById('tipo-salida').checked ? 'var(--danger)' : 'var(--border)';
        });
    });
}

function openAjusteModal(producto) {
    ajusteProducto = producto;
    document.getElementById('ajuste-nombre').textContent       = producto.nombre;
    document.getElementById('ajuste-stock-actual').textContent = producto.stock;
    document.getElementById('ajuste-cantidad').value           = '';
    document.getElementById('ajuste-motivo').value             = '';
    document.getElementById('tipo-entrada').checked            = true;
    document.getElementById('tipo-entrada-label').style.borderColor = 'var(--success)';
    document.getElementById('tipo-salida-label').style.borderColor  = 'var(--border)';
    openModal('modal-ajuste');
    setTimeout(() => document.getElementById('ajuste-cantidad').focus(), 100);
}

function saveAjuste() {
    const cantidad = parseInt(document.getElementById('ajuste-cantidad').value);
    const tipo     = document.querySelector('input[name="tipo-ajuste"]:checked').value;

    if (!cantidad || cantidad <= 0) { showToast('Ingresa una cantidad válida', 'error'); return; }

    const btn = document.getElementById('btn-guardar-ajuste');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aplicando...';

    fetch(BASE + 'modules/inventario/api.php?action=ajustar_stock', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: ajusteProducto.id, tipo, cantidad }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Aplicar Ajuste';
        if (data.error) { showToast(data.message, 'error'); return; }
        showToast(`Stock actualizado → ${data.nuevo_stock} unidades`, 'success');
        closeModal('modal-ajuste');
        loadProductos();
        loadStats();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Aplicar Ajuste';
        showToast('Error al ajustar stock', 'error');
    });
}

// ---- Toggle activo ----
function toggleActivo(id) {
    fetch(BASE + 'modules/inventario/api.php?action=toggle_activo', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showToast(data.message, 'error'); return; }
        const estado = (data.activo === true || data.activo === 't') ? 'activado' : 'desactivado';
        showToast(`Producto ${estado}`, 'success');
        loadProductos();
        loadStats();
    })
    .catch(() => showToast('Error al cambiar estado', 'error'));
}

// ---- Lector de código de barras ----
function setupBarcodeScanner() {
    document.addEventListener('barcodescan', function (e) {
        const code      = e.detail.code.trim();
        const modalOpen = document.getElementById('modal-producto').classList.contains('open');

        if (modalOpen) {
            // Rellenar el campo código en el formulario
            document.getElementById('p-codigo').value = code;
            document.getElementById('p-nombre').focus();
            showToast('<i class="fas fa-barcode"></i> Código escaneado: ' + code, 'success');
        } else {
            // Buscar el producto en la tabla
            document.getElementById('f-q').value = code;
            loadProductos();
            showToast('<i class="fas fa-barcode"></i> Buscando: ' + code, 'info');
        }
    });
}

// ---- Helpers ----
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type = 'info') {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
</script>

<?php include '../../includes/footer.php'; ?>
