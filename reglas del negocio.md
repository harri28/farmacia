# Reglas del Negocio — FarmaSystem

## Multi-tenencia
- Cada empresa (tenant) puede tener varias sucursales; cada sucursal tiene su propio schema de PostgreSQL (`sucursal_001`, `sucursal_002`, ...).
- Las tablas globales (`tenants`, `sucursales`, `usuarios`, `categorias`, etc.) viven en el schema `public` y son compartidas entre todas las sucursales del tenant.
- Cada sucursal tiene su propio inventario, ventas, caja y comprobantes — no se comparten entre sucursales salvo por traslados.

## Roles y accesos
| Rol | Acceso |
|---|---|
| `superadmin` | Panel multi-tenant, gestiona todas las empresas |
| `gerente` | Acceso completo, igual que admin (incluye panel de administración) |
| `admin` | Ventas, caja, clientes, inventario, almacén, compras, traslados, facturación, admin |
| `cajero` | Solo ventas y caja |

## Reglas de caja
- No se puede registrar una venta si la caja no está en estado `abierta`.
- Los movimientos de caja (ingresos/egresos manuales) quedan registrados por separado de las ventas.

## Reglas de stock
- El stock se descuenta al registrar una venta y se restaura al anularla.
- `total_vendido` se incrementa con cada venta pero **no** se revierte al anular (es un contador histórico, no de stock).
- Los traslados entre sucursales siguen el flujo: `Borrador → Enviado` (bloquea ítems y descuenta stock de origen) → `Recibido` (incrementa stock en destino) o `Anulado`.
- El stock "formal" (con rastro/auditoría) entra o sale por **Almacén** (Ingresos con proveedor/costo, Salidas con motivo) o por una venta/traslado.
- Además existen dos formas de corrección manual **rápida**, sin generar registro en Ingresos/Salidas de Almacén, y **restringidas solo a `admin`/`gerente`**:
  - El botón de ajuste rápido (ícono ⚙) en Inventario → tabla de productos.
  - El campo Stock del formulario "Editar Producto" (al crear un producto nuevo, el campo sí es editable por cualquier rol con acceso a Inventario, ya que ahí no hay valor previo que sobrescribir).
- Estas dos correcciones manuales están pensadas como ajustes puntuales (ej. conteo físico, error de digitación), no como reemplazo del flujo de Ingresos/Salidas de Almacén.

## Reglas de clientes
- Existe un cliente por defecto **"Clientes Varios"** (`numero_documento = '00000000'`) para ventas rápidas (tickets/nota de venta) sin identificar al comprador.
- SUNAT exige identificar al comprador en:
  - Boletas mayores a **S/ 700**
  - Todas las facturas
- Por lo tanto, "Clientes Varios" solo puede usarse en tickets/nota de venta que no superen ese límite y no requieran factura.

## Reglas de impuestos por producto
- `afectacion_tipo`: `GRAV` (gravado con IGV), `EXO` (exonerado), `INA` (inafecto), `EXP` (exportación).
- Si `incluye_igv = false` y el producto es `GRAV`, el precio registrado es neto y se cobra `precio_venta * 1.18`.
- `porcentaje_igv` se guarda por producto (por defecto 18%) para soportar cambios futuros de tasa.
- ICBPER (bolsa plástica): si `icbper_activo` está activo, se cobra un cargo adicional por unidad según `factor_icbper`.

## Numeración de documentos
- Venta interna: `V20260317-0001`
- Ingreso de almacén: `I20260317-0001`
- Boleta SUNAT: `B001-00001`
- Factura SUNAT: `F001-00001`
- Notas de crédito: series `NC01` (genérica), `BC01` (origen boleta), `FC01` (origen factura) — la serie se determina según el tipo de documento que se está afectando.

## Facturación electrónica (SUNAT)
- Todo comprobante (boleta/factura/nota de crédito) se envía a SUNAT vía SOAP directo o Nubefact, según configuración del tenant.
- El ambiente (producción vs beta) se configura por tenant en `sunat_server` (`'1'` = producción, `'3'` = beta/sandbox, valor por defecto).
- Las notas de crédito requieren un documento de origen (boleta o factura) y un motivo/tipo válido del catálogo SUNAT.

## Integridad transaccional
- Todas las mutaciones de ventas e ingresos (registrar, anular) se ejecutan dentro de una transacción (`BEGIN`/`COMMIT`/`ROLLBACK`) para evitar estados inconsistentes entre stock, caja y comprobantes.

## Compras y cuentas por pagar/cobrar
- Una orden de compra aprobada y recibida (`orden_recibir`) genera automáticamente un ingreso de almacén y, opcionalmente, una cuenta por pagar al proveedor.
- Las cuentas por cobrar permiten registrar pagos parciales de clientes sobre ventas a crédito.

---
*Pendiente de completar: reglas específicas de descuentos/promociones, políticas de devolución, límites de crédito por cliente, y cualquier regla de negocio particular por tenant que no esté generalizada en el código.*
