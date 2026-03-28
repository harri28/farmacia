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
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="cliente-search" class="form-control"
                        placeholder="Buscar cliente (opcional)..." autocomplete="off" style="font-size:.82rem;padding:7px 7px 7px 32px">
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

            <div class="cart-footer">
                <div style="display:flex;gap:8px">
                    <select class="form-control" id="tipo-pago" style="font-size:.82rem;flex:1">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="yape">📱 Yape</option>
                        <option value="plin">📱 Plin</option>
                        <option value="tarjeta">💳 Tarjeta</option>
                        <option value="transferencia">🏦 Transferencia</option>
                    </select>
                    <select class="form-control" id="tipo-comprobante" style="font-size:.82rem;flex:1">
                        <option value="ticket">Ticket</option>
                        <option value="boleta">Boleta</option>
                        <option value="factura">Factura</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px">
                    <button class="btn btn-outline btn-sm w-100" onclick="clearCart()" id="btn-clear" disabled>
                        <i class="fas fa-trash"></i> Limpiar
                    </button>
                    <button class="btn btn-success w-100" onclick="procesarVenta()" id="btn-vender" disabled>
                        <i class="fas fa-check"></i> Cobrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Cobro / Pago -->
<div class="modal-overlay" id="modal-cobro">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-money-bill-wave" style="color:var(--success);margin-right:8px"></i>Confirmar Cobro</h3>
            <button class="modal-close" onclick="closeModal('modal-cobro')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:var(--success-light);border-radius:var(--radius);padding:16px;text-align:center;margin-bottom:16px">
                <div style="font-size:.83rem;color:var(--text-muted);margin-bottom:4px">Total a cobrar</div>
                <div style="font-size:2rem;font-weight:800;color:var(--success)" id="cobro-total">S/ 0.00</div>
            </div>
            <div id="cobro-efectivo-section">
                <div class="form-group">
                    <label class="form-label">Monto recibido</label>
                    <div class="input-group">
                        <span class="input-group-icon" style="font-weight:700;font-size:.85rem;color:var(--text)">S/</span>
                        <input type="number" id="monto-recibido" class="form-control" placeholder="0.00"
                            step="0.10" min="0" oninput="calcularVuelto()">
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;background:var(--surface-2);padding:12px 14px;border-radius:var(--radius-sm)">
                    <span style="font-size:.9rem;font-weight:600">Vuelto</span>
                    <span style="font-size:1.2rem;font-weight:700;color:var(--primary)" id="vuelto">S/ 0.00</span>
                </div>
                <div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap" id="quick-amounts"></div>
            </div>
            <div id="cobro-resumen" style="margin-top:14px;font-size:.83rem;color:var(--text-muted)">
                <div style="display:flex;justify-content:space-between;padding:4px 0"><span>Tipo de pago:</span><span id="res-pago" style="font-weight:600;color:var(--text)"></span></div>
                <div style="display:flex;justify-content:space-between;padding:4px 0"><span>Comprobante:</span><span id="res-comprobante" style="font-weight:600;color:var(--text)"></span></div>
                <div style="display:flex;justify-content:space-between;padding:4px 0"><span>Items:</span><span id="res-items" style="font-weight:600;color:var(--text)"></span></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-cobro')">Cancelar</button>
            <button class="btn btn-success btn-lg" id="btn-confirmar-venta" onclick="confirmarVenta()">
                <i class="fas fa-check"></i> Confirmar Venta
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Ticket de venta -->
<div class="modal-overlay" id="modal-ticket">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-receipt" style="color:var(--primary);margin-right:8px"></i>Venta Completada</h3>
            <button class="modal-close" onclick="closeModal('modal-ticket');resetPOS()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="ticket-body"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="printTicket()"><i class="fas fa-print"></i> Imprimir</button>
            <button class="btn btn-primary" onclick="closeModal('modal-ticket');resetPOS()">
                <i class="fas fa-plus"></i> Nueva Venta
            </button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<script>
// ============================================================
// POS JavaScript
// ============================================================

const BASE = '../../';
let allProducts = [];
let cart = [];
let selectedCliente = null;
let currentCat = 0;
let currentVentaData = null;

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    setupSearch();
    setupClienteSearch();
    setupBarcodeScanner();
});

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
    const btnClear  = document.getElementById('btn-clear');
    const btnVender = document.getElementById('btn-vender');

    countEl.textContent = cart.reduce((s, i) => s + i.qty, 0);

    if (!cart.length) {
        emptyEl.style.display = 'flex';
        summaryEl.style.display = 'none';
        btnClear.disabled = true;
        btnVender.disabled = true;
        // Limpiar items pero dejar el empty div
        const oldItems = itemsEl.querySelectorAll('.cart-item');
        oldItems.forEach(el => el.remove());
        return;
    }

    emptyEl.style.display = 'none';
    summaryEl.style.display = 'block';
    btnClear.disabled = false;
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
    const total     = cart.reduce((s, i) => s + i.qty * parseFloat(i.product.precio_venta), 0);
    const tipoPago  = document.getElementById('tipo-pago').value;
    const tipoComp  = document.getElementById('tipo-comprobante').value;

    // Validación: factura requiere cliente con RUC
    if (tipoComp === 'factura') {
        if (!selectedCliente) {
            showToast('Para emitir factura debe buscar y seleccionar un cliente', 'error');
            document.getElementById('cliente-search').focus();
            return;
        }
        if (!selectedCliente.ruc) {
            showToast('El cliente seleccionado no tiene RUC. Para factura es obligatorio.', 'error');
            return;
        }
    }

    document.getElementById('cobro-total').textContent = `S/ ${total.toFixed(2)}`;
    document.getElementById('res-pago').textContent        = tipoPago.charAt(0).toUpperCase() + tipoPago.slice(1);
    document.getElementById('res-comprobante').textContent = tipoComp.charAt(0).toUpperCase() + tipoComp.slice(1);
    document.getElementById('res-items').textContent       = cart.reduce((s,i) => s + i.qty, 0) + ' items';
    document.getElementById('monto-recibido').value        = '';
    document.getElementById('vuelto').textContent          = 'S/ 0.00';

    const efectivoSection = document.getElementById('cobro-efectivo-section');
    efectivoSection.style.display = tipoPago === 'efectivo' ? 'block' : 'none';

    if (tipoPago === 'efectivo') {
        const quickAmounts = document.getElementById('quick-amounts');
        const amounts = [Math.ceil(total), Math.ceil(total/10)*10, Math.ceil(total/20)*20, 100, 200].filter((v,i,a) => a.indexOf(v)===i && v >= total).slice(0,4);
        quickAmounts.innerHTML = amounts.map(a =>
            `<button class="btn btn-outline btn-sm" onclick="setMonto(${a})">S/ ${a}</button>`
        ).join('');
    }

    openModal('modal-cobro');
}

function setMonto(amount) {
    document.getElementById('monto-recibido').value = amount;
    calcularVuelto();
}

function calcularVuelto() {
    const total    = parseFloat(document.getElementById('cobro-total').textContent.replace('S/ ','')) || 0;
    const recibido = parseFloat(document.getElementById('monto-recibido').value) || 0;
    const vuelto   = recibido - total;
    const el = document.getElementById('vuelto');
    el.textContent = `S/ ${Math.max(0, vuelto).toFixed(2)}`;
    el.style.color = vuelto >= 0 ? 'var(--success)' : 'var(--danger)';
}

function confirmarVenta() {
    const tipoPago  = document.getElementById('tipo-pago').value;
    const tipoComp  = document.getElementById('tipo-comprobante').value;
    const total     = cart.reduce((s, i) => s + i.qty * parseFloat(i.product.precio_venta), 0);

    if (tipoPago === 'efectivo') {
        const recibido = parseFloat(document.getElementById('monto-recibido').value) || 0;
        if (recibido < total) { showToast('El monto recibido es insuficiente', 'error'); return; }
    }

    const btn = document.getElementById('btn-confirmar-venta');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    const payload = {
        items: cart.map(i => ({ producto_id: i.id, cantidad: i.qty, precio: parseFloat(i.product.precio_venta) })),
        cliente_id: selectedCliente ? selectedCliente.id : null,
        tipo_pago: tipoPago,
        tipo_comprobante: tipoComp,
        monto_recibido: parseFloat(document.getElementById('monto-recibido').value) || total
    };

    fetch(BASE + 'modules/ventas/api.php?action=registrar_venta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Confirmar Venta';
        if (data.error) { showToast(data.message, 'error'); return; }
        closeModal('modal-cobro');
        showTicket(data);
        showToast('Venta registrada correctamente', 'success');
        loadProducts(); // Recargar stock
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Confirmar Venta';
        showToast('Error al procesar la venta', 'error');
    });
}

function showTicket(data) {
    const vuelto = (data.monto_recibido || 0) - data.total;
    const c = data.comprobante;

    // Sección de comprobante electrónico
    let comprobanteHtml = '';
    if (c) {
        if (!c.error_nubefact) {
            const iconoTipo = c.tipo === 'factura' ? 'fa-file-invoice-dollar' : 'fa-file-invoice';
            comprobanteHtml = `
                <div style="background:var(--success-light,#dcfce7);border-radius:var(--radius);padding:12px 14px;margin-top:12px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <i class="fas ${iconoTipo}" style="color:var(--success)"></i>
                        <span style="font-weight:700;font-size:.9rem">${c.numero_completo}</span>
                        <span style="background:var(--success);color:#fff;font-size:.68rem;padding:2px 7px;border-radius:20px">SUNAT ✓</span>
                    </div>
                    <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:10px">${c.estado_sunat}</div>
                    <div style="display:flex;gap:7px;flex-wrap:wrap">
                        ${c.enlace_del_pdf ? `<a href="${c.enlace_del_pdf}" target="_blank" class="btn btn-success btn-sm" style="font-size:.78rem"><i class="fas fa-file-pdf"></i> Ver PDF</a>` : ''}
                        ${c.enlace_del_xml ? `<a href="${c.enlace_del_xml}" target="_blank" class="btn btn-outline btn-sm" style="font-size:.78rem"><i class="fas fa-code"></i> XML</a>` : ''}
                        ${c.enlace_del_cdr ? `<a href="${c.enlace_del_cdr}" target="_blank" class="btn btn-outline btn-sm" style="font-size:.78rem"><i class="fas fa-certificate"></i> CDR</a>` : ''}
                    </div>
                </div>`;
        } else {
            comprobanteHtml = `
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius);padding:11px 14px;margin-top:12px">
                    <div style="font-weight:600;font-size:.83rem;color:var(--danger);margin-bottom:3px">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error al emitir ${c.numero_completo || 'comprobante electrónico'}
                    </div>
                    <div style="font-size:.76rem;color:var(--text-muted)">${c.mensaje || c.estado_sunat}</div>
                    <div style="font-size:.73rem;color:var(--text-light);margin-top:4px">La venta quedó guardada. Puedes reintentar desde el historial.</div>
                </div>`;
        }
    }

    document.getElementById('ticket-body').innerHTML = `
        <div style="text-align:center;margin-bottom:16px">
            <div style="font-size:2.5rem;color:var(--success)"><i class="fas fa-check-circle"></i></div>
            <div style="font-size:1.1rem;font-weight:700;margin-top:8px">¡Venta exitosa!</div>
            <div style="font-size:.82rem;color:var(--text-muted)">${data.numero_venta}</div>
        </div>
        <div style="background:var(--surface-2);border-radius:var(--radius);padding:14px;font-size:.85rem">
            ${(data.items || cart).map(i => `
            <div style="display:flex;justify-content:space-between;padding:4px 0">
                <span>${i.nombre || i.product?.nombre || ''} x${i.qty || i.cantidad}</span>
                <span style="font-weight:600">S/ ${(parseFloat(i.precio || i.precio_venta || i.product?.precio_venta || 0) * (i.qty || i.cantidad)).toFixed(2)}</span>
            </div>`).join('')}
            <hr style="margin:10px 0;border-color:var(--border)">
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1rem">
                <span>TOTAL</span><span style="color:var(--success)">S/ ${parseFloat(data.total).toFixed(2)}</span>
            </div>
            ${vuelto > 0 ? `<div style="display:flex;justify-content:space-between;margin-top:4px;color:var(--text-muted)"><span>Vuelto</span><span>S/ ${vuelto.toFixed(2)}</span></div>` : ''}
        </div>
        ${comprobanteHtml}`;
    openModal('modal-ticket');
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
    window.print();
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