<?php
// ============================================================
// ARCHIVO: farmacia/modules/inventario/index.php
// MÓDULO:  Inventario → Gestión de Productos
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin', 'gerente'];
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
                    <th>Unidad</th>
                    <th class="text-right">Mín.</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                <tr><td colspan="12" style="text-align:center;padding:30px;color:var(--text-light)">
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
                    <div class="input-group">
                        <span class="input-group-icon" title="Escanea el código de barras o escríbelo manualmente">
                            <i class="fas fa-barcode" style="color:var(--primary)"></i>
                        </span>
                        <input type="text" id="p-codigo" class="form-control" placeholder="MED001 o escanea el código">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;justify-content:space-between;align-items:center">
                        Categoría
                        <span style="display:flex;gap:10px">
                            <button type="button" onclick="toggleNuevaCategoria()"
                                    style="font-size:.75rem;color:var(--primary);background:none;border:none;cursor:pointer;padding:0;font-weight:600">
                                <i class="fas fa-plus-circle"></i> Nueva
                            </button>
                            <button type="button" onclick="abrirGestionCategorias()"
                                    style="font-size:.75rem;color:var(--text-muted);background:none;border:none;cursor:pointer;padding:0;font-weight:600">
                                <i class="fas fa-cog"></i> Gestionar
                            </button>
                        </span>
                    </label>
                    <select id="p-categoria" class="form-control">
                        <option value="">Sin categoría</option>
                    </select>
                    <!-- Mini-form nueva categoría -->
                    <div id="nueva-cat-form" style="display:none;margin-top:8px">
                        <div style="display:flex;gap:6px">
                            <input type="text" id="nueva-cat-nombre" class="form-control"
                                   placeholder="Nombre de la categoría"
                                   style="flex:1;font-size:.85rem"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault();guardarCategoria();}
                                              if(event.key==='Escape'){toggleNuevaCategoria();}">
                            <button type="button" class="btn btn-primary btn-sm" onclick="guardarCategoria()" title="Guardar">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleNuevaCategoria()" title="Cancelar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="p-nombre" class="form-control" placeholder="Paracetamol 500mg x 10 tab">
                </div>

                <div style="grid-column:1/-1;display:flex;gap:14px">
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Codigo SUNAT</label>
                        <input type="text" id="p-codigo-sunat" class="form-control" placeholder="00000000" maxlength="8">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Codigo de barras</label>
                        <input type="text" id="p-codigo-barras" class="form-control" placeholder="Opcional para lector o etiqueta">
                    </div>
                </div>

                <div style="grid-column:1/-1;display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Laboratorio</label>
                        <input type="text" id="p-laboratorio" class="form-control" placeholder="Bayer">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Presentación</label>
                        <input type="text" id="p-presentacion" class="form-control" placeholder="Tabletas x 10">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Fecha de Vencimiento <span style="font-size:.78rem;color:var(--text-muted)">(opcional)</span></label>
                        <input type="date" id="p-fecha-vencimiento" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Precio de Compra (S/)</label>
                    <input type="number" id="p-precio-compra" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Precio de Venta (S/) <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="p-precio-venta" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>

                <!-- Stock Inicial · Unidad · Stock Mínimo en una fila -->
                <div style="grid-column:1/-1;display:flex;gap:14px">
                    <div class="form-group" id="stock-inicial-group" style="flex:1">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" id="p-stock" class="form-control" placeholder="0" min="0">
                    </div>
                    <div class="form-group" style="flex:1.6">
                        <label class="form-label">Unidad comercial <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="p-unidad" class="form-control"
                               list="unidades-list" placeholder="Ej: unidad, caja x 10, frasco 100ml"
                               autocomplete="off">
                        <datalist id="unidades-list">
                            <option value="unidad">
                            <option value="caja">
                            <option value="caja x 10">
                            <option value="caja x 20">
                            <option value="caja x 30">
                            <option value="caja x 100">
                            <option value="blíster x 10">
                            <option value="blíster x 14">
                            <option value="blíster x 20">
                            <option value="blíster x 30">
                            <option value="paquete">
                            <option value="frasco">
                            <option value="frasco 60ml">
                            <option value="frasco 100ml">
                            <option value="frasco 120ml">
                            <option value="frasco 250ml">
                            <option value="ampolla">
                            <option value="sobre">
                            <option value="sachet">
                            <option value="tubo">
                            <option value="parche">
                            <option value="kilogramo">
                            <option value="gramo">
                            <option value="litro">
                            <option value="mililitro">
                        </datalist>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Stock Mínimo</label>
                        <input type="number" id="p-stock-minimo" class="form-control" placeholder="5" min="0">
                    </div>
                </div>

                <div style="grid-column:1/-1;display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
                    <div class="form-group" style="flex:1;min-width:220px">
                        <label class="form-label">Unidad SUNAT</label>
                        <select id="p-unidad-codigo" class="form-control">
                            <option value="NIU">NIU - Unidad</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1.5;min-width:280px">
                        <label class="form-label">Afectacion IGV</label>
                        <select id="p-afectacion-igv-codigo" class="form-control">
                            <option value="10">10 - Gravado</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:.8;min-width:140px">
                        <label class="form-label">IGV (%)</label>
                        <input type="number" id="p-porcentaje-igv" class="form-control" placeholder="18.00" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="flex:1;min-width:220px">
                        <label class="form-label">Precio</label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;padding:12px 14px;border:1px solid var(--border);border-radius:12px;background:var(--surface-2)">
                            <input type="checkbox" id="p-incluye-igv" style="width:16px;height:16px;cursor:pointer" checked>
                            <span id="p-incluye-igv-texto">El precio de venta guardado ya incluye IGV.</span>
                        </label>
                    </div>
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

<!-- ===================== MODAL: Gestionar Categorías ===================== -->
<div class="modal-overlay" id="modal-categorias">
    <div class="modal" style="max-width:480px">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-tags" style="color:var(--primary);margin-right:8px"></i>Gestionar Categorías
            </h3>
            <button class="modal-close" onclick="closeModal('modal-categorias')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding:0">

            <!-- Formulario nueva categoría dentro del modal -->
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;gap:8px">
                <input type="text" id="gcat-nueva-nombre" class="form-control" placeholder="Nueva categoría..."
                       style="flex:1"
                       onkeydown="if(event.key==='Enter'){event.preventDefault();guardarCategoriaModal();}">
                <button class="btn btn-primary btn-sm" onclick="guardarCategoriaModal()">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </div>

            <!-- Lista de categorías -->
            <div id="gcat-lista" style="max-height:340px;overflow-y:auto">
                <div style="padding:30px;text-align:center;color:var(--text-light)">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
.select2-container { width: 100% !important; }
.select2-container .select2-selection--single {
    height: 48px;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 9px 12px;
    background: #fff;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    color: var(--text-dark);
    padding-left: 0;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 46px;
    right: 10px;
}
.select2-dropdown {
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 8px 10px;
}
</style>

<script>
// ============================================================
// Inventario JavaScript
// ============================================================

const BASE = '../../';
let categorias  = [];
let editingId   = null;
let ajusteProducto = null;
let facturacionCatalogos = { unidades: [], afectaciones_igv: [] };
let empresaFacturaConIgv = true;

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    Promise.all([loadCategorias(), loadFacturacionCatalogos(), loadEmpresaConfig()]).then(() => {
        loadStats();
        loadProductos();
    }).catch(() => {
        showToast('No se pudieron cargar los catalogos de facturacion', 'error');
    });
    setupSearch();
    setupTipoAjuste();
    setupBarcodeScanner();
    setupFacturacionProducto();
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

async function loadFacturacionCatalogos() {
    const data = await fetch(BASE + 'modules/inventario/api.php?action=catalogos_facturacion').then(r => r.json());
    if (data.error) {
        throw new Error(data.message || 'No se pudieron cargar los catalogos de facturacion');
    }

    facturacionCatalogos = data;

    const unidadSel = document.getElementById('p-unidad-codigo');
    unidadSel.innerHTML = data.unidades.map(item =>
        `<option value="${item.codigo}">${item.codigo} - ${item.descripcion}</option>`
    ).join('');
    unidadSel.dataset.ready = '1';
    initUnidadSunatSelect();

    const afectacionSel = document.getElementById('p-afectacion-igv-codigo');
    afectacionSel.innerHTML = data.afectaciones_igv.map(item =>
        `<option value="${item.codigo}" data-tipo="${item.tipo}">${item.codigo} - ${item.descripcion}</option>`
    ).join('');
}

async function loadEmpresaConfig() {
    const data = await fetch(BASE + 'modules/admin/api.php?action=config_get').then(r => r.json());
    empresaFacturaConIgv = data.tax_enabled === true || data.tax_enabled === 't' || data.tax_enabled === 1 || data.tax_enabled === '1';
}

function initUnidadSunatSelect() {
    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
        return;
    }

    const $select = window.jQuery('#p-unidad-codigo');
    if ($select.data('select2')) {
        $select.select2('destroy');
    }

    $select.select2({
        width: '100%',
        dropdownParent: window.jQuery('#modal-producto .modal-body'),
        placeholder: 'Selecciona unidad SUNAT',
        language: {
            noResults: () => 'Sin resultados',
            searching: () => 'Buscando...'
        }
    });
}

function setupFacturacionProducto() {
    const afectacionSel = document.getElementById('p-afectacion-igv-codigo');
    const incluyeCheckbox = document.getElementById('p-incluye-igv');

    afectacionSel.addEventListener('change', () => aplicarReglasIgvPorAfectacion());
    incluyeCheckbox.addEventListener('change', actualizarTextoIncluyeIgv);
}

function getAfectacionSeleccionada() {
    const codigo = document.getElementById('p-afectacion-igv-codigo').value;
    return facturacionCatalogos.afectaciones_igv.find(item => item.codigo === codigo) || null;
}

function aplicarDefaultFacturacionProducto() {
    document.getElementById('p-afectacion-igv-codigo').value = empresaFacturaConIgv ? '10' : '20';
    document.getElementById('p-porcentaje-igv').value = empresaFacturaConIgv ? '18.00' : '0.00';
    document.getElementById('p-incluye-igv').checked = empresaFacturaConIgv;
    aplicarReglasIgvPorAfectacion(true);
}

function aplicarReglasIgvPorAfectacion(restaurarPorDefecto = false) {
    const afectacion = getAfectacionSeleccionada();
    const esGravado = afectacion && afectacion.tipo === 'GRAV';
    const igvInput = document.getElementById('p-porcentaje-igv');
    const incluyeCheckbox = document.getElementById('p-incluye-igv');
    const estabaBloqueado = incluyeCheckbox.disabled;

    if (!esGravado) {
        igvInput.value = '0.00';
        igvInput.disabled = true;
        incluyeCheckbox.checked = false;
        incluyeCheckbox.disabled = true;
    } else {
        igvInput.disabled = false;
        incluyeCheckbox.disabled = false;

        if (restaurarPorDefecto || parseFloat(igvInput.value || '0') <= 0) {
            igvInput.value = '18.00';
        }
        if (restaurarPorDefecto || estabaBloqueado) {
            incluyeCheckbox.checked = empresaFacturaConIgv;
        }
    }

    actualizarTextoIncluyeIgv();
}

function actualizarTextoIncluyeIgv() {
    const afectacion = getAfectacionSeleccionada();
    const esGravado = afectacion && afectacion.tipo === 'GRAV';
    const texto = document.getElementById('p-incluye-igv-texto');

    if (!texto) {
        return;
    }

    if (!esGravado) {
        texto.textContent = 'Este producto no usa IGV por la afectacion elegida.';
        return;
    }

    texto.textContent = document.getElementById('p-incluye-igv').checked
        ? 'El precio de venta guardado ya incluye IGV.'
        : 'El precio de venta se guardara sin IGV incluido.';
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
        '<tr><td colspan="12" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(BASE + 'modules/inventario/api.php?' + params)
        .then(r => r.json())
        .then(data => {
            document.getElementById('result-count').textContent = data.length + ' producto(s)';
            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="12" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-box-open" style="font-size:1.3rem"></i><br><br>No se encontraron productos</td></tr>';
                return;
            }

            const hoy = new Date(); hoy.setHours(0,0,0,0);
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

                let vencCell = '<td style="font-size:.82rem;color:var(--text-light)">—</td>';
                if (p.fecha_vencimiento) {
                    const fv   = new Date(p.fecha_vencimiento + 'T00:00:00');
                    const dias = Math.floor((fv - hoy) / 86400000);
                    if (dias < 0) {
                        vencCell = `<td><span class="badge badge-danger" style="font-size:.72rem" title="${p.fecha_vencimiento}">Vencido</span></td>`;
                    } else if (dias <= 30) {
                        vencCell = `<td><span class="badge badge-warning" style="font-size:.72rem;background:#fef3c7;color:#92400e" title="${p.fecha_vencimiento}">Vence ${dias}d</span></td>`;
                    } else {
                        vencCell = `<td style="font-size:.82rem;color:var(--text-muted)">${p.fecha_vencimiento}</td>`;
                    }
                }

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
                    <td style="font-size:.78rem;color:var(--text-muted)">
                        <div>${p.unidad || 'unidad'}</div>
                        <div style="font-size:.72rem;color:var(--text-light)">${p.unidad_codigo || 'NIU'} · ${p.afectacion_igv_tipo === 'GRAV' ? ('IGV ' + parseFloat(p.porcentaje_igv || 18).toFixed(2) + '%') : 'Sin IGV'}</div>
                    </td>
                    <td class="text-right" style="font-size:.85rem;color:var(--text-muted)">${p.stock_minimo}</td>
                    ${vencCell}
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
    const afectacionCodigoProducto = producto?.afectacion_igv_codigo ?? '10';
    const afectacionProducto = facturacionCatalogos.afectaciones_igv.find(item => item.codigo === afectacionCodigoProducto);
    const productoEsGravado = !afectacionProducto || afectacionProducto.tipo === 'GRAV';
    const incluyeIgvGuardado = producto?.incluye_igv == 't' || producto?.incluye_igv === true;

    title.innerHTML = editingId
        ? '<i class="fas fa-edit" style="color:var(--primary);margin-right:8px"></i>Editar Producto'
        : '<i class="fas fa-plus" style="color:var(--primary);margin-right:8px"></i>Nuevo Producto';

    // Campo stock solo en creación
    stockGroup.style.display = editingId ? 'none' : 'block';

    // Resetear / poblar form
    document.getElementById('p-codigo').value        = producto?.codigo        ?? '';
    document.getElementById('p-nombre').value        = producto?.nombre        ?? '';
    document.getElementById('p-categoria').value     = producto?.categoria_id  ?? '';
    document.getElementById('p-codigo-sunat').value  = producto?.codigo_sunat  ?? '00000000';
    document.getElementById('p-codigo-barras').value = producto?.codigo_barras ?? '';
    document.getElementById('p-laboratorio').value   = producto?.laboratorio   ?? '';
    document.getElementById('p-presentacion').value  = producto?.presentacion  ?? '';
    document.getElementById('p-precio-compra').value = producto ? parseFloat(producto.precio_compra).toFixed(2) : '';
    document.getElementById('p-precio-venta').value  = producto ? parseFloat(producto.precio_venta).toFixed(2)  : '';
    document.getElementById('p-stock').value         = producto?.stock         ?? '';
    document.getElementById('p-stock-minimo').value  = producto?.stock_minimo  ?? '5';
    document.getElementById('p-unidad').value        = producto?.unidad        ?? 'unidad';
    document.getElementById('p-unidad-codigo').value = producto?.unidad_codigo ?? 'NIU';
    document.getElementById('p-afectacion-igv-codigo').value = producto?.afectacion_igv_codigo ?? '10';
    document.getElementById('p-porcentaje-igv').value = producto ? parseFloat(producto.porcentaje_igv || 18).toFixed(2) : '18.00';
    document.getElementById('p-incluye-igv').checked = !producto
        ? empresaFacturaConIgv
        : (productoEsGravado ? (incluyeIgvGuardado || empresaFacturaConIgv) : false);
    document.getElementById('p-receta').checked           = producto?.requiere_receta == 't' || producto?.requiere_receta === true;
    document.getElementById('p-favorito').checked         = producto?.favorito == 't'        || producto?.favorito === true;
    document.getElementById('p-fecha-vencimiento').value  = producto?.fecha_vencimiento ?? '';

    if (!producto) {
        aplicarDefaultFacturacionProducto();
    } else {
        aplicarReglasIgvPorAfectacion();
    }

    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
        window.jQuery('#p-unidad-codigo').trigger('change.select2');
    }

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
        codigo_sunat:    document.getElementById('p-codigo-sunat').value.trim() || '00000000',
        codigo_barras:   document.getElementById('p-codigo-barras').value.trim() || null,
        laboratorio:     document.getElementById('p-laboratorio').value,
        presentacion:    document.getElementById('p-presentacion').value,
        precio_compra:   parseFloat(document.getElementById('p-precio-compra').value) || 0,
        precio_venta,
        stock:           parseInt(document.getElementById('p-stock').value)        || 0,
        stock_minimo:    parseInt(document.getElementById('p-stock-minimo').value) || 5,
        unidad:          document.getElementById('p-unidad').value.trim() || 'unidad',
        unidad_codigo:   document.getElementById('p-unidad-codigo').value || 'NIU',
        afectacion_igv_codigo: document.getElementById('p-afectacion-igv-codigo').value || '10',
        porcentaje_igv:  parseFloat(document.getElementById('p-porcentaje-igv').value) || 18,
        incluye_igv:     document.getElementById('p-incluye-igv').checked,
        requiere_receta:    document.getElementById('p-receta').checked,
        favorito:           document.getElementById('p-favorito').checked,
        fecha_vencimiento:  document.getElementById('p-fecha-vencimiento').value || null,
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

// ---- Gestionar categorías (modal) ----
function abrirGestionCategorias() {
    openModal('modal-categorias');
    document.getElementById('gcat-nueva-nombre').value = '';
    renderListaCategorias();
}

function renderListaCategorias() {
    const lista = document.getElementById('gcat-lista');
    if (!categorias.length) {
        lista.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted)">Sin categorías aún</div>';
        return;
    }
    lista.innerHTML = categorias.map(c => `
        <div id="gcat-row-${c.id}" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-bottom:1px solid var(--border)">
            <i class="fas fa-tag" style="color:var(--primary);font-size:.8rem;flex-shrink:0"></i>
            <span class="gcat-label" style="flex:1;font-size:.9rem">${c.nombre}</span>
            <input class="form-control gcat-input" style="flex:1;display:none;font-size:.85rem" value="${c.nombre}"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();confirmarEdicion(${c.id});}
                              if(event.key==='Escape'){cancelarEdicion(${c.id});}">
            <div class="gcat-actions" style="display:flex;gap:4px;flex-shrink:0">
                <button class="btn btn-ghost btn-sm" title="Editar" onclick="iniciarEdicion(${c.id})">
                    <i class="fas fa-pencil-alt" style="color:var(--primary)"></i>
                </button>
                <button class="btn btn-ghost btn-sm" title="Eliminar" onclick="eliminarCategoria(${c.id},'${c.nombre.replace(/'/g,"\\'")}')">
                    <i class="fas fa-trash" style="color:var(--danger)"></i>
                </button>
            </div>
            <div class="gcat-edit-actions" style="display:none;gap:4px;flex-shrink:0">
                <button class="btn btn-primary btn-sm" onclick="confirmarEdicion(${c.id})">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-ghost btn-sm" onclick="cancelarEdicion(${c.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function iniciarEdicion(id) {
    const row = document.getElementById('gcat-row-' + id);
    row.querySelector('.gcat-label').style.display        = 'none';
    row.querySelector('.gcat-input').style.display        = 'block';
    row.querySelector('.gcat-actions').style.display      = 'none';
    row.querySelector('.gcat-edit-actions').style.display = 'flex';
    row.querySelector('.gcat-input').focus();
    row.querySelector('.gcat-input').select();
}

function cancelarEdicion(id) {
    const row = document.getElementById('gcat-row-' + id);
    const cat = categorias.find(c => c.id == id);
    row.querySelector('.gcat-input').value                = cat.nombre;
    row.querySelector('.gcat-label').style.display        = '';
    row.querySelector('.gcat-input').style.display        = 'none';
    row.querySelector('.gcat-actions').style.display      = 'flex';
    row.querySelector('.gcat-edit-actions').style.display = 'none';
}

function confirmarEdicion(id) {
    const row    = document.getElementById('gcat-row-' + id);
    const nombre = row.querySelector('.gcat-input').value.trim();
    if (!nombre) { showToast('El nombre no puede estar vacío', 'error'); return; }

    fetch(BASE + 'modules/inventario/api.php?action=editar_categoria', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, nombre }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showToast(data.message, 'error'); return; }
        // Actualizar array local
        const cat = categorias.find(c => c.id == id);
        cat.nombre = nombre;
        // Actualizar ambos selectores
        actualizarSelectoresCategorias();
        renderListaCategorias();
        showToast('Categoría actualizada', 'success');
    })
    .catch(() => showToast('Error al editar', 'error'));
}

function eliminarCategoria(id, nombre) {
    if (!confirm(`¿Eliminar la categoría "${nombre}"?`)) return;

    fetch(BASE + 'modules/inventario/api.php?action=eliminar_categoria', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showToast(data.message, 'error'); return; }
        categorias = categorias.filter(c => c.id != id);
        actualizarSelectoresCategorias();
        renderListaCategorias();
        showToast('Categoría eliminada', 'success');
    })
    .catch(() => showToast('Error al eliminar', 'error'));
}

function guardarCategoriaModal() {
    const nombre = document.getElementById('gcat-nueva-nombre').value.trim();
    if (!nombre) { showToast('Escribe un nombre para la categoría', 'error'); return; }

    fetch(BASE + 'modules/inventario/api.php?action=crear_categoria', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showToast(data.message, 'error'); return; }
        categorias.push({ id: data.id, nombre: data.nombre });
        categorias.sort((a, b) => a.nombre.localeCompare(b.nombre));
        actualizarSelectoresCategorias();
        renderListaCategorias();
        document.getElementById('gcat-nueva-nombre').value = '';
        document.getElementById('gcat-nueva-nombre').focus();
        showToast('Categoría "' + data.nombre + '" creada', 'success');
    })
    .catch(() => showToast('Error al crear categoría', 'error'));
}

function actualizarSelectoresCategorias() {
    // Selector en el formulario de producto
    const selProd = document.getElementById('p-categoria');
    const valActual = selProd.value;
    selProd.innerHTML = '<option value="">Sin categoría</option>';
    categorias.forEach(c => selProd.add(new Option(c.nombre, c.id)));
    selProd.value = valActual;

    // Selector de filtro en la tabla
    const selFiltro = document.getElementById('f-categoria');
    const valFiltro = selFiltro.value;
    selFiltro.innerHTML = '<option value="0">Todas</option>';
    categorias.forEach(c => selFiltro.add(new Option(c.nombre, c.id)));
    selFiltro.value = valFiltro;
}

// ---- Nueva categoría inline ----
function toggleNuevaCategoria() {
    const form = document.getElementById('nueva-cat-form');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'flex';
    form.style.flexDirection = 'column';
    if (!visible) {
        document.getElementById('nueva-cat-nombre').value = '';
        document.getElementById('nueva-cat-nombre').focus();
    }
}

function guardarCategoria() {
    const nombre = document.getElementById('nueva-cat-nombre').value.trim();
    if (!nombre) { showToast('Escribe un nombre para la categoría', 'error'); return; }

    fetch(BASE + 'modules/inventario/api.php?action=crear_categoria', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ nombre }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showToast(data.message, 'error'); return; }

        categorias.push({ id: data.id, nombre: data.nombre });
        categorias.sort((a, b) => a.nombre.localeCompare(b.nombre));
        actualizarSelectoresCategorias();
        document.getElementById('p-categoria').value = data.id;
        toggleNuevaCategoria();
        showToast('Categoría "' + data.nombre + '" creada', 'success');
    })
    .catch(() => showToast('Error al crear categoría', 'error'));
}

// ---- Lector de código de barras ----
function setupBarcodeScanner() {
    document.addEventListener('barcodescan', function (e) {
        const code       = e.detail.code.trim();
        const modalProd  = document.getElementById('modal-producto').classList.contains('open');
        const modalAjuste = document.getElementById('modal-ajuste').classList.contains('open');

        if (modalProd) {
            const campo = document.getElementById('p-codigo');
            campo.value = code;
            campo.style.transition = 'background .2s';
            campo.style.background = 'rgba(var(--primary-rgb,79,70,229),.08)';
            setTimeout(() => { campo.style.background = ''; }, 800);
            document.getElementById('p-nombre').focus();
            showToast('<i class="fas fa-barcode"></i> Código escaneado: ' + code, 'success');
        } else if (!modalAjuste) {
            // Buscar el producto en la tabla (solo si no hay otro modal abierto)
            document.getElementById('f-q').value = code;
            loadProductos();
            showToast('<i class="fas fa-barcode"></i> Buscando: ' + code, 'info');
        }
    });

    // También permitir escribir manualmente en el campo código del modal
    // y que el campo luzca activo cuando el modal está abierto
    document.getElementById('modal-producto').addEventListener('click', function () {
        // Re-enfocar p-codigo si se hace clic dentro del modal sin enfocar otro input
        if (document.activeElement === document.body || document.activeElement === this) {
            document.getElementById('p-codigo').focus();
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
