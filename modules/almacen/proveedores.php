<?php
// ============================================================
// ARCHIVO: farmacia/modules/almacen/proveedores.php
// MÓDULO:  Almacén → Gestión de Proveedores
// ============================================================

require_once '../../config/database.php';

$base_path      = '../../';
$current_module = 'almacen';
$current_page   = 'proveedores';
$page_title     = 'Proveedores — FarmaSystem';
$breadcrumb     = '<strong>Almacén</strong> / Proveedores';

include '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><i class="fas fa-truck" style="color:var(--primary);margin-right:8px"></i>Proveedores</div>
        <div class="page-subtitle">Gestiona los proveedores de mercadería</div>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openProveedorModal()">
            <i class="fas fa-plus"></i> Nuevo Proveedor
        </button>
    </div>
</div>

<!-- Filtro -->
<div class="card" style="margin-bottom:20px">
    <div style="display:flex;gap:12px;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:3">
            <label class="form-label">Buscar</label>
            <div class="input-group">
                <span class="input-group-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="f-q" class="form-control" placeholder="Razón social o RUC...">
            </div>
        </div>
        <div class="form-group" style="margin:0;flex:1">
            <label class="form-label">Estado</label>
            <select class="form-control" id="f-activos">
                <option value="">Todos</option>
                <option value="1">Solo activos</option>
            </select>
        </div>
        <button class="btn btn-outline" onclick="resetFiltros()"><i class="fas fa-times"></i> Limpiar</button>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Proveedores registrados</div>
        <span id="result-count" style="font-size:.82rem;color:var(--text-muted)">Cargando...</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>RUC</th>
                    <th>Razón Social</th>
                    <th>Nombre Comercial</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Contacto</th>
                    <th class="text-right">Ingresos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)">
                    <i class="fas fa-spinner fa-spin"></i>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== MODAL: Agregar / Editar Proveedor ===================== -->
<div class="modal-overlay" id="modal-proveedor">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-proveedor-title">
                <i class="fas fa-truck" style="color:var(--primary);margin-right:8px"></i>Nuevo Proveedor
            </h3>
            <button class="modal-close" onclick="closeModal('modal-proveedor')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

                <div class="form-group">
                    <label class="form-label">RUC</label>
                    <input type="text" id="p-ruc" class="form-control" placeholder="20100055858" maxlength="20">
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre Comercial</label>
                    <input type="text" id="p-nombre-comercial" class="form-control" placeholder="Bayer">
                </div>

                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Razón Social <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="p-razon-social" class="form-control" placeholder="LABORATORIOS BAYER S.A.">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" id="p-telefono" class="form-control" placeholder="01-6285500">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="p-email" class="form-control" placeholder="ventas@proveedor.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Persona de Contacto</label>
                    <input type="text" id="p-contacto" class="form-control" placeholder="Juan Pérez">
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" id="p-direccion" class="form-control" placeholder="Av. Industrial 123, Lima">
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-proveedor')">Cancelar</button>
            <button class="btn btn-primary" id="btn-guardar-proveedor" onclick="saveProveedor()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
const BASE = '../../';
let editingId = null;

document.addEventListener('DOMContentLoaded', () => {
    loadProveedores();
    setupSearch();
});

// ---- Listar ----
function loadProveedores() {
    const params = new URLSearchParams({
        action:  'proveedores_listar',
        q:       document.getElementById('f-q').value,
        activos: document.getElementById('f-activos').value,
    });
    document.getElementById('tabla-body').innerHTML =
        '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    fetch(BASE + 'modules/almacen/api.php?' + params)
        .then(r => r.json())
        .then(data => {
            document.getElementById('result-count').textContent = data.length + ' proveedor(es)';
            if (!data.length) {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)"><i class="fas fa-truck" style="font-size:1.3rem"></i><br><br>No se encontraron proveedores</td></tr>';
                return;
            }
            document.getElementById('tabla-body').innerHTML = data.map(p => {
                const activoBadge = (p.activo === true || p.activo === 't')
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>';
                return `<tr>
                    <td style="font-family:monospace;font-size:.82rem">${p.ruc || '—'}</td>
                    <td style="font-weight:500;font-size:.88rem">${p.razon_social}</td>
                    <td style="font-size:.85rem;color:var(--text-muted)">${p.nombre_comercial || '—'}</td>
                    <td style="font-size:.82rem">${p.telefono || '—'}</td>
                    <td style="font-size:.82rem">${p.email || '—'}</td>
                    <td style="font-size:.82rem">${p.contacto_nombre || '—'}</td>
                    <td class="text-right"><span class="badge badge-gray">${p.total_ingresos}</span></td>
                    <td>${activoBadge}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <button class="btn btn-ghost btn-sm" title="Editar" onclick='openProveedorModal(${JSON.stringify(p)})'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm"
                                title="${(p.activo === true || p.activo === 't') ? 'Desactivar' : 'Activar'}"
                                onclick="toggleProveedor(${p.id})"
                                style="color:${(p.activo === true || p.activo === 't') ? 'var(--danger)' : 'var(--success)'}">
                                <i class="fas fa-${(p.activo === true || p.activo === 't') ? 'toggle-on' : 'toggle-off'}"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        })
        .catch(() => showToast('Error al cargar proveedores', 'error'));
}

function setupSearch() {
    let timer;
    document.getElementById('f-q').addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(loadProveedores, 250);
    });
    document.getElementById('f-activos').addEventListener('change', loadProveedores);
}

function resetFiltros() {
    document.getElementById('f-q').value      = '';
    document.getElementById('f-activos').value = '';
    loadProveedores();
}

// ---- Modal ----
function openProveedorModal(p = null) {
    editingId = p ? p.id : null;
    document.getElementById('modal-proveedor-title').innerHTML = editingId
        ? '<i class="fas fa-edit" style="color:var(--primary);margin-right:8px"></i>Editar Proveedor'
        : '<i class="fas fa-plus" style="color:var(--primary);margin-right:8px"></i>Nuevo Proveedor';

    document.getElementById('p-ruc').value             = p?.ruc              ?? '';
    document.getElementById('p-razon-social').value    = p?.razon_social     ?? '';
    document.getElementById('p-nombre-comercial').value = p?.nombre_comercial ?? '';
    document.getElementById('p-telefono').value        = p?.telefono         ?? '';
    document.getElementById('p-email').value           = p?.email            ?? '';
    document.getElementById('p-contacto').value        = p?.contacto_nombre  ?? '';
    document.getElementById('p-direccion').value       = p?.direccion        ?? '';

    openModal('modal-proveedor');
    setTimeout(() => document.getElementById('p-ruc').focus(), 100);
}

function saveProveedor() {
    const razon_social = document.getElementById('p-razon-social').value.trim();
    if (!razon_social) { showToast('La razón social es requerida', 'error'); return; }

    const payload = {
        id:               editingId,
        ruc:              document.getElementById('p-ruc').value.trim(),
        razon_social,
        nombre_comercial: document.getElementById('p-nombre-comercial').value.trim(),
        telefono:         document.getElementById('p-telefono').value.trim(),
        email:            document.getElementById('p-email').value.trim(),
        contacto_nombre:  document.getElementById('p-contacto').value.trim(),
        direccion:        document.getElementById('p-direccion').value.trim(),
    };

    const action = editingId ? 'proveedor_actualizar' : 'proveedor_crear';
    const btn    = document.getElementById('btn-guardar-proveedor');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    fetch(BASE + `modules/almacen/api.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        if (d.error) { showToast(d.message, 'error'); return; }
        showToast(d.message, 'success');
        closeModal('modal-proveedor');
        loadProveedores();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        showToast('Error al guardar', 'error');
    });
}

function toggleProveedor(id) {
    fetch(BASE + 'modules/almacen/api.php?action=proveedor_toggle_activo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) { showToast(d.message, 'error'); return; }
        const estado = (d.activo === true || d.activo === 't') ? 'activado' : 'desactivado';
        showToast('Proveedor ' + estado, 'success');
        loadProveedores();
    })
    .catch(() => showToast('Error al cambiar estado', 'error'));
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
