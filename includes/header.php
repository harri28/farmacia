<?php
// ============================================================
// ARCHIVO: farmacia/includes/header.php
// DESCRIPCIÓN: Cabecera y navegación principal del sistema
// ============================================================

$current_module = $current_module ?? '';
$current_page   = $current_page ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'FarmaSystem' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_path ?? '' ?>assets/css/style.css">
    <script src="<?= $base_path ?? '' ?>assets/js/barcode-scanner.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-pills"></i>
        </div>
        <div class="brand-text">
            <span class="brand-name">FarmaSystem</span>
            <span class="brand-sub">v1.0</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-label">PRINCIPAL</span>

            <a href="<?= $base_path ?? '' ?>modules/ventas/index.php"
               class="nav-item <?= $current_module === 'ventas' ? 'active' : '' ?>">
                <i class="fas fa-cash-register"></i>
                <span>Ventas</span>
                <div class="nav-sub <?= $current_module === 'ventas' ? 'open' : '' ?>">
                    <a href="<?= $base_path ?? '' ?>modules/ventas/index.php"
                       class="<?= $current_page === 'pos' ? 'active' : '' ?>">
                        <i class="fas fa-store"></i> Punto de Venta
                    </a>
                    <a href="<?= $base_path ?? '' ?>modules/ventas/historial.php"
                       class="<?= $current_page === 'historial' ? 'active' : '' ?>">
                        <i class="fas fa-history"></i> Historial
                    </a>
                    <a href="<?= $base_path ?? '' ?>modules/ventas/favoritos.php"
                       class="<?= $current_page === 'favoritos' ? 'active' : '' ?>">
                        <i class="fas fa-star"></i> Más Vendidos
                    </a>
                </div>
            </a>

            <a href="<?= $base_path ?? '' ?>modules/inventario/index.php"
               class="nav-item <?= $current_module === 'inventario' ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i>
                <span>Inventario</span>
            </a>

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
            <a href="<?= $base_path ?? '' ?>modules/caja/index.php"
               class="nav-item <?= $current_module === 'caja' ? 'active' : '' ?>">
                <i class="fas fa-cash-register"></i>
                <span>Caja</span>
            </a>
            <a href="#" class="nav-item disabled">
                <i class="fas fa-shopping-cart"></i>
                <span>Compras</span>
                <span class="badge-soon">Pronto</span>
            </a>
            <a href="#" class="nav-item disabled">
                <i class="fas fa-file-invoice"></i>
                <span>Facturación</span>
                <span class="badge-soon">Pronto</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">A</div>
            <div>
                <div class="user-name">Administrador</div>
                <div class="user-role">Admin</div>
            </div>
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