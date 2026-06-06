# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**FarmaSystem** is a multi-tenant pharmacy Point of Sale (POS) web application built with PHP, vanilla JavaScript, and PostgreSQL. It runs on XAMPP (Apache + PHP). It integrates with Peru's SUNAT electronic invoicing system (direct SOAP and via Nubefact API).

## Running the Application

**Prerequisites**: XAMPP with Apache running, PostgreSQL 17 running with the database initialized.

**Database credentials** in `config/database.php`: host `localhost:5432`, db `farmacia`, user `postgres`, pass `1234`.

**First-time production setup**:
```powershell
# Create the database
& "C:\Program Files\PostgreSQL\17\bin\psql.exe" -U postgres -c "CREATE DATABASE farmacia;"
# Run the one-shot deploy script
& "C:\Program Files\PostgreSQL\17\bin\psql.exe" -U postgres -d farmacia -f database/deploy_production.sql
# Then visit: http://localhost/farmacia/database/setup.php for initial tenant/user setup
```

**Applying a migration**:
```powershell
$env:PGPASSWORD = "1234"; & "C:\Program Files\PostgreSQL\17\bin\psql.exe" -U postgres -d farmacia -f database/migration_XX_name.sql
```

**Entry point**: `http://localhost/farmacia/modules/ventas/index.php`  
There is no root `index.php` — navigating to `/farmacia/` will 404.

There is no build step, no package manager, and no test runner.

## Architecture

### Multi-Tenancy

The core architectural pattern is PostgreSQL schema-based multi-tenancy:

- **`public` schema** — Global tables shared across all tenants: `tenants`, `sucursales`, `usuarios`, `usuario_sucursal`, `superadmins`, `ubigeo_*`, `fe_tipos_*` catalogs, `ordenes_traslado`
- **Per-branch schema** (e.g., `sucursal_001`) — Branch-specific tables: `productos`, `ventas`, `caja`, `ingresos`, `comprobantes_electronicos`, etc.
- `getDB()` runs `SET search_path TO {schema}, public` after login, switching context to the active branch
- Session variables: `tenant_id`, `sucursal_id`, `sucursal_schema`, `rol`

### Role System

Three roles with separate access gates:

| Role | Access |
|---|---|
| `superadmin` | Multi-tenant panel (`/modules/superadmin/`) — auto-redirected on login |
| `admin` | Full pharmacy: ventas, caja, clientes, inventario, almacén, compras, traslados, facturación, admin |
| `cajero` | Restricted: ventas + caja only |

Page-level gate: `requireAuth(['admin'])` at top of PHP pages.  
API-level gate: `requireApiAuth(['admin'])` inside `api.php` switch cases.

### Request Flow

```
Browser → modules/*/index.php (sets $base_path, $current_module, $current_page, $page_title, $breadcrumb)
              ↓ includes header.php (renders sidebar + topbar based on role)
              ↓ Vanilla JS makes fetch() calls
              ↓ modules/*/api.php?action=<name>
              ↓ PDO queries via getDB()
              ↓ jsonResponse([...]) → JSON
```

### Page Convention

Every PHP page must set these variables **before** `include '../../includes/header.php'`:
- `$base_path` — relative path back to project root (e.g. `'../../'`)
- `$current_module` — controls sidebar active state (e.g. `'ventas'`)
- `$current_page` — controls sub-nav active state (e.g. `'pos'`, `'historial'`)
- `$page_title` — HTML `<title>`
- `$breadcrumb` — topbar breadcrumb HTML string

### Key Files

- **`config/database.php`** — PDO singleton (`getDB()`), shared utilities: `jsonResponse()`, `formatMoney()`, `generarNumeroVenta()`, `generarNumeroIngreso()`, `getTenantConfig()`
- **`config/auth.php`** — Session start, `requireAuth()`, `requireApiAuth()`, role helpers (`isAdmin()`, `isCajero()`, `isSuperadmin()`), session accessors (`sesionId()`, `sesionRol()`, `sesionSucursal()`, `sesionTenantId()`)
- **`config/sunat.php`** — SUNAT direct integration: `enviar_sunat()` orchestrator, XML builders for UBL 2.1 Invoices and Credit Notes, SOAP/CURL sender, CDR parser
- **`config/nubefact.php`** — Alternative Nubefact API integration: `enviar_nubefact()`
- **`includes/header.php`** — Outputs full `<html><head>`, sidebar (role-filtered nav), topbar, opens `<main>`
- **`includes/footer.php`** — Closes `<main>`, sidebar toggle JS, date display
- **`assets/css/style.css`** — Complete design system with CSS custom properties (`--primary`, `--surface-2`, etc.)
- **`assets/js/barcode-scanner.js`** — Global HID keyboard barcode scanner detection; fires `CustomEvent('barcodescan', { detail: { code } })` on `document`. Included by header.php. Keystroke timing: < 50ms between chars + Enter + ≥ 3 chars = scan event.

### Database Schema

**Number formats:**
- Sale: `V20260317-0001` (from `generarNumeroVenta()`)
- Ingreso: `I20260317-0001` (from `generarNumeroIngreso()`)
- SUNAT boleta: `B001-00001`, factura: `F001-00001` (tracked in `series_comprobantes`)

**Key stock rules:**
- Stock decremented on `registrar_venta`, restored on `anular_venta`
- `total_vendido` on `productos` increments on sale but is **not** decremented on cancellation
- `cajas` must be `estado = 'abierta'` before sales can be registered

**PostgreSQL quirk:** Boolean columns return string `'t'`/`'f'` in PDO — JS checks `p.favorito == 't'`.

**All sale/ingreso mutations use** `PDO::beginTransaction()` with rollback on failure.

### API Modules

Each module has `api.php` with a `switch ($action)` dispatcher:

- **`ventas/api.php`** — `productos`, `buscar_cliente`, `registrar_venta`, `anular_venta`, `historial`, `detalle_venta`, `stats_dia`, `top_vendidos`, `favoritos`, `toggle_favorito`
- **`inventario/api.php`** — `stats`, `listar`, `categorias`, `crear`, `actualizar`, `ajustar_stock`, `toggle_activo`
- **`almacen/api.php`** — `proveedores_listar`, `proveedor_crear`, `proveedor_actualizar`, `proveedor_toggle_activo`, `stats_almacen`, `ingresos_listar`, `ingreso_detalle`, `buscar_producto`, `registrar_ingreso`, `anular_ingreso`
- **`caja/api.php`** — `estado`, `usuarios`, `aperturar`, `cerrar`, `registrar_movimiento`, `movimientos`, `historial`
- **`clientes/api.php`** — `listar_tipos_documento`, `cliente_listar`, `cliente_obtener`, `cliente_crear`, `cliente_actualizar`, `cliente_toggle_activo`, `cliente_consultar_documento` (calls Factiliza API for DNI/RUC lookup)
- **`admin/api.php`** — `ubigeo_resolver`, `usuarios_listar`, `usuario_crear`, `usuario_actualizar`, `usuario_cambiar_password`, `usuario_toggle_activo`, `usuario_sucursal_asignar`, `sucursales_listar`, `sucursal_crear`, `sucursal_actualizar`
- **`facturacion/api.php`** — `reporte_listar`, `reporte_detalle`, `nota_credito_listar_origenes`, `nota_credito_crear` (calls `enviar_sunat()`), `cdr_descargar`

### Electronic Invoicing

Two integration paths:

**1. Direct SUNAT SOAP (primary in `config/sunat.php`):**
- Builds UBL 2.1 XML in memory
- Signs XML with RSA certificate (`.pfx` from `facturacion/certs/`)
- ZIPs and POSTs via SOAP CURL to SUNAT endpoint
- Parses CDR response and updates `comprobantes_electronicos`
- Production endpoint: `https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService`
- Sandbox endpoint: `https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService`

**2. Nubefact API (alternative in `config/nubefact.php`):**
- REST API; prices are IGV-inclusive; `valor_unitario = precio / 1.18`
- Correlative tracking in `series_comprobantes` (B001 boletas, F001 facturas)

**`facturacion/` directory:**
- `signature.php` + `api_signature/XMLSec*.php` — RSA XML signing library
- `certs/` — `.pfx` certificate files
- `storage/xml/` — Generated XML/ZIP files
- `storage/cdr/` — SUNAT CDR response files

### Stock Transfers (Traslados)

Tables in `public` schema (cross-branch): `ordenes_traslado`, `orden_traslado_detalles`.

Flow: `Borrador → Enviado` (locks items, decrements origin stock) → `Recibido` (increments dest stock) or `Anulado`.

### Modules Status

- **Fully implemented**: Ventas (POS + Historial + Favoritos), Inventario, Almacén (Ingresos + Proveedores), Caja, Clientes, Admin (Users + Branches), Facturación (Reportes + Notas de Crédito + Rentabilidad), Traslados
- **Partially implemented**: Compras (purchase orders with `ordenes_compra` + `cuentas_por_pagar` tables)
- **Planned/disabled in UI**: Ecommerce (sidebar shows "Pronto" badge)
