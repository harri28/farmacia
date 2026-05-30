<?php
// ============================================================
// ARCHIVO: farmacia/modules/ventas/index.php
// MÓDULO:  Ventas → Punto de Venta (POS)
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$current_module = 'ventas';
$current_page   = 'pos';
$page_title     = 'Punto de Venta — FarmaSystem';
$breadcrumb     = '<strong>Ventas</strong> / Punto de Venta';

$db = getDB();

// Datos del tenant para el ticket de impresión
$_tenant_info = ['ruc' => '', 'telefono' => '', 'direccion' => ''];
if (sesionTenantId()) {
    $t = $db->prepare("SELECT ruc, telefono, direccion FROM public.tenants WHERE id = :id");
    $t->execute([':id' => sesionTenantId()]);
    $_tenant_info = $t->fetch() ?: $_tenant_info;
}

// Categorías para filtro
$categorias = $db->query("SELECT id, nombre FROM categorias WHERE activo = TRUE ORDER BY nombre")->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><i class="fas fa-store" style="color:var(--primary);margin-right:8px"></i>Punto de Venta</div>
        <div class="page-subtitle">Registra ventas rápidamente — <?= date('d/m/Y H:i') ?></div>
    </div>
    <div class="page-actions">
        <a href="historial.php" class="btn btn-outline btn-sm">
            <i class="fas fa-history"></i> Historial
        </a>
    </div>
</div>

<div class="pos-layout">
    <!-- COLUMNA IZQUIERDA: búsqueda + productos -->
    <div class="pos-left">
        <!-- Barra de búsqueda + filtros -->
        <div>
            <div class="input-group" style="margin-bottom:10px">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="search-input" class="form-control"
                    placeholder="Buscar producto por nombre o código..." autocomplete="off">
            </div>
            <div class="filter-chips" id="cat-chips">
                <span class="chip active" data-cat="0">Todos</span>
                <?php foreach ($categorias as $cat): ?>
                <span class="chip" data-cat="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Grilla de productos -->
        <div class="products-grid" id="products-grid">
            <div style="grid-column:1/-1;text-align:center;color:var(--text-light);padding:40px">
                <i class="fas fa-spinner fa-spin" style="font-size:1.5rem"></i>
                <p style="margin-top:10px">Cargando productos...</p>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: carrito -->
    <div class="pos-right">
        <div class="cart-panel">
            <div class="cart-header">
                <div class="cart-title"><i class="fas fa-shopping-cart" style="margin-right:7px;color:var(--primary)"></i>Carrito</div>
                <span class="cart-count" id="cart-count">0</span>
            </div>

            <!-- Cliente -->
            <div style="padding:10px 14px;border-bottom:1px solid var(--border)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:flex;align-items:center;gap:5px">
                        <i class="fas fa-user" style="color:var(--primary)"></i> Cliente <span style="font-weight:400">(opcional)</span>
                    </span>
                    <button onclick="openModalNuevoCliente()" title="Agregar nuevo cliente"
                        style="background:var(--primary);border:none;color:#fff;border-radius:50%;width:22px;height:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.7rem;flex-shrink:0">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-search"></i></span>
                    <input type="text" id="cliente-search" class="form-control"
                        placeholder="Buscar por nombre o DNI..." autocomplete="off" style="font-size:.82rem;padding:7px 7px 7px 32px">
                </div>
                <div id="cliente-info" style="display:none;margin-top:6px;font-size:.78rem;color:var(--text-muted);background:var(--primary-light);padding:6px 10px;border-radius:var(--radius-sm)">
                    <i class="fas fa-user-check" style="color:var(--primary);margin-right:5px"></i>
                    <span id="cliente-nombre"></span>
                    <button onclick="clearCliente()" style="float:right;background:none;border:none;color:var(--text-light);font-size:.8rem;cursor:pointer">✕</button>
                </div>
            </div>

            <div class="cart-items" id="cart-items">
                <div class="cart-empty" id="cart-empty">
                    <i class="fas fa-shopping-basket"></i>
                    <span>El carrito está vacío</span>
                    <small>Selecciona productos del panel izquierdo</small>
                </div>
            </div>

            <div class="cart-summary" id="cart-summary" style="display:none">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="sum-subtotal">S/ 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Descuento</span>
                    <span id="sum-descuento" style="color:var(--success)">-S/ 0.00</span>
                </div>
                <div class="summary-row">
                    <span>IGV (18%)</span>
                    <span id="sum-igv">S/ 0.00</span>
                </div>
                <div class="summary-row total">
                    <span>TOTAL</span>
                    <span id="sum-total">S/ 0.00</span>
                </div>
            </div>

            <!-- Banner estado de caja -->
            <div id="caja-banner" style="display:none;margin:0 0 10px;padding:9px 12px;border-radius:8px;font-size:.8rem;font-weight:600;display:flex;align-items:center;gap:8px"></div>

            <div class="cart-footer" style="padding:12px 14px">
                <button class="btn btn-success w-100" onclick="procesarVenta()" id="btn-vender" disabled
                    style="height:52px;font-size:1rem;display:flex;align-items:center;justify-content:center;gap:10px">
                    <i class="fas fa-check-circle" style="font-size:1.1rem"></i>
                    <span>Cobrar</span>
                    <span id="footer-total" style="background:rgba(255,255,255,.2);padding:3px 12px;border-radius:20px;font-size:.95rem;font-weight:800">S/ 0.00</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Cobro / Pago -->
<div class="modal-overlay" id="modal-cobro">
    <div class="modal" style="max-width:500px">
        <div class="modal-header" style="padding:14px 18px">
            <h3 class="modal-title" style="font-size:.88rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase">
                Información de la Venta
            </h3>
            <button class="modal-close" onclick="closeModal('modal-cobro')" style="display:flex;align-items:center;gap:5px;font-size:.8rem;background:none;border:none;color:var(--text-muted);cursor:pointer">
                Cerrar <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" style="padding:16px 18px">

            <!-- Fila 1: Tipo de documento | Tipo impresión | Serie -->
            <div style="display:grid;grid-template-columns:2fr 1.2fr 1fr;gap:10px;margin-bottom:12px">
                <div>
                    <label class="form-label" style="font-size:.71rem">Tipo de documento</label>
                    <select class="form-control" id="tipo-comprobante" style="font-size:.82rem" onchange="onComprobanteChange()">
                        <option value="boleta">Boleta Electrónica</option>
                        <option value="factura">Factura Electrónica</option>
                        <option value="ticket">Ticket</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:.71rem">Tipo impresión</label>
                    <select class="form-control" id="tipo-impresion" style="font-size:.82rem">
                        <option value="ticket">Ticket</option>
                        <option value="a4">A4</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:.71rem">Serie</label>
                    <select class="form-control" id="cobro-serie" style="font-size:.82rem">
                        <option value="B001">B001</option>
                    </select>
                </div>
            </div>

            <!-- Fila 2: Cliente / Razón social + botón borrar + DNI ó RUC -->
            <div style="display:grid;grid-template-columns:1fr auto 150px;gap:8px;align-items:end;margin-bottom:10px">
                <div>
                    <label class="form-label" style="font-size:.71rem">Cliente / Razón social</label>
                    <input type="text" id="cobro-cliente-nombre" class="form-control" placeholder="CLIENTE"
                        style="font-size:.82rem" autocomplete="off">
                </div>
                <button onclick="clearCobroCliente()" title="Quitar cliente"
                    style="height:36px;padding:0 10px;background:none;border:1px solid #fecaca;color:#dc2626;border-radius:var(--radius-sm);cursor:pointer">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <div>
                    <label class="form-label" style="font-size:.71rem">DNI ó RUC</label>
                    <input type="text" id="cobro-cliente-doc" class="form-control" placeholder="—"
                        style="font-size:.82rem" maxlength="11" autocomplete="off">
                </div>
            </div>

            <!-- Botón SUNAT: consultar/validar comprobante -->
            <div style="margin-bottom:10px">
                <button onclick="consultarSunat()" title="Consultar en SUNAT"
                    style="display:flex;align-items:center;gap:8px;background:none;border:1px solid #e2e8f0;border-radius:var(--radius-sm);padding:5px 12px;cursor:pointer;transition:border-color .2s"
                    onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#e2e8f0'">
                    <img src="../../assets/img/logos/sunat.png" alt="SUNAT" style="height:22px;width:22px;object-fit:contain">
                    <span style="font-size:.78rem;font-weight:600;color:var(--text-muted)">Consultar SUNAT</span>
                </button>
            </div>

            <!-- Fila 3: Dirección -->
            <div style="margin-bottom:10px">
                <label class="form-label" style="font-size:.71rem">Dirección</label>
                <input type="text" id="cobro-direccion" class="form-control" placeholder=""
                    style="font-size:.82rem">
            </div>

            <!-- Botón dividir cuenta -->
            <div style="text-align:right;margin-bottom:14px">
                <button class="btn btn-primary btn-sm" style="font-size:.77rem" onclick="showToast('Función próximamente disponible','info')">
                    <i class="fas fa-plus"></i> Dividir cuenta
                </button>
            </div>

            <div style="border-top:1px solid var(--border);margin-bottom:14px"></div>

            <!-- Fila 4: Tipo de pago | Cuenta de banco | Monto a pagar -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px">
                <div>
                    <label class="form-label" style="font-size:.71rem">Tipo de pago</label>
                    <select class="form-control" id="tipo-pago" style="font-size:.82rem" onchange="calcularVuelto()">
                        <option value="efectivo">Efectivo</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:.71rem">Cuenta de banco</label>
                    <select class="form-control" id="cuenta-banco" style="font-size:.82rem">
                        <option value="caja_chica">CAJA CHICA</option>
                        <option value="banco">BANCO</option>
                        <option value="yape_cuenta">YAPE</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:.71rem">Monto a pagar</label>
                    <div class="input-group">
                        <span class="input-group-icon" style="font-size:.78rem;font-weight:700;color:var(--text-muted)">S/</span>
                        <input type="text" id="monto-recibido" class="form-control" readonly
                            style="font-size:.82rem;padding-left:28px;background:var(--surface-2);color:var(--text-muted);cursor:default">
                    </div>
                </div>
            </div>

            <!-- Efectivo del cliente -->
            <div style="margin-bottom:12px">
                <label class="form-label" style="font-size:.71rem">Efectivo del cliente</label>
                <div class="input-group">
                    <span class="input-group-icon" style="font-size:.85rem;font-weight:700;color:var(--primary)">S/</span>
                    <input type="number" id="monto-cliente" class="form-control" placeholder="0.00"
                        step="0.10" min="0" oninput="calcularVuelto()"
                        style="font-size:1rem;padding-left:30px;font-weight:600;border-color:var(--primary)"
                        autocomplete="off">
                </div>
            </div>

            <!-- Observación -->
            <div style="margin-bottom:14px">
                <label class="form-label" style="font-size:.71rem">Observación</label>
                <input type="text" id="cobro-observacion" class="form-control" placeholder="glosa" style="font-size:.82rem">
            </div>

            <!-- Toggle: imprimir observación -->
            <div style="margin-bottom:10px">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.81rem;color:var(--text)">
                    <span id="track-obs" onclick="toggleSwitch('check-obs','track-obs')"
                        style="width:38px;height:21px;background:var(--primary);border-radius:11px;position:relative;flex-shrink:0;cursor:pointer;transition:background .2s">
                        <span style="position:absolute;top:3px;left:3px;width:15px;height:15px;background:#fff;border-radius:50%;transition:transform .2s;transform:translateX(17px)"></span>
                    </span>
                    <input type="checkbox" id="check-obs" checked style="display:none">
                    Imprimir la observación en el comprobante
                </label>
            </div>
            <!-- Toggle: crédito -->
            <div style="margin-bottom:16px">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.81rem;color:var(--text)">
                    <span id="track-credito" onclick="toggleSwitch('check-credito','track-credito')"
                        style="width:38px;height:21px;background:#d1d5db;border-radius:11px;position:relative;flex-shrink:0;cursor:pointer;transition:background .2s">
                        <span style="position:absolute;top:3px;left:3px;width:15px;height:15px;background:#fff;border-radius:50%;transition:transform .2s"></span>
                    </span>
                    <input type="checkbox" id="check-credito" style="display:none">
                    ¿La venta es a crédito?
                </label>
            </div>

            <!-- Totales: Total de venta | Total a pagar | Vuelto -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;border-top:2px solid var(--border);padding-top:14px;text-align:center">
                <div>
                    <div style="font-size:.71rem;color:var(--text-muted);margin-bottom:4px">Total de venta</div>
                    <div style="font-size:1.15rem;font-weight:700;color:var(--primary)" id="cobro-total-venta">S/ 0.00</div>
                </div>
                <div style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                    <div style="font-size:.71rem;color:var(--text-muted);margin-bottom:4px">Total a pagar</div>
                    <div style="font-size:1.15rem;font-weight:700;color:var(--primary)" id="cobro-total">S/ 0.00</div>
                </div>
                <div>
                    <div style="font-size:.71rem;color:var(--text-muted);margin-bottom:4px">Vuelto</div>
                    <div style="font-size:1.15rem;font-weight:700;color:var(--success)" id="vuelto">S/ 0.00</div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="justify-content:space-between;padding:12px 18px">
            <button class="btn btn-outline btn-sm" onclick="previsualizarComprobante()" style="font-size:.8rem">
                Previsualizar comprobante
            </button>
            <div style="display:flex;gap:8px">
                <button class="btn btn-primary" id="btn-confirmar-venta" onclick="confirmarVenta()" style="font-size:.85rem">
                    <i class="fas fa-check"></i> Pagar y emitir comprobante
                </button>
                <button class="btn btn-outline" onclick="closeModal('modal-cobro')" style="font-size:.85rem">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Vista previa del comprobante -->
<div class="modal-overlay" id="modal-preview">
    <div class="modal" style="max-width:380px">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-eye" style="color:var(--primary);margin-right:8px"></i>Vista Previa del Comprobante</h3>
            <button class="modal-close" onclick="closeModal('modal-preview')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding:16px;background:#e5e7eb;max-height:70vh;overflow-y:auto">
            <div style="background:#fff;border-radius:4px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.15)">
                <div id="preview-ticket-body"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-preview')">Cerrar</button>
            <button class="btn btn-outline btn-sm" onclick="printReceipt(document.getElementById('preview-ticket-body').innerHTML)">
                <i class="fas fa-print"></i> Imprimir vista previa
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Ticket de venta -->
<div class="modal-overlay" id="modal-ticket">
    <div class="modal" style="max-width:380px">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-check-circle" style="color:var(--success);margin-right:8px"></i>Venta Completada</h3>
            <button class="modal-close" onclick="closeModal('modal-ticket');resetPOS()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding:16px;background:#e5e7eb;max-height:70vh;overflow-y:auto">
            <div style="background:#fff;border-radius:4px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.15)">
                <div id="ticket-body"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline btn-sm" onclick="printReceipt(document.getElementById('ticket-body').innerHTML)">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button class="btn btn-outline btn-sm" onclick="enviarWhatsApp()" style="border-color:#25d366;color:#25d366">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>
            <button class="btn btn-primary" onclick="closeModal('modal-ticket');resetPOS()">
                <i class="fas fa-plus"></i> Nueva Venta
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Nuevo Cliente -->
<div class="modal-overlay" id="modal-nuevo-cliente">
    <div class="modal" style="max-width:420px">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-user-plus" style="color:var(--primary);margin-right:8px"></i>Nuevo Cliente</h3>
            <button class="modal-close" onclick="closeModal('modal-nuevo-cliente')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="form-group">
                    <label class="form-label">Nombres <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="nc-nombres" class="form-control" placeholder="Juan" autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellidos</label>
                    <input type="text" id="nc-apellidos" class="form-control" placeholder="Pérez" autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">DNI</label>
                    <input type="text" id="nc-dni" class="form-control" placeholder="12345678" maxlength="8" autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">RUC</label>
                    <input type="text" id="nc-ruc" class="form-control" placeholder="20123456789" maxlength="11" autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" id="nc-telefono" class="form-control" placeholder="987654321" maxlength="12" autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="nc-email" class="form-control" placeholder="correo@ejemplo.com" autocomplete="off">
                </div>
            </div>
            <div class="form-group" style="margin-top:2px">
                <label class="form-label">Dirección</label>
                <input type="text" id="nc-direccion" class="form-control" placeholder="Av. Principal 123" autocomplete="off">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-nuevo-cliente')">Cancelar</button>
            <button class="btn btn-primary" id="btn-guardar-cliente" onclick="guardarNuevoCliente()">
                <i class="fas fa-save"></i> Guardar Cliente
            </button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<style>
/* La impresión de tickets se hace en ventana nueva (printReceipt) — no se necesita @media print aquí */
</style>

<script>
// ============================================================
// POS JavaScript
// ============================================================

const BASE = '../../';
const EMPRESA_NOMBRE  = <?= json_encode(sesionTenantNombre()) ?>;
const EMPRESA_RUC     = <?= json_encode($_tenant_info['ruc']       ?? '') ?>;
const EMPRESA_TEL     = <?= json_encode($_tenant_info['telefono']  ?? '') ?>;
const EMPRESA_DIR     = <?= json_encode($_tenant_info['direccion'] ?? '') ?>;
const SUCURSAL_NOMBRE = <?= json_encode(sesionSucursal()) ?>;
const VENDEDOR_NOMBRE = <?= json_encode(sesionNombre()) ?>;
let allProducts = [];
let cart = [];
let selectedCliente = null;
let currentCat = 0;
let currentVentaData = null;
let _lastSale = null;

// ---- Init ----
let cajaAbierta = false;

document.addEventListener('DOMContentLoaded', () => {
    checkCaja();
    loadProducts();
    setupSearch();
    setupClienteSearch();
    setupBarcodeScanner();
});

function checkCaja() {
    fetch(BASE + 'modules/caja/api.php?action=estado')
        .then(r => r.json())
        .then(d => {
            const banner = document.getElementById('caja-banner');
            if (d.caja && d.caja.estado === 'abierta') {
                cajaAbierta = true;
                banner.style.display = 'flex';
                banner.style.background = '#f0fdf4';
                banner.style.border = '1px solid #bbf7d0';
                banner.style.color = '#15803d';
                banner.innerHTML = '<i class="fas fa-cash-register"></i> Caja abierta — ' + (d.caja.nombre || 'Caja Principal');
            } else {
                cajaAbierta = false;
                banner.style.display = 'flex';
                banner.style.background = '#fef2f2';
                banner.style.border = '1px solid #fecaca';
                banner.style.color = '#dc2626';
                banner.innerHTML = '<i class="fas fa-lock"></i> Caja cerrada — <a href="../caja/index.php" style="color:#dc2626;margin-left:4px;font-weight:700">Ir a aperturar</a>';
            }
        })
        .catch(() => { cajaAbierta = false; });
}

// ---- Cargar Productos ----
function loadProducts() {
    fetch(BASE + 'modules/ventas/api.php?action=productos')
        .then(r => r.json())
        .then(data => {
            allProducts = data;
            renderProducts(allProducts);
        })
        .catch(() => showToast('Error al cargar productos', 'error'));
}

function renderProducts(products) {
    const grid = document.getElementById('products-grid');
    if (!products.length) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-light);padding:40px"><i class="fas fa-box-open" style="font-size:1.5rem"></i><p style="margin-top:10px">No se encontraron productos</p></div>';
        return;
    }
    grid.innerHTML = products.map(p => {
        const outStock = parseInt(p.stock) <= 0;
        const lowStock = parseInt(p.stock) > 0 && parseInt(p.stock) <= parseInt(p.stock_minimo);
        const cls = outStock ? 'out-stock' : (lowStock ? 'low-stock' : '');
        const stockLabel = outStock ? '<span class="product-stock out">Sin stock</span>' :
                           (lowStock ? `<span class="product-stock low">Stock: ${p.stock} ⚠</span>` :
                           `<span class="product-stock">Stock: ${p.stock}</span>`);
        return `
        <div class="product-card ${cls}" onclick="addToCart(${p.id})" data-id="${p.id}">
            ${p.favorito == 't' ? '<span class="fav-icon"><i class="fas fa-star"></i></span>' : ''}
            <div class="product-name">${p.nombre}</div>
            <div class="product-lab">${p.laboratorio || ''}</div>
            <div class="product-price">S/ ${parseFloat(p.precio_venta).toFixed(2)}</div>
            ${stockLabel}
        </div>`;
    }).join('');
}

// ---- Búsqueda ----
function setupSearch() {
    let timer;
    document.getElementById('search-input').addEventListener('input', e => {
        clearTimeout(timer);
        timer = setTimeout(() => filterProducts(e.target.value, currentCat), 200);
    });

    document.getElementById('cat-chips').addEventListener('click', e => {
        const chip = e.target.closest('.chip');
        if (!chip) return;
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        currentCat = parseInt(chip.dataset.cat);
        filterProducts(document.getElementById('search-input').value, currentCat);
    });
}

function filterProducts(query, catId) {
    let filtered = allProducts;
    if (catId > 0) filtered = filtered.filter(p => parseInt(p.categoria_id) === catId);
    if (query.trim()) {
        const q = query.toLowerCase().trim();
        filtered = filtered.filter(p =>
            p.nombre.toLowerCase().includes(q) ||
            p.codigo.toLowerCase().includes(q) ||
            (p.laboratorio && p.laboratorio.toLowerCase().includes(q))
        );
    }
    renderProducts(filtered);
}

// ---- Carrito ----
function addToCart(productId) {
    const product = allProducts.find(p => parseInt(p.id) === productId);
    if (!product || parseInt(product.stock) <= 0) return;

    const existing = cart.find(i => i.id === productId);
    if (existing) {
        if (existing.qty >= parseInt(product.stock)) {
            showToast('No hay más stock disponible', 'error'); return;
        }
        existing.qty++;
    } else {
        cart.push({ id: productId, product, qty: 1 });
    }
    renderCart();
    // Feedback visual
    const card = document.querySelector(`.product-card[data-id="${productId}"]`);
    if (card) {
        card.style.transform = 'scale(.96)';
        setTimeout(() => card.style.transform = '', 150);
    }
}

function changeQty(id, delta) {
    const idx = cart.findIndex(i => i.id === id);
    if (idx === -1) return;
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    renderCart();
}

function removeItem(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const itemsEl   = document.getElementById('cart-items');
    const emptyEl   = document.getElementById('cart-empty');
    const summaryEl = document.getElementById('cart-summary');
    const countEl   = document.getElementById('cart-count');
    const btnVender = document.getElementById('btn-vender');

    countEl.textContent = cart.reduce((s, i) => s + i.qty, 0);

    if (!cart.length) {
        emptyEl.style.display = 'flex';
        summaryEl.style.display = 'none';
        btnVender.disabled = true;
        // Limpiar items pero dejar el empty div
        const oldItems = itemsEl.querySelectorAll('.cart-item');
        oldItems.forEach(el => el.remove());
        return;
    }

    emptyEl.style.display = 'none';
    summaryEl.style.display = 'block';
    btnVender.disabled = false;

    // Rebuild items
    const oldItems = itemsEl.querySelectorAll('.cart-item');
    oldItems.forEach(el => el.remove());

    cart.forEach(item => {
        const subtotal = item.qty * parseFloat(item.product.precio_venta);
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div class="cart-item-info">
                <div class="cart-item-name">${item.product.nombre}</div>
                <div class="cart-item-price">S/ ${parseFloat(item.product.precio_venta).toFixed(2)} c/u</div>
                <div class="cart-item-controls">
                    <button class="qty-btn" onclick="changeQty(${item.id},-1)"><i class="fas fa-minus"></i></button>
                    <span class="qty-value">${item.qty}</span>
                    <button class="qty-btn" onclick="changeQty(${item.id},1)"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="cart-item-right">
                <button class="cart-item-del" onclick="removeItem(${item.id})"><i class="fas fa-trash"></i></button>
                <div class="cart-item-total">S/ ${subtotal.toFixed(2)}</div>
            </div>`;
        itemsEl.insertBefore(div, emptyEl);
    });

    // Totals
    const subtotal   = cart.reduce((s, i) => s + i.qty * parseFloat(i.product.precio_venta), 0);
    const descuento  = 0;
    const base       = subtotal - descuento;
    const igv        = base * 0.18;
    const total      = base;

    document.getElementById('sum-subtotal').textContent  = `S/ ${subtotal.toFixed(2)}`;
    document.getElementById('sum-descuento').textContent = `-S/ ${descuento.toFixed(2)}`;
    document.getElementById('sum-igv').textContent       = `S/ ${igv.toFixed(2)}`;
    document.getElementById('sum-total').textContent     = `S/ ${total.toFixed(2)}`;
    document.getElementById('footer-total').textContent  = `S/ ${total.toFixed(2)}`;
}

// ---- Cliente ----
function setupClienteSearch() {
    let timer;
    document.getElementById('cliente-search').addEventListener('input', e => {
        clearTimeout(timer);
        const q = e.target.value.trim();
        if (!q) return;
        timer = setTimeout(() => {
            fetch(BASE + `modules/ventas/api.php?action=buscar_cliente&q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.id) {
                        selectedCliente = data;  // incluye ruc ahora
                        let label = data.nombres + ' ' + (data.apellidos || '');
                        if (data.ruc)  label += ' · RUC: ' + data.ruc;
                        else if (data.dni) label += ' · DNI: ' + data.dni;
                        document.getElementById('cliente-nombre').textContent = label;
                        document.getElementById('cliente-info').style.display = 'block';
                        document.getElementById('cliente-search').style.display = 'none';
                    }
                });
        }, 400);
    });
}

function clearCliente() {
    selectedCliente = null;
    document.getElementById('cliente-info').style.display = 'none';
    document.getElementById('cliente-search').style.display = 'block';
    document.getElementById('cliente-search').value = '';
}

// ---- Procesar Venta ----
function procesarVenta() {
    if (!cart.length) return;
    if (!cajaAbierta) {
        showToast('Debes aperturar la caja antes de registrar ventas', 'error');
        return;
    }
    const total    = cart.reduce((s, i) => s + i.qty * parseFloat(i.product.precio_venta), 0);
    const totalStr = `S/ ${total.toFixed(2)}`;

    document.getElementById('cobro-total-venta').textContent = totalStr;
    document.getElementById('cobro-total').textContent       = totalStr;
    document.getElementById('monto-recibido').value          = total.toFixed(2); // display readonly
    document.getElementById('monto-cliente').value           = '';
    document.getElementById('tipo-pago').value               = 'efectivo';
    document.getElementById('tipo-comprobante').value        = 'boleta';
    document.getElementById('cobro-observacion').value       = '';

    // Poblar datos del cliente
    if (selectedCliente) {
        const nombre = ((selectedCliente.nombres || '') + ' ' + (selectedCliente.apellidos || '')).trim();
        document.getElementById('cobro-cliente-nombre').value = nombre;
        document.getElementById('cobro-cliente-doc').value    = selectedCliente.ruc || selectedCliente.dni || '';
        document.getElementById('cobro-direccion').value      = selectedCliente.direccion || '';
    } else {
        document.getElementById('cobro-cliente-nombre').value = '';
        document.getElementById('cobro-cliente-doc').value    = '';
        document.getElementById('cobro-direccion').value      = '';
    }

    onComprobanteChange();
    calcularVuelto();
    openModal('modal-cobro');
}

function onComprobanteChange() {
    const tipo    = document.getElementById('tipo-comprobante').value;
    const serieEl = document.getElementById('cobro-serie');
    if (tipo === 'boleta') {
        serieEl.innerHTML = '<option value="B001">B001</option>';
    } else if (tipo === 'factura') {
        serieEl.innerHTML = '<option value="F001">F001</option>';
    } else {
        serieEl.innerHTML = '<option value="">—</option>';
    }
}

function toggleSwitch(checkId, trackId) {
    const cb  = document.getElementById(checkId);
    const dot = document.querySelector('#' + trackId + ' span');
    cb.checked = !cb.checked;
    const track = document.getElementById(trackId);
    if (cb.checked) {
        track.style.background = 'var(--primary)';
        dot.style.transform = 'translateX(17px)';
    } else {
        track.style.background = '#d1d5db';
        dot.style.transform = 'translateX(0)';
    }
}

function consultarSunat() {
    const doc = document.getElementById('cobro-cliente-doc').value.trim();
    if (!doc) {
        showToast('Ingresa primero un DNI o RUC en el carrito para consultar', 'info');
        return;
    }
    const tipo = doc.length === 11 ? 'ruc' : 'dni';
    const url  = tipo === 'ruc'
        ? `https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRuc&nroRuc=${doc}`
        : `https://www.reniec.gob.pe/portal/html/registro-civil/externo/externo.htm`;
    window.open(url, '_blank');
}

function clearCobroCliente() {
    document.getElementById('cobro-cliente-nombre').value = '';
    document.getElementById('cobro-cliente-doc').value    = '';
    document.getElementById('cobro-direccion').value      = '';
    clearCliente();
}

// ---- Ticket ESC/POS ----
function buildTicketHTML(opts) {
    const {
        items = [], total = 0, igv = 0, descuento = 0,
        monto_recibido = 0, tipo_pago = 'efectivo',
        tipo_comprobante = 'ticket', numero_venta = null,
        numero_comprobante = null, cliente = null,
        fecha = '', hora = '', preview = false
    } = opts;

    const PAGOS = { efectivo:'Efectivo', yape:'Yape', plin:'Plin', tarjeta:'Tarjeta', transferencia:'Transferencia' };
    const pagoLabel  = PAGOS[tipo_pago] || tipo_pago;
    const vuelto     = Math.max(0, (monto_recibido || 0) - total);
    const opGravada  = Math.max(0, total - igv);

    const COMP = { boleta:'BOLETA DE VENTA ELECTRÓNICA', factura:'FACTURA ELECTRÓNICA', ticket:'TICKET DE VENTA' };
    const compLabel = COMP[tipo_comprobante] || 'COMPROBANTE DE VENTA';
    const numComp   = numero_comprobante || numero_venta || '---';

    const nombreCliente = cliente
        ? ((cliente.nombres || '') + ' ' + (cliente.apellidos || '')).trim()
        : '';

    const sep = (double = false) =>
        `<div style="border-top:${double ? '2px solid' : '1px dashed'} #000;margin:6px 0"></div>`;

    const row2 = (l, r, opts2 = {}) => {
        const sz   = opts2.big  ? '14px' : '11px';
        const fw   = opts2.bold ? '800'  : '400';
        return `<div style="display:flex;justify-content:space-between;font-size:${sz};font-weight:${fw};line-height:1.6">
            <span>${l}</span><span>${r}</span></div>`;
    };

    const itemsHTML = items.map(i => {
        const nombre = (i.nombre || i.product?.nombre || '');
        const qty    = i.qty    || i.cantidad || 1;
        const pu     = parseFloat(i.precio || i.precio_venta || i.product?.precio_venta || 0);
        const sub    = (pu * qty).toFixed(2);
        return `<div style="margin-bottom:5px">
            <div style="display:flex;justify-content:space-between;font-size:11px">
                <span style="flex:1;padding-right:6px;word-break:break-word">${nombre}</span>
                <span style="white-space:nowrap;font-weight:600">S/${sub}</span>
            </div>
            <div style="font-size:10px;color:#555;padding-left:2px">${qty} unid. x S/${pu.toFixed(2)}</div>
        </div>`;
    }).join('');

    return `<div id="pos-ticket" style="font-family:'Courier New',Courier,monospace;color:#000;font-size:12px;line-height:1.5;width:100%">

        <div style="text-align:center;margin-bottom:10px">
            <div style="font-size:15px;font-weight:900;letter-spacing:1px;text-transform:uppercase">${EMPRESA_NOMBRE || 'FARMACIA'}</div>
            ${EMPRESA_RUC     ? `<div style="font-size:11px">RUC: ${EMPRESA_RUC}</div>` : ''}
            ${SUCURSAL_NOMBRE ? `<div style="font-size:11px;font-weight:600">${SUCURSAL_NOMBRE}</div>` : ''}
            ${EMPRESA_DIR     ? `<div style="font-size:10px">${EMPRESA_DIR}</div>` : ''}
            ${EMPRESA_TEL     ? `<div style="font-size:10px">Tel: ${EMPRESA_TEL}</div>` : ''}
        </div>

        <div style="border-top:2px solid #000;border-bottom:2px solid #000;text-align:center;padding:5px 0;margin-bottom:8px">
            <div style="font-size:11px;font-weight:700">${compLabel}</div>
            <div style="font-size:11px">${numComp}</div>
        </div>

        <div style="font-size:11px;margin-bottom:6px">
            <div>Fecha   : ${fecha} ${hora}</div>
            <div>Cajero  : ${VENDEDOR_NOMBRE}</div>
            ${nombreCliente               ? `<div>Cliente : ${nombreCliente}</div>`  : ''}
            ${cliente?.dni                ? `<div>DNI     : ${cliente.dni}</div>`    : ''}
            ${cliente?.ruc                ? `<div>RUC     : ${cliente.ruc}</div>`    : ''}
            ${cliente?.direccion          ? `<div>Dir.    : ${cliente.direccion}</div>` : ''}
        </div>

        ${sep()}
        <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:700;margin-bottom:3px">
            <span>DESCRIPCIÓN</span><span>TOTAL</span>
        </div>
        ${sep()}
        <div style="margin-bottom:4px">${itemsHTML}</div>
        ${sep()}

        <div style="font-size:11px;margin:4px 0">
            ${row2('OP. GRAVADA:', `S/ ${opGravada.toFixed(2)}`)}
            ${row2('IGV (18%)  :', `S/ ${igv.toFixed(2)}`)}
            ${descuento > 0 ? row2('DESCUENTO  :', `-S/ ${parseFloat(descuento).toFixed(2)}`) : ''}
        </div>
        ${sep(true)}
        ${row2('TOTAL:', `S/ ${total.toFixed(2)}`, {bold:true, big:true})}
        ${sep(true)}

        <div style="font-size:11px;margin-top:5px">
            ${row2(pagoLabel + ':', `S/ ${parseFloat(monto_recibido).toFixed(2)}`)}
            ${vuelto > 0 ? row2('<b>VUELTO:</b>', `<b>S/ ${vuelto.toFixed(2)}</b>`) : ''}
        </div>

        ${sep()}
        <div style="text-align:center;font-size:11px;padding:4px 0">
            <div>¡Gracias por su compra!</div>
            <div>Vuelva pronto</div>
            ${preview ? `<div style="margin-top:8px;font-size:10px;font-weight:700;color:#888;border:1px dashed #aaa;padding:2px 8px;display:inline-block">
                ★ VISTA PREVIA — NO ES COMPROBANTE VÁLIDO ★</div>` : ''}
        </div>
    </div>`;
}

function printReceipt(innerHtml) {
    const w = window.open('', '_blank', 'width=420,height=700');
    w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Courier New',Courier,monospace; font-size:12px; color:#000; width:80mm; margin:0 auto; padding:4mm 3mm; }
@media print { @page { size:80mm auto; margin:4mm 3mm; } body { padding:0; } }
</style></head><body>${innerHtml}</body></html>`);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); }, 400);
}

function previsualizarComprobante() {
    if (!cart.length) { showToast('El carrito está vacío', 'error'); return; }

    const now   = new Date();
    const fecha = now.toLocaleDateString('es-PE', {day:'2-digit',month:'2-digit',year:'numeric'});
    const hora  = now.toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
    const total = cart.reduce((s, i) => s + i.qty * parseFloat(i.product.precio_venta), 0);
    const igv   = total * 0.18;

    const html = buildTicketHTML({
        items:            cart,
        total,
        igv,
        monto_recibido:   parseFloat(document.getElementById('monto-cliente')?.value) || total,
        tipo_pago:        document.getElementById('tipo-pago').value,
        tipo_comprobante: document.getElementById('tipo-comprobante').value,
        cliente:          selectedCliente,
        fecha, hora,
        preview: true,
    });

    document.getElementById('preview-ticket-body').innerHTML = html;
    openModal('modal-preview');
}

function calcularVuelto() {
    const total    = parseFloat(document.getElementById('cobro-total').textContent.replace('S/ ','')) || 0;
    const cliente  = parseFloat(document.getElementById('monto-cliente').value) || 0;
    const vuelto   = cliente - total;
    const el       = document.getElementById('vuelto');
    if (cliente <= 0) {
        el.textContent = 'S/ 0.00';
        el.style.color = 'var(--success)';
    } else {
        el.textContent = `S/ ${Math.max(0, vuelto).toFixed(2)}`;
        el.style.color  = vuelto >= 0 ? 'var(--success)' : 'var(--danger)';
    }
}

function confirmarVenta() {
    const tipoPago  = document.getElementById('tipo-pago').value;
    const tipoComp  = document.getElementById('tipo-comprobante').value;
    const total     = cart.reduce((s, i) => s + i.qty * parseFloat(i.product.precio_venta), 0);

    // Validación: factura requiere cliente con RUC
    if (tipoComp === 'factura') {
        if (!selectedCliente) {
            showToast('Para emitir factura debe seleccionar un cliente', 'error');
            closeModal('modal-cobro');
            document.getElementById('cliente-search').focus();
            return;
        }
        if (!selectedCliente.ruc) {
            showToast('El cliente no tiene RUC. Obligatorio para factura.', 'error');
            return;
        }
    }

    const montoCliente = parseFloat(document.getElementById('monto-cliente').value) || 0;
    if (tipoPago === 'efectivo' && montoCliente < total) {
        showToast('El efectivo del cliente es insuficiente', 'error');
        document.getElementById('monto-cliente').focus();
        return;
    }

    const btn = document.getElementById('btn-confirmar-venta');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    const payload = {
        items: cart.map(i => ({ producto_id: i.id, cantidad: i.qty, precio: parseFloat(i.product.precio_venta) })),
        cliente_id: selectedCliente ? selectedCliente.id : null,
        tipo_pago: tipoPago,
        tipo_comprobante: tipoComp,
        monto_recibido: montoCliente > 0 ? montoCliente : total
    };

    fetch(BASE + 'modules/ventas/api.php?action=registrar_venta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Pagar y emitir comprobante';
        if (data.error) { showToast(data.message, 'error'); return; }
        closeModal('modal-cobro');
        showTicket(data);
        showToast('Venta registrada correctamente', 'success');
        loadProducts(); // Recargar stock
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Pagar y emitir comprobante';
        showToast('Error al procesar la venta', 'error');
    });
}

function showTicket(data) {
    const c      = data.comprobante;
    const items  = data.items || cart;
    const now    = new Date();
    const fecha  = now.toLocaleDateString('es-PE', {day:'2-digit',month:'2-digit',year:'numeric'});
    const hora   = now.toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit',second:'2-digit'});

    const ticketHTML = buildTicketHTML({
        items,
        total:             parseFloat(data.total),
        igv:               parseFloat(data.igv) || parseFloat(data.total) * 0.18,
        descuento:         parseFloat(data.descuento) || 0,
        monto_recibido:    parseFloat(data.monto_recibido) || parseFloat(data.total),
        tipo_pago:         data.tipo_pago || 'efectivo',
        tipo_comprobante:  data.tipo_comprobante || 'ticket',
        numero_venta:      data.numero_venta,
        numero_comprobante: (c && !c.error_nubefact) ? c.numero_completo : null,
        cliente:           selectedCliente,
        fecha, hora,
        preview: false,
    });

    // Bloque Nubefact (solo pantalla)
    let comprobanteHtml = '';
    if (c) {
        if (!c.error_nubefact) {
            const ico = c.tipo === 'factura' ? 'fa-file-invoice-dollar' : 'fa-file-invoice';
            comprobanteHtml = `
                <div style="background:#dcfce7;border-radius:8px;padding:12px 14px;margin-top:14px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <i class="fas ${ico}" style="color:#16a34a"></i>
                        <span style="font-weight:700;font-size:.88rem">${c.numero_completo}</span>
                        <span style="background:#16a34a;color:#fff;font-size:.66rem;padding:2px 7px;border-radius:20px">SUNAT ✓</span>
                    </div>
                    <div style="font-size:.74rem;color:#555;margin-bottom:8px">${c.estado_sunat}</div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                        ${c.enlace_del_pdf ? `<a href="${c.enlace_del_pdf}" target="_blank" class="btn btn-success btn-sm" style="font-size:.76rem"><i class="fas fa-file-pdf"></i> PDF</a>` : ''}
                        ${c.enlace_del_xml ? `<a href="${c.enlace_del_xml}" target="_blank" class="btn btn-outline btn-sm" style="font-size:.76rem"><i class="fas fa-code"></i> XML</a>` : ''}
                        ${c.enlace_del_cdr ? `<a href="${c.enlace_del_cdr}" target="_blank" class="btn btn-outline btn-sm" style="font-size:.76rem"><i class="fas fa-certificate"></i> CDR</a>` : ''}
                    </div>
                </div>`;
        } else {
            comprobanteHtml = `
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:11px 14px;margin-top:14px">
                    <div style="font-weight:600;font-size:.82rem;color:#dc2626;margin-bottom:3px">
                        <i class="fas fa-exclamation-triangle"></i> Error al emitir ${c.numero_completo || 'comprobante'}
                    </div>
                    <div style="font-size:.75rem;color:#555">${c.mensaje || c.estado_sunat}</div>
                    <div style="font-size:.72rem;color:#888;margin-top:4px">La venta quedó guardada. Puedes reintentar desde el historial.</div>
                </div>`;
        }
    }

    document.getElementById('ticket-body').innerHTML = ticketHTML + comprobanteHtml;

    _lastSale = { numero_venta: data.numero_venta, total: data.total, items, cliente: selectedCliente };
    openModal('modal-ticket');
}

function enviarWhatsApp() {
    if (!_lastSale) return;
    const s = _lastSale;
    const lineas = s.items.map(i => {
        const nombre = i.nombre || i.product?.nombre || '';
        const qty    = i.qty || i.cantidad || 1;
        const precio = parseFloat(i.precio || i.precio_venta || i.product?.precio_venta || 0);
        return `  • ${nombre} x${qty}  S/ ${(precio * qty).toFixed(2)}`;
    });
    const mensaje =
        `🧾 *Comprobante de venta*\n` +
        `N°: ${s.numero_venta}\n\n` +
        lineas.join('\n') +
        `\n\n*TOTAL: S/ ${parseFloat(s.total).toFixed(2)}*\n\n` +
        `Gracias por su compra 🙏`;
    const phone   = s.cliente?.telefono ? s.cliente.telefono.replace(/\D/g, '') : '';
    const url     = phone
        ? `https://wa.me/51${phone}?text=${encodeURIComponent(mensaje)}`
        : `https://wa.me/?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}

function resetPOS() {
    cart = [];
    selectedCliente = null;
    clearCliente();
    renderCart();
    document.getElementById('search-input').value = '';
    filterProducts('', 0);
    document.querySelectorAll('.chip').forEach((c,i) => i===0 ? c.classList.add('active') : c.classList.remove('active'));
    currentCat = 0;
}

function printTicket() {
    printReceipt(document.getElementById('ticket-body').innerHTML);
}

// ---- Lector de código de barras ----
function setupBarcodeScanner() {
    document.addEventListener('barcodescan', function (e) {
        const code    = e.detail.code.trim().toUpperCase();
        const product = allProducts.find(p => p.codigo.toUpperCase() === code);

        if (product) {
            addToCart(parseInt(product.id));
            showToast('<i class="fas fa-barcode"></i> ' + product.nombre, 'success');
        } else {
            // Código no encontrado: mostrar en búsqueda para que el cajero vea
            document.getElementById('search-input').value = e.detail.code;
            filterProducts(e.detail.code, currentCat);
            showToast('Código <strong>' + code + '</strong> no encontrado', 'error');
        }
    });
}

// ---- Nuevo Cliente ----
function openModalNuevoCliente() {
    ['nc-nombres','nc-apellidos','nc-dni','nc-ruc','nc-telefono','nc-email','nc-direccion']
        .forEach(id => { document.getElementById(id).value = ''; });
    openModal('modal-nuevo-cliente');
    setTimeout(() => document.getElementById('nc-nombres').focus(), 100);
}

function guardarNuevoCliente() {
    const nombres = document.getElementById('nc-nombres').value.trim();
    if (!nombres) { showToast('El nombre es obligatorio', 'error'); document.getElementById('nc-nombres').focus(); return; }

    const payload = {
        nombres,
        apellidos:  document.getElementById('nc-apellidos').value.trim(),
        dni:        document.getElementById('nc-dni').value.trim(),
        ruc:        document.getElementById('nc-ruc').value.trim(),
        telefono:   document.getElementById('nc-telefono').value.trim(),
        email:      document.getElementById('nc-email').value.trim(),
        direccion:  document.getElementById('nc-direccion').value.trim(),
    };

    const btn = document.getElementById('btn-guardar-cliente');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    fetch(BASE + 'modules/ventas/api.php?action=crear_cliente', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cliente';
        if (data.error) { showToast(data.message, 'error'); return; }

        // Seleccionar el cliente recién creado
        selectedCliente = data.cliente;
        let label = data.cliente.nombres + ' ' + (data.cliente.apellidos || '');
        if (data.cliente.ruc) label += ' · RUC: ' + data.cliente.ruc;
        else if (data.cliente.dni) label += ' · DNI: ' + data.cliente.dni;
        document.getElementById('cliente-nombre').textContent = label;
        document.getElementById('cliente-info').style.display = 'block';
        document.getElementById('cliente-search').style.display = 'none';

        closeModal('modal-nuevo-cliente');
        showToast('Cliente guardado y seleccionado', 'success');
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cliente';
        showToast('Error al guardar el cliente', 'error');
    });
}

// ---- Modal / Toast helpers ----
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type = 'info') {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
</script>

<?php include '../../includes/footer.php'; ?>