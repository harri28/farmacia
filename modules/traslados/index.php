<?php
$required_roles = ['admin', 'gerente', 'cajero'];
$base_path = '../../';
$current_module = 'traslados';
$current_page = 'traslados';
$page_title = 'Traslados de stock — FarmaSystem';
$breadcrumb = '<i class="fas fa-exchange-alt"></i> Traslados de stock';
require_once '../../config/database.php';
require_once '../../includes/header.php';
?>

<style>
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:24px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px 20px;display:flex;align-items:center;gap:14px}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.si-indigo{background:#eef2ff;color:#6366f1}.si-amber{background:#fef3c7;color:#d97706}.si-green{background:#dcfce7;color:#16a34a}.si-red{background:#fee2e2;color:#dc2626}
.stat-value{font-size:1.5rem;font-weight:700;color:var(--text-primary);line-height:1}.stat-label{font-size:.75rem;color:var(--text-muted);margin-top:3px}
.toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap}
.toolbar-filters{display:flex;gap:8px;flex-wrap:wrap}
.filter-btn{padding:6px 14px;border:1.5px solid var(--border);border-radius:20px;background:none;font-size:.78rem;font-weight:600;color:var(--text-muted);cursor:pointer;transition:.15s}
.filter-btn:hover{border-color:var(--primary);color:var(--primary)}.filter-btn.active{background:var(--primary);border-color:var(--primary);color:#fff}
.table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow-x:auto;overflow-y:hidden}
.data-table{width:100%;border-collapse:collapse}
.data-table thead th{background:var(--surface-2);padding:11px 16px;font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);text-align:left}
.data-table tbody tr{border-top:1px solid var(--border);transition:background .1s}.data-table tbody tr:hover{background:var(--surface-2)}
.data-table td{padding:12px 16px;font-size:.87rem;color:var(--text-secondary);vertical-align:middle}
.row-pending-receive{background:#fff8e8}
.row-pending-receive:hover{background:#fef3c7 !important}
.pending-hint{display:inline-flex;align-items:center;gap:6px;margin-top:6px;padding:4px 10px;border-radius:999px;background:#fef3c7;color:#b45309;font-size:.72rem;font-weight:700}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.b-borrador{background:#f1f5f9;color:#64748b}.b-enviado{background:#dbeafe;color:#1d4ed8}.b-recibido{background:#dcfce7;color:#15803d}.b-anulado{background:#fee2e2;color:#dc2626}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;padding:16px}
.modal-overlay.active{display:flex}.modal{background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.15);overflow:hidden}
.modal-lg{max-width:980px}.modal-md{max-width:680px}
.modal-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-title{font-size:1rem;font-weight:700;color:var(--text-primary)}.modal-close{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.05rem;border-radius:6px;padding:4px}
.modal-body{padding:20px 24px;max-height:74vh;overflow-y:auto}.modal-footer{padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end}
.form-row{display:grid;gap:14px;margin-bottom:14px}.form-row.c2{grid-template-columns:1fr 1fr}.form-row.c3{grid-template-columns:1.2fr 1fr 1fr}
.form-group label{display:block;font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;background:var(--surface);border:1.5px solid var(--border);border-radius:8px;font-size:.88rem;color:var(--text-primary);outline:none;transition:border-color .15s;font-family:inherit}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--primary)}
.items-table{width:100%;border-collapse:collapse;margin-bottom:12px}.items-table th{padding:8px 10px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);text-align:left;border-bottom:1px solid var(--border)}
.items-table td{padding:6px}.items-table input{padding:7px 9px;border:1.5px solid var(--border);border-radius:7px;font-size:.83rem;color:var(--text-primary);background:var(--surface);width:100%;outline:none}
.btn-del-row{background:#fee2e2;border:none;color:#dc2626;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:.8rem}.btn-del-row:hover{background:#dc2626;color:#fff}
.search-results{display:none;position:absolute;top:100%;left:0;right:0;background:var(--surface);border:1px solid var(--border);border-radius:10px;box-shadow:0 12px 30px rgba(0,0,0,.12);max-height:230px;overflow:auto;z-index:30}
.result-item{padding:10px 12px;cursor:pointer;border-bottom:1px solid var(--border)}.result-item:last-child{border-bottom:none}.result-item:hover{background:var(--surface-2)}
.inline-actions{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
@media (max-width:900px){.form-row.c2,.form-row.c3,.detail-grid{grid-template-columns:1fr}}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Traslados de stock</h1>
        <p class="page-subtitle">Mueve productos entre sucursales de forma controlada</p>
    </div>
    <button class="btn btn-primary" onclick="abrirNuevoTraslado()">
        <i class="fas fa-plus"></i> Nuevo traslado
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon si-indigo"><i class="fas fa-file-alt"></i></div><div><div class="stat-value" id="sTotalTraslados">—</div><div class="stat-label">Traslados registrados</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon si-amber"><i class="fas fa-paper-plane"></i></div><div><div class="stat-value" id="sEnviados">—</div><div class="stat-label">Pendientes por recibir</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-check-circle"></i></div><div><div class="stat-value" id="sRecibidos">—</div><div class="stat-label">Recibidos</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon si-red"><i class="fas fa-ban"></i></div><div><div class="stat-value" id="sAnulados">—</div><div class="stat-label">Anulados</div></div></div>
    </div>
</div>

<div class="toolbar">
    <div class="toolbar-filters" id="filtrosTraslado">
        <button class="filter-btn active" onclick="filtrarEstado('', this)">Todos</button>
        <button class="filter-btn" onclick="filtrarEstado('borrador', this)">Borrador</button>
        <button class="filter-btn" onclick="filtrarEstado('enviado', this)">Enviado</button>
        <button class="filter-btn" onclick="filtrarEstado('recibido', this)">Recibido</button>
        <button class="filter-btn" onclick="filtrarEstado('anulado', this)">Anulado</button>
    </div>
    <div class="form-group" style="margin:0;min-width:260px;flex:1 1 260px;max-width:400px">
        <input type="text" id="f-q" class="form-control" placeholder="Buscar número o sucursal...">
    </div>
</div>

<div class="table-wrap table-responsive">
    <table class="data-table">
        <thead><tr>
            <th>N° Traslado</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Items</th>
            <th>Unidades</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr></thead>
        <tbody id="tbodyTraslados">
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modal-traslado">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-exchange-alt" style="color:var(--primary);margin-right:8px"></i>Nuevo traslado</div>
            <button class="modal-close" onclick="closeModal('modal-traslado')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-row c2">
                <div class="form-group">
                    <label>Sucursal origen</label>
                    <input type="text" id="t-origen" class="form-control" readonly value="<?= htmlspecialchars(sesionSucursal()) ?>">
                </div>
                <div class="form-group">
                    <label>Sucursal destino *</label>
                    <select id="t-destino" class="form-control"></select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="position:relative">
                    <label>Agregar producto</label>
                    <input type="text" id="t-producto-buscar" class="form-control" placeholder="Busca por código o nombre">
                    <div class="search-results" id="t-resultados"></div>
                </div>
            </div>
            <table class="items-table">
                <thead><tr><th>Código</th><th>Producto</th><th style="width:120px">Stock origen</th><th style="width:120px">Cantidad</th><th style="width:140px">Costo</th><th style="width:40px"></th></tr></thead>
                <tbody id="tbodyItemsTraslado">
                    <tr id="emptyItems"><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">Agrega productos para trasladar</td></tr>
                </tbody>
            </table>
            <div class="form-row">
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea id="t-observaciones" class="form-control" rows="3" placeholder="Detalle interno del traslado..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-traslado')">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarTraslado()"><i class="fas fa-save"></i> Guardar traslado</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-detalle-traslado">
    <div class="modal modal-md">
        <div class="modal-header">
            <div class="modal-title" id="detalleTitulo">Detalle del traslado</div>
            <button class="modal-close" onclick="closeModal('modal-detalle-traslado')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detalleBody">
            <div style="text-align:center;padding:32px;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
        <div class="modal-footer inline-actions" id="detalleActions"></div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
const BASE='../../';
const CURRENT_SUCURSAL_ID = <?= (int) sesionSucursalId() ?>;
let trasladoEstadoFiltro = '';
let trasladoItems = [];
let sucursales = [];
let trasladoActual = null;

function showToast(msg,type='info'){const icons={success:'check-circle',error:'exclamation-circle',info:'info-circle'};const t=document.createElement('div');t.className=`toast ${type}`;t.innerHTML=`<i class="fas fa-${icons[type]||'info-circle'}"></i> ${msg}`;document.getElementById('toast-container').appendChild(t);setTimeout(()=>t.remove(),3500);}
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function post(action,body){return fetch(BASE+'modules/traslados/api.php?action='+action,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)}).then(r=>r.json());}
function closeModal(id){document.getElementById(id).classList.remove('active');}
function openModal(id){document.getElementById(id).classList.add('active');}
function badgeEstado(e){return `b-${e}`;}
function puedeRecibir(row){return row.estado === 'enviado' && parseInt(row.sucursal_destino_id) === CURRENT_SUCURSAL_ID;}
function puedeAnular(row){return row.estado === 'enviado' && parseInt(row.sucursal_origen_id) === CURRENT_SUCURSAL_ID;}

function filtrarEstado(estado, btn){
    trasladoEstadoFiltro=estado;
    document.querySelectorAll('#filtrosTraslado .filter-btn').forEach(btn=>btn.classList.remove('active'));
    if (btn) btn.classList.add('active');
    loadTraslados();
}

async function loadSucursales(){
    const data = await fetch(BASE+'modules/traslados/api.php?action=sucursales').then(r=>r.json());
    sucursales = Array.isArray(data) ? data : [];
    const sel = document.getElementById('t-destino');
    sel.innerHTML = '<option value="">Selecciona destino</option>' + sucursales
        .filter(s => (s.activo===true || s.activo==='t') && parseInt(s.id)!==CURRENT_SUCURSAL_ID)
        .map(s => `<option value="${s.id}">${esc(s.nombre)}</option>`).join('');
}

async function loadTraslados(){
    const q = document.getElementById('f-q').value.trim();
    const data = await fetch(BASE+'modules/traslados/api.php?action=listar&estado='+encodeURIComponent(trasladoEstadoFiltro)+'&q='+encodeURIComponent(q)).then(r=>r.json());
    const tbody = document.getElementById('tbodyTraslados');
    if (data.error) { tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:30px;color:#dc2626">${esc(data.message)}</td></tr>`; return; }
    document.getElementById('sTotalTraslados').textContent = data.length;
    document.getElementById('sEnviados').textContent = data.filter(r=>puedeRecibir(r)).length;
    document.getElementById('sRecibidos').textContent = data.filter(r=>r.estado==='recibido').length;
    document.getElementById('sAnulados').textContent = data.filter(r=>r.estado==='anulado').length;
    if (!data.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">No hay traslados registrados</td></tr>'; return; }
    tbody.innerHTML = data.map(r => {
        const dt = new Date(r.created_at);
        const pendienteRecibir = puedeRecibir(r);
        const acciones = [
            `<button class="btn btn-ghost btn-sm" onclick="verDetalle(${parseInt(r.id)})" title="Ver detalle"><i class="fas fa-eye"></i></button>`
        ];
        if (pendienteRecibir) {
            acciones.unshift(`<button class="btn btn-primary btn-sm" onclick="recibirTraslado(${parseInt(r.id)})"><i class="fas fa-check"></i> Recibir</button>`);
        } else if (puedeAnular(r)) {
            acciones.unshift(`<button class="btn btn-outline btn-sm" onclick="anularTraslado(${parseInt(r.id)})">Anular</button>`);
        }
        return `<tr class="${pendienteRecibir ? 'row-pending-receive' : ''}">
            <td><strong>${esc(r.numero_traslado)}</strong></td>
            <td>${esc(r.sucursal_origen)}</td>
            <td>${esc(r.sucursal_destino)}${pendienteRecibir ? '<div class="pending-hint"><i class="fas fa-hand-holding-box"></i> Pendiente de recibir</div>' : ''}</td>
            <td>${parseInt(r.total_items||0)}</td>
            <td>${parseInt(r.total_unidades||0)}</td>
            <td><span class="badge ${badgeEstado(r.estado)}">${esc(r.estado)}</span></td>
            <td>${dt.toLocaleDateString('es-PE')}<br><small style="color:var(--text-muted)">${dt.toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit'})}</small></td>
            <td><div class="inline-actions">${acciones.join('')}</div></td>
        </tr>`;
    }).join('');
}

function abrirNuevoTraslado(){
    trasladoItems = [];
    renderItemsTraslado();
    document.getElementById('t-destino').value = '';
    document.getElementById('t-observaciones').value = '';
    document.getElementById('t-producto-buscar').value = '';
    document.getElementById('t-resultados').style.display = 'none';
    openModal('modal-traslado');
}

function renderItemsTraslado(){
    const tbody = document.getElementById('tbodyItemsTraslado');
    if (!trasladoItems.length){
        tbody.innerHTML = '<tr id="emptyItems"><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">Agrega productos para trasladar</td></tr>';
        return;
    }
    tbody.innerHTML = trasladoItems.map((item,index)=>`<tr>
        <td>${esc(item.codigo)}</td>
        <td>${esc(item.nombre)}</td>
        <td>${parseInt(item.stock||0)}</td>
        <td><input type="number" min="1" max="${parseInt(item.stock||0)}" value="${parseInt(item.cantidad||1)}" onchange="trasladoItems[${index}].cantidad=parseInt(this.value||1);"></td>
        <td><input type="number" min="0" step="0.01" value="${parseFloat(item.costo_unitario||0).toFixed(2)}" onchange="trasladoItems[${index}].costo_unitario=parseFloat(this.value||0);"></td>
        <td><button class="btn-del-row" onclick="eliminarItemTraslado(${index})"><i class="fas fa-trash"></i></button></td>
    </tr>`).join('');
}

function eliminarItemTraslado(index){trasladoItems.splice(index,1);renderItemsTraslado();}

async function buscarProductos(q){
    const box = document.getElementById('t-resultados');
    if (!q || q.length < 1){ box.style.display='none'; box.innerHTML=''; return; }
    const data = await fetch(BASE+'modules/traslados/api.php?action=buscar_producto&q='+encodeURIComponent(q)).then(r=>r.json());
    if (data.error){ box.style.display='block'; box.innerHTML=`<div class="result-item">${esc(data.message || 'Error al buscar productos')}</div>`; return; }
    if (!data.length){ box.style.display='block'; box.innerHTML='<div class="result-item">Sin resultados</div>'; return; }
    box.innerHTML = data.map(p=>`<div class="result-item" onclick='agregarProductoTraslado(${JSON.stringify(p).replace(/'/g,"&#39;")})'><strong>${esc(p.codigo)}</strong> — ${esc(p.nombre)}<br><small>Stock: ${parseInt(p.stock||0)} · Costo: S/ ${parseFloat(p.precio_compra||0).toFixed(2)}</small></div>`).join('');
    box.style.display='block';
}

function agregarProductoTraslado(producto){
    const existing = trasladoItems.findIndex(i => i.codigo === producto.codigo);
    if (existing >= 0) {
        const nextQty = parseInt(trasladoItems[existing].cantidad || 1) + 1;
        if (nextQty > parseInt(producto.stock||0)) { showToast('No hay más stock disponible en origen', 'error'); return; }
        trasladoItems[existing].cantidad = nextQty;
    } else {
        if (parseInt(producto.stock||0) <= 0) { showToast('El producto no tiene stock disponible', 'error'); return; }
        trasladoItems.push({
            producto_id: producto.id,
            codigo: producto.codigo,
            nombre: producto.nombre,
            stock: parseInt(producto.stock||0),
            cantidad: 1,
            costo_unitario: parseFloat(producto.precio_compra||0)
        });
    }
    document.getElementById('t-producto-buscar').value='';
    document.getElementById('t-resultados').style.display='none';
    renderItemsTraslado();
}

async function guardarTraslado(){
    const destino = document.getElementById('t-destino').value;
    const observaciones = document.getElementById('t-observaciones').value.trim();
    if (!destino) { showToast('Selecciona la sucursal destino', 'error'); return; }
    if (!trasladoItems.length) { showToast('Agrega al menos un producto', 'error'); return; }
    for (const item of trasladoItems) {
        if (!item.cantidad || item.cantidad <= 0) { showToast('Todas las cantidades deben ser válidas', 'error'); return; }
        if (item.cantidad > item.stock) { showToast(`La cantidad de ${item.nombre} supera el stock disponible`, 'error'); return; }
    }
    const res = await post('crear', {
        sucursal_destino_id: parseInt(destino),
        observaciones,
        items: trasladoItems
    });
    if (res.error) { showToast(res.message, 'error'); return; }
    showToast(res.message, 'success');
    closeModal('modal-traslado');
    loadTraslados();
}

async function verDetalle(id){
    openModal('modal-detalle-traslado');
    document.getElementById('detalleBody').innerHTML = '<div style="text-align:center;padding:32px;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i></div>';
    document.getElementById('detalleActions').innerHTML = '';
    const data = await fetch(BASE+'modules/traslados/api.php?action=detalle&id='+id).then(r=>r.json());
    if (data.error) { document.getElementById('detalleBody').innerHTML = `<div style="color:#dc2626">${esc(data.message)}</div>`; return; }
    trasladoActual = data;
    document.getElementById('detalleTitulo').textContent = `Traslado ${data.numero_traslado}`;
    document.getElementById('detalleBody').innerHTML = `
        <div class="detail-grid">
            <div><strong>Origen:</strong><br>${esc(data.sucursal_origen)}</div>
            <div><strong>Destino:</strong><br>${esc(data.sucursal_destino)}</div>
            <div><strong>Estado:</strong><br><span class="badge ${badgeEstado(data.estado)}">${esc(data.estado)}</span></div>
            <div><strong>Solicitado:</strong><br>${data.usuario_solicita_nombre ? esc(data.usuario_solicita_nombre) : '—'}</div>
        </div>
        <div style="margin-bottom:12px"><strong>Observaciones:</strong><br>${esc(data.observaciones || 'Sin observaciones')}</div>
        <table class="items-table">
            <thead><tr><th>Código</th><th>Producto</th><th>Cantidad</th><th>Stock origen</th><th>Stock destino</th></tr></thead>
            <tbody>
                ${(data.items||[]).map(item=>`<tr><td>${esc(item.producto_codigo)}</td><td>${esc(item.producto_nombre)}</td><td>${parseInt(item.cantidad)}</td><td>${parseInt(item.stock_origen_snapshot||0)}</td><td>${parseInt(item.stock_destino_snapshot||0)}</td></tr>`).join('')}
            </tbody>
        </table>`;

    const actions = [];
    if (data.estado === 'borrador' && parseInt(data.sucursal_origen_id) === CURRENT_SUCURSAL_ID) {
        actions.push(`<button class="btn btn-primary" onclick="enviarTraslado(${parseInt(data.id)})"><i class="fas fa-paper-plane"></i> Enviar</button>`);
        actions.push(`<button class="btn btn-outline" onclick="anularTraslado(${parseInt(data.id)})">Anular</button>`);
    }
    if (data.estado === 'enviado' && parseInt(data.sucursal_destino_id) === CURRENT_SUCURSAL_ID) {
        actions.push(`<button class="btn btn-primary" onclick="recibirTraslado(${parseInt(data.id)})"><i class="fas fa-check"></i> Recibir</button>`);
    }
    if (data.estado === 'enviado' && parseInt(data.sucursal_origen_id) === CURRENT_SUCURSAL_ID) {
        actions.push(`<button class="btn btn-outline" onclick="anularTraslado(${parseInt(data.id)})">Anular</button>`);
    }
    actions.push(`<button class="btn btn-ghost" onclick="closeModal('modal-detalle-traslado')">Cerrar</button>`);
    document.getElementById('detalleActions').innerHTML = actions.join('');
}

async function enviarTraslado(id){
    if (!confirm('¿Enviar este traslado? Se descontará el stock de la sucursal origen.')) return;
    const res = await post('enviar', {id});
    if (res.error) { showToast(res.message, 'error'); return; }
    showToast(res.message, 'success');
    closeModal('modal-detalle-traslado');
    loadTraslados();
}

async function recibirTraslado(id){
    if (!confirm('¿Recibir este traslado? Se sumará el stock en la sucursal destino.')) return;
    const res = await post('recibir', {id});
    if (res.error) { showToast(res.message, 'error'); return; }
    showToast(res.message, 'success');
    closeModal('modal-detalle-traslado');
    loadTraslados();
}

async function anularTraslado(id){
    if (!confirm('¿Anular este traslado?')) return;
    const res = await post('anular', {id});
    if (res.error) { showToast(res.message, 'error'); return; }
    showToast(res.message, 'success');
    closeModal('modal-detalle-traslado');
    loadTraslados();
}

document.getElementById('f-q').addEventListener('keyup', e => { if (e.key === 'Enter') loadTraslados(); });
document.getElementById('t-producto-buscar').addEventListener('input', e => buscarProductos(e.target.value.trim()));
document.addEventListener('click', (e) => {
    if (!document.getElementById('t-resultados').contains(e.target) && e.target.id !== 't-producto-buscar') {
        document.getElementById('t-resultados').style.display='none';
    }
});
document.querySelectorAll('.modal-overlay').forEach(overlay => overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(overlay.id); }));

loadSucursales().then(loadTraslados);
</script>

<?php require_once '../../includes/footer.php'; ?>
