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
- **Pendiente de decisión (feedback de cliente, 2026-07-23):** hoy la venta se bloquea si `stock < cantidad` (`ventas/api.php`, acción `registrar_venta`), incluso con stock 0 — aplica a todos los roles por igual. El cliente reporta que el cajero muchas veces no puede actualizar el stock (lo hace la jefa al cierre de turno) y hay productos que se registran tarde, por lo que pide poder vender igual aunque el stock figure en 0. No implementado aún porque permitir stock negativo rompe las alertas actuales de "agotados" (`stock = 0` exacto) y "stock bajo" (`stock > 0 AND stock <= stock_minimo`), que dejarían de detectar esos productos. Opciones a evaluar más adelante: (a) permitir stock negativo y ajustar esas alertas a `stock <= 0`/`stock <= stock_minimo` sin exigir `stock > 0`, (b) agregar una confirmación explícita al vender sin stock en vez de bloquear en silencio, o ambas.

## Toma de Inventario (conteo físico por sesión)
- Reemplaza el viejo flujo de descarga/subida de Excel. Vive como una pestaña dentro de Inventario, **visible solo para admin/gerente**.
- Se crea una **sesión** (`toma_inventario_sesiones`) eligiendo categorías + un plazo en días (sin tope máximo, puede ser más de 5 días). Al crearla se siembra una fila por cada producto activo de esas categorías (`toma_inventario_detalles`), con el stock del sistema en ese momento como referencia — es un snapshot fijo: productos agregados a la categoría después no aparecen en esa sesión.
- El conteo físico se guarda aparte de `productos.stock` (autosave por fila, sin botón "Guardar" general). Las diferencias (contado − stock del sistema al momento de contar) **no tocan el stock real** hasta que la sesión se cierra.
- **"Vencida" no es un estado guardado en la base** — se calcula al vuelo (`estado = 'activa' AND fecha_límite < NOW()`). Si se cumple el plazo y quedan productos sin contar, la sesión no se autocompleta ni se bloquea: sigue activa y contable, mostrando el badge "Vencida" hasta que un admin la extienda (`toma_extender`) o la cierre.
- **No hay bloqueo técnico de un solo usuario**: cualquier admin/gerente puede abrir una sesión activa y seguir contando, aunque en la práctica la trabaje una sola persona. Si dos personas cuentan el mismo producto casi al mismo tiempo, gana el último guardado — riesgo conocido y aceptado, no un bug.
- Al cerrar (`toma_aplicar`): los productos **sin contar quedan intactos** (no generan movimiento de stock); los contados aplican su diferencia al stock real **por delta** (`stock = stock + (contado − stock_sistema_al_contar)`), nunca sobrescribiendo el valor absoluto — así no se pierden ventas ocurridas durante los días que duró el conteo. Una sesión cerrada o cancelada no se puede reabrir ni editar; una corrección posterior se hace con el Ajuste de Stock normal.
- El cierre queda registrado en Auditoría (`public.audit_log`), pero el conteo línea-por-línea no genera ningún registro en Ingresos/Salidas de Almacén — es, igual que el Ajuste de Stock rápido, una corrección paralela al flujo formal de Almacén.

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
