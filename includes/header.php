<?php
// ============================================================
// ARCHIVO: farmacia/includes/header.php
// ============================================================

require_once ($base_path ?? '../../') . 'config/auth.php';
requireAuth($required_roles ?? []);

$current_module = $current_module ?? '';
$current_page   = $current_page   ?? '';

$_inicial = strtoupper(substr(sesionNombre(), 0, 1)) ?: 'U';

$_brand      = getTenantConfig();
$_brand_name = htmlspecialchars($_brand['nombre_sistema'] ?: 'FarmaSystem');
$_brand_logo = $_brand['logo_path'] ?? null;
$_brand_logo_abs = $_brand_logo
    ? realpath(__DIR__ . '/../' . $_brand_logo)
    : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? $_brand_name ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_path ?? '' ?>assets/css/style.css">
    <script src="<?= $base_path ?? '' ?>assets/js/barcode-scanner.js"></script>
    <style>
        /* Rol badge en sidebar */
        .role-badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04rem;
        }
        .role-badge.admin  { background: #eef2ff; color: #4f46e5; }
        .role-badge.cajero { background: #f0fdf4; color: #16a34a; }
        .sucursal-label {
            font-size: .72rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-footer .user-info { align-items: flex-start; }
        .logout-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            font-size: .9rem;
            padding: 4px 6px;
            border-radius: 6px;
            transition: color .15s, background .15s;
            margin-left: auto;
            align-self: center;
        }
        .logout-btn:hover { color: #dc2626; background: #fef2f2; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <?php if ($_brand_logo_abs && file_exists($_brand_logo_abs)): ?>
            <img id="sidebar-brand-logo" src="<?= ($base_path ?? '') . htmlspecialchars($_brand_logo) ?>"
                 alt="Logo" style="height:36px;width:auto;object-fit:contain;flex-shrink:0">
            <span class="brand-name" id="sidebar-brand-name"><?= $_brand_name ?></span>
        <?php else: ?>
            <div class="brand-icon" id="sidebar-brand-icon"><i class="fas fa-pills"></i></div>
            <div class="brand-text" id="sidebar-brand-text">
                <span class="brand-name" id="sidebar-brand-name"><?= $_brand_name ?></span>
                <span class="brand-sub" id="sidebar-brand-sub">v1.0</span>
            </div>
        <?php endif; ?>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-label">PRINCIPAL</span>

            <?php if (isAdmin()): ?>
            <!-- Dashboard: solo admin -->
            <a href="<?= $base_path ?? '' ?>modules/dashboard/index.php"
               class="nav-item <?= $current_module === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>

            <!-- Ventas: admin + cajero -->
            <a href="<?= $base_path ?? '' ?>modules/ventas/index.php"
               class="nav-item <?= $current_module === 'ventas' ? 'active' : '' ?>">
                <i class="fas fa-cash-register"></i>
                <span>Ventas</span>
            </a>

            <!-- Caja: admin + cajero -->
            <a href="<?= $base_path ?? '' ?>modules/caja/index.php"
               class="nav-item <?= $current_module === 'caja' ? 'active' : '' ?>">
                <i class="fas fa-cash-register"></i>
                <span>Caja</span>
            </a>

            <?php if (isAdmin()): ?>
            <!-- Clientes: solo admin -->
            <a href="<?= $base_path ?? '' ?>modules/clientes/index.php"
               class="nav-item <?= $current_module === 'clientes' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>

            <!-- Inventario: solo admin -->
            <a href="<?= $base_path ?? '' ?>modules/inventario/index.php"
               class="nav-item <?= $current_module === 'inventario' ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i>
                <span>Inventario</span>
            </a>

            <!-- Almacén: solo admin -->
            <a href="<?= $base_path ?? '' ?>modules/almacen/index.php"
               class="nav-item <?= $current_module === 'almacen' ? 'active' : '' ?>">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
                <div class="nav-sub <?= $current_module === 'almacen' ? 'open' : '' ?>">
                    <a href="<?= $base_path ?? '' ?>modules/almacen/index.php"
                       class="<?= $current_page === 'ingresos' ? 'active' : '' ?>">
                        <i class="fas fa-truck-loading"></i> Ingresos de Stock
                    </a>
                    <a href="<?= $base_path ?? '' ?>modules/almacen/proveedores.php"
                       class="<?= $current_page === 'proveedores' ? 'active' : '' ?>">
                        <i class="fas fa-truck"></i> Proveedores
                    </a>
                </div>
            </a>

            <a href="<?= $base_path ?? '' ?>modules/compras/index.php"
               class="nav-item <?= ($current_module ?? '') === 'compras' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Compras</span>
            </a>

            <a href="<?= $base_path ?? '' ?>modules/traslados/index.php"
               class="nav-item <?= ($current_module ?? '') === 'traslados' ? 'active' : '' ?>">
                <i class="fas fa-exchange-alt"></i>
                <span>Traslados</span>
            </a>

            <!-- Facturación: solo admin -->
            <a href="<?= $base_path ?? '' ?>modules/facturacion/index.php"
               class="nav-item <?= $current_module === 'facturacion' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice"></i>
                <span>Facturación</span>
                <div class="nav-sub <?= $current_module === 'facturacion' ? 'open' : '' ?>">
                    <a href="<?= $base_path ?? '' ?>modules/facturacion/index.php"
                       class="<?= $current_page === 'reporte' ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i> Reporte de Ventas
                    </a>
                    <a href="<?= $base_path ?? '' ?>modules/facturacion/notas_credito.php"
                       class="<?= $current_page === 'notas_credito' ? 'active' : '' ?>">
                        <i class="fas fa-receipt"></i> Notas de crédito
                    </a>
                    <a href="<?= $base_path ?? '' ?>modules/facturacion/rentabilidad.php"
                       class="<?= $current_page === 'rentabilidad' ? 'active' : '' ?>">
                        <i class="fas fa-chart-pie"></i> Rentabilidad
                    </a>
                </div>
            </a>

            <span class="nav-label" style="margin-top:12px">SISTEMA</span>

            <!-- E-commerce: solo admin -->
            <a href="<?= $base_path ?? '' ?>modules/ecommerce/index.php"
               class="nav-item <?= $current_module === 'ecommerce' ? 'active' : '' ?>">
                <i class="fas fa-store"></i>
                <span>E-commerce</span>
            </a>

            <!-- Admin: solo admin -->
            <a href="<?= $base_path ?? '' ?>modules/admin/index.php"
               class="nav-item <?= $current_module === 'admin' ? 'active' : '' ?>">
                <i class="fas fa-cogs"></i>
                <span>Administración</span>
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info" style="display:flex;align-items:center;gap:10px;width:100%">
            <div class="user-avatar"><?= $_inicial ?></div>
            <div style="flex:1;min-width:0">
                <div class="user-name" style="display:flex;align-items:center;gap:6px">
                    <?= htmlspecialchars(sesionNombre()) ?>
                    <span class="role-badge <?= sesionRol() ?>"><?= sesionRol() === 'admin' ? 'Admin' : 'Cajero' ?></span>
                </div>
                <?php if (sesionTenantNombre()): ?>
                <div class="sucursal-label" style="font-size:.68rem;color:var(--primary);font-weight:600;margin-bottom:1px">
                    <i class="fas fa-building" style="font-size:.6rem;margin-right:2px"></i>
                    <?= htmlspecialchars(sesionTenantNombre()) ?>
                </div>
                <?php endif; ?>
                <div class="sucursal-label">
                    <i class="fas fa-store" style="font-size:.65rem;margin-right:3px"></i>
                    <?= htmlspecialchars(sesionSucursal()) ?>
                </div>
            </div>
            <a href="<?= $base_path ?? '' ?>modules/auth/logout.php"
               class="logout-btn" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>

<!-- TOPBAR -->
<div class="topbar">
    <button class="topbar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="topbar-breadcrumb">
        <span><?= $breadcrumb ?? 'Inicio' ?></span>
    </div>
    <div class="topbar-actions">
        <div class="topbar-date">
            <i class="far fa-calendar"></i>
            <span id="current-date"></span>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<main class="main-content" id="main-content">
