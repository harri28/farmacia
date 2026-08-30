<?php
// ============================================================
// ARCHIVO: farmacia/modules/superadmin/index.php
// DESCRIPCIÓN: Panel de control del superadmin
// ============================================================

$required_roles = ['superadmin'];
$base_path      = '../../';
$current_module = 'superadmin';
$current_page   = 'dashboard';
$page_title     = 'Panel Superadmin — FarmaSystem';
$breadcrumb     = '<i class="fas fa-shield-alt"></i> Panel Superadmin';

require_once '../../config/auth.php';
requireAuth($required_roles);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        /* ---- Topbar ---- */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
        }
        .topbar-brand .badge-sa {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: .85rem;
            color: #64748b;
        }
        .topbar-right .user-info strong { color: #1e293b; }
        .btn-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 7px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: .82rem;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .btn-logout:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

        /* ---- Main layout ---- */
        .main { padding: 28px 32px; max-width: 1400px; margin: 0 auto; }

        /* ---- Page header ---- */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .page-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
        }
        .page-header p { color: #64748b; font-size: .85rem; margin-top: 2px; }

        /* ---- Stat cards ---- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .stat-icon.indigo  { background: #eef2ff; color: #6366f1; }
        .stat-icon.green   { background: #dcfce7; color: #16a34a; }
        .stat-icon.blue    { background: #dbeafe; color: #2563eb; }
        .stat-icon.amber   { background: #fef3c7; color: #d97706; }
        .stat-value { font-size: 1.8rem; font-weight: 700; color: #1e293b; line-height: 1; }
        .stat-label { font-size: .78rem; color: #64748b; margin-top: 3px; }

        /* ---- Section header ---- */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* ---- Primary button ---- */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: .85rem;
            font-weight: 600;
            transition: opacity .15s;
        }
        .btn-primary:hover { opacity: .88; }

        /* ---- Table ---- */
        .table-wrap {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        #sucursalesLista, #usuariosLista { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f8fafc;
            padding: 12px 16px;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .1s, box-shadow .1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr.clickable-row { cursor: pointer; }
        tbody tr.clickable-row:hover { background: #f0f4ff; }
        tbody tr.clickable-row:hover td:first-child { color: #4f46e5; }
        td { padding: 13px 16px; font-size: .88rem; color: #475569; vertical-align: middle; }
        .td-main { font-weight: 600; color: #1e293b; }
        .td-mono { font-family: monospace; font-size: .82rem; color: #64748b; }

        /* ---- Badges ---- */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-activo   { background: #dcfce7; color: #16a34a; }
        .badge-inactivo { background: #fee2e2; color: #dc2626; }
        .badge-basico     { background: #e2e8f0 !important; color: #475569 !important; }
        .badge-pro        { background: #fef3c7 !important; color: #b45309 !important; }
        .badge-enterprise { background: #ede9fe !important; color: #6d28d9 !important; }

        /* ---- Action buttons ---- */
        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            font-size: .8rem;
            transition: background .15s, color .15s;
        }
        .btn-edit   { background: #eef2ff; color: #6366f1; }
        .btn-edit:hover   { background: #6366f1; color: #fff; }
        .btn-toggle { background: #fef3c7; color: #d97706; }
        .btn-toggle:hover { background: #d97706; color: #fff; }
        .btn-toggle.inactivo { background: #fee2e2; color: #dc2626; }
        .btn-toggle.inactivo:hover { background: #dc2626; color: #fff; }
        .btn-branch { background: #e0f2fe; color: #0284c7; }
        .btn-branch:hover { background: #0284c7; color: #fff; }
        .btn-user   { background: #dcfce7; color: #16a34a; }
        .btn-user:hover   { background: #16a34a; color: #fff; }

        /* ---- Modal overlay ---- */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 500;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            display: block;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
            animation: modalIn .18s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-16px) scale(.97); }
            to   { opacity: 1; transform: none; }
        }
        .modal-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title { font-size: 1rem; font-weight: 700; color: #1e293b; }
        .modal-close {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            border-radius: 6px;
            transition: color .15s;
        }
        .modal-close:hover { color: #1e293b; }
        .modal-body { padding: 20px 24px; }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* ---- Form elements ---- */
        .form-group { margin-bottom: 16px; }
        .form-group:last-child { margin-bottom: 0; }
        label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 6px;
        }
        input, select {
            width: 100%;
            padding: 10px 12px;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            color: #1e293b;
            font-size: .9rem;
            outline: none;
            transition: border-color .15s;
        }
        input:focus, select:focus { border-color: #6366f1; }
        select option { background: #fff; color: #1e293b; }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 9px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: .85rem;
            font-weight: 600;
            transition: background .15s;
        }
        .btn-cancel:hover { background: #e2e8f0; }

        /* ---- Empty state ---- */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #475569;
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; }

        /* ---- Toast ---- */
        #toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: .85rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,.12);
            z-index: 9999;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity .25s, transform .25s;
            pointer-events: none;
            max-width: 320px;
        }
        #toast.show { opacity: 1; transform: none; }
        #toast.ok  { border-color: #86efac; }
        #toast.err { border-color: #fca5a5; }
        #toast i { font-size: 1rem; }
        #toast.ok  i { color: #16a34a; }
        #toast.err i { color: #dc2626; }

        /* ---- Tabs ---- */
        .tab-btn {
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 18px;
            font-size: .85rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: color .15s, border-color .15s;
            margin-bottom: -1px;
        }
        .tab-btn:hover  { color: #1e293b; }
        .tab-btn.active { color: #6366f1; border-bottom-color: #6366f1; }

        /* ---- Loading ---- */
        .loading-row td {
            text-align: center;
            padding: 48px;
            color: #64748b;
        }

        /* ---- Grid 2 cols ---- */
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 520px) { .grid2 { grid-template-columns: 1fr; } }

        /* ---- Planes grid ---- */
        .planes-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; }

        /* ---- Mobile (celular, Android/iOS) ---- */
        @media (max-width: 640px) {
            .topbar { height: auto; min-height: 60px; padding: 10px 14px; flex-wrap: wrap; gap: 8px; }
            .topbar-brand { font-size: .92rem; }
            .topbar-right { font-size: .8rem; gap: 10px; }
            .main { padding: 16px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .section-header { flex-wrap: wrap; gap: 10px; }
            .section-header .btn-primary { width: 100%; justify-content: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 14px; }
            .stat-value { font-size: 1.4rem; }
            .planes-grid { grid-template-columns: 1fr; }
            .btn-icon { width: 38px; height: 38px; font-size: .85rem; }
            .modal-header, .modal-body, .modal-footer { padding-left: 16px; padding-right: 16px; }
            #toast { left: 16px; right: 16px; bottom: calc(16px + env(safe-area-inset-bottom)); max-width: none; }
        }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <div class="topbar-brand">
        <i class="fas fa-shield-alt" style="color:#6366f1"></i>
        FarmaSystem
        <span class="badge-sa">Superadmin</span>
    </div>
    <div class="topbar-right">
        <span class="user-info">
            <strong><?= htmlspecialchars(sesionNombre()) ?></strong>
        </span>
        <button onclick="abrirPerfil()" class="btn-logout" style="background:#eef2ff;color:#6366f1;border-color:#c7d2fe">
            <i class="fas fa-user-cog"></i> Mi cuenta
        </button>
        <a href="../auth/logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</div>

<!-- Main -->
<div class="main">

    <div class="page-header">
        <div>
            <h1>Panel de control</h1>
            <p>Gestión global de empresas, sucursales y usuarios del sistema</p>
        </div>
    </div>

    <!-- Planes -->
    <div style="margin-bottom:20px">
        <button onclick="togglePlanes()" id="btn-ver-planes"
                style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 16px;
                       font-size:.82rem;font-weight:600;color:#475569;cursor:pointer;display:flex;align-items:center;gap:6px">
            <i class="fas fa-layer-group" style="color:#6366f1"></i>
            Ver planes
            <i class="fas fa-chevron-down" id="planes-chevron" style="font-size:.7rem;transition:transform .2s"></i>
        </button>

        <div id="planes-grid" class="planes-grid" style="display:none;margin-top:12px">

            <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px 18px;border-top:3px solid #94a3b8">
                <div style="margin-bottom:8px">
                    <span style="background:#e2e8f0;color:#475569;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px">BÁSICO</span>
                </div>
                <ul style="margin:0;padding-left:16px;font-size:.82rem;color:#475569;line-height:1.8">
                    <li>1 sucursal</li>
                    <li>Hasta 2 usuarios</li>
                    <li>Ventas, inventario y caja</li>
                    <li>Sin facturación electrónica</li>
                </ul>
            </div>

            <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px 18px;border-top:3px solid #f59e0b">
                <div style="margin-bottom:8px">
                    <span style="background:#fef3c7;color:#b45309;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px">PRO</span>
                </div>
                <ul style="margin:0;padding-left:16px;font-size:.82rem;color:#475569;line-height:1.8">
                    <li>Hasta 3 sucursales</li>
                    <li>Hasta 10 usuarios</li>
                    <li>Módulo de compras y créditos</li>
                    <li>Facturación electrónica (SUNAT)</li>
                </ul>
            </div>

            <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px 18px;border-top:3px solid #6d28d9">
                <div style="margin-bottom:8px">
                    <span style="background:#ede9fe;color:#6d28d9;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px">ENTERPRISE</span>
                </div>
                <ul style="margin:0;padding-left:16px;font-size:.82rem;color:#475569;line-height:1.8">
                    <li>Sucursales ilimitadas</li>
                    <li>Usuarios ilimitados</li>
                    <li>Todos los módulos</li>
                    <li>Soporte prioritario</li>
                </ul>
            </div>

        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon indigo"><i class="fas fa-building"></i></div>
            <div>
                <div class="stat-value" id="sTenantsActivos">—</div>
                <div class="stat-label">Empresas activas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-store"></i></div>
            <div>
                <div class="stat-value" id="sSucursales">—</div>
                <div class="stat-label">Sucursales activas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-value" id="sUsuarios">—</div>
                <div class="stat-label">Usuarios activos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-database"></i></div>
            <div>
                <div class="stat-value" id="sTenantsTotal">—</div>
                <div class="stat-label">Empresas en total</div>
            </div>
        </div>
    </div>

    <!-- Tenants table -->
    <div class="section-header">
        <div class="section-title"><i class="fas fa-building" style="color:#818cf8;margin-right:8px"></i>Empresas registradas</div>
        <button class="btn-primary" onclick="abrirModalTenant()">
            <i class="fas fa-plus"></i> Nueva empresa
        </button>
    </div>

    <div class="table-wrap">
        <table id="tablaTenants">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Empresa</th>
                    <th>URL</th>
                    <th>Plan</th>
                    <th>Sucursales</th>
                    <th>Usuarios</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tenantsTbody">
                <tr class="loading-row"><td colspan="9"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
            </tbody>
        </table>
    </div>

</div><!-- /main -->

<!-- ============================================================
     MODAL: Crear / Editar tenant
     ============================================================ -->
<div class="modal-overlay" id="modalTenantOverlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalTenantTitle">Nueva empresa</span>
            <button class="modal-close" onclick="cerrarModal('modalTenantOverlay')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="tenantId">
            <div class="form-group">
                <label>Logo de la empresa</label>
                <div style="display:flex;align-items:center;gap:14px">
                    <div id="logoPreviewWrap" style="width:64px;height:64px;border-radius:10px;border:1px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8fafc;flex-shrink:0">
                        <i class="fas fa-image" id="logoIconPlaceholder" style="color:#cbd5e1;font-size:1.4rem"></i>
                        <img id="logoPreviewImg" src="" alt="logo" style="display:none;width:100%;height:100%;object-fit:contain">
                    </div>
                    <div style="flex:1">
                        <label for="tenantLogoFile" style="display:inline-flex;align-items:center;gap:7px;cursor:pointer;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;font-weight:600;color:#475569">
                            <i class="fas fa-upload"></i> Elegir imagen
                        </label>
                        <input type="file" id="tenantLogoFile" accept="image/*" style="display:none" onchange="previewLogo(this)">
                        <p id="logoFileName" style="font-size:.75rem;color:#94a3b8;margin-top:6px">JPG, PNG, WEBP — máx. 2 MB</p>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre de la empresa *</label>
                <input type="text" id="tenantNombre" placeholder="Ej: Farmacias del Norte S.A.C.">
            </div>
            <div class="form-group">
                <label>RUC</label>
                <input type="text" id="tenantRuc" placeholder="Ej: 20601234567" maxlength="11">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="tenantTelefono" placeholder="Ej: 01-234-5678">
                </div>
                <div class="form-group">
                    <label>Plan</label>
                    <select id="tenantPlan" style="color:#1e293b">
                        <option value="basico"     style="color:#1e293b">Básico</option>
                        <option value="pro"        style="color:#1e293b">Pro</option>
                        <option value="enterprise" style="color:#1e293b">Enterprise</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Dirección principal</label>
                <input type="text" id="tenantDireccion" placeholder="Ej: Av. Los Álamos 123, Lima">
            </div>
            <div class="form-group">
                <label>URL del sistema</label>
                <input type="text" id="tenantUrl" placeholder="Ej: farmacia1.misistema.com">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal('modalTenantOverlay')">Cancelar</button>
            <button class="btn-primary" onclick="guardarTenant()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL: Gestión (Sucursales + Usuarios) de un tenant
     ============================================================ -->
<div class="modal-overlay" id="modalGestionOverlay">
    <div class="modal" style="max-width:720px">
        <div class="modal-header">
            <span class="modal-title" id="modalGestionTitle">Gestión</span>
            <button class="modal-close" onclick="cerrarModal('modalGestionOverlay')"><i class="fas fa-times"></i></button>
        </div>

        <!-- Pestañas -->
        <div style="display:flex;border-bottom:1px solid #e2e8f0;padding:0 24px">
            <button class="tab-btn active" id="tabBtnSucursales" onclick="switchTab('sucursales')">
                <i class="fas fa-store"></i> Sucursales
            </button>
            <button class="tab-btn" id="tabBtnUsuarios" onclick="switchTab('usuarios')">
                <i class="fas fa-users"></i> Usuarios
            </button>
        </div>

        <!-- Pestaña Sucursales -->
        <div id="tabSucursales">
            <div style="padding:16px 24px 0">
                <div id="sucursalesLista"></div>
            </div>
            <div style="padding:12px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:.8rem;color:#64748b" id="sucursalesCount"></span>
                <button class="btn-primary" onclick="mostrarFormSucursal()">
                    <i class="fas fa-plus"></i> Nueva sucursal
                </button>
            </div>
            <div id="formSucursalWrap" style="display:none;padding:0 24px 20px">
                <div style="border-top:1px solid #e2e8f0;padding-top:16px">
                    <p style="font-size:.85rem;font-weight:700;color:#1e293b;margin-bottom:14px">
                        <i class="fas fa-plus-circle" style="color:#6366f1;margin-right:6px"></i>Nueva sucursal
                    </p>
                    <div class="grid2">
                        <div class="form-group"><label>Nombre *</label><input type="text" id="sNombre" placeholder="Ej: Sucursal Norte"></div>
                        <div class="form-group"><label>Teléfono</label><input type="text" id="sTelefono" placeholder="01-234-5678"></div>
                    </div>
                    <div class="form-group"><label>Dirección</label><input type="text" id="sDireccion" placeholder="Av. Principal 123"></div>
                    <div style="display:flex;gap:10px;justify-content:flex-end">
                        <button class="btn-cancel" onclick="ocultarFormSucursal()">Cancelar</button>
                        <button class="btn-primary" onclick="crearSucursal()"><i class="fas fa-save"></i> Crear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña Usuarios -->
        <div id="tabUsuarios" style="display:none">
            <div style="padding:16px 24px 0">
                <div id="usuariosLista"></div>
            </div>
            <div style="padding:12px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:.8rem;color:#64748b" id="usuariosCount"></span>
                <button class="btn-primary" onclick="mostrarFormUsuario()">
                    <i class="fas fa-user-plus"></i> Nuevo usuario
                </button>
            </div>
            <div id="formUsuarioWrap" style="display:none;padding:0 24px 20px">
                <div style="border-top:1px solid #e2e8f0;padding-top:16px">
                    <p style="font-size:.85rem;font-weight:700;color:#1e293b;margin-bottom:14px">
                        <i class="fas fa-user-plus" style="color:#6366f1;margin-right:6px"></i>
                        <span id="formUsuarioTitulo">Nuevo usuario</span>
                    </p>
                    <input type="hidden" id="uEditId">
                    <div class="grid2">
                        <div class="form-group"><label>Nombre *</label><input type="text" id="uNombre" placeholder="Juan"></div>
                        <div class="form-group"><label>Apellido</label><input type="text" id="uApellido" placeholder="Pérez"></div>
                    </div>
                    <div class="grid2">
                        <div class="form-group"><label>Usuario *</label><input type="text" id="uUsername" placeholder="juan_perez" autocomplete="off"></div>
                        <div class="form-group"><label id="uPassLabel">Contraseña *</label><input type="password" id="uPassword" placeholder="Mínimo 4 caracteres" autocomplete="new-password"></div>
                    </div>
                    <div class="form-group">
                        <label>Correo electrónico <span style="font-weight:400;color:#94a3b8">(para recuperación de contraseña)</span></label>
                        <input type="email" id="uEmail" placeholder="correo@ejemplo.com" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Sucursal asignada</label>
                        <select id="uSucursal">
                            <option value="">— Sin asignar —</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end">
                        <button class="btn-cancel" onclick="ocultarFormUsuario()">Cancelar</button>
                        <button class="btn-primary" id="btnGuardarUsuario" onclick="guardarUsuario()"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================================
     MODAL: Mi cuenta / Perfil del superadmin
     ============================================================ -->
<div class="modal-overlay" id="modalPerfilOverlay">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-user-cog" style="color:#6366f1;margin-right:8px"></i>Mi cuenta</span>
            <button class="modal-close" onclick="cerrarModal('modalPerfilOverlay')"><i class="fas fa-times"></i></button>
        </div>

        <!-- Tabs -->
        <div style="display:flex;border-bottom:1px solid #e2e8f0;padding:0 24px">
            <button class="tab-btn active" id="tabDatos"     onclick="switchPerfilTab('datos')">
                <i class="fas fa-id-card"></i> Datos personales
            </button>
            <button class="tab-btn"         id="tabPassword" onclick="switchPerfilTab('password')">
                <i class="fas fa-lock"></i> Contraseña
            </button>
        </div>

        <!-- Alerta dentro del modal -->
        <div id="perfilAlert" style="display:none;margin:16px 24px 0;border-radius:9px;padding:10px 14px;font-size:.83rem;display:none;align-items:center;gap:8px"></div>

        <!-- TAB: Datos personales -->
        <div id="panelDatos" class="modal-body">
            <div class="grid2">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="pNombre" placeholder="Juan">
                </div>
                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" id="pApellido" placeholder="Pérez">
                </div>
            </div>
            <div class="form-group">
                <label>Usuario *</label>
                <input type="text" id="pUsername" placeholder="superadmin" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Correo electrónico <span style="font-weight:400;color:#94a3b8">(recuperación de contraseña)</span></label>
                <input type="email" id="pEmail" placeholder="correo@ejemplo.com">
            </div>
            <div style="text-align:right;margin-top:4px">
                <button class="btn-cancel" onclick="cerrarModal('modalPerfilOverlay')" style="margin-right:8px">Cancelar</button>
                <button class="btn-primary" onclick="guardarDatosPerfil()"><i class="fas fa-save"></i> Guardar cambios</button>
            </div>
        </div>

        <!-- TAB: Contraseña -->
        <div id="panelPassword" class="modal-body" style="display:none">
            <div class="form-group">
                <label>Contraseña actual *</label>
                <div style="position:relative">
                    <input type="password" id="pPassActual" placeholder="••••••••" autocomplete="current-password" style="padding-right:38px">
                    <button type="button" onclick="togglePerfilPass('pPassActual','icoActual')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:2px">
                        <i class="fas fa-eye" id="icoActual"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>Nueva contraseña *</label>
                <div style="position:relative">
                    <input type="password" id="pPassNueva" placeholder="Mínimo 4 caracteres" autocomplete="new-password" style="padding-right:38px">
                    <button type="button" onclick="togglePerfilPass('pPassNueva','icoNueva')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:2px">
                        <i class="fas fa-eye" id="icoNueva"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>Confirmar nueva contraseña *</label>
                <div style="position:relative">
                    <input type="password" id="pPassConfirm" placeholder="••••••••" autocomplete="new-password" style="padding-right:38px">
                    <button type="button" onclick="togglePerfilPass('pPassConfirm','icoConfirm')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:2px">
                        <i class="fas fa-eye" id="icoConfirm"></i>
                    </button>
                </div>
            </div>
            <div style="text-align:right;margin-top:4px">
                <button class="btn-cancel" onclick="cerrarModal('modalPerfilOverlay')" style="margin-right:8px">Cancelar</button>
                <button class="btn-primary" onclick="guardarPassword()"><i class="fas fa-key"></i> Cambiar contraseña</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast"><i id="toastIcon" class="fas fa-check-circle"></i> <span id="toastMsg"></span></div>

<script>
const API = 'api.php';

// ---- Utilidades ----

function togglePlanes() {
    const grid    = document.getElementById('planes-grid');
    const chevron = document.getElementById('planes-chevron');
    const abierto = grid.style.display === 'grid';
    grid.style.display    = abierto ? 'none' : 'grid';
    chevron.style.transform = abierto ? '' : 'rotate(180deg)';
}

function toast(msg, tipo = 'ok') {
    const el = document.getElementById('toast');
    el.className = 'show ' + tipo;
    document.getElementById('toastIcon').className = tipo === 'ok'
        ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    document.getElementById('toastMsg').textContent = msg;
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.className = el.className.replace('show', '').trim(); }, 3500);
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('active');
}

function planBadge(plan) {
    const map = { basico: 'badge-basico', pro: 'badge-pro', enterprise: 'badge-enterprise' };
    const labels = { basico: 'Básico', pro: 'Pro', enterprise: 'Enterprise' };
    return `<span class="badge ${map[plan] || 'badge-basico'}">${labels[plan] || plan}</span>`;
}

function fmtDate(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ---- Cargar stats ----

async function cargarStats() {
    try {
        const r = await fetch(`${API}?action=stats`);
        const d = await r.json();
        document.getElementById('sTenantsActivos').textContent = d.tenants_activos ?? '—';
        document.getElementById('sSucursales').textContent     = d.sucursales_activas ?? '—';
        document.getElementById('sUsuarios').textContent       = d.usuarios_activos ?? '—';
        document.getElementById('sTenantsTotal').textContent   = d.tenants_total ?? '—';
    } catch { /* silent */ }
}

// ---- Cargar tenants ----

let _tenants = [];

async function cargarTenants() {
    const tbody = document.getElementById('tenantsTbody');
    tbody.innerHTML = '<tr class="loading-row"><td colspan="8"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
    try {
        const r = await fetch(`${API}?action=tenants_listar`);
        _tenants = await r.json();
        renderTenants();
    } catch {
        tbody.innerHTML = '<tr class="loading-row"><td colspan="8" style="color:#f87171"><i class="fas fa-exclamation-circle"></i> Error al cargar datos</td></tr>';
    }
}

function renderTenants() {
    const tbody = document.getElementById('tenantsTbody');
    if (!_tenants.length) {
        tbody.innerHTML = '<tr><td colspan="9"><div class="empty-state"><i class="fas fa-building"></i>No hay empresas registradas</div></td></tr>';
        return;
    }
    tbody.innerHTML = _tenants.map(t => `
        <tr class="clickable-row" onclick="irEmpresa(${t.id})">
            <td style="text-align:center;padding:6px 12px">
                ${t.logo
                    ? `<img src="../../${escHtml(t.logo)}" alt="logo" style="width:40px;height:40px;object-fit:contain;border-radius:6px;border:1px solid #e2e8f0">`
                    : `<div style="width:40px;height:40px;border-radius:6px;border:1px dashed #cbd5e1;display:inline-flex;align-items:center;justify-content:center;color:#cbd5e1"><i class="fas fa-image"></i></div>`}
            </td>
            <td class="td-main">${escHtml(t.nombre)}</td>
            <td style="font-size:.8rem">
                ${t.url
                    ? `<span style="display:flex;align-items:center;gap:6px">
                        <span style="color:#6366f1;font-family:monospace">${escHtml(t.url)}</span>
                        <button onclick="event.stopPropagation();navigator.clipboard.writeText('${escHtml(t.url)}').then(()=>toast('URL copiada','ok'))"
                                style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:2px 4px" title="Copiar URL">
                            <i class="fas fa-copy"></i>
                        </button>
                       </span>`
                    : '<span style="color:#cbd5e1">—</span>'}
            </td>
            <td>${planBadge(t.plan)}</td>
            <td style="text-align:center">${t.total_sucursales ?? 0}</td>
            <td style="text-align:center">${t.total_usuarios ?? 0}</td>
            <td>
                <span class="badge ${t.activo ? 'badge-activo' : 'badge-inactivo'}">
                    ${t.activo ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td style="color:#64748b;font-size:.8rem">${fmtDate(t.created_at)}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button class="btn-icon btn-edit" title="Editar empresa"
                        onclick="event.stopPropagation(); abrirModalTenant(${t.id})"
                        ${!t.activo ? 'disabled style="opacity:.35;cursor:not-allowed;pointer-events:none"' : ''}>
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn-icon btn-toggle ${t.activo ? '' : 'inactivo'}"
                        title="${t.activo ? 'Desactivar' : 'Activar'} empresa"
                        onclick="event.stopPropagation(); toggleTenant(${t.id}, this)">
                        <i class="fas fa-power-off"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ---- Modal Tenant ----

function abrirModalTenant(id = null) {
    document.getElementById('tenantId').value       = id ?? '';
    document.getElementById('tenantNombre').value   = '';
    document.getElementById('tenantRuc').value      = '';
    document.getElementById('tenantTelefono').value = '';
    document.getElementById('tenantDireccion').value= '';
    document.getElementById('tenantPlan').value     = 'basico';
    document.getElementById('tenantUrl').value      = '';
    document.getElementById('tenantLogoFile').value = '';
    document.getElementById('logoFileName').textContent = 'JPG, PNG, WEBP — máx. 2 MB';
    setLogoPreview(null);
    document.getElementById('modalTenantTitle').textContent = id ? 'Editar empresa' : 'Nueva empresa';

    if (id) {
        const t = _tenants.find(x => x.id == id);
        if (t) {
            document.getElementById('tenantNombre').value   = t.nombre;
            document.getElementById('tenantRuc').value      = t.ruc       || '';
            document.getElementById('tenantTelefono').value = t.telefono  || '';
            document.getElementById('tenantDireccion').value= t.direccion || '';
            document.getElementById('tenantPlan').value     = t.plan      || 'basico';
            document.getElementById('tenantUrl').value      = t.url       || '';
            if (t.logo) setLogoPreview('../../' + t.logo);
        }
    }
    document.getElementById('modalTenantOverlay').classList.add('active');
    setTimeout(() => document.getElementById('tenantNombre').focus(), 80);
}

function setLogoPreview(src) {
    const img  = document.getElementById('logoPreviewImg');
    const icon = document.getElementById('logoIconPlaceholder');
    if (src) {
        img.src          = src;
        img.style.display = 'block';
        icon.style.display = 'none';
    } else {
        img.src          = '';
        img.style.display = 'none';
        icon.style.display = '';
    }
}

function previewLogo(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('logoFileName').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => setLogoPreview(e.target.result);
    reader.readAsDataURL(file);
}

async function guardarTenant() {
    const id        = document.getElementById('tenantId').value;
    const nombre    = document.getElementById('tenantNombre').value.trim();
    const ruc       = document.getElementById('tenantRuc').value.trim();
    const telefono  = document.getElementById('tenantTelefono').value.trim();
    const direccion = document.getElementById('tenantDireccion').value.trim();
    const plan      = document.getElementById('tenantPlan').value;
    const url       = document.getElementById('tenantUrl').value.trim();

    if (!nombre) { toast('El nombre es requerido', 'err'); return; }

    const action = id ? 'tenant_actualizar' : 'tenant_crear';
    const body   = id
        ? { id: parseInt(id), nombre, ruc, telefono, direccion, plan, url }
        : { nombre, ruc, telefono, direccion, plan, url };

    try {
        const r = await fetch(`${API}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }

        // Subir logo si se seleccionó un archivo
        const logoFile = document.getElementById('tenantLogoFile').files[0];
        if (logoFile) {
            const tenantId = id || d.id;
            const fd = new FormData();
            fd.append('tenant_id', tenantId);
            fd.append('logo', logoFile);
            const lr = await fetch(`${API}?action=tenant_logo_upload`, { method: 'POST', body: fd });
            const ld = await lr.json();
            if (ld.error) { toast(ld.message, 'err'); }
        }

        toast(d.message);
        cerrarModal('modalTenantOverlay');
        cargarStats();
        cargarTenants();
    } catch { toast('Error de conexión', 'err'); }
}

// ---- Toggle activo ----

async function toggleTenant(id, btn) {
    try {
        const r = await fetch(`${API}?action=tenant_toggle_activo`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        toast(d.activo ? 'Empresa activada' : 'Empresa desactivada');
        cargarStats();
        cargarTenants();
    } catch { toast('Error de conexión', 'err'); }
}

// ---- Modal Gestión (Sucursales + Usuarios) ----

let _gestionTenantId   = null;
let _gestionTenantNombre = '';
let _tabActiva = 'sucursales';

async function abrirGestion(tenantId, tenantNombre, tab = 'sucursales') {
    _gestionTenantId     = tenantId;
    _gestionTenantNombre = tenantNombre;
    document.getElementById('modalGestionTitle').textContent = tenantNombre;
    ocultarFormSucursal();
    ocultarFormUsuario();
    document.getElementById('modalGestionOverlay').classList.add('active');
    switchTab(tab);
}

function switchTab(tab) {
    _tabActiva = tab;
    document.getElementById('tabSucursales').style.display = tab === 'sucursales' ? '' : 'none';
    document.getElementById('tabUsuarios').style.display   = tab === 'usuarios'   ? '' : 'none';
    document.getElementById('tabBtnSucursales').classList.toggle('active', tab === 'sucursales');
    document.getElementById('tabBtnUsuarios').classList.toggle('active', tab === 'usuarios');
    if (tab === 'sucursales') cargarSucursales(_gestionTenantId);
    if (tab === 'usuarios')   cargarUsuarios(_gestionTenantId);
}

// ---------- SUCURSALES ----------

async function cargarSucursales(tenantId) {
    document.getElementById('sucursalesLista').innerHTML =
        '<p style="padding:24px;text-align:center;color:#64748b"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';
    try {
        const r = await fetch(`${API}?action=sucursales_listar&tenant_id=${tenantId}`);
        const lista = await r.json();
        document.getElementById('sucursalesCount').textContent =
            lista.length ? `${lista.length} sucursal${lista.length !== 1 ? 'es' : ''}` : 'Sin sucursales';

        if (!lista.length) {
            document.getElementById('sucursalesLista').innerHTML =
                '<div style="padding:32px;text-align:center;color:#94a3b8"><i class="fas fa-store" style="font-size:2rem;display:block;margin-bottom:10px"></i>No hay sucursales</div>';
            return;
        }
        document.getElementById('sucursalesLista').innerHTML = `
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:left">Nombre</th>
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:left">Schema</th>
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:center">Usuarios</th>
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:left">Estado</th>
                <th style="padding:9px 14px"></th>
            </tr></thead>
            <tbody>${lista.map(s => `
            <tr style="border-bottom:1px solid #f1f5f9" id="srow-${s.id}">
                <td style="padding:10px 14px;font-weight:600;color:#1e293b;font-size:.88rem">${escHtml(s.nombre)}</td>
                <td style="padding:10px 14px;font-family:monospace;font-size:.76rem;color:#64748b">${escHtml(s.schema_name)}</td>
                <td style="padding:10px 14px;text-align:center;color:#475569;font-size:.88rem">${s.total_usuarios}</td>
                <td style="padding:10px 14px">
                    <span style="padding:2px 9px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;
                        background:${s.activo ? '#dcfce7' : '#fee2e2'};color:${s.activo ? '#16a34a' : '#dc2626'}">
                        ${s.activo ? 'Activa' : 'Inactiva'}
                    </span>
                </td>
                <td style="padding:10px 14px;text-align:right">
                    <div style="display:flex;gap:5px;justify-content:flex-end">
                        <button class="btn-icon btn-edit" title="Editar" onclick="editarSucursal(${s.id},'${escHtml(s.nombre)}','${escHtml(s.direccion||'')}','${escHtml(s.telefono||'')}')">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn-icon btn-toggle ${s.activo ? '' : 'inactivo'}" title="${s.activo ? 'Desactivar' : 'Activar'}"
                            onclick="toggleSucursal(${s.id})">
                            <i class="fas fa-power-off"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr id="sedit-${s.id}" style="display:none;background:#f8fafc">
                <td colspan="5" style="padding:12px 14px">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end">
                        <div><label style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;display:block;margin-bottom:4px">Nombre</label>
                            <input type="text" id="se-nombre-${s.id}" value="${escHtml(s.nombre)}" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.85rem;color:#1e293b"></div>
                        <div><label style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;display:block;margin-bottom:4px">Dirección</label>
                            <input type="text" id="se-dir-${s.id}" value="${escHtml(s.direccion||'')}" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.85rem;color:#1e293b"></div>
                        <div><label style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;display:block;margin-bottom:4px">Teléfono</label>
                            <input type="text" id="se-tel-${s.id}" value="${escHtml(s.telefono||'')}" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.85rem;color:#1e293b"></div>
                        <div style="display:flex;gap:6px">
                            <button class="btn-primary" style="padding:8px 14px;font-size:.82rem" onclick="guardarSucursal(${s.id})"><i class="fas fa-check"></i></button>
                            <button class="btn-cancel" style="padding:8px 12px;font-size:.82rem" onclick="document.getElementById('sedit-${s.id}').style.display='none'"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </td>
            </tr>`).join('')}
            </tbody>
        </table>`;
    } catch {
        document.getElementById('sucursalesLista').innerHTML =
            '<p style="padding:20px;text-align:center;color:#dc2626">Error al cargar sucursales</p>';
    }
}

function editarSucursal(id) {
    document.querySelectorAll('[id^="sedit-"]').forEach(r => r.style.display = 'none');
    document.getElementById(`sedit-${id}`).style.display = '';
}

async function guardarSucursal(id) {
    const nombre    = document.getElementById(`se-nombre-${id}`).value.trim();
    const direccion = document.getElementById(`se-dir-${id}`).value.trim();
    const telefono  = document.getElementById(`se-tel-${id}`).value.trim();
    if (!nombre) { toast('El nombre es requerido', 'err'); return; }
    try {
        const r = await fetch(`${API}?action=sucursal_actualizar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, nombre, direccion, telefono }),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        toast(d.message);
        cargarSucursales(_gestionTenantId);
        cargarTenants();
    } catch { toast('Error de conexión', 'err'); }
}

function mostrarFormSucursal() {
    document.getElementById('formSucursalWrap').style.display = 'block';
    setTimeout(() => document.getElementById('sNombre').focus(), 60);
}
function ocultarFormSucursal() {
    document.getElementById('formSucursalWrap').style.display = 'none';
    ['sNombre','sTelefono','sDireccion'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
}

async function crearSucursal() {
    const nombre    = document.getElementById('sNombre').value.trim();
    const telefono  = document.getElementById('sTelefono').value.trim();
    const direccion = document.getElementById('sDireccion').value.trim();
    if (!nombre) { toast('El nombre es requerido', 'err'); return; }
    try {
        const r = await fetch(`${API}?action=sucursal_crear`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tenant_id: _gestionTenantId, nombre, telefono, direccion }),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        toast(d.message);
        ocultarFormSucursal();
        cargarSucursales(_gestionTenantId);
        cargarTenants();
    } catch { toast('Error de conexión', 'err'); }
}

async function toggleSucursal(id) {
    try {
        const r = await fetch(`${API}?action=sucursal_toggle_activo`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        toast(d.activo ? 'Sucursal activada' : 'Sucursal desactivada');
        cargarSucursales(_gestionTenantId);
    } catch { toast('Error de conexión', 'err'); }
}

// ---------- USUARIOS ----------

async function cargarUsuarios(tenantId) {
    document.getElementById('usuariosLista').innerHTML =
        '<p style="padding:24px;text-align:center;color:#64748b"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';
    try {
        const r = await fetch(`${API}?action=usuarios_listar&tenant_id=${tenantId}`);
        const lista = await r.json();
        document.getElementById('usuariosCount').textContent =
            lista.length ? `${lista.length} usuario${lista.length !== 1 ? 's' : ''}` : 'Sin usuarios';

        if (!lista.length) {
            document.getElementById('usuariosLista').innerHTML =
                '<div style="padding:32px;text-align:center;color:#94a3b8"><i class="fas fa-users" style="font-size:2rem;display:block;margin-bottom:10px"></i>No hay usuarios</div>';
            return;
        }
        document.getElementById('usuariosLista').innerHTML = `
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:left">Nombre</th>
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:left">Usuario</th>
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:left">Accesos</th>
                <th style="padding:9px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;text-align:left">Estado</th>
                <th style="padding:9px 14px"></th>
            </tr></thead>
            <tbody>${lista.map(u => {
                const accesos = typeof u.accesos === 'string' ? JSON.parse(u.accesos) : (u.accesos || []);
                const accesoText = accesos.length
                    ? accesos.map(a => `<span style="display:inline-block;padding:1px 7px;border-radius:12px;font-size:.68rem;background:#eef2ff;color:#4f46e5;margin:1px">${escHtml(a.sucursal)} <span style="opacity:.7">${a.rol}</span></span>`).join(' ')
                    : '<span style="color:#94a3b8;font-size:.78rem">Sin asignar</span>';
                return `
            <tr style="border-bottom:1px solid #f1f5f9" id="urow-${u.id}">
                <td style="padding:10px 14px;font-weight:600;color:#1e293b;font-size:.88rem">${escHtml(u.nombre)} ${escHtml(u.apellido||'')}</td>
                <td style="padding:10px 14px;font-family:monospace;font-size:.82rem;color:#475569">${escHtml(u.username)}</td>
                <td style="padding:10px 14px">${accesoText}</td>
                <td style="padding:10px 14px">
                    <span style="padding:2px 9px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;
                        background:${u.activo ? '#dcfce7' : '#fee2e2'};color:${u.activo ? '#16a34a' : '#dc2626'}">
                        ${u.activo ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td style="padding:10px 14px;text-align:right">
                    <div style="display:flex;gap:5px;justify-content:flex-end">
                        <button class="btn-icon btn-edit" title="Editar"
                            onclick="prepararEditUsuario(${u.id},'${escHtml(u.nombre)}','${escHtml(u.apellido||'')}','${escHtml(u.username)}','${escHtml(u.email||'')}')">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn-icon btn-toggle ${u.activo ? '' : 'inactivo'}" title="${u.activo ? 'Desactivar' : 'Activar'}"
                            onclick="toggleUsuario(${u.id})">
                            <i class="fas fa-power-off"></i>
                        </button>
                    </div>
                </td>
            </tr>`; }).join('')}
            </tbody>
        </table>`;
    } catch {
        document.getElementById('usuariosLista').innerHTML =
            '<p style="padding:20px;text-align:center;color:#dc2626">Error al cargar usuarios</p>';
    }
}

function mostrarFormUsuario() {
    document.getElementById('uEditId').value   = '';
    document.getElementById('uNombre').value   = '';
    document.getElementById('uApellido').value = '';
    document.getElementById('uUsername').value = '';
    document.getElementById('uPassword').value = '';
    document.getElementById('uEmail').value    = '';
    document.getElementById('formUsuarioTitulo').textContent = 'Nuevo usuario';
    document.getElementById('uPassLabel').textContent = 'Contraseña *';
    cargarSucursalesEnSelect();
    document.getElementById('formUsuarioWrap').style.display = 'block';
    setTimeout(() => document.getElementById('uNombre').focus(), 60);
}

function prepararEditUsuario(id, nombre, apellido, username, email) {
    document.getElementById('uEditId').value   = id;
    document.getElementById('uNombre').value   = nombre;
    document.getElementById('uApellido').value = apellido;
    document.getElementById('uUsername').value = username;
    document.getElementById('uPassword').value = '';
    document.getElementById('uEmail').value    = email || '';
    document.getElementById('formUsuarioTitulo').textContent = 'Editar usuario';
    document.getElementById('uPassLabel').textContent = 'Nueva contraseña (dejar vacío para no cambiar)';
    cargarSucursalesEnSelect();
    document.getElementById('formUsuarioWrap').style.display = 'block';
    document.getElementById('formUsuarioWrap').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function ocultarFormUsuario() {
    document.getElementById('formUsuarioWrap').style.display = 'none';
}

async function cargarSucursalesEnSelect() {
    const sel = document.getElementById('uSucursal');
    sel.innerHTML = '<option value="">Cargando...</option>';
    try {
        const r = await fetch(`${API}?action=sucursales_listar&tenant_id=${_gestionTenantId}`);
        const lista = await r.json();
        sel.innerHTML = '<option value="">— Sin asignar —</option>';
        lista.filter(s => s.activo).forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nombre;
            sel.appendChild(opt);
        });
    } catch { sel.innerHTML = '<option value="">— Error —</option>'; }
}

async function guardarUsuario() {
    const editId   = document.getElementById('uEditId').value;
    const nombre   = document.getElementById('uNombre').value.trim();
    const apellido = document.getElementById('uApellido').value.trim();
    const username = document.getElementById('uUsername').value.trim();
    const password = document.getElementById('uPassword').value;
    const email    = document.getElementById('uEmail').value.trim();
    const sucursalId = document.getElementById('uSucursal').value;

    if (!nombre || !username) { toast('Nombre y usuario son requeridos', 'err'); return; }

    try {
        if (editId) {
            // Actualizar datos
            const r = await fetch(`${API}?action=usuario_actualizar`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(editId), nombre, apellido, username, email }),
            });
            const d = await r.json();
            if (d.error) { toast(d.message, 'err'); return; }
            // Cambiar contraseña si se ingresó
            if (password) {
                if (password.length < 4) { toast('Contraseña mínimo 4 caracteres', 'err'); return; }
                const rp = await fetch(`${API}?action=usuario_cambiar_password`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parseInt(editId), password }),
                });
                const dp = await rp.json();
                if (dp.error) { toast(dp.message, 'err'); return; }
            }
            toast('Usuario actualizado');
        } else {
            // Crear nuevo
            if (!password || password.length < 4) { toast('Contraseña mínimo 4 caracteres', 'err'); return; }
            const body = { tenant_id: _gestionTenantId, nombre, apellido, username, password, email };
            if (sucursalId) body.sucursal_id = parseInt(sucursalId);
            const r = await fetch(`${API}?action=crear_admin_tenant`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const d = await r.json();
            if (d.error) { toast(d.message, 'err'); return; }
            toast(d.message);
        }
        ocultarFormUsuario();
        cargarUsuarios(_gestionTenantId);
        cargarStats();
        cargarTenants();
    } catch { toast('Error de conexión', 'err'); }
}

async function toggleUsuario(id) {
    try {
        const r = await fetch(`${API}?action=usuario_toggle_activo`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        toast(d.activo ? 'Usuario activado' : 'Usuario desactivado');
        cargarUsuarios(_gestionTenantId);
        cargarStats();
    } catch { toast('Error de conexión', 'err'); }
}

// ---- Cerrar modal al click en overlay ----

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) {
            cerrarModal(overlay.id);
            ocultarFormSucursal();
            ocultarFormUsuario();
        }
    });
});

function irEmpresa(id) {
    window.location.href = `empresa.php?id=${id}`;
}

// ---- Mi cuenta / Perfil ----

function togglePerfilPass(inputId, iconId) {
    const inp = document.getElementById(inputId);
    const ico = document.getElementById(iconId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function switchPerfilTab(tab) {
    document.getElementById('panelDatos').style.display    = tab === 'datos'    ? 'block' : 'none';
    document.getElementById('panelPassword').style.display = tab === 'password' ? 'block' : 'none';
    document.getElementById('tabDatos').classList.toggle('active',    tab === 'datos');
    document.getElementById('tabPassword').classList.toggle('active', tab === 'password');
    perfilAlertHide();
}

function perfilAlertShow(msg, tipo) {
    const el = document.getElementById('perfilAlert');
    el.style.display = 'flex';
    el.style.background = tipo === 'ok' ? '#f0fdf4' : '#fef2f2';
    el.style.border     = `1px solid ${tipo === 'ok' ? '#bbf7d0' : '#fecaca'}`;
    el.style.color      = tipo === 'ok' ? '#15803d' : '#dc2626';
    el.innerHTML = `<i class="fas ${tipo === 'ok' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${msg}`;
}

function perfilAlertHide() {
    const el = document.getElementById('perfilAlert');
    el.style.display = 'none';
}

async function abrirPerfil() {
    perfilAlertHide();
    switchPerfilTab('datos');
    document.getElementById('pPassActual').value  = '';
    document.getElementById('pPassNueva').value   = '';
    document.getElementById('pPassConfirm').value = '';
    try {
        const r = await fetch(`${API}?action=perfil`);
        const d = await r.json();
        if (!d.error) {
            document.getElementById('pNombre').value   = d.nombre   || '';
            document.getElementById('pApellido').value = d.apellido || '';
            document.getElementById('pUsername').value = d.username || '';
            document.getElementById('pEmail').value    = d.email    || '';
        }
    } catch {}
    document.getElementById('modalPerfilOverlay').classList.add('active');
}

async function guardarDatosPerfil() {
    const nombre   = document.getElementById('pNombre').value.trim();
    const apellido = document.getElementById('pApellido').value.trim();
    const username = document.getElementById('pUsername').value.trim();
    const email    = document.getElementById('pEmail').value.trim();
    if (!nombre || !username) { perfilAlertShow('Nombre y usuario son requeridos.', 'err'); return; }
    try {
        const r = await fetch(`${API}?action=perfil_actualizar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, apellido, username, email }),
        });
        const d = await r.json();
        if (d.error) { perfilAlertShow(d.message, 'err'); return; }
        perfilAlertShow('Datos actualizados correctamente.', 'ok');
        // Actualizar nombre en topbar
        document.querySelector('.topbar-right .user-info strong').textContent = d.nombre_completo || nombre;
    } catch { perfilAlertShow('Error de conexión.', 'err'); }
}

async function guardarPassword() {
    const actual   = document.getElementById('pPassActual').value;
    const nueva    = document.getElementById('pPassNueva').value;
    const confirm  = document.getElementById('pPassConfirm').value;
    if (!actual)         { perfilAlertShow('Ingresa tu contraseña actual.', 'err'); return; }
    if (nueva.length < 4){ perfilAlertShow('La nueva contraseña debe tener mínimo 4 caracteres.', 'err'); return; }
    if (nueva !== confirm){ perfilAlertShow('Las contraseñas nuevas no coinciden.', 'err'); return; }
    try {
        const r = await fetch(`${API}?action=perfil_cambiar_password`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password_actual: actual, password_nueva: nueva }),
        });
        const d = await r.json();
        if (d.error) { perfilAlertShow(d.message, 'err'); return; }
        perfilAlertShow('Contraseña cambiada correctamente.', 'ok');
        document.getElementById('pPassActual').value  = '';
        document.getElementById('pPassNueva').value   = '';
        document.getElementById('pPassConfirm').value = '';
    } catch { perfilAlertShow('Error de conexión.', 'err'); }
}

// ---- Init ----
cargarStats();
cargarTenants();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
