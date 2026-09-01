<?php
// ============================================================
// ARCHIVO: farmacia/modules/superadmin/empresa.php
// DESCRIPCIÓN: Panel de administración de una empresa (tenant)
// ============================================================

require_once '../../config/auth.php';
requireAuth(['superadmin']);
require_once '../../config/database.php';

$tenant_id = intval($_GET['id'] ?? 0);
if (!$tenant_id) { header('Location: index.php'); exit; }

$db = getDB();
$tenant = $db->prepare("SELECT * FROM public.tenants WHERE id = :id");
$tenant->execute([':id' => $tenant_id]);
$tenant = $tenant->fetch();
if (!$tenant) { header('Location: index.php'); exit; }

$planes = ['basico' => 'Básico', 'pro' => 'Pro', 'enterprise' => 'Enterprise'];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <title><?= htmlspecialchars($tenant['nombre']) ?> — FarmaSystem</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; color: #1e293b; min-height: 100vh; }

        /* Topbar */
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 0 28px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .back-btn {
            display: flex; align-items: center; gap: 7px;
            background: #f1f5f9; border: 1px solid #e2e8f0;
            color: #475569; padding: 7px 14px; border-radius: 8px;
            text-decoration: none; font-size: .82rem; font-weight: 600;
            transition: background .15s;
        }
        .back-btn:hover { background: #e2e8f0; }
        .topbar-brand { font-weight: 700; font-size: .95rem; color: #1e293b; }
        .topbar-brand span { color: #6366f1; }
        .btn-logout {
            display: flex; align-items: center; gap: 6px;
            background: #f1f5f9; color: #475569;
            border: 1px solid #e2e8f0; padding: 7px 14px;
            border-radius: 8px; cursor: pointer; font-size: .82rem;
            text-decoration: none; transition: background .15s, color .15s;
        }
        .btn-logout:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

        /* Main */
        .main { padding: 28px 32px; max-width: 1300px; margin: 0 auto; }

        /* Hero header */
        .hero {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 24px 28px;
            display: flex; align-items: center; gap: 20px;
            margin-bottom: 28px; box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .hero-icon {
            width: 56px; height: 56px; background: #eef2ff;
            border-radius: 14px; display: flex; align-items: center;
            justify-content: center; font-size: 1.4rem; color: #6366f1; flex-shrink: 0;
        }
        .hero-info { flex: 1; }
        .hero-nombre { font-size: 1.3rem; font-weight: 700; color: #1e293b; }
        .hero-meta { display: flex; align-items: center; gap: 10px; margin-top: 6px; flex-wrap: wrap; }
        .badge {
            display: inline-block; padding: 3px 10px;
            border-radius: 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase;
        }
        .badge-activo     { background: #dcfce7; color: #16a34a; }
        .badge-inactivo   { background: #fee2e2; color: #dc2626; }
        .badge-basico     { background: #e2e8f0; color: #475569; }
        .badge-pro        { background: #fef3c7; color: #b45309; }
        .badge-enterprise { background: #ede9fe; color: #6d28d9; }
        .hero-slug { font-size: .8rem; color: #94a3b8; font-family: monospace; }
        .hero-actions { display: flex; gap: 10px; }

        /* Stats row */
        .stats-row {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 14px; margin-bottom: 28px;
        }
        @media (max-width: 640px) { .stats-row { grid-template-columns: 1fr; } }
        .stat-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 12px; padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .stat-icon.blue   { background: #dbeafe; color: #2563eb; }
        .stat-icon.green  { background: #dcfce7; color: #16a34a; }
        .stat-icon.indigo { background: #eef2ff; color: #6366f1; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #1e293b; line-height: 1; }
        .stat-label { font-size: .78rem; color: #64748b; margin-top: 3px; }

        /* Sections */
        .sections { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 900px) { .sections { grid-template-columns: 1fr; } }

        .section-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .section-head {
            padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .section-head-title {
            font-size: .95rem; font-weight: 700; color: #1e293b;
            display: flex; align-items: center; gap: 8px;
        }
        .section-head-title i { color: #6366f1; }

        /* Table inside section */
        #listaSucursales, #listaUsuarios { overflow-x: auto; }
        .sec-table { width: 100%; border-collapse: collapse; }
        .sec-table thead th {
            background: #f8fafc; padding: 9px 16px;
            font-size: .72rem; font-weight: 700;
            text-transform: uppercase; color: #64748b; text-align: left;
        }
        .sec-table tbody tr { border-top: 1px solid #f1f5f9; transition: background .1s; }
        .sec-table tbody tr:hover { background: #fafafe; }
        .sec-table td { padding: 11px 16px; font-size: .86rem; color: #475569; vertical-align: middle; }
        .td-name { font-weight: 600; color: #1e293b; }

        /* Empty state */
        .empty { padding: 40px 20px; text-align: center; color: #94a3b8; font-size: .85rem; }
        .empty i { font-size: 2rem; display: block; margin-bottom: 10px; }

        /* Buttons */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff; border: none; padding: 8px 16px;
            border-radius: 8px; cursor: pointer; font-size: .82rem;
            font-weight: 600; transition: opacity .15s; white-space: nowrap;
        }
        .btn-primary:hover { opacity: .88; }
        .btn-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 7px; border: none;
            cursor: pointer; font-size: .75rem; transition: background .15s, color .15s;
        }
        .btn-edit         { background: #eef2ff; color: #6366f1; }
        .btn-edit:hover   { background: #6366f1; color: #fff; }
        .btn-toggle       { background: #fef3c7; color: #d97706; }
        .btn-toggle:hover { background: #d97706; color: #fff; }
        .btn-toggle.off   { background: #fee2e2; color: #dc2626; }
        .btn-toggle.off:hover { background: #dc2626; color: #fff; }
        .btn-cancel {
            background: #f1f5f9; color: #475569;
            border: 1px solid #e2e8f0; padding: 8px 14px;
            border-radius: 8px; cursor: pointer; font-size: .82rem; font-weight: 600;
        }
        .btn-cancel:hover { background: #e2e8f0; }

        /* Inline form panel */
        .form-panel {
            display: none; border-top: 1px solid #e2e8f0;
            padding: 18px 20px; background: #f8fafc;
        }
        .form-panel.open { display: block; }
        .form-panel-title { font-size: .85rem; font-weight: 700; color: #1e293b; margin-bottom: 14px; }
        .form-row { display: grid; gap: 12px; margin-bottom: 12px; }
        .form-row.cols2 { grid-template-columns: 1fr 1fr; }
        .form-row.cols3 { grid-template-columns: 1fr 1fr 1fr; }
        .form-group label {
            display: block; font-size: .72rem; font-weight: 600;
            color: #64748b; text-transform: uppercase; margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%; padding: 9px 11px;
            background: #fff; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: .88rem; color: #1e293b;
            outline: none; transition: border-color .15s;
        }
        .form-group input:focus, .form-group select:focus { border-color: #6366f1; }
        .sunat-readonly {
            width: 100%; padding: 9px 11px;
            background: #f8fafc; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: .88rem; color: #1e293b;
            font-family: monospace; min-height: 20px;
        }
        .sunat-readonly.empty { color: #94a3b8; font-family: inherit; font-style: italic; }
        .form-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 4px; }

        /* Inline edit row */
        .edit-row { display: none; background: #f8fafc; }
        .edit-row td { padding: 10px 14px; }

        /* Loading */
        .loading { padding: 32px; text-align: center; color: #94a3b8; }

        /* Toast */
        #toast {
            position: fixed; bottom: 24px; right: 24px;
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 12px 18px;
            font-size: .85rem; color: #1e293b;
            display: flex; align-items: center; gap: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.1);
            z-index: 9999; opacity: 0; transform: translateY(8px);
            transition: opacity .25s, transform .25s; pointer-events: none;
        }
        #toast.show { opacity: 1; transform: none; }
        #toast.ok  { border-color: #86efac; }
        #toast.err { border-color: #fca5a5; }
        #toast.ok  i { color: #16a34a; }
        #toast.err i { color: #dc2626; }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.4); z-index: 500;
            align-items: center; justify-content: center; padding: 16px;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            display: block;
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 16px; width: 100%; max-width: 440px;
            box-shadow: 0 20px 50px rgba(0,0,0,.15);
            animation: modalIn .18s ease;
        }
        @keyframes modalIn { from{opacity:0;transform:translateY(-12px) scale(.97)} to{opacity:1;transform:none} }
        .modal-header {
            padding: 18px 22px 14px; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-title { font-size: .95rem; font-weight: 700; color: #1e293b; }
        .modal-close { background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1rem; border-radius: 6px; padding: 3px; }
        .modal-close:hover { color: #1e293b; }
        .modal-body { padding: 18px 22px; }
        .modal-footer { padding: 14px 22px; border-top: 1px solid #e2e8f0; display: flex; gap: 8px; justify-content: flex-end; }

        /* ---- Mobile (celular, Android/iOS) ---- */
        @media (max-width: 640px) {
            .topbar { height: auto; min-height: 60px; padding: 10px 14px; flex-wrap: wrap; gap: 8px; }
            .topbar-left { gap: 10px; }
            .main { padding: 16px; }
            .hero { flex-direction: column; align-items: flex-start; gap: 14px; padding: 18px 20px; }
            .hero-actions { width: 100%; }
            .hero-actions .btn-primary { width: 100%; justify-content: center; }
            .form-row.cols2, .form-row.cols3 { grid-template-columns: 1fr; }
            .btn-icon { width: 36px; height: 36px; font-size: .8rem; }
            .modal-header, .modal-body, .modal-footer { padding-left: 16px; padding-right: 16px; }
            #toast { left: 16px; right: 16px; bottom: calc(16px + env(safe-area-inset-bottom)); }
        }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Panel</a>
        <div class="topbar-brand">FarmaSystem <span>/ <?= htmlspecialchars($tenant['nombre']) ?></span></div>
    </div>
    <a href="../auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
</div>

<!-- Main -->
<div class="main">

    <!-- Hero -->
    <div class="hero">
        <div class="hero-icon" id="heroLogoWrap">
            <?php if (!empty($tenant['logo'])): ?>
                <img src="../../<?= htmlspecialchars($tenant['logo']) ?>" alt="logo"
                     style="width:56px;height:56px;object-fit:contain;border-radius:14px">
            <?php else: ?>
                <i class="fas fa-building"></i>
            <?php endif; ?>
        </div>
        <div class="hero-info">
            <div class="hero-nombre"><?= htmlspecialchars($tenant['nombre']) ?></div>
            <div class="hero-meta">
                <span class="badge badge-<?= $tenant['activo'] ? 'activo' : 'inactivo' ?>">
                    <?= $tenant['activo'] ? 'Activa' : 'Inactiva' ?>
                </span>
                <span class="badge badge-<?= $tenant['plan'] ?>">
                    <?= $planes[$tenant['plan']] ?? $tenant['plan'] ?>
                </span>
                <span class="hero-slug"><i class="fas fa-tag" style="margin-right:4px"></i><?= htmlspecialchars($tenant['slug']) ?></span>
            </div>
        </div>
        <div class="hero-actions">
            <button class="btn-primary" onclick="abrirEditEmpresa()">
                <i class="fas fa-pen"></i> Editar empresa
            </button>
            <button class="btn-cancel" onclick="abrirSunatInfo()"
                    style="display:inline-flex;align-items:center;gap:7px;background:#eef2ff;color:#4f46e5;border-color:#c7d2fe">
                <i class="fas fa-key"></i> Datos SUNAT
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-store"></i></div>
            <div>
                <div class="stat-value" id="cntSucursales">—</div>
                <div class="stat-label">Sucursales</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-value" id="cntUsuarios">—</div>
                <div class="stat-label">Usuarios</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon indigo"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div class="stat-value" style="font-size:1rem;margin-top:4px">
                    <?= date('d M Y', strtotime($tenant['created_at'])) ?>
                </div>
                <div class="stat-label">Fecha de registro</div>
            </div>
        </div>
    </div>

    <!-- Sections -->
    <div class="sections">

        <!-- SUCURSALES -->
        <div class="section-card">
            <div class="section-head">
                <div class="section-head-title">
                    <i class="fas fa-store"></i> Sucursales
                </div>
                <button class="btn-primary" onclick="toggleForm('formSucursal')">
                    <i class="fas fa-plus"></i> Nueva
                </button>
            </div>

            <!-- Form nueva sucursal -->
            <div class="form-panel" id="formSucursal">
                <div class="form-panel-title"><i class="fas fa-plus-circle" style="color:#6366f1;margin-right:6px"></i>Nueva sucursal</div>
                <div class="form-row cols2">
                    <div class="form-group"><label>Nombre *</label><input type="text" id="sNombre" placeholder="Sucursal Norte"></div>
                    <div class="form-group"><label>Teléfono</label><input type="text" id="sTel" placeholder="01-234-5678"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Dirección</label><input type="text" id="sDir" placeholder="Av. Principal 123"></div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="toggleForm('formSucursal')">Cancelar</button>
                    <button class="btn-primary" onclick="crearSucursal()"><i class="fas fa-save"></i> Crear</button>
                </div>
            </div>

            <div id="listaSucursales"><div class="loading"><i class="fas fa-spinner fa-spin"></i></div></div>
        </div>

        <!-- USUARIOS -->
        <div class="section-card">
            <div class="section-head">
                <div class="section-head-title">
                    <i class="fas fa-users"></i> Usuarios
                </div>
                <button class="btn-primary" onclick="toggleForm('formUsuario')">
                    <i class="fas fa-user-plus"></i> Nuevo
                </button>
            </div>

            <!-- Form nuevo usuario -->
            <div class="form-panel" id="formUsuario">
                <div class="form-panel-title" id="formUsuarioTitulo">
                    <i class="fas fa-user-plus" style="color:#6366f1;margin-right:6px"></i>Nuevo usuario
                </div>
                <input type="hidden" id="uEditId">
                <div class="form-row cols2">
                    <div class="form-group"><label>Nombre *</label><input type="text" id="uNombre" placeholder="Juan"></div>
                    <div class="form-group"><label>Apellido</label><input type="text" id="uApellido" placeholder="Pérez"></div>
                </div>
                <div class="form-row cols2">
                    <div class="form-group"><label>Usuario *</label><input type="text" id="uUsername" placeholder="juan_perez" autocomplete="off"></div>
                    <div class="form-group">
                        <label id="uPassLabel">Contraseña *</label>
                        <div style="position:relative">
                            <input type="password" id="uPassword" placeholder="Mín. 4 caracteres" style="padding-right:40px;width:100%">
                            <button type="button" id="uPassToggle" onclick="togglePassVisibility()"
                                title="Mostrar/ocultar contraseña"
                                style="position:absolute;right:0;top:0;height:100%;width:38px;background:none;border:none;cursor:pointer;color:#94a3b8;font-size:.95rem;display:flex;align-items:center;justify-content:center;border-radius:0 8px 8px 0;transition:color .15s"
                                onmouseenter="this.style.color='#6366f1'" onmouseleave="this.style.color='#94a3b8'">
                                <i class="fas fa-eye" id="uPassEyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="uPassHint" style="display:none;margin:-8px 0 10px;padding:8px 12px;background:#fefce8;border:1px solid #fde68a;border-radius:8px;font-size:.8rem;color:#92400e">
                    <i class="fas fa-info-circle" style="margin-right:5px"></i>
                    Se muestra la contraseña actual. Modifícala si deseas cambiarla, o déjala igual.
                </div>
                <div class="form-row cols2">
                    <div class="form-group">
                        <label>Sucursal asignada</label>
                        <select id="uSucursal"><option value="">— Sin asignar —</option></select>
                    </div>
                    <div class="form-group">
                        <label>Rol</label>
                        <select id="uRol">
                            <option value="cajero">Cajero</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea id="uObservaciones" placeholder="Notas sobre el usuario..." rows="2"
                            style="width:100%;padding:9px 11px;background:#fff;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.88rem;color:#1e293b;outline:none;resize:vertical;font-family:inherit;transition:border-color .15s"
                            onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="cerrarFormUsuario()">Cancelar</button>
                    <button class="btn-primary" onclick="guardarUsuario()"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </div>

            <div id="listaUsuarios"><div class="loading"><i class="fas fa-spinner fa-spin"></i></div></div>
        </div>

    </div>
</div>

<!-- Modal editar empresa -->
<div class="modal-overlay" id="modalEmpresaOverlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Editar empresa</span>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Logo</label>
                    <div style="display:flex;align-items:center;gap:14px">
                        <div style="width:60px;height:60px;border-radius:10px;border:1px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8fafc;flex-shrink:0">
                            <?php if (!empty($tenant['logo'])): ?>
                                <img id="eLogoPreviewImg" src="../../<?= htmlspecialchars($tenant['logo']) ?>" alt="logo" style="width:100%;height:100%;object-fit:contain">
                                <i id="eLogoIcon" class="fas fa-image" style="color:#cbd5e1;font-size:1.3rem;display:none"></i>
                            <?php else: ?>
                                <img id="eLogoPreviewImg" src="" alt="logo" style="display:none;width:100%;height:100%;object-fit:contain">
                                <i id="eLogoIcon" class="fas fa-image" style="color:#cbd5e1;font-size:1.3rem"></i>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1">
                            <label for="eLogoFile" style="display:inline-flex;align-items:center;gap:7px;cursor:pointer;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:7px 13px;font-size:.8rem;font-weight:600;color:#475569">
                                <i class="fas fa-upload"></i> Elegir imagen
                            </label>
                            <input type="file" id="eLogoFile" accept="image/*" style="display:none" onchange="ePreviewLogo(this)">
                            <p id="eLogoFileName" style="font-size:.74rem;color:#94a3b8;margin-top:5px">JPG, PNG, WEBP — máx. 2 MB</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Nombre *</label><input type="text" id="eNombre" value="<?= htmlspecialchars($tenant['nombre']) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Plan</label>
                    <select id="ePlan">
                        <option value="basico"     <?= $tenant['plan']==='basico'     ? 'selected':'' ?>>Básico</option>
                        <option value="pro"        <?= $tenant['plan']==='pro'        ? 'selected':'' ?>>Pro</option>
                        <option value="enterprise" <?= $tenant['plan']==='enterprise' ? 'selected':'' ?>>Enterprise</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" onclick="guardarEmpresa()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Datos SUNAT (solo lectura) + Notas internas de superadmin -->
<div class="modal-overlay" id="modalSunatInfoOverlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-key" style="color:#4f46e5;margin-right:8px"></i>Datos SUNAT</span>
            <button class="modal-close" onclick="cerrarSunatInfo()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:.78rem;color:#94a3b8;margin-bottom:16px">
                Editable desde acá por el superadmin. El Admin de la empresa también puede cambiar estos datos desde su propio panel (Configuración) — el que se guarde último es el que queda.
            </p>
            <div class="form-row">
                <div class="form-group">
                    <label>RUC</label>
                    <input type="text" id="sInfoRuc" placeholder="Sin configurar">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Usuario SOL</label>
                    <input type="text" id="sInfoUsuario" placeholder="Sin configurar">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Clave SOL</label>
                    <input type="text" id="sInfoClave" placeholder="Sin configurar">
                </div>
            </div>
            <div class="form-row" style="margin-top:6px">
                <div class="form-group">
                    <label>Notas <span style="text-transform:none;font-weight:400;color:#94a3b8">(solo visibles para superadmin)</span></label>
                    <textarea id="sInfoNotas" rows="4" style="width:100%;padding:9px 11px;background:#fff;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.88rem;color:#1e293b;outline:none;resize:vertical;font-family:inherit"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarSunatInfo()">Cerrar</button>
            <button class="btn-primary" onclick="guardarSunatInfo()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal upgrade de plan -->
<div class="modal-overlay" id="modalUpgradeOverlay">
    <div class="modal" style="max-width:420px">
        <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:16px 16px 0 0">
            <span class="modal-title" style="color:#fff"><i class="fas fa-arrow-circle-up" style="margin-right:8px"></i>Límite de plan alcanzado</span>
            <button class="modal-close" style="color:rgba(255,255,255,.7)" onclick="cerrarUpgrade()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="text-align:center;padding:28px 24px 16px">
            <div style="width:64px;height:64px;background:#eef2ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.8rem">🏪</div>
            <p id="upgradeMsg" style="font-size:.93rem;color:#475569;line-height:1.6;margin-bottom:18px"></p>
            <div id="upgradePlanes" style="display:flex;gap:12px;justify-content:center;margin-bottom:6px"></div>
        </div>
        <div class="modal-footer" style="justify-content:center;gap:10px">
            <button class="btn-cancel" onclick="cerrarUpgrade()">Cancelar</button>
            <button class="btn-primary" id="btnConfirmarUpgrade" onclick="confirmarUpgrade()">
                <i class="fas fa-rocket"></i> <span id="btnUpgradeLabel">Cambiar plan</span>
            </button>
        </div>
    </div>
</div>

<div id="toast"><i id="toastIcon" class="fas fa-check-circle"></i><span id="toastMsg"></span></div>

<script>
const API        = 'api.php';
const TENANT_ID  = <?= $tenant_id ?>;

// ---- Utilidades ----
function toast(msg, tipo = 'ok') {
    const el = document.getElementById('toast');
    el.className = 'show ' + tipo;
    document.getElementById('toastIcon').className = tipo === 'ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    document.getElementById('toastMsg').textContent = msg;
    clearTimeout(el._t);
    el._t = setTimeout(() => el.className = el.className.replace('show','').trim(), 3500);
}
function esc(s) {
    return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/\r?\n/g,' ');
}
function toggleForm(id) {
    const el = document.getElementById(id);
    el.classList.toggle('open');
    if (el.classList.contains('open')) {
        el.querySelector('input')?.focus();
        if (id === 'formUsuario') cargarSucursalesSelect();
    }
}
function cerrarModal() {
    document.getElementById('modalEmpresaOverlay').classList.remove('active');
}

// ---- Stats ----
function actualizarStats(sucursales, usuarios) {
    document.getElementById('cntSucursales').textContent = sucursales ?? '—';
    document.getElementById('cntUsuarios').textContent   = usuarios   ?? '—';
}

// ---- SUCURSALES ----
async function cargarSucursales() {
    try {
        const r = await fetch(`${API}?action=sucursales_listar&tenant_id=${TENANT_ID}`);
        const lista = await r.json();
        const activas = lista.filter(s => !s.archivada);
        actualizarStats(activas.length, null);

        if (!lista.length) {
            document.getElementById('listaSucursales').innerHTML =
                '<div class="empty"><i class="fas fa-store"></i>No hay sucursales registradas</div>';
            return;
        }
        document.getElementById('listaSucursales').innerHTML = `
        <table class="sec-table">
            <thead><tr>
                <th>Nombre</th><th>Dirección</th><th>Usuarios</th><th>Estado</th><th></th>
            </tr></thead>
            <tbody>${lista.map(s => `
            <tr id="srow-${s.id}" style="${s.archivada ? 'opacity:.55' : ''}">
                <td class="td-name">
                    ${esc(s.nombre)}
                    ${s.archivada ? '<span style="margin-left:6px;padding:1px 7px;border-radius:20px;font-size:.65rem;font-weight:700;text-transform:uppercase;background:#f1f5f9;color:#94a3b8">Archivada</span>' : ''}
                </td>
                <td style="color:#64748b;font-size:.82rem">${esc(s.direccion||'—')}</td>
                <td style="text-align:center">${s.total_usuarios}</td>
                <td>${s.archivada
                    ? '<span style="padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;background:#f1f5f9;color:#94a3b8">Archivada</span>'
                    : `<span style="padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;background:${s.activo?'#dcfce7':'#fee2e2'};color:${s.activo?'#16a34a':'#dc2626'}">${s.activo?'Activa':'Inactiva'}</span>`
                }</td>
                <td style="text-align:right">
                    <div style="display:flex;gap:5px;justify-content:flex-end">
                        ${s.archivada ? `
                            <button class="btn-icon" title="Restaurar sucursal"
                                style="background:#f0fdf4;color:#16a34a"
                                onclick="restaurarSucursal(${s.id})"><i class="fas fa-box-open"></i></button>
                        ` : `
                            <button class="btn-icon btn-edit" title="Editar" onclick="abrirEditSucursal(${s.id})"><i class="fas fa-pen"></i></button>
                            <button class="btn-icon btn-toggle ${s.activo?'':'off'}" title="${s.activo?'Desactivar':'Activar'}"
                                onclick="toggleSucursal(${s.id}, ${s.activo ? 'true' : 'false'}, '${esc(s.nombre)}')"><i class="fas fa-power-off"></i></button>
                            <button class="btn-icon" title="Archivar sucursal (optimizar costos)"
                                style="background:#fef3c7;color:#b45309"
                                onclick="archivarSucursal(${s.id},'${esc(s.nombre)}')"><i class="fas fa-archive"></i></button>
                        `}
                    </div>
                </td>
            </tr>
            <tr class="edit-row" id="sedit-${s.id}">
                <td colspan="5">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end">
                        <div class="form-group"><label>Nombre</label><input type="text" id="se-n-${s.id}" value="${esc(s.nombre)}"></div>
                        <div class="form-group"><label>Dirección</label><input type="text" id="se-d-${s.id}" value="${esc(s.direccion||'')}"></div>
                        <div class="form-group"><label>Teléfono</label><input type="text" id="se-t-${s.id}" value="${esc(s.telefono||'')}"></div>
                        <div style="display:flex;gap:6px;padding-bottom:1px">
                            <button class="btn-primary" style="padding:9px 13px" onclick="guardarSucursal(${s.id})"><i class="fas fa-check"></i></button>
                            <button class="btn-cancel" style="padding:9px 11px" onclick="cerrarEditSucursal(${s.id})"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </td>
            </tr>`).join('')}
            </tbody>
        </table>`;
    } catch { document.getElementById('listaSucursales').innerHTML = '<div class="empty" style="color:#dc2626">Error al cargar</div>'; }
}

function abrirEditSucursal(id) {
    document.querySelectorAll('.edit-row').forEach(r => r.style.display = 'none');
    document.getElementById(`sedit-${id}`).style.display = '';
    document.getElementById(`se-n-${id}`).focus();
}
function cerrarEditSucursal(id) { document.getElementById(`sedit-${id}`).style.display = 'none'; }

async function guardarSucursal(id) {
    const nombre = document.getElementById(`se-n-${id}`).value.trim();
    if (!nombre) { toast('El nombre es requerido', 'err'); return; }
    const r = await fetch(`${API}?action=sucursal_actualizar`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id, nombre, direccion: document.getElementById(`se-d-${id}`).value.trim(), telefono: document.getElementById(`se-t-${id}`).value.trim() }),
    });
    const d = await r.json();
    if (d.error) { toast(d.message,'err'); return; }
    toast(d.message);
    cargarSucursales();
}

async function crearSucursal() {
    const nombre = document.getElementById('sNombre').value.trim();
    if (!nombre) { toast('El nombre es requerido', 'err'); return; }
    const r = await fetch(`${API}?action=sucursal_crear`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ tenant_id: TENANT_ID, nombre, telefono: document.getElementById('sTel').value.trim(), direccion: document.getElementById('sDir').value.trim() }),
    });
    const d = await r.json();
    if (d.error && d.upgrade) { mostrarUpgrade(d); return; }
    if (d.error) { toast(d.message,'err'); return; }
    toast(d.message);
    document.getElementById('formSucursal').classList.remove('open');
    ['sNombre','sTel','sDir'].forEach(id => document.getElementById(id).value = '');
    cargarSucursales();
    cargarSucursalesSelect();
}

async function archivarSucursal(id, nombre) {
    if (!confirm(`¿Archivar "${nombre}"?\n\nLa sucursal quedará inactiva y no contará para el límite de tu plan. Si el total baja del límite Pro, el plan se reducirá automáticamente.`)) return;
    try {
        const r = await fetch(`${API}?action=sucursal_archivar`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}),
        });
        const d = await r.json();
        if (d.error) { toast(d.message,'err'); return; }
        if (d.plan_nuevo) {
            const PLAN_LABELS = { basico:'Básico', pro:'Pro', enterprise:'Enterprise' };
            toast(`Sucursal archivada. Plan reducido automáticamente a ${PLAN_LABELS[d.plan_nuevo]||d.plan_nuevo}.`);
            const badge = document.querySelector('.badge-basico, .badge-pro, .badge-enterprise');
            if (badge) {
                badge.className = `badge badge-${d.plan_nuevo}`;
                badge.textContent = PLAN_LABELS[d.plan_nuevo];
            }
        } else {
            toast('Sucursal archivada');
        }
        cargarSucursales();
    } catch { toast('Error de conexión','err'); }
}

async function restaurarSucursal(id) {
    try {
        const r = await fetch(`${API}?action=sucursal_restaurar`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}),
        });
        const d = await r.json();
        if (d.error) { toast(d.message,'err'); return; }
        const PLAN_LABELS = { basico:'Básico', pro:'Pro', enterprise:'Enterprise' };
        const planActual = document.querySelector('.badge-basico, .badge-pro, .badge-enterprise')?.className.match(/badge-(\w+)/)?.[1];
        if (d.plan_nuevo && d.plan_nuevo !== planActual) {
            toast(`Sucursal restaurada. Plan actualizado a ${PLAN_LABELS[d.plan_nuevo]||d.plan_nuevo}.`);
            const badge = document.querySelector('.badge-basico, .badge-pro, .badge-enterprise');
            if (badge) {
                badge.className = `badge badge-${d.plan_nuevo}`;
                badge.textContent = PLAN_LABELS[d.plan_nuevo];
            }
        } else {
            toast('Sucursal restaurada');
        }
        cargarSucursales();
    } catch { toast('Error de conexión','err'); }
}

async function toggleSucursal(id, activaActualmente, nombre) {
    if (activaActualmente && !confirm(`¿Desactivar "${nombre}"?\n\nEstá ACTIVA y funcionando — al desactivarla, sus usuarios ya no podrán operar en ella.`)) return;
    const r = await fetch(`${API}?action=sucursal_toggle_activo`, {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}),
    });
    const d = await r.json();
    if (d.error) { toast(d.message,'err'); return; }
    toast(d.activo ? 'Sucursal activada' : 'Sucursal desactivada');
    cargarSucursales();
}

// ---- USUARIOS ----
async function cargarUsuarios() {
    try {
        const r = await fetch(`${API}?action=usuarios_listar&tenant_id=${TENANT_ID}`);
        const lista = await r.json();
        actualizarStats(null, lista.length);

        if (!lista.length) {
            document.getElementById('listaUsuarios').innerHTML =
                '<div class="empty"><i class="fas fa-users"></i>No hay usuarios registrados</div>';
            return;
        }
        document.getElementById('listaUsuarios').innerHTML = `
        <table class="sec-table">
            <thead><tr><th>Nombre</th><th>Usuario</th><th>Sucursal</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
            <tbody>${lista.map(u => {
                const acc = typeof u.accesos === 'string' ? JSON.parse(u.accesos) : (u.accesos||[]);
                const primera = acc[0] ?? null;
                const sucText = primera
                    ? `<div style="display:flex;align-items:center;gap:6px">
                           <i class="fas fa-store" style="color:#6366f1;font-size:.75rem"></i>
                           <span style="font-weight:600;color:#1e293b;font-size:.85rem">${esc(primera.sucursal)}</span>
                           ${acc.length > 1 ? `<span style="font-size:.72rem;color:#94a3b8">+${acc.length-1}</span>` : ''}
                       </div>`
                    : '<span style="color:#94a3b8;font-size:.78rem">—</span>';
                const rolColors = { admin: ['#eef2ff','#4f46e5'], cajero: ['#f0fdf4','#16a34a'] };
                const [rBg, rColor] = rolColors[primera?.rol] ?? ['#f1f5f9','#64748b'];
                const rolText = primera
                    ? `<span style="padding:2px 9px;border-radius:20px;font-size:.72rem;font-weight:700;text-transform:uppercase;background:${rBg};color:${rColor}">${primera.rol}</span>`
                    : '<span style="color:#94a3b8;font-size:.78rem">—</span>';
                return `
                <tr id="urow-${u.id}">
                    <td class="td-name">${esc(u.nombre)} ${esc(u.apellido||'')}</td>
                    <td style="font-family:monospace;font-size:.82rem">${esc(u.username)}</td>
                    <td>${sucText}</td>
                    <td>${rolText}</td>
                    <td><span style="padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;
                        background:${u.activo?'#dcfce7':'#fee2e2'};color:${u.activo?'#16a34a':'#dc2626'}">
                        ${u.activo?'Activo':'Inactivo'}</span></td>
                    <td style="text-align:right">
                        <div style="display:flex;gap:5px;justify-content:flex-end">
                            <button class="btn-icon btn-edit" title="Editar"
                                onclick="prepEditUsuario(${u.id},'${esc(u.nombre)}','${esc(u.apellido||'')}','${esc(u.username)}','${esc(u.password_plain||'')}','${esc(u.observaciones||'')}',${primera?.sucursal_id||'null'},'${primera?.rol||'cajero'}')">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-icon btn-toggle ${u.activo?'':'off'}" title="${u.activo?'Desactivar':'Activar'}"
                                onclick="toggleUsuario(${u.id})"><i class="fas fa-power-off"></i></button>
                        </div>
                    </td>
                </tr>`;
            }).join('')}
            </tbody>
        </table>`;
    } catch { document.getElementById('listaUsuarios').innerHTML = '<div class="empty" style="color:#dc2626">Error al cargar</div>'; }
}

async function cargarSucursalesSelect(preselect = null) {
    const sel = document.getElementById('uSucursal');
    sel.innerHTML = '<option value="">Cargando...</option>';
    const r = await fetch(`${API}?action=sucursales_listar&tenant_id=${TENANT_ID}`);
    const lista = await r.json();
    sel.innerHTML = '<option value="">— Sin asignar —</option>';
    lista.filter(s=>s.activo).forEach(s => {
        const o = document.createElement('option');
        o.value = s.id;
        o.textContent = s.nombre;
        if (preselect && parseInt(preselect) === parseInt(s.id)) o.selected = true;
        sel.appendChild(o);
    });
}

function togglePassVisibility() {
    const inp  = document.getElementById('uPassword');
    const icon = document.getElementById('uPassEyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function cerrarFormUsuario() {
    document.getElementById('formUsuario').classList.remove('open');
    document.getElementById('uEditId').value = '';
    ['uNombre','uApellido','uUsername','uPassword'].forEach(id => document.getElementById(id).value='');
    document.getElementById('uObservaciones').value = '';
    document.getElementById('uSucursal').value = '';
    document.getElementById('uRol').value = 'cajero';
    document.getElementById('formUsuarioTitulo').innerHTML = '<i class="fas fa-user-plus" style="color:#6366f1;margin-right:6px"></i>Nuevo usuario';
    document.getElementById('uPassLabel').textContent = 'Contraseña *';
    document.getElementById('uPassHint').style.display = 'none';
    // Restaurar ojo
    document.getElementById('uPassword').type = 'password';
    document.getElementById('uPassEyeIcon').className = 'fas fa-eye';
}

function prepEditUsuario(id, nombre, apellido, username, passwordPlain, observaciones, sucursalId, rol) {
    document.getElementById('uEditId').value         = id;
    document.getElementById('uNombre').value         = nombre;
    document.getElementById('uApellido').value       = apellido;
    document.getElementById('uUsername').value       = username;
    document.getElementById('uPassword').value       = passwordPlain || '';
    document.getElementById('uObservaciones').value  = observaciones || '';
    document.getElementById('uRol').value            = rol || 'cajero';
    document.getElementById('formUsuarioTitulo').innerHTML = '<i class="fas fa-pen" style="color:#6366f1;margin-right:6px"></i>Editar usuario';
    document.getElementById('uPassLabel').textContent = 'Nueva contraseña';
    document.getElementById('uPassHint').style.display = '';
    document.getElementById('uPassword').type = 'password';
    document.getElementById('uPassEyeIcon').className = 'fas fa-eye';
    const panel = document.getElementById('formUsuario');
    panel.classList.add('open');
    cargarSucursalesSelect(sucursalId);
    panel.scrollIntoView({ behavior:'smooth', block:'nearest' });
}

async function guardarUsuario() {
    const editId   = document.getElementById('uEditId').value;
    const nombre   = document.getElementById('uNombre').value.trim();
    const apellido = document.getElementById('uApellido').value.trim();
    const username = document.getElementById('uUsername').value.trim();
    const password      = document.getElementById('uPassword').value;
    const sucId         = document.getElementById('uSucursal').value;
    const rol           = document.getElementById('uRol').value;
    const observaciones = document.getElementById('uObservaciones').value.trim();
    if (!nombre||!username) { toast('Nombre y usuario son requeridos','err'); return; }
    try {
        if (editId) {
            const r = await fetch(`${API}?action=usuario_actualizar`,{
                method:'POST',headers:{'Content-Type':'application/json'},
                body:JSON.stringify({id:parseInt(editId),nombre,apellido,username,observaciones}),
            });
            const d = await r.json();
            if (d.error){toast(d.message,'err');return;}
            if (password) {
                if (password.length<4){toast('Contraseña mínimo 4 caracteres','err');return;}
                const rp = await fetch(`${API}?action=usuario_cambiar_password`,{
                    method:'POST',headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({id:parseInt(editId),password}),
                });
                const dp = await rp.json();
                if(dp.error){toast(dp.message,'err');return;}
            }
            // Guardar asignación de sucursal
            const rs = await fetch(`${API}?action=usuario_asignar_sucursal`,{
                method:'POST',headers:{'Content-Type':'application/json'},
                body:JSON.stringify({usuario_id:parseInt(editId),sucursal_id:sucId?parseInt(sucId):0,rol}),
            });
            const ds = await rs.json();
            if(ds.error){toast(ds.message,'err');return;}
            toast('Usuario actualizado');
        } else {
            if (!password||password.length<4){toast('Contraseña mínimo 4 caracteres','err');return;}
            const body={tenant_id:TENANT_ID,nombre,apellido,username,password,observaciones};
            if(sucId) { body.sucursal_id=parseInt(sucId); body.rol=rol; }
            const r=await fetch(`${API}?action=crear_admin_tenant`,{
                method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body),
            });
            const d=await r.json();
            if(d.error){toast(d.message,'err');return;}
            toast(d.message);
        }
        cerrarFormUsuario();
        cargarUsuarios();
        // Actualizar contador de sucursales también (por si se asignó)
        cargarSucursales();
    } catch { toast('Error de conexión','err'); }
}

async function toggleUsuario(id) {
    const r = await fetch(`${API}?action=usuario_toggle_activo`,{
        method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id}),
    });
    const d = await r.json();
    if(d.error){toast(d.message,'err');return;}
    toast(d.activo?'Usuario activado':'Usuario desactivado');
    cargarUsuarios();
}

// ---- Editar empresa ----
function ePreviewLogo(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('eLogoFileName').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        const img  = document.getElementById('eLogoPreviewImg');
        const icon = document.getElementById('eLogoIcon');
        img.src = e.target.result;
        img.style.display  = 'block';
        icon.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function abrirEditEmpresa() {
    document.getElementById('eLogoFile').value = '';
    document.getElementById('eLogoFileName').textContent = 'JPG, PNG, WEBP — máx. 2 MB';
    document.getElementById('modalEmpresaOverlay').classList.add('active');
    setTimeout(()=>document.getElementById('eNombre').focus(),80);
}

async function guardarEmpresa() {
    const nombre = document.getElementById('eNombre').value.trim();
    const plan   = document.getElementById('ePlan').value;
    if (!nombre) { toast('El nombre es requerido','err'); return; }

    const r = await fetch(`${API}?action=tenant_actualizar`,{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({id:TENANT_ID,nombre,plan}),
    });
    const d = await r.json();
    if(d.error){toast(d.message,'err');return;}

    // Subir logo si se eligió uno
    const logoFile = document.getElementById('eLogoFile').files[0];
    if (logoFile) {
        const fd = new FormData();
        fd.append('tenant_id', TENANT_ID);
        fd.append('logo', logoFile);
        const lr = await fetch(`${API}?action=tenant_logo_upload`, { method:'POST', body:fd });
        const ld = await lr.json();
        if (ld.error) { toast(ld.message,'err'); }
        else {
            // Actualizar logo en el hero sin recargar
            const wrap = document.getElementById('heroLogoWrap');
            wrap.innerHTML = `<img src="../../${ld.logo}" alt="logo" style="width:56px;height:56px;object-fit:contain;border-radius:14px">`;
        }
    }

    toast(d.message);
    cerrarModal();
    document.querySelector('.hero-nombre').textContent = nombre;
    document.title = nombre + ' — FarmaSystem';
}

// ---- Cerrar modal al click fuera ----
document.getElementById('modalEmpresaOverlay').addEventListener('click', e => {
    if (e.target.id === 'modalEmpresaOverlay') cerrarModal();
});

// ---- Datos SUNAT (editable) + Notas internas de superadmin ----
async function abrirSunatInfo() {
    document.getElementById('modalSunatInfoOverlay').classList.add('active');
    document.getElementById('sInfoRuc').value = '';
    document.getElementById('sInfoUsuario').value = '';
    document.getElementById('sInfoClave').value = '';
    document.getElementById('sInfoNotas').value = '';

    try {
        const r = await fetch(`${API}?action=tenant_sunat_info&tenant_id=${TENANT_ID}`);
        const d = await r.json();
        if (d.error) { toast(d.message,'err'); return; }
        document.getElementById('sInfoRuc').value = d.ruc || '';
        document.getElementById('sInfoUsuario').value = d.sunat_username || '';
        document.getElementById('sInfoClave').value = d.sunat_password || '';
        document.getElementById('sInfoNotas').value = d.notas_superadmin || '';
    } catch { toast('Error al cargar los datos SUNAT','err'); }
}

function cerrarSunatInfo() {
    document.getElementById('modalSunatInfoOverlay').classList.remove('active');
}

async function guardarSunatInfo() {
    const ruc            = document.getElementById('sInfoRuc').value;
    const sunat_username  = document.getElementById('sInfoUsuario').value;
    const sunat_password  = document.getElementById('sInfoClave').value;
    const notas           = document.getElementById('sInfoNotas').value;
    const r = await fetch(`${API}?action=tenant_sunat_guardar`,{
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({tenant_id:TENANT_ID, ruc, sunat_username, sunat_password, notas}),
    });
    const d = await r.json();
    if (d.error) { toast(d.message,'err'); return; }
    toast(d.message);
}

document.getElementById('modalSunatInfoOverlay').addEventListener('click', e => {
    if (e.target.id === 'modalSunatInfoOverlay') cerrarSunatInfo();
});

// ---- Actualizar stats combinados ----
let _cntSuc = 0, _cntUsr = 0;
const _origActualizarStats = actualizarStats;
function actualizarStats(suc, usr) {
    if (suc !== null) { _cntSuc = suc; document.getElementById('cntSucursales').textContent = suc; }
    if (usr !== null) { _cntUsr = usr; document.getElementById('cntUsuarios').textContent   = usr; }
}

// ---- Modal upgrade de plan ----
const PLAN_LABELS = { basico: 'Básico', pro: 'Pro', enterprise: 'Enterprise' };
const PLAN_COLORS = { basico: '#64748b', pro: '#b45309', enterprise: '#6d28d9' };
const PLAN_ICONS  = { basico: '📦', pro: '⭐', enterprise: '🚀' };
let _upgradePlanSiguiente = null;

function mostrarUpgrade(data) {
    _upgradePlanSiguiente = data.plan_siguiente;
    const labelActual    = PLAN_LABELS[data.plan_actual]    || data.plan_actual;
    const labelSiguiente = PLAN_LABELS[data.plan_siguiente] || data.plan_siguiente;

    document.getElementById('upgradeMsg').innerHTML =
        `Estás en el plan <strong>${labelActual}</strong> que permite hasta <strong>${data.limite} sucursal(es)</strong>.<br>
         Para agregar más sucursales necesitas cambiar al plan <strong>${labelSiguiente}</strong>.`;

    document.getElementById('upgradePlanes').innerHTML = `
        <div style="flex:1;border:2px solid #e2e8f0;border-radius:12px;padding:14px 10px;text-align:center;opacity:.6">
            <div style="font-size:1.4rem">${PLAN_ICONS[data.plan_actual]||'📦'}</div>
            <div style="font-weight:700;font-size:.85rem;color:${PLAN_COLORS[data.plan_actual]||'#64748b'};margin-top:4px">${labelActual}</div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:2px">Máx. ${data.limite} sucursal(es)</div>
        </div>
        <div style="display:flex;align-items:center;color:#94a3b8;font-size:1.1rem">→</div>
        <div style="flex:1;border:2px solid #6366f1;border-radius:12px;padding:14px 10px;text-align:center;background:#fafafe">
            <div style="font-size:1.4rem">${PLAN_ICONS[data.plan_siguiente]||'🚀'}</div>
            <div style="font-weight:700;font-size:.85rem;color:${PLAN_COLORS[data.plan_siguiente]||'#6d28d9'};margin-top:4px">${labelSiguiente}</div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:2px">Sucursales ilimitadas</div>
        </div>`;

    document.getElementById('btnUpgradeLabel').textContent = `Cambiar a ${labelSiguiente}`;
    document.getElementById('modalUpgradeOverlay').classList.add('active');
}

function cerrarUpgrade() {
    document.getElementById('modalUpgradeOverlay').classList.remove('active');
    _upgradePlanSiguiente = null;
}

async function confirmarUpgrade() {
    if (!_upgradePlanSiguiente) return;
    const btn = document.getElementById('btnConfirmarUpgrade');
    btn.disabled = true;
    try {
        const r = await fetch(`${API}?action=tenant_actualizar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: TENANT_ID, nombre: document.querySelector('.hero-nombre').textContent, plan: _upgradePlanSiguiente }),
        });
        const d = await r.json();
        if (d.error) { toast(d.message, 'err'); return; }
        cerrarUpgrade();
        toast('Plan actualizado a ' + (PLAN_LABELS[_upgradePlanSiguiente] || _upgradePlanSiguiente));
        // Actualizar badge del plan en el hero
        const badge = document.querySelector('.badge-basico, .badge-pro, .badge-enterprise');
        if (badge) {
            badge.className = `badge badge-${_upgradePlanSiguiente}`;
            badge.textContent = PLAN_LABELS[_upgradePlanSiguiente];
        }
    } catch { toast('Error de conexión', 'err'); }
    finally { btn.disabled = false; }
}

document.getElementById('modalUpgradeOverlay').addEventListener('click', e => {
    if (e.target.id === 'modalUpgradeOverlay') cerrarUpgrade();
});

// ---- Init ----
cargarSucursales();
cargarUsuarios();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
