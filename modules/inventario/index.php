<?php
// ============================================================
// ARCHIVO: farmacia/modules/inventario/index.php
// MÓDULO:  Inventario → Gestión de Productos
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin', 'gerente', 'cajero'];
$current_module = 'inventario';
$current_page   = 'inventario';
$page_title     = 'Inventario — FarmaSystem';
$breadcrumb     = '<strong>Inventario</strong> / Gestión de Productos';

include '../../includes/header.php';
?>

<style>
.inv-tabs {
    display: flex;
    gap: 6px;
    margin-bottom: 20px;
    background: var(--surface-2);
    border-radius: var(--radius);
    padding: 5px;
    width: fit-content;
}
.inv-tab {
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
.inv-tab:hover { background: var(--surface); color: var(--text); }
.inv-tab.active { background: var(--primary); color: #fff; }
.inv-tab.active:hover { background: var(--primary-dark, var(--primary)); }
</style>

<div class="page-header">
    <div>
        <div class="page-title" id="inv-page-title">
            <i class="fas fa-boxes" style="color:var(--primary);margin-right:8px"></i>Inventario
        </div>
        <div class="page-subtitle" id="inv-page-subtitle">Gestiona productos, precios y niveles de stock</div>
    </div>
    <div class="page-actions" id="inv-page-actions">
        <button class="btn btn-primary" onclick="openProductoModal()">
            <i class="fas fa-plus"></i> Nuevo Producto
        </button>
    </div>
</div>

<!-- Botón Regresar (solo visible al crear/editar producto) -->
<button id="btn-regresar" onclick="cerrarTabProducto()" style="display:none;align-items:center;gap:7px;padding:6px 14px;margin-bottom:12px;border:1.5px solid var(--border);border-radius:var(--radius);background:var(--surface-2);color:var(--text-muted);font-size:.85rem;font-weight:500;cursor:pointer;transition:background .15s,color .15s">
    <i class="fas fa-arrow-left"></i> Regresar
</button>

<!-- Tabs -->
<div class="inv-tabs" id="inv-tabs-bar">
    <button class="inv-tab active" id="tab-btn-inventario" onclick="switchTab('inventario')">
        <i class="fas fa-boxes"></i> Inventario
    </button>
    <button class="inv-tab" id="tab-btn-categorias" onclick="switchTab('categorias')">
        <i class="fas fa-tags"></i> Categorías
    </button>
</div>

<!-- ===================== TAB: INVENTARIO ===================== -->
<div id="tab-inventario">

<!-- Stat cards -->
<div class="row g-3 mb-4" id="stats-container">
    <?php foreach (['blue','green','yellow','red'] as $c): ?>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon <?= $c ?>"><i class="fas fa-spinner fa-spin"></i></div>
            <div><div class="stat-value">—</div><div class="stat-label">...</div></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label">Buscar</label>
            <div class="input-group">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-q" class="form-control" placeholder="Nombre, código o laboratorio...">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Categoría</label>
            <select class="form-control" id="f-categoria">
                <option value="0">Todas</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Estado de stock</label>
            <select class="form-control" id="f-stock">
                <option value="">Activos</option>
                <option value="bajo">⚠ Stock bajo</option>
                <option value="agotado">🔴 Agotado</option>
                <option value="ok">✅ Stock OK</option>
                <option value="inactivo">Inactivos</option>
            </select>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-outline w-100" onclick="resetFiltros()">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Productos</div>
        <span style="font-size:.82rem;color:var(--text-muted)" id="result-count">Cargando...</span>
    </div>
    <div class="table-wrap table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>SKU</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th class="text-right">P. Venta</th>
                    <th style="text-align:center">Stock</th>
                    <th>Unidad</th>
                    <th class="text-right">Mín.</th>
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

</div><!-- /tab-inventario -->

<!-- ===================== TAB: CATEGORÍAS ===================== -->
<div id="tab-categorias" style="display:none">

    <div class="card mb-4">
        <label class="form-label">Buscar categoría</label>
        <div class="input-group">
            <span class="input-group-icon"><i class="fas fa-search"></i></span>
            <input type="text" id="cat-q" class="form-control" placeholder="Nombre...">
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Categorías de productos</div>
            <span style="font-size:.82rem;color:var(--text-muted)" id="cat-count">—</span>
        </div>
        <div class="table-wrap table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th style="text-align:center;width:120px">Productos</th>
                        <th style="width:140px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cat-tabla-body">
                    <tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-light)">
                        <i class="fas fa-spinner fa-spin"></i>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /tab-categorias -->

<!-- ===================== TAB: NUEVO / EDITAR PRODUCTO ===================== -->
<div id="tab-producto" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title" id="tab-producto-card-title">
                <i class="fas fa-plus" style="color:var(--primary);margin-right:8px"></i>Información de Producto
            </div>
        </div>
        <div style="display:flex;align-items:stretch">

            <!-- ── Columna izquierda: todos los campos ── -->
            <div style="flex:1;padding:20px;display:flex;flex-direction:column;gap:14px">

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Código <span style="color:var(--danger)">*</span></label>
                        <div class="input-group">
                            <span class="input-group-icon" title="Escanea el código de barras o escríbelo manualmente">
                                <i class="fas fa-barcode" style="color:var(--primary)"></i>
                            </span>
                            <input type="text" id="p-codigo" class="form-control" placeholder="MED001 o escanea el código">
                        </div>
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">SKU <span style="font-size:.78rem;color:var(--text-muted)">(código interno)</span></label>
                        <input type="text" id="p-sku" class="form-control" placeholder="Ej: FAR-001">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="p-nombre" class="form-control" placeholder="Paracetamol 500mg x 10 tab">
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" style="display:flex;justify-content:space-between;align-items:center">
                        Categoría
                        <span style="display:flex;gap:10px">
                            <button type="button" onclick="toggleNuevaCategoria()"
                                    style="font-size:.75rem;color:var(--primary);background:none;border:none;cursor:pointer;padding:0;font-weight:600">
                                <i class="fas fa-plus-circle"></i> Nueva
                            </button>
                            <button type="button" onclick="cerrarTabProducto();switchTab('categorias')"
                                    style="font-size:.75rem;color:var(--text-muted);background:none;border:none;cursor:pointer;padding:0;font-weight:600">
                                <i class="fas fa-cog"></i> Gestionar
                            </button>
                        </span>
                    </label>
                    <select id="p-categoria" class="form-control">
                        <option value="">Sin categoría</option>
                    </select>
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

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Presentación</label>
                        <input type="text" id="p-presentacion" class="form-control" placeholder="Tabletas x 10">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Fecha de Vencimiento <span style="font-size:.78rem;color:var(--text-muted)">(opcional)</span></label>
                        <input type="date" id="p-fecha-vencimiento" class="form-control">
                    </div>
                </div>

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Precio de Compra (S/)</label>
                        <input type="number" id="p-precio-compra" class="form-control" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Precio de Venta (S/) <span style="color:var(--danger)">*</span></label>
                        <input type="number" id="p-precio-venta" class="form-control" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>

                <div style="display:flex;gap:14px">
                    <div class="form-group" id="stock-inicial-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Stock</label>
                        <input type="number" id="p-stock" class="form-control" placeholder="0" min="0">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Alerta de Stock</label>
                        <input type="number" id="p-stock-minimo" class="form-control" placeholder="5" min="0">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label" style="display:flex;justify-content:space-between;align-items:center">
                            <span>Unidad de medida <span style="color:var(--danger)">*</span></span>
                            <button type="button" onclick="toggleNuevaUnidad()"
                                    style="font-size:.75rem;color:var(--primary);background:none;border:none;cursor:pointer;padding:0;font-weight:600">
                                <i class="fas fa-plus-circle"></i> Nueva
                            </button>
                        </label>
                        <select id="p-unidad" class="form-control">
                            <option value="unidad">unidad</option>
                            <option value="caja">caja</option>
                            <option value="caja x 10">caja x 10</option>
                            <option value="caja x 20">caja x 20</option>
                            <option value="caja x 30">caja x 30</option>
                            <option value="caja x 100">caja x 100</option>
                            <option value="blíster x 10">blíster x 10</option>
                            <option value="blíster x 14">blíster x 14</option>
                            <option value="blíster x 20">blíster x 20</option>
                            <option value="blíster x 30">blíster x 30</option>
                            <option value="paquete">paquete</option>
                            <option value="frasco">frasco</option>
                            <option value="frasco 60ml">frasco 60ml</option>
                            <option value="frasco 100ml">frasco 100ml</option>
                            <option value="frasco 120ml">frasco 120ml</option>
                            <option value="frasco 250ml">frasco 250ml</option>
                            <option value="ampolla">ampolla</option>
                            <option value="sobre">sobre</option>
                            <option value="sachet">sachet</option>
                            <option value="tubo">tubo</option>
                            <option value="parche">parche</option>
                            <option value="kilogramo">kilogramo</option>
                            <option value="gramo">gramo</option>
                            <option value="litro">litro</option>
                            <option value="mililitro">mililitro</option>
                        </select>
                        <div id="nueva-unidad-form" style="display:none;margin-top:8px">
                            <div style="display:flex;gap:6px">
                                <input type="text" id="nueva-unidad-nombre" class="form-control"
                                       placeholder="Ej: blíster x 28"
                                       style="flex:1;font-size:.85rem"
                                       onkeydown="if(event.key==='Enter'){event.preventDefault();guardarNuevaUnidad();}
                                                  if(event.key==='Escape'){toggleNuevaUnidad();}">
                                <button type="button" class="btn btn-primary btn-sm" onclick="guardarNuevaUnidad()" title="Agregar">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleNuevaUnidad()" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap">
                    <div class="form-group" style="flex:1.5;min-width:200px;margin-bottom:0">
                        <label class="form-label">Afectacion IGV</label>
                        <select id="p-afectacion-igv-codigo" class="form-control">
                            <option value="10">10 - Gravado</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:.8;min-width:120px;margin-bottom:0">
                        <label class="form-label">IGV (%)</label>
                        <input type="number" id="p-porcentaje-igv" class="form-control" placeholder="18.00" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="flex:1;min-width:200px;margin-bottom:0">
                        <label class="form-label">Precio</label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;padding:12px 14px;border:1px solid var(--border);border-radius:12px;background:var(--surface-2)">
                            <input type="checkbox" id="p-incluye-igv" style="width:16px;height:16px;cursor:pointer" checked>
                            <span id="p-incluye-igv-texto">El precio de venta guardado ya incluye IGV.</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:24px;align-items:center;padding:6px 0">
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

            <!-- ── Columna derecha: imagen ── -->
            <div style="flex:1;padding:20px;border-left:1px solid var(--border);display:flex;flex-direction:column;align-items:center;justify-content:center">
                <label class="form-label" style="align-self:center;margin-bottom:10px">Imagen del producto</label>
                <div id="p-imagen-zona"
                     onclick="document.getElementById('p-imagen-input').click()"
                     ondragover="event.preventDefault();this.style.borderColor='var(--primary)'"
                     ondragleave="this.style.borderColor='var(--border)'"
                     ondrop="handleImagenDrop(event)"
                     style="width:180px;height:180px;border:2px dashed var(--border);border-radius:var(--radius);display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;background:var(--surface-2);transition:border-color .2s,background .2s;position:relative;overflow:hidden">
                    <div id="p-imagen-placeholder" style="text-align:center;color:var(--text-muted);padding:12px">
                        <i class="fas fa-image" style="font-size:2.5rem;margin-bottom:8px;display:block"></i>
                        <span style="font-size:.78rem">Clic o arrastra</span>
                    </div>
                    <img id="p-imagen-preview" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover">
                    <button type="button" id="p-imagen-quitar" onclick="event.stopPropagation();quitarImagen()"
                            style="display:none;position:absolute;top:6px;right:6px;background:rgba(0,0,0,.5);color:#fff;border:none;border-radius:50%;width:26px;height:26px;cursor:pointer;font-size:.8rem;align-items:center;justify-content:center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="file" id="p-imagen-input" accept="image/*" style="display:none" onchange="handleImagenSelect(this)">
            </div>

        </div>
    </div><!-- /card Información de Producto -->

    <!-- ── Card: Información de Medicamentos ── -->
    <div class="card" style="margin-top:20px">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-pills" style="color:var(--primary);margin-right:8px"></i>Información de Medicamentos
            </div>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:14px">

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Código de Producto DIGEMID</label>
                        <input type="text" id="p-digemid-codigo" class="form-control" placeholder="Ej: 123456">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Precio de empaque DIGEMID (S/)</label>
                        <input type="number" id="p-digemid-precio" class="form-control" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Registro Sanitario</label>
                        <input type="text" id="p-registro-sanitario" class="form-control" placeholder="Ej: E.F.A. N° 12345">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Vencimiento del Registro Sanitario</label>
                        <input type="date" id="p-vencimiento-registro" class="form-control">
                    </div>
                </div>

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Condición de venta</label>
                        <select id="p-condicion-venta" class="form-control">
                            <option value="">— Seleccionar —</option>
                            <option value="sin_receta">Sin receta médica</option>
                            <option value="con_receta">Con receta médica</option>
                            <option value="con_receta_retenida">Con receta médica retenida</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Principio activo</label>
                        <input type="text" id="p-principio-activo" class="form-control" placeholder="Ej: Paracetamol">
                    </div>
                </div>

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Laboratorio</label>
                        <input type="text" id="p-laboratorio" class="form-control" placeholder="Ej: Bayer">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Concentración</label>
                        <input type="text" id="p-concentracion" class="form-control" placeholder="Ej: 500mg">
                    </div>
                </div>

                <div style="display:flex;gap:14px">
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Forma farmacéutica</label>
                        <input type="text" id="p-forma-farmaceutica" class="form-control" placeholder="Ej: Tableta, Jarabe, Ampolla">
                    </div>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label">Presentación farmacéutica</label>
                        <input type="text" id="p-presentacion-farmaceutica" class="form-control" placeholder="Ej: Caja x 10 tabletas">
                    </div>
                </div>

            </div>
        </div>
    </div><!-- /card Información de Medicamentos -->

    <!-- Botones -->
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;padding-bottom:8px">
        <button class="btn btn-outline" onclick="cerrarTabProducto()">
            <i class="fas fa-arrow-left"></i> Cancelar
        </button>
        <button class="btn btn-primary" id="btn-guardar-producto" onclick="saveProducto()">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</div><!-- /tab-producto -->

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


<!-- ===================== MODAL: Confirmar Eliminación ===================== -->
<div class="modal-overlay" id="modal-confirmar-eliminar">
    <div class="modal" style="max-width:400px">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-trash" style="color:var(--danger);margin-right:8px"></i>Eliminar categoría
            </h3>
            <button class="modal-close" onclick="closeModal('modal-confirmar-eliminar')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:.93rem;color:var(--text)">
                ¿Estás seguro de que deseas eliminar la categoría
                <strong id="confirmar-eliminar-nombre" style="color:var(--danger)"></strong>?
            </p>
            <p style="font-size:.82rem;color:var(--text-muted);margin-top:8px">
                Esta acción no se puede deshacer. Solo se puede eliminar si ningún producto activo la usa.
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-confirmar-eliminar')">Cancelar</button>
            <button class="btn btn-danger" id="btn-confirmar-eliminar" onclick="confirmarEliminarCategoria()">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<!-- ===================== MODAL: Nueva Categoría ===================== -->
<div class="modal-overlay" id="modal-nueva-categoria">
    <div class="modal" style="max-width:420px">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-tag" style="color:var(--primary);margin-right:8px"></i>Nueva Categoría
            </h3>
            <button class="modal-close" onclick="closeModal('modal-nueva-categoria')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                <input type="text" id="nueva-cat-modal-nombre" class="form-control"
                       placeholder="Ej: Antibióticos"
                       onkeydown="if(event.key==='Enter'){event.preventDefault();guardarNuevaCategoriaModal();}
                                  if(event.key==='Escape'){closeModal('modal-nueva-categoria');}">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-nueva-categoria')">Cancelar</button>
            <button class="btn btn-primary" id="btn-guardar-nueva-cat" onclick="guardarNuevaCategoriaModal()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<div class="app-toast-container" id="toast-container"></div>

<link href="<?= $base_path ?>assets/vendor/select2/select2.min.css" rel="stylesheet">
<script src="<?= $base_path ?>assets/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="<?= $base_path ?>assets/vendor/select2/select2.min.js"></script>
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
let tabAnterior = 'inventario';

// ---- Tabs ----
function switchTab(tab) {
    ['inventario', 'categorias', 'producto'].forEach(t => {
        document.getElementById('tab-' + t).style.display = t === tab ? '' : 'none';
        const btn = document.getElementById('tab-btn-' + t);
        if (btn) btn.classList.toggle('active', t === tab);
    });

    const esProducto = tab === 'producto';
    document.getElementById('inv-tabs-bar').style.display = esProducto ? 'none' : '';
    document.getElementById('btn-regresar').style.display = esProducto ? 'flex' : 'none';

    if (tab === 'inventario') {
        document.getElementById('inv-page-title').innerHTML     = '<i class="fas fa-boxes" style="color:var(--primary);margin-right:8px"></i>Inventario';
        document.getElementById('inv-page-subtitle').textContent = 'Gestiona productos, precios y niveles de stock';
        document.getElementById('inv-page-actions').innerHTML   = '<button class="btn btn-primary" onclick="openProductoModal()"><i class="fas fa-plus"></i> Nuevo Producto</button>';
    } else if (tab === 'categorias') {
        document.getElementById('inv-page-title').innerHTML     = '<i class="fas fa-tags" style="color:var(--primary);margin-right:8px"></i>Categorías';
        document.getElementById('inv-page-subtitle').textContent = 'Gestiona las categorías de productos';
        document.getElementById('inv-page-actions').innerHTML   = '<button class="btn btn-primary" onclick="abrirNuevaCategoria()"><i class="fas fa-plus"></i> Nueva Categoría</button>';
        renderCatTabla();
    } else if (tab === 'producto') {
        document.getElementById('inv-page-actions').innerHTML = '';
    }
}

function cerrarTabProducto() {
    switchTab(tabAnterior);
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    Promise.all([loadCategorias(), loadFacturacionCatalogos(), loadEmpresaConfig()]).then(() => {
        loadStats();
        loadProductos();
    }).catch(() => {
        showToast('No se pudieron cargar los catalogos de facturacion', 'error');
    });
    setupSearch();
    setupCatSearch();
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
                <div class="col-6 col-lg-3">
                    <div class="stat-card" style="cursor:pointer" onclick="filtrarPor('${c.label}')">
                        <div class="stat-icon ${c.color}"><i class="fas fa-${c.icon}"></i></div>
                        <div><div class="stat-value">${c.val}</div><div class="stat-label">${c.label}</div></div>
                    </div>
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

    initUnidadComercialSelect();

    const afectacionSel = document.getElementById('p-afectacion-igv-codigo');
    afectacionSel.innerHTML = data.afectaciones_igv.map(item =>
        `<option value="${item.codigo}" data-tipo="${item.tipo}">${item.codigo} - ${item.descripcion}</option>`
    ).join('');
}

async function loadEmpresaConfig() {
    const data = await fetch(BASE + 'modules/admin/api.php?action=config_get').then(r => r.json());
    empresaFacturaConIgv = data.tax_enabled === true || data.tax_enabled === 't' || data.tax_enabled === 1 || data.tax_enabled === '1';
}

function initUnidadComercialSelect() {
    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
        return;
    }

    const $select = window.jQuery('#p-unidad');
    if ($select.data('select2')) {
        $select.select2('destroy');
    }

    $select.select2({
        width: '100%',
        tags: true,
        dropdownParent: window.jQuery('#tab-producto'),
        placeholder: 'Selecciona o escribe una unidad',
        language: {
            noResults: () => 'Escribe para crear una unidad personalizada',
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
        '<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(BASE + 'modules/inventario/api.php?' + params)
        .then(r => r.json())
        .then(data => {
            if (!Array.isArray(data)) throw new Error('unexpected');
            document.getElementById('result-count').textContent = data.length + ' producto(s)';
            if (!data.length) {
                const hayFiltros = params.get('q') ||
                    (params.get('categoria_id') && params.get('categoria_id') !== '0') ||
                    (params.get('stock_status') && params.get('stock_status') !== '');
                document.getElementById('tabla-body').innerHTML = hayFiltros
                    ? `<tr><td colspan="8"><div class="empty-state"><i class="fas fa-search"></i>No se encontraron productos con esos filtros</div></td></tr>`
                    : `<tr><td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <div>No hay productos registrados</div>
                            <a onclick="openProductoModal()" style="font-size:inherit;color:var(--primary);text-decoration:underline;cursor:pointer">Agregar productos</a>
                        </div>
                       </td></tr>`;
                return;
            }

            document.getElementById('tabla-body').innerHTML = data.map(p => {
                const agotado  = parseInt(p.stock) === 0;
                const stockBajo = parseInt(p.stock) > 0 && parseInt(p.stock) <= parseInt(p.stock_minimo);
                const stockCls  = agotado ? 'color:var(--danger);font-weight:700' :
                                  (stockBajo ? 'color:var(--warning,#f59e0b);font-weight:700' : 'color:var(--success);font-weight:600');
                const stockBadge = agotado  ? '<span class="badge badge-danger" style="font-size:.72rem">Agotado</span>' :
                                   (stockBajo ? '<span class="badge badge-warning" style="font-size:.72rem;background:#fef3c7;color:#92400e">Bajo</span>' : '');
                const favIcon = (p.favorito == 't' || p.favorito === true)
                    ? '<i class="fas fa-star" style="color:#f59e0b;margin-left:4px" title="Favorito"></i>' : '';
                const recetaIcon = (p.requiere_receta == 't' || p.requiere_receta === true)
                    ? '<i class="fas fa-prescription" style="color:var(--primary);margin-left:4px" title="Requiere receta"></i>' : '';

                return `<tr style="font-size:14px">
                    <td style="font-family:monospace;color:var(--text-muted)">${p.codigo}</td>
                    <td style="font-family:monospace;color:var(--text-muted)">${p.codigo_interno || '<span style="color:var(--text-light)">—</span>'}</td>
                    <td style="font-weight:500">
                        <span onclick='openProductoModal(${JSON.stringify(p)})'
                              style="color:var(--primary);cursor:pointer">${p.nombre}</span>${favIcon}${recetaIcon}
                    </td>
                    <td>${p.categoria || '<span style="color:var(--text-light)">—</span>'}</td>
                    <td class="text-right" style="font-weight:600">S/ ${parseFloat(p.precio_venta).toFixed(2)}</td>
                    <td style="text-align:center">
                        <span style="${stockCls}">${p.stock}</span>
                        ${stockBadge}
                    </td>
                    <td style="color:var(--text-muted)">${p.unidad || 'unidad'}</td>
                    <td class="text-right" style="color:var(--text-muted)">${p.stock_minimo}</td>
                </tr>`;
            }).join('');
        })
        .catch(() => {
            document.getElementById('tabla-body').innerHTML =
                '<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)"><i class="fas fa-exclamation-circle" style="font-size:1.3rem;color:var(--danger)"></i><br><br>Error al cargar los productos. Intenta recargar la página.</td></tr>';
        });
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

// ---- Tab Producto (crear / editar) ----
function openProductoModal(producto = null) {
    editingId = producto ? producto.id : null;

    // Recordar desde qué tab se abrió para poder volver
    const tabActual = document.getElementById('tab-categorias').style.display !== 'none'
        ? 'categorias' : 'inventario';
    tabAnterior = tabActual;

    const esNuevo = !editingId;
    const titulo  = 'Información de Producto';
    const icono   = esNuevo ? 'fa-plus' : 'fa-edit';

    document.getElementById('tab-producto-card-title').innerHTML =
        `<i class="fas ${icono}" style="color:var(--primary);margin-right:8px"></i>${titulo}`;
    document.getElementById('inv-page-title').innerHTML =
        `<i class="fas ${icono}" style="color:var(--primary);margin-right:8px"></i>${titulo}`;
    document.getElementById('inv-page-subtitle').textContent =
        esNuevo ? 'Completa los datos para registrar un nuevo producto' : 'Actualiza los datos del producto';

    const stockGroup = document.getElementById('stock-inicial-group');
    const afectacionCodigoProducto = producto?.afectacion_igv_codigo ?? '10';
    const afectacionProducto = facturacionCatalogos.afectaciones_igv.find(item => item.codigo === afectacionCodigoProducto);
    const productoEsGravado = !afectacionProducto || afectacionProducto.tipo === 'GRAV';
    const incluyeIgvGuardado = producto?.incluye_igv == 't' || producto?.incluye_igv === true;

    // Campo stock solo en creación
    stockGroup.style.display = editingId ? 'none' : 'block';

    // Poblar campos
    document.getElementById('p-codigo').value        = producto?.codigo        ?? '';
    document.getElementById('p-sku').value           = producto?.codigo_interno ?? '';
    document.getElementById('p-nombre').value        = producto?.nombre        ?? '';
    document.getElementById('p-categoria').value     = producto?.categoria_id  ?? '';


    document.getElementById('p-presentacion').value  = producto?.presentacion  ?? '';
    quitarImagen();
    if (producto?.imagen_url) {
        document.getElementById('p-imagen-preview').src = producto.imagen_url;
        document.getElementById('p-imagen-preview').style.display = 'block';
        document.getElementById('p-imagen-placeholder').style.display = 'none';
        document.getElementById('p-imagen-quitar').style.display = 'flex';
    }
    document.getElementById('p-precio-compra').value = producto ? parseFloat(producto.precio_compra).toFixed(2) : '';
    document.getElementById('p-precio-venta').value  = producto ? parseFloat(producto.precio_venta).toFixed(2)  : '';
    document.getElementById('p-stock').value         = producto?.stock         ?? '';
    document.getElementById('p-stock-minimo').value  = producto?.stock_minimo  ?? '5';
    const unidadVal = producto?.unidad ?? 'unidad';
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
        const $unidadSel = window.jQuery('#p-unidad');
        if ($unidadSel.find(`option[value="${unidadVal}"]`).length === 0) {
            $unidadSel.append(new Option(unidadVal, unidadVal, true, true));
        }
        $unidadSel.val(unidadVal).trigger('change.select2');
    } else {
        document.getElementById('p-unidad').value = unidadVal;
    }
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
        window.jQuery('#p-unidad').trigger('change.select2');
    }

    switchTab('producto');
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
        sku:             document.getElementById('p-sku').value.trim() || null,
        nombre,
        categoria_id:    document.getElementById('p-categoria').value     || null,


        presentacion:    document.getElementById('p-presentacion').value,
        precio_compra:   parseFloat(document.getElementById('p-precio-compra').value) || 0,
        precio_venta,
        stock:           parseInt(document.getElementById('p-stock').value)        || 0,
        stock_minimo:    parseInt(document.getElementById('p-stock-minimo').value) || 5,
        unidad:          document.getElementById('p-unidad').value.trim() || 'unidad',
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
        switchTab('inventario');
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

// ---- Categorías (tab) ----
function abrirGestionCategorias() {
    switchTab('categorias');
}

function setupCatSearch() {
    let timer;
    document.getElementById('cat-q').addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(renderCatTabla, 150);
    });
}

function renderCatTabla() {
    const q     = document.getElementById('cat-q').value.toLowerCase();
    const lista = categorias.filter(c => !q || c.nombre.toLowerCase().includes(q));
    const tbody = document.getElementById('cat-tabla-body');
    document.getElementById('cat-count').textContent = lista.length + ' categoría(s)';

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="3"><div class="empty-state"><i class="fas fa-tags"></i>${q ? 'Sin resultados para "' + q + '"' : 'Sin categorías aún'}</div></td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map(c => `
        <tr id="gcat-row-${c.id}">
            <td>
                <span class="gcat-label" style="font-size:14px">${c.nombre}</span>
                <input class="form-control gcat-input" style="display:none;max-width:320px;font-size:14px" value="${c.nombre}"
                       onkeydown="if(event.key==='Enter'){event.preventDefault();confirmarEdicion(${c.id});}
                                  if(event.key==='Escape'){cancelarEdicion(${c.id});}">
            </td>
            <td style="text-align:center">
                <span style="font-weight:600;color:${parseInt(c.total_productos) > 0 ? 'var(--primary)' : 'var(--text-muted)'}">
                    ${c.total_productos}
                </span>
            </td>
            <td>
                <div class="gcat-actions" style="display:flex;gap:4px">
                    <button class="btn btn-ghost btn-sm" title="Editar" onclick="iniciarEdicion(${c.id})">
                        <i class="fas fa-pencil-alt" style="color:var(--primary)"></i>
                    </button>
                    <button class="btn btn-ghost btn-sm" title="Eliminar" onclick="eliminarCategoria(${c.id},'${c.nombre.replace(/'/g,"\\'")}')">
                        <i class="fas fa-trash" style="color:var(--danger)"></i>
                    </button>
                </div>
                <div class="gcat-edit-actions" style="display:none;gap:4px">
                    <button class="btn btn-primary btn-sm" onclick="confirmarEdicion(${c.id})">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-ghost btn-sm" onclick="cancelarEdicion(${c.id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirNuevaCategoria() {
    document.getElementById('nueva-cat-modal-nombre').value = '';
    openModal('modal-nueva-categoria');
    setTimeout(() => document.getElementById('nueva-cat-modal-nombre').focus(), 80);
}

function guardarNuevaCategoriaModal() {
    const nombre = document.getElementById('nueva-cat-modal-nombre').value.trim();
    if (!nombre) { showToast('Escribe un nombre para la categoría', 'error'); return; }

    const btn = document.getElementById('btn-guardar-nueva-cat');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    fetch(BASE + 'modules/inventario/api.php?action=crear_categoria', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        if (data.error) { showToast(data.message, 'error'); return; }
        categorias.push({ id: data.id, nombre: data.nombre });
        categorias.sort((a, b) => a.nombre.localeCompare(b.nombre));
        actualizarSelectoresCategorias();
        renderCatTabla();
        closeModal('modal-nueva-categoria');
        showToast('Categoría "' + data.nombre + '" creada', 'success');
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        showToast('Error al crear categoría', 'error');
    });
}

function iniciarEdicion(id) {
    const row = document.getElementById('gcat-row-' + id);
    row.querySelector('.gcat-label').style.display        = 'none';
    row.querySelector('.gcat-input').style.display        = 'inline-block';
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
        const cat = categorias.find(c => c.id == id);
        cat.nombre = nombre;
        actualizarSelectoresCategorias();
        renderCatTabla();
        showToast('Categoría actualizada', 'success');
    })
    .catch(() => showToast('Error al editar', 'error'));
}

let _eliminarCatId = null;

function eliminarCategoria(id, nombre) {
    _eliminarCatId = id;
    document.getElementById('confirmar-eliminar-nombre').textContent = nombre;
    openModal('modal-confirmar-eliminar');
}

function confirmarEliminarCategoria() {
    if (!_eliminarCatId) return;
    const btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';

    fetch(BASE + 'modules/inventario/api.php?action=eliminar_categoria', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: _eliminarCatId }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
        if (data.error) { showToast(data.message, 'error'); return; }
        categorias = categorias.filter(c => c.id != _eliminarCatId);
        _eliminarCatId = null;
        actualizarSelectoresCategorias();
        renderCatTabla();
        closeModal('modal-confirmar-eliminar');
        showToast('Categoría eliminada', 'success');
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
        showToast('Error al eliminar', 'error');
    });
}

function actualizarSelectoresCategorias() {
    const selProd = document.getElementById('p-categoria');
    const valActual = selProd.value;
    selProd.innerHTML = '<option value="">Sin categoría</option>';
    categorias.forEach(c => selProd.add(new Option(c.nombre, c.id)));
    selProd.value = valActual;

    const selFiltro = document.getElementById('f-categoria');
    const valFiltro = selFiltro.value;
    selFiltro.innerHTML = '<option value="0">Todas</option>';
    categorias.forEach(c => selFiltro.add(new Option(c.nombre, c.id)));
    selFiltro.value = valFiltro;
}

// ---- Nueva unidad de medida inline ----
function toggleNuevaUnidad() {
    const form = document.getElementById('nueva-unidad-form');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'flex';
    form.style.flexDirection = 'column';
    if (!visible) {
        document.getElementById('nueva-unidad-nombre').value = '';
        document.getElementById('nueva-unidad-nombre').focus();
    }
}

function guardarNuevaUnidad() {
    const nombre = document.getElementById('nueva-unidad-nombre').value.trim();
    if (!nombre) { showToast('Escribe el nombre de la unidad', 'error'); return; }

    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
        const $sel = window.jQuery('#p-unidad');
        if ($sel.find(`option[value="${nombre}"]`).length === 0) {
            $sel.append(new Option(nombre, nombre, true, true));
        }
        $sel.val(nombre).trigger('change.select2');
    } else {
        document.getElementById('p-unidad').value = nombre;
    }

    toggleNuevaUnidad();
    showToast(`Unidad "${nombre}" agregada`, 'success');
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

// ---- Imagen de producto ----
function handleImagenSelect(input) {
    if (input.files && input.files[0]) mostrarImagenPreview(input.files[0]);
}

function handleImagenDrop(event) {
    event.preventDefault();
    document.getElementById('p-imagen-zona').style.borderColor = 'var(--border)';
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) mostrarImagenPreview(file);
}

function mostrarImagenPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('p-imagen-preview').src = e.target.result;
        document.getElementById('p-imagen-preview').style.display = 'block';
        document.getElementById('p-imagen-placeholder').style.display = 'none';
        document.getElementById('p-imagen-quitar').style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function quitarImagen() {
    document.getElementById('p-imagen-preview').src = '';
    document.getElementById('p-imagen-preview').style.display = 'none';
    document.getElementById('p-imagen-placeholder').style.display = '';
    document.getElementById('p-imagen-quitar').style.display = 'none';
    document.getElementById('p-imagen-input').value = '';
}

// ---- Lector de código de barras ----
function setupBarcodeScanner() {
    document.addEventListener('barcodescan', function (e) {
        const code         = e.detail.code.trim();
        const esTabProducto = document.getElementById('tab-producto').style.display !== 'none';
        const modalAjuste   = document.getElementById('modal-ajuste').classList.contains('open');

        if (esTabProducto) {
            const campo = document.getElementById('p-codigo');
            campo.value = code;
            campo.style.transition = 'background .2s';
            campo.style.background = 'rgba(var(--primary-rgb,79,70,229),.08)';
            setTimeout(() => { campo.style.background = ''; }, 800);
            document.getElementById('p-nombre').focus();
            showToast('<i class="fas fa-barcode"></i> Código escaneado: ' + code, 'success');
        } else if (!modalAjuste) {
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
    toast.className = `app-toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
</script>

<?php include '../../includes/footer.php'; ?>
