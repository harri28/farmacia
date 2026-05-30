<?php
// ============================================================
// ARCHIVO: farmacia/modules/admin/index.php
// MÓDULO:  Administración — Usuarios y Sucursales
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$current_module = 'admin';
$current_page   = 'admin';
$page_title     = 'Administración — FarmaSystem';
$breadcrumb     = '<strong>Administración</strong>';
$required_roles = ['admin'];

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">
            <i class="fas fa-cogs" style="color:var(--primary);margin-right:8px"></i>Administración
        </div>
        <div class="page-subtitle">Gestión de usuarios, roles y sucursales</div>
    </div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--border);padding-bottom:0">
    <button class="tab-btn active" id="tab-usuarios" onclick="switchTab('usuarios')">
        <i class="fas fa-users"></i> Usuarios
    </button>
    <button class="tab-btn" id="tab-sucursales" onclick="switchTab('sucursales')">
        <i class="fas fa-store"></i> Sucursales
    </button>
    <button class="tab-btn" id="tab-configuracion" onclick="switchTab('configuracion')">
        <i class="fas fa-paint-brush"></i> Configuración
    </button>
</div>

<!-- ======================================================
     TAB: USUARIOS
     ====================================================== -->
<div id="pane-usuarios">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Usuarios del sistema</div>
            <button class="btn btn-primary btn-sm" style="margin-left:auto" onclick="abrirModalUsuario()">
                <i class="fas fa-plus"></i> Nuevo usuario
            </button>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Username</th>
                        <th>Accesos / Roles</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-usuarios">
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================
     TAB: SUCURSALES
     ====================================================== -->
<div id="pane-sucursales" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Sucursales registradas</div>
            <button class="btn btn-primary btn-sm" style="margin-left:auto" onclick="abrirModalSucursal()">
                <i class="fas fa-plus"></i> Nueva sucursal
            </button>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Schema</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-sucursales">
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================
     MODAL: Usuario
     ====================================================== -->
<div class="modal-overlay" id="modal-usuario">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-usuario-titulo">Nuevo usuario</h3>
            <button class="modal-close" onclick="closeModal('modal-usuario')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="u-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" id="u-nombre" class="form-control" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido</label>
                    <input type="text" id="u-apellido" class="form-control" placeholder="Apellido">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre de usuario *</label>
                <input type="text" id="u-username" class="form-control" placeholder="usuario123" autocomplete="off">
            </div>
            <div class="form-group" id="row-password">
                <label class="form-label">Contraseña *</label>
                <div class="input-group">
                    <input type="password" id="u-password" class="form-control" placeholder="Mínimo 4 caracteres" autocomplete="new-password">
                </div>
                <small id="lbl-password-opcional" style="color:var(--text-muted);font-size:.76rem;display:none">Dejar vacío para no cambiar la contraseña</small>
            </div>
            <hr style="border:none;border-top:1px solid var(--border);margin:16px 0">
            <div style="font-size:.82rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04rem;margin-bottom:12px">
                Acceso a sucursal
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group" style="margin:0">
                    <label class="form-label">Sucursal</label>
                    <select id="u-sucursal" class="form-control">
                        <option value="">— Sin asignar —</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label class="form-label">Rol</label>
                    <select id="u-rol" class="form-control">
                        <option value="cajero">Cajero</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            <div style="font-size:.76rem;color:var(--text-muted);margin-top:8px">
                <i class="fas fa-info-circle"></i> Puedes agregar más accesos desde el menú de acciones del usuario.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-usuario')">Cancelar</button>
            <button class="btn btn-primary" id="btn-guardar-usuario" onclick="guardarUsuario()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Cambiar contraseña -->
<div class="modal-overlay" id="modal-password">
    <div class="modal" style="max-width:380px">
        <div class="modal-header">
            <h3 class="modal-title">Cambiar contraseña</h3>
            <button class="modal-close" onclick="closeModal('modal-password')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cp-id">
            <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:16px">
                Usuario: <strong id="cp-username"></strong>
            </p>
            <div class="form-group">
                <label class="form-label">Nueva contraseña *</label>
                <input type="password" id="cp-password" class="form-control" placeholder="Mínimo 4 caracteres">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-password')">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarPassword()">
                <i class="fas fa-key"></i> Actualizar
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Asignar acceso adicional -->
<div class="modal-overlay" id="modal-acceso">
    <div class="modal" style="max-width:400px">
        <div class="modal-header">
            <h3 class="modal-title">Asignar acceso a sucursal</h3>
            <button class="modal-close" onclick="closeModal('modal-acceso')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ac-usuario-id">
            <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:16px">
                Usuario: <strong id="ac-username"></strong>
            </p>
            <div class="form-group">
                <label class="form-label">Sucursal</label>
                <select id="ac-sucursal" class="form-control"></select>
            </div>
            <div class="form-group">
                <label class="form-label">Rol</label>
                <select id="ac-rol" class="form-control">
                    <option value="cajero">Cajero</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-acceso')">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarAcceso()">
                <i class="fas fa-plus"></i> Asignar
            </button>
        </div>
    </div>
</div>

<!-- ======================================================
     MODAL: Sucursal
     ====================================================== -->
<div class="modal-overlay" id="modal-sucursal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-sucursal-titulo">Nueva sucursal</h3>
            <button class="modal-close" onclick="closeModal('modal-sucursal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="s-id">
            <div class="form-group">
                <label class="form-label">Nombre de la sucursal *</label>
                <input type="text" id="s-nombre" class="form-control" placeholder="Ej: Farmacia Central">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" id="s-telefono" class="form-control" placeholder="01-123-4567">
                </div>
                <div class="form-group">
                    <!-- Placeholder para mantener el grid -->
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Dirección</label>
                <input type="text" id="s-direccion" class="form-control" placeholder="Av. Principal 123, Lima">
            </div>
            <div id="row-schema-info" style="display:none;background:var(--surface-2);border-radius:8px;padding:10px 14px;font-size:.8rem;color:var(--text-muted)">
                <i class="fas fa-database" style="color:var(--primary)"></i>
                Schema: <code id="s-schema-display" style="font-weight:600;color:var(--primary)"></code>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-sucursal')">Cancelar</button>
            <button class="btn btn-primary" id="btn-guardar-sucursal" onclick="guardarSucursal()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- ======================================================
     TAB: CONFIGURACIÓN DE MARCA
     ====================================================== -->
<div id="pane-configuracion" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-paint-brush" style="color:var(--primary);margin-right:8px"></i>Configuración de marca
            </div>
        </div>
        <div style="padding:24px;max-width:520px">

            <!-- Nombre -->
            <div class="form-group">
                <label class="form-label">Nombre del sistema *</label>
                <input type="text" id="cfg-nombre" class="form-control"
                       placeholder="Ej: Farmacia San Juan" maxlength="100">
                <small style="color:var(--text-muted);font-size:.76rem">
                    Aparece en la barra lateral y el título del navegador.
                </small>
            </div>

            <!-- Logo -->
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Logo de la empresa</label>

                <!-- Vista previa centrada -->
                <div style="display:flex;align-items:center;justify-content:center;
                            padding:20px;border:2px dashed var(--border);border-radius:12px;
                            background:var(--surface-2);margin-bottom:12px">
                    <div id="logo-preview-wrap"
                         style="width:160px;height:80px;display:flex;align-items:center;justify-content:center;overflow:hidden">
                        <i class="fas fa-pills" id="logo-placeholder" style="font-size:2.4rem;color:var(--text-light)"></i>
                        <img id="logo-img" src="" alt="Logo"
                             style="max-width:160px;max-height:80px;object-fit:contain;display:none">
                    </div>
                </div>

                <!-- Selector de archivo + eliminar -->
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <label for="logo-file-input" class="btn btn-outline btn-sm" style="cursor:pointer;margin:0">
                        <i class="fas fa-folder-open"></i> Elegir imagen
                    </label>
                    <input type="file" id="logo-file-input"
                           accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                           style="display:none" onchange="previewLogoLocal(this)">
                    <button class="btn btn-outline btn-sm" id="btn-eliminar-logo" onclick="eliminarLogo()"
                            style="display:none;color:#dc2626;border-color:#dc2626">
                        <i class="fas fa-trash"></i> Eliminar logo
                    </button>
                    <span id="logo-file-name" style="font-size:.82rem;color:var(--text-muted)">Ningún archivo seleccionado</span>
                </div>
                <small style="color:var(--text-muted);font-size:.76rem;margin-top:6px;display:block">
                    JPG, PNG, GIF, WEBP o SVG — máx. 2 MB.
                </small>
            </div>

            <!-- Botón único -->
            <div style="border-top:1px solid var(--border);margin-top:24px;padding-top:20px;display:flex;justify-content:flex-end">
                <button class="btn btn-primary" id="btn-guardar-config" onclick="guardarConfig()">
                    <i class="fas fa-save"></i> Guardar y Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<style>
.tab-btn {
    padding: 10px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: .88rem;
    font-weight: 600;
    color: var(--text-muted);
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: .15s;
}
.tab-btn:hover { color: var(--primary); }
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }

.badge-rol-admin  { background:#eef2ff; color:#4f46e5; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }
.badge-rol-cajero { background:#f0fdf4; color:#16a34a; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }
</style>

<script>
const BASE = '../../';
let sucursalesList = [];

// ---- Tabs ----
function switchTab(tab) {
    ['usuarios', 'sucursales', 'configuracion'].forEach(t => {
        document.getElementById('pane-' + t).style.display = t === tab ? '' : 'none';
        document.getElementById('tab-' + t).classList.toggle('active', t === tab);
    });
    if (tab === 'sucursales')    loadSucursales();
    if (tab === 'configuracion') loadConfig();
}

// ================================================================
// USUARIOS
// ================================================================

function loadUsuarios() {
    fetch(BASE + 'modules/admin/api.php?action=usuarios_listar')
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                document.getElementById('tbody-usuarios').innerHTML =
                    '<tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-users"></i><br><br>No hay usuarios registrados</td></tr>';
                return;
            }
            document.getElementById('tbody-usuarios').innerHTML = data.map(u => {
                const accesos = (typeof u.accesos === 'string' ? JSON.parse(u.accesos) : u.accesos) || [];
                const accBadges = accesos.map(a =>
                    `<span class="badge badge-rol-${a.rol === 'admin' ? 'admin' : 'cajero'}" style="margin-right:4px;${!a.activo?'opacity:.4':''}">
                        ${a.sucursal} · ${a.rol === 'admin' ? 'Admin' : 'Cajero'}
                        ${a.activo ? `<i class="fas fa-times" style="cursor:pointer;margin-left:4px" onclick="revocarAcceso(${u.id},'${escH(u.username)}',${a.sucursal_id})" title="Revocar"></i>` : ''}
                     </span>`
                ).join('') || '<span style="color:var(--text-light);font-size:.8rem">Sin acceso</span>';

                return `<tr>
                    <td>
                        <div style="font-weight:600">${escH(u.nombre)} ${escH(u.apellido||'')}</div>
                    </td>
                    <td><code style="font-size:.82rem">${escH(u.username)}</code></td>
                    <td style="max-width:260px">${accBadges}
                        <button class="btn btn-ghost btn-sm" style="padding:2px 8px;font-size:.72rem;margin-left:4px"
                            onclick="abrirModalAcceso(${u.id},'${escH(u.username)}')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                    <td><span class="badge ${u.activo=='t'||u.activo===true?'badge-success':'badge-danger'}">${u.activo=='t'||u.activo===true?'Activo':'Inactivo'}</span></td>
                    <td>
                        <button class="btn btn-ghost btn-sm" onclick="abrirEditarUsuario(${u.id},'${escH(u.nombre)}','${escH(u.apellido||'')}','${escH(u.username)}')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-ghost btn-sm" onclick="abrirCambiarPassword(${u.id},'${escH(u.username)}')" title="Cambiar contraseña">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-ghost btn-sm" onclick="toggleActivoUsuario(${u.id})" title="${u.activo=='t'||u.activo===true?'Desactivar':'Activar'}">
                            <i class="fas fa-${u.activo=='t'||u.activo===true?'ban':'check'}"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');
        })
        .catch(() => showToast('Error al cargar usuarios', 'error'));
}

function abrirModalUsuario() {
    document.getElementById('modal-usuario-titulo').textContent = 'Nuevo usuario';
    document.getElementById('u-id').value         = '';
    document.getElementById('u-nombre').value     = '';
    document.getElementById('u-apellido').value   = '';
    document.getElementById('u-username').value   = '';
    document.getElementById('u-password').value   = '';
    document.getElementById('u-sucursal').value   = '';
    document.getElementById('u-rol').value        = 'cajero';
    document.getElementById('row-password').style.display = '';
    document.getElementById('lbl-password-opcional').style.display = 'none';
    openModal('modal-usuario');
}

function abrirEditarUsuario(id, nombre, apellido, username) {
    document.getElementById('modal-usuario-titulo').textContent = 'Editar usuario';
    document.getElementById('u-id').value       = id;
    document.getElementById('u-nombre').value   = nombre;
    document.getElementById('u-apellido').value = apellido;
    document.getElementById('u-username').value = username;
    document.getElementById('u-password').value = '';
    document.getElementById('lbl-password-opcional').style.display = '';
    openModal('modal-usuario');
}

function guardarUsuario() {
    const id       = document.getElementById('u-id').value;
    const nombre   = document.getElementById('u-nombre').value.trim();
    const apellido = document.getElementById('u-apellido').value.trim();
    const username = document.getElementById('u-username').value.trim();
    const password = document.getElementById('u-password').value;
    const sucId    = document.getElementById('u-sucursal').value;
    const rol      = document.getElementById('u-rol').value;

    if (!nombre || !username) { showToast('Nombre y usuario son requeridos', 'error'); return; }

    const action = id ? 'usuario_actualizar' : 'usuario_crear';
    const body   = id
        ? { id, nombre, apellido, username }
        : { nombre, apellido, username, password, sucursal_id: sucId, rol };

    if (!id && !password) { showToast('La contraseña es requerida', 'error'); return; }

    post(action, body).then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast(d.message, 'success');
        closeModal('modal-usuario');
        loadUsuarios();
    });
}

function abrirCambiarPassword(id, username) {
    document.getElementById('cp-id').value        = id;
    document.getElementById('cp-username').textContent = username;
    document.getElementById('cp-password').value  = '';
    openModal('modal-password');
}

function guardarPassword() {
    const id       = document.getElementById('cp-id').value;
    const password = document.getElementById('cp-password').value;
    if (!password || password.length < 4) { showToast('Mínimo 4 caracteres', 'error'); return; }
    post('usuario_cambiar_password', { id, password }).then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast(d.message, 'success');
        closeModal('modal-password');
    });
}

function toggleActivoUsuario(id) {
    if (!confirm('¿Cambiar el estado de este usuario?')) return;
    post('usuario_toggle_activo', { id }).then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Estado actualizado', 'success');
        loadUsuarios();
    });
}

function abrirModalAcceso(userId, username) {
    document.getElementById('ac-usuario-id').value = userId;
    document.getElementById('ac-username').textContent = username;
    const sel = document.getElementById('ac-sucursal');
    sel.innerHTML = sucursalesList.filter(s => s.activo === true || s.activo === 't')
        .map(s => `<option value="${s.id}">${escH(s.nombre)}</option>`).join('');
    openModal('modal-acceso');
}

function guardarAcceso() {
    const uid = document.getElementById('ac-usuario-id').value;
    const sid = document.getElementById('ac-sucursal').value;
    const rol = document.getElementById('ac-rol').value;
    post('asignar_acceso', { usuario_id: uid, sucursal_id: sid, rol }).then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Acceso asignado', 'success');
        closeModal('modal-acceso');
        loadUsuarios();
    });
}

function revocarAcceso(userId, username, sucursalId) {
    if (!confirm(`¿Revocar acceso de "${username}" a esta sucursal?`)) return;
    post('revocar_acceso', { usuario_id: userId, sucursal_id: sucursalId }).then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Acceso revocado', 'success');
        loadUsuarios();
    });
}

// ================================================================
// SUCURSALES
// ================================================================

function loadSucursales() {
    fetch(BASE + 'modules/admin/api.php?action=sucursales_listar')
        .then(r => r.json())
        .then(data => {
            sucursalesList = data;
            // Actualizar select de nuevo usuario también
            const selU = document.getElementById('u-sucursal');
            const curr = selU.value;
            selU.innerHTML = '<option value="">— Sin asignar —</option>' +
                data.filter(s => s.activo === true || s.activo === 't')
                    .map(s => `<option value="${s.id}">${escH(s.nombre)}</option>`).join('');
            if (curr) selU.value = curr;

            if (!data.length) {
                document.getElementById('tbody-sucursales').innerHTML =
                    '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-store"></i><br><br>No hay sucursales registradas</td></tr>';
                return;
            }
            document.getElementById('tbody-sucursales').innerHTML = data.map(s => `<tr>
                <td><strong>${escH(s.nombre)}</strong></td>
                <td><code style="font-size:.8rem;color:var(--primary)">${escH(s.schema_name)}</code></td>
                <td style="font-size:.83rem;color:var(--text-muted)">${escH(s.direccion||'—')}</td>
                <td style="font-size:.83rem">${escH(s.telefono||'—')}</td>
                <td><span class="badge badge-gray">${s.total_usuarios} usuarios</span></td>
                <td><span class="badge ${s.activo=='t'||s.activo===true?'badge-success':'badge-danger'}">${s.activo=='t'||s.activo===true?'Activa':'Inactiva'}</span></td>
                <td>
                    <button class="btn btn-ghost btn-sm" onclick="abrirEditarSucursal(${s.id},'${escH(s.nombre)}','${escH(s.direccion||'')}','${escH(s.telefono||'')}','${escH(s.schema_name)}')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-ghost btn-sm" onclick="toggleActivoSucursal(${s.id})" title="${s.activo=='t'||s.activo===true?'Desactivar':'Activar'}">
                        <i class="fas fa-${s.activo=='t'||s.activo===true?'ban':'check'}"></i>
                    </button>
                </td>
            </tr>`).join('');
        })
        .catch(() => showToast('Error al cargar sucursales', 'error'));
}

function abrirModalSucursal() {
    document.getElementById('modal-sucursal-titulo').textContent = 'Nueva sucursal';
    document.getElementById('s-id').value        = '';
    document.getElementById('s-nombre').value    = '';
    document.getElementById('s-telefono').value  = '';
    document.getElementById('s-direccion').value = '';
    document.getElementById('row-schema-info').style.display = 'none';
    document.getElementById('btn-guardar-sucursal').innerHTML = '<i class="fas fa-save"></i> Crear sucursal';
    openModal('modal-sucursal');
}

function abrirEditarSucursal(id, nombre, dir, tel, schema) {
    document.getElementById('modal-sucursal-titulo').textContent = 'Editar sucursal';
    document.getElementById('s-id').value        = id;
    document.getElementById('s-nombre').value    = nombre;
    document.getElementById('s-telefono').value  = tel;
    document.getElementById('s-direccion').value = dir;
    document.getElementById('s-schema-display').textContent = schema;
    document.getElementById('row-schema-info').style.display = '';
    document.getElementById('btn-guardar-sucursal').innerHTML = '<i class="fas fa-save"></i> Guardar cambios';
    openModal('modal-sucursal');
}

function guardarSucursal() {
    const id       = document.getElementById('s-id').value;
    const nombre   = document.getElementById('s-nombre').value.trim();
    const telefono = document.getElementById('s-telefono').value.trim();
    const dir      = document.getElementById('s-direccion').value.trim();

    if (!nombre) { showToast('El nombre es requerido', 'error'); return; }

    const btn = document.getElementById('btn-guardar-sucursal');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    const action = id ? 'sucursal_actualizar' : 'sucursal_crear';
    const body   = id ? { id, nombre, direccion: dir, telefono } : { nombre, direccion: dir, telefono };

    post(action, body).then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast(d.message, 'success');
        closeModal('modal-sucursal');
        loadSucursales();
    });
}

function toggleActivoSucursal(id) {
    if (!confirm('¿Cambiar el estado de esta sucursal?')) return;
    post('sucursal_toggle_activo', { id }).then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Estado actualizado', 'success');
        loadSucursales();
    });
}

// ================================================================
// CONFIGURACIÓN DE MARCA
// ================================================================

let _logoPathActual = null;

function loadConfig() {
    fetch(BASE + 'modules/admin/api.php?action=config_get')
        .then(r => r.json())
        .then(data => {
            document.getElementById('cfg-nombre').value = data.nombre_sistema || 'FarmaSystem';
            _logoPathActual = data.logo_path || null;
            renderLogoPreview(_logoPathActual);
        })
        .catch(() => showToast('Error al cargar configuración', 'error'));
}

function renderLogoPreview(logoPath) {
    const img    = document.getElementById('logo-img');
    const icon   = document.getElementById('logo-placeholder');
    const btnDel = document.getElementById('btn-eliminar-logo');
    if (logoPath) {
        img.src = BASE + logoPath;
        img.style.display = '';
        icon.style.display = 'none';
        btnDel.style.display = '';
    } else {
        img.style.display = 'none';
        img.src = '';
        icon.style.display = '';
        btnDel.style.display = 'none';
    }
}

function previewLogoLocal(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('logo-file-name').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logo-img').src = e.target.result;
        document.getElementById('logo-img').style.display = '';
        document.getElementById('logo-placeholder').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

async function guardarConfig() {
    const nombre = document.getElementById('cfg-nombre').value.trim();
    if (!nombre) { showToast('El nombre del sistema es requerido', 'error'); return; }

    const btn = document.getElementById('btn-guardar-config');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
        const r1 = await post('config_guardar', { nombre_sistema: nombre });
        if (r1.error) { showToast(r1.message, 'error'); return; }

        const input = document.getElementById('logo-file-input');
        if (input.files[0]) {
            const fd = new FormData();
            fd.append('logo', input.files[0]);
            const r2 = await fetch(BASE + 'modules/admin/api.php?action=logo_subir', {
                method: 'POST', body: fd
            }).then(r => r.json());
            if (r2.error) { showToast(r2.message, 'error'); return; }
            _logoPathActual = r2.logo_path;
            renderLogoPreview(_logoPathActual);
            input.value = '';
            document.getElementById('logo-file-name').textContent = 'Ningún archivo seleccionado';
        }

        showToast('Configuración guardada — recarga la página para ver los cambios en el menú', 'success');
    } catch (e) {
        showToast('Error al guardar la configuración', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar y Actualizar';
    }
}

function eliminarLogo() {
    if (!confirm('¿Eliminar el logo actual? Se restaurará el ícono por defecto.')) return;
    post('logo_eliminar', {}).then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast('Logo eliminado — recarga la página para verlo en el menú', 'success');
        _logoPathActual = null;
        renderLogoPreview(null);
        document.getElementById('logo-file-input').value = '';
        document.getElementById('logo-file-name').textContent = 'Ningún archivo seleccionado';
    });
}

// ================================================================
// HELPERS
// ================================================================

function post(action, body) {
    return fetch(BASE + 'modules/admin/api.php?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
    }).then(r => r.json());
}

function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type = 'info') {
    const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    loadSucursales();
    loadUsuarios();
});
</script>

<?php include '../../includes/footer.php'; ?>
