<?php
require_once '../../config/database.php';

$base_path      = '../../';
$required_roles = ['admin'];
$current_module = 'clientes';
$current_page   = 'clientes';
$page_title     = 'Clientes — FarmaSystem';
$breadcrumb     = '<i class="fas fa-users"></i> Mis Clientes';

include '../../includes/header.php';
?>

<style>
/* ---- Stats ---- */
.stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px; }
.stat-card  { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:18px 20px;
              display:flex; align-items:center; gap:14px; }
.stat-icon  { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.si-blue   { background:#dbeafe; color:#2563eb; }
.si-green  { background:#dcfce7; color:#16a34a; }
.si-indigo { background:#eef2ff; color:#6366f1; }
.si-amber  { background:#fef3c7; color:#d97706; }
.stat-val  { font-size:1.45rem; font-weight:700; color:var(--text-primary); line-height:1; }
.stat-lbl  { font-size:.74rem; color:var(--text-muted); margin-top:3px; }

/* ---- Toolbar ---- */
.toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
.search-wrap { position:relative; flex:1; min-width:220px; max-width:380px; }
.search-wrap input { width:100%; padding:9px 12px 9px 36px; border:1.5px solid var(--border); border-radius:8px;
                     background:var(--surface); color:var(--text-primary); font-size:.87rem; outline:none; transition:border-color .15s; }
.search-wrap input:focus { border-color:var(--primary); }
.search-wrap i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:.85rem; pointer-events:none; }
.filter-pills { display:flex; gap:6px; }
.pill { padding:6px 14px; border:1.5px solid var(--border); border-radius:20px; background:none;
        font-size:.78rem; font-weight:600; color:var(--text-muted); cursor:pointer; transition:.15s; }
.pill:hover  { border-color:var(--primary); color:var(--primary); }
.pill.active { background:var(--primary); border-color:var(--primary); color:#fff; }

/* ---- Tabla ---- */
.table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.data-table { width:100%; border-collapse:collapse; }
.data-table thead th { background:var(--surface-2); padding:11px 16px; font-size:.72rem; font-weight:700;
                       text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); text-align:left; white-space:nowrap; }
.data-table tbody tr { border-top:1px solid var(--border); transition:background .1s; }
.data-table tbody tr:hover { background:var(--surface-2); }
.data-table td { padding:11px 16px; font-size:.86rem; color:var(--text-secondary); vertical-align:middle; }
.td-nombre { font-weight:600; color:var(--text-primary); }
.td-sub    { font-size:.75rem; color:var(--text-muted); margin-top:2px; }

/* ---- Avatar inicial ---- */
.avatar { width:34px; height:34px; border-radius:50%; background:var(--primary); color:#fff;
          font-size:.85rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* ---- Badges ---- */
.badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; text-transform:uppercase; }
.b-activo   { background:#dcfce7; color:#15803d; }
.b-inactivo { background:#f1f5f9; color:#64748b; }
.b-completada { background:#dcfce7; color:#15803d; }
.b-anulada    { background:#fee2e2; color:#dc2626; }
.b-pendiente  { background:#fef3c7; color:#b45309; }

/* ---- Modal ---- */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:500;
                 align-items:center; justify-content:center; padding:16px; }
.modal-overlay.active { display:flex; }
.modal { background:var(--surface); border:1px solid var(--border); border-radius:16px; width:100%;
         box-shadow:0 20px 60px rgba(0,0,0,.15); animation:modalIn .18s ease; overflow:hidden; }
.modal-sm { max-width:460px; }
.modal-lg { max-width:720px; }
@keyframes modalIn { from{opacity:0;transform:translateY(-12px) scale(.97)} to{opacity:1;transform:none} }
.modal-header { padding:18px 24px; border-bottom:1px solid var(--border);
                display:flex; align-items:center; justify-content:space-between; }
.modal-title  { font-size:.98rem; font-weight:700; color:var(--text-primary); }
.modal-close  { background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1rem;
                border-radius:6px; padding:4px 6px; transition:color .15s; }
.modal-close:hover { color:var(--text-primary); }
.modal-body   { padding:20px 24px; max-height:72vh; overflow-y:auto; }
.modal-footer { padding:14px 24px; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:flex-end; }

/* ---- Form ---- */
.form-row    { display:grid; gap:14px; margin-bottom:14px; }
.form-row.c2 { grid-template-columns:1fr 1fr; }
.form-group label { display:block; font-size:.74rem; font-weight:600; color:var(--text-muted);
                    text-transform:uppercase; letter-spacing:.04em; margin-bottom:5px; }
.form-group input, .form-group textarea {
    width:100%; padding:9px 12px; background:var(--surface); border:1.5px solid var(--border);
    border-radius:8px; font-size:.88rem; color:var(--text-primary); outline:none;
    transition:border-color .15s; font-family:inherit; box-sizing:border-box; }
.form-group input:focus, .form-group textarea:focus { border-color:var(--primary); }
.form-group textarea { resize:vertical; min-height:64px; }

/* ---- Historial ---- */
.hist-meta { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:18px; }
.hist-meta-item { background:var(--surface-2); border-radius:8px; padding:12px 14px; }
.hist-meta-item .label { font-size:.72rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:3px; }
.hist-meta-item .value { font-size:1rem; font-weight:700; color:var(--text-primary); }

/* ---- Empty ---- */
.empty-state { padding:56px 20px; text-align:center; color:var(--text-muted); }
.empty-state i { font-size:2.4rem; display:block; margin-bottom:12px; }

/* ---- Btn sizes ---- */
.btn-sm { padding:6px 12px; font-size:.78rem; }
.btn-icon { padding:6px 9px; }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Mis Clientes</h1>
        <p class="page-subtitle">Gestiona tus clientes y consulta su historial de compras</p>
    </div>
    <button class="btn btn-primary" onclick="abrirModal()">
        <i class="fas fa-user-plus"></i> Nuevo Cliente
    </button>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-users"></i></div>
        <div><div class="stat-val" id="sTotal">—</div><div class="stat-lbl">Total registrados</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-user-check"></i></div>
        <div><div class="stat-val" id="sActivos">—</div><div class="stat-lbl">Clientes activos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-indigo"><i class="fas fa-user-plus"></i></div>
        <div><div class="stat-val" id="sNuevos">—</div><div class="stat-lbl">Nuevos este mes</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-amber"><i class="fas fa-receipt"></i></div>
        <div><div class="stat-val" id="sFacturado">—</div><div class="stat-lbl">Total facturado</div></div>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="inputBuscar" placeholder="Nombre, DNI, RUC, teléfono…" oninput="debouncarBusqueda()">
        </div>
        <div class="filter-pills">
            <button class="pill active" onclick="filtrar('',this)">Todos</button>
            <button class="pill" onclick="filtrar('activo',this)">Activos</button>
            <button class="pill" onclick="filtrar('inactivo',this)">Inactivos</button>
        </div>
    </div>
    <span id="contadorLabel" style="font-size:.8rem;color:var(--text-muted)"></span>
</div>

<!-- Tabla -->
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Documento</th>
                <th>Contacto</th>
                <th style="text-align:center">Compras</th>
                <th style="text-align:right">Total gastado</th>
                <th>Última compra</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="tbodyClientes">
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </td></tr>
        </tbody>
    </table>
</div>

<!-- ====================================================
     MODAL: Crear / Editar cliente
==================================================== -->
<div class="modal-overlay" id="overlayForm">
<div class="modal modal-sm">
    <div class="modal-header">
        <span class="modal-title" id="modalFormTitulo"><i class="fas fa-user-plus" style="color:var(--primary);margin-right:8px"></i>Nuevo Cliente</span>
        <button class="modal-close" onclick="cerrarModal('overlayForm')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="fId">
        <div class="form-row c2">
            <div class="form-group">
                <label>Nombres *</label>
                <input type="text" id="fNombres" placeholder="Juan">
            </div>
            <div class="form-group">
                <label>Apellidos</label>
                <input type="text" id="fApellidos" placeholder="Pérez García">
            </div>
        </div>
        <div class="form-row c2">
            <div class="form-group">
                <label>DNI</label>
                <input type="text" id="fDni" maxlength="20" placeholder="12345678">
            </div>
            <div class="form-group">
                <label>RUC</label>
                <input type="text" id="fRuc" maxlength="20" placeholder="20512345678">
            </div>
        </div>
        <div class="form-row c2">
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" id="fTelefono" maxlength="20" placeholder="987 654 321">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="fEmail" placeholder="correo@ejemplo.com">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Dirección</label>
                <textarea id="fDireccion" placeholder="Av. Principal 123, Lima"></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" onclick="cerrarModal('overlayForm')">Cancelar</button>
        <button class="btn btn-primary" onclick="guardarCliente()"><i class="fas fa-save"></i> Guardar</button>
    </div>
</div>
</div>

<!-- ====================================================
     MODAL: Historial de compras
==================================================== -->
<div class="modal-overlay" id="overlayHistorial">
<div class="modal modal-lg">
    <div class="modal-header">
        <span class="modal-title" id="histTitulo"><i class="fas fa-history" style="color:var(--primary);margin-right:8px"></i>Historial de compras</span>
        <button class="modal-close" onclick="cerrarModal('overlayHistorial')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="histBody">
        <div style="text-align:center;padding:40px;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" onclick="cerrarModal('overlayHistorial')">Cerrar</button>
    </div>
</div>
</div>

<script>
const API   = '<?= $base_path ?>modules/clientes/api.php';
let _estado = '';
let _debounce;

// ----------------------------------------------------------------
// Init
// ----------------------------------------------------------------
cargarStats();
cargarClientes();

// ----------------------------------------------------------------
// Stats
// ----------------------------------------------------------------
async function cargarStats() {
    try {
        const r = await fetch(`${API}?action=stats`);
        const d = await r.json();
        document.getElementById('sTotal').textContent     = d.total ?? '—';
        document.getElementById('sActivos').textContent   = d.activos ?? '—';
        document.getElementById('sNuevos').textContent    = d.nuevos_mes ?? '—';
        document.getElementById('sFacturado').textContent = 'S/ ' + parseFloat(d.total_facturado || 0).toFixed(2);
    } catch {}
}

// ----------------------------------------------------------------
// Listar
// ----------------------------------------------------------------
function debouncarBusqueda() {
    clearTimeout(_debounce);
    _debounce = setTimeout(cargarClientes, 280);
}

function filtrar(estado, btn) {
    _estado = estado;
    document.querySelectorAll('.filter-pills .pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    cargarClientes();
}

async function cargarClientes() {
    const tbody = document.getElementById('tbodyClientes');
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i></td></tr>';
    const q = document.getElementById('inputBuscar').value.trim();
    try {
        const r    = await fetch(`${API}?action=listar&q=${encodeURIComponent(q)}&estado=${_estado}`);
        const data = await r.json();
        if (data.error) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> ${esc(data.message)}</td></tr>`;
            return;
        }
        document.getElementById('contadorLabel').textContent = data.length + ' cliente' + (data.length !== 1 ? 's' : '');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><i class="fas fa-users"></i>No se encontraron clientes</div></td></tr>';
            return;
        }
        tbody.innerHTML = data.map(c => {
            const nombre  = esc(c.nombres) + (c.apellidos ? ' ' + esc(c.apellidos) : '');
            const inicial = (c.nombres[0] || 'C').toUpperCase();
            const doc     = c.dni ? `<div>DNI: ${esc(c.dni)}</div>` : '';
            const ruc     = c.ruc ? `<div style="font-size:.75rem;color:var(--text-muted)">RUC: ${esc(c.ruc)}</div>` : '';
            const tel     = c.telefono ? `<div><i class="fas fa-phone" style="font-size:.7rem;color:var(--text-muted);margin-right:3px"></i>${esc(c.telefono)}</div>` : '';
            const email   = c.email ? `<div style="font-size:.75rem;color:var(--text-muted)"><i class="fas fa-envelope" style="font-size:.65rem;margin-right:3px"></i>${esc(c.email)}</div>` : '';
            const activo  = c.activo === true || c.activo === 't' || c.activo === '1';
            return `
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="avatar" style="background:${avatarColor(c.id)}">${inicial}</div>
                        <div>
                            <div class="td-nombre">${nombre}</div>
                            ${c.created_at ? `<div class="td-sub">Desde ${fmtDate(c.created_at)}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td>${doc}${ruc}</td>
                <td>${tel}${email}${!tel && !email ? '<span style="color:var(--text-muted);font-size:.8rem">—</span>' : ''}</td>
                <td style="text-align:center;font-weight:600">${c.total_compras ?? 0}</td>
                <td style="text-align:right;font-weight:600">S/ ${parseFloat(c.total_gastado || 0).toFixed(2)}</td>
                <td style="font-size:.8rem;color:var(--text-muted)">${c.ultima_compra ? fmtDate(c.ultima_compra) : '—'}</td>
                <td><span class="badge ${activo ? 'b-activo' : 'b-inactivo'}">${activo ? 'Activo' : 'Inactivo'}</span></td>
                <td>
                    <div style="display:flex;gap:5px;justify-content:flex-end">
                        <button class="btn btn-secondary btn-sm btn-icon" title="Historial de compras" onclick="verHistorial(${c.id})">
                            <i class="fas fa-history"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm btn-icon" title="Editar" onclick='abrirModal(${JSON.stringify(c)})'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm btn-icon" title="${activo ? 'Desactivar' : 'Activar'}"
                                style="color:${activo ? '#dc2626' : '#16a34a'}"
                                onclick="toggleActivo(${c.id},'${nombre}',${activo})">
                            <i class="fas fa-${activo ? 'user-slash' : 'user-check'}"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    } catch { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;color:#dc2626">Error al cargar clientes</td></tr>'; }
}

// ----------------------------------------------------------------
// Modal Form
// ----------------------------------------------------------------
function abrirModal(cliente = null) {
    const edit = cliente && cliente.id;
    document.getElementById('modalFormTitulo').innerHTML = edit
        ? '<i class="fas fa-user-edit" style="color:var(--primary);margin-right:8px"></i>Editar Cliente'
        : '<i class="fas fa-user-plus" style="color:var(--primary);margin-right:8px"></i>Nuevo Cliente';
    document.getElementById('fId').value        = edit ? cliente.id : '';
    document.getElementById('fNombres').value   = cliente?.nombres    ?? '';
    document.getElementById('fApellidos').value = cliente?.apellidos  ?? '';
    document.getElementById('fDni').value       = cliente?.dni        ?? '';
    document.getElementById('fRuc').value       = cliente?.ruc        ?? '';
    document.getElementById('fTelefono').value  = cliente?.telefono   ?? '';
    document.getElementById('fEmail').value     = cliente?.email      ?? '';
    document.getElementById('fDireccion').value = cliente?.direccion  ?? '';
    document.getElementById('overlayForm').classList.add('active');
    setTimeout(() => document.getElementById('fNombres').focus(), 80);
}

async function guardarCliente() {
    const id      = document.getElementById('fId').value;
    const nombres = document.getElementById('fNombres').value.trim();
    if (!nombres) { toast('El nombre es obligatorio', 'err'); return; }

    const payload = {
        id:        id ? parseInt(id) : undefined,
        nombres,
        apellidos: document.getElementById('fApellidos').value.trim(),
        dni:       document.getElementById('fDni').value.trim(),
        ruc:       document.getElementById('fRuc').value.trim(),
        telefono:  document.getElementById('fTelefono').value.trim(),
        email:     document.getElementById('fEmail').value.trim(),
        direccion: document.getElementById('fDireccion').value.trim(),
    };
    const action = id ? 'actualizar' : 'crear';
    try {
        const r = await fetch(`${API}?action=${action}`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        toast(d.message);
        cerrarModal('overlayForm');
        cargarClientes();
        cargarStats();
    } catch { toast('Error de conexión', 'err'); }
}

// ----------------------------------------------------------------
// Toggle activo
// ----------------------------------------------------------------
async function toggleActivo(id, nombre, activo) {
    const accion = activo ? 'desactivar' : 'activar';
    if (!confirm(`¿Deseas ${accion} a ${nombre}?`)) return;
    try {
        const r = await fetch(`${API}?action=toggle_activo`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        toast(d.message);
        cargarClientes();
        cargarStats();
    } catch { toast('Error de conexión', 'err'); }
}

// ----------------------------------------------------------------
// Historial
// ----------------------------------------------------------------
async function verHistorial(id) {
    document.getElementById('histTitulo').innerHTML = '<i class="fas fa-history" style="color:var(--primary);margin-right:8px"></i>Historial de compras';
    document.getElementById('histBody').innerHTML   = '<div style="text-align:center;padding:40px;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i></div>';
    document.getElementById('overlayHistorial').classList.add('active');
    try {
        const r = await fetch(`${API}?action=historial&id=${id}`);
        const c = await r.json();
        if (c.error) {
            document.getElementById('histBody').innerHTML = `<p style="color:#dc2626;text-align:center">${esc(c.message)}</p>`;
            return;
        }
        const nombre = esc(c.nombres) + (c.apellidos ? ' ' + esc(c.apellidos) : '');
        document.getElementById('histTitulo').innerHTML = `<i class="fas fa-history" style="color:var(--primary);margin-right:8px"></i>${nombre}`;

        const ventas      = c.ventas || [];
        const completadas = ventas.filter(v => v.estado === 'completada');
        const totalGastado = completadas.reduce((s, v) => s + parseFloat(v.total), 0);

        const metodos = { efectivo:'Efectivo', yape:'Yape', plin:'Plin', tarjeta:'Tarjeta', transferencia:'Transf.' };

        document.getElementById('histBody').innerHTML = `
            <div class="hist-meta">
                <div class="hist-meta-item">
                    <div class="label">Compras completadas</div>
                    <div class="value">${completadas.length}</div>
                </div>
                <div class="hist-meta-item">
                    <div class="label">Total gastado</div>
                    <div class="value">S/ ${totalGastado.toFixed(2)}</div>
                </div>
                ${c.dni  ? `<div class="hist-meta-item"><div class="label">DNI</div><div class="value">${esc(c.dni)}</div></div>`  : ''}
                ${c.ruc  ? `<div class="hist-meta-item"><div class="label">RUC</div><div class="value">${esc(c.ruc)}</div></div>`  : ''}
                ${c.telefono ? `<div class="hist-meta-item"><div class="label">Teléfono</div><div class="value">${esc(c.telefono)}</div></div>` : ''}
                ${c.email    ? `<div class="hist-meta-item"><div class="label">Email</div><div class="value" style="font-size:.85rem">${esc(c.email)}</div></div>` : ''}
            </div>
            ${ventas.length ? `
            <table class="data-table" style="font-size:.84rem">
                <thead><tr>
                    <th>N° Venta</th>
                    <th>Fecha</th>
                    <th style="text-align:center">Ítems</th>
                    <th>Método</th>
                    <th style="text-align:right">Total</th>
                    <th>Estado</th>
                </tr></thead>
                <tbody>
                ${ventas.map(v => `
                    <tr style="border-top:1px solid var(--border)">
                        <td style="font-weight:600;color:var(--text-primary)">${esc(v.numero_venta)}</td>
                        <td style="color:var(--text-muted)">${fmtDate(v.created_at)}</td>
                        <td style="text-align:center">${v.num_items}</td>
                        <td>${esc(metodos[v.metodo_pago] ?? v.metodo_pago ?? '—')}</td>
                        <td style="text-align:right;font-weight:600">S/ ${parseFloat(v.total).toFixed(2)}</td>
                        <td><span class="badge b-${v.estado}">${v.estado.charAt(0).toUpperCase() + v.estado.slice(1)}</span></td>
                    </tr>`).join('')}
                </tbody>
            </table>` : '<div class="empty-state"><i class="fas fa-receipt"></i>Este cliente aún no tiene compras registradas</div>'}
        `;
    } catch { document.getElementById('histBody').innerHTML = '<p style="color:#dc2626;text-align:center;padding:20px">Error al cargar historial</p>'; }
}

// ----------------------------------------------------------------
// Utilidades
// ----------------------------------------------------------------
function cerrarModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); });
});

function fmtDate(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString('es-PE', { day:'2-digit', month:'short', year:'numeric' });
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function toast(msg, tipo = 'ok') {
    const el = document.getElementById('app-toast') || (() => {
        const t = document.createElement('div');
        t.id = 'app-toast';
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 18px;font-size:.85rem;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);z-index:9999;opacity:0;transform:translateY(8px);transition:opacity .25s,transform .25s;pointer-events:none;max-width:320px';
        document.body.appendChild(t);
        return t;
    })();
    el.style.borderColor = tipo === 'ok' ? '#86efac' : '#fca5a5';
    el.innerHTML = `<i class="fas fa-${tipo === 'ok' ? 'check' : 'exclamation'}-circle" style="color:${tipo === 'ok' ? '#16a34a' : '#dc2626'}"></i><span>${esc(msg)}</span>`;
    el.style.opacity = '1'; el.style.transform = 'none';
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(8px)'; }, 3500);
}

const AVATAR_COLORS = ['#6366f1','#8b5cf6','#ec4899','#0ea5e9','#14b8a6','#f59e0b','#10b981','#ef4444'];
function avatarColor(id) { return AVATAR_COLORS[id % AVATAR_COLORS.length]; }
</script>

<?php include '../../includes/footer.php'; ?>
