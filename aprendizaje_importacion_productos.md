# Aprendizaje: importación de productos (datos legacy → FarmaSystem)

Este archivo documenta cómo interpretar los archivos de inventario que el cliente
(generycpharma) está entregando para cargar el catálogo de productos, producto
por producto, en sesiones de "entrenamiento" antes de generar el SQL real.
**No generar el INSERT/SQL de carga hasta que el usuario lo pida explícitamente**
(regla de trabajo general del proyecto — ver `CLAUDE.md` / memoria de feedback
"Confirmar antes de codificar").

## Origen de los datos

- El cliente organiza los archivos en carpetas por sucursal, ej. `2. JR. LIMA TAMBO/`,
  `2 JR. ALONSO DE ALVARADO/` (carpetas sueltas en la raíz del repo, no versionadas).
- Dentro de cada carpeta, archivos de texto tipo `inventario1-10.txt` con **varios
  bloques JSON de una API paginada pegados uno tras otro** (NO es un único JSON
  válido — hay que parsear con `raw_decode` en loop o similar, saltando espacios
  entre bloques).
- Cada bloque tiene la forma:
  ```json
  {
    "results": [ {...producto...}, ... ],
    "count": 2820,
    "total": 2820,
    "paginate_by": 50,
    "shop": "",
    "is_digemid": true,
    "company_name": "BOTICA GENERYC PHARMA #1 - LIMATAMBO"
  }
  ```
- Los bloques se solapan entre sí (paginación), así que hay que **deduplicar por
  `id`** antes de insertar. Ejemplo: `inventario1-10.txt` de Lima Tambo trae 11
  bloques, 550 filas crudas, mostrando 500 productos únicos por id, de un total
  declarado de 2820 en la sucursal (o sea, el archivo es solo una parte del total,
  vendrán más archivos/bloques).

### Campos del producto de origen (por elemento de `results`)

`id`, `description`, `linea` (id numérico de categoría, no usado — se usa
`linea_name`), `sale_price`, `purchase_price`, `stock`, `slug`, `linea_name`,
`percentage_profit`, `deleted_at`, `tooltip`, `stock_unit`, `barcode`,
`thumbnail`, `is_control`, `company_code`, `sublinea_name`, `is_show_modal_price`.

**`is_control` NO sirve como señal de "requiere receta"**: aparece en `true` para
productos claramente no controlados (ej. un peluche de bebé "OSITA NOVIA"), por
lo que se ignora. `requiere_receta` queda en su default del sistema (`false`)
salvo que el cliente diga lo contrario.

## Mapeo de campos confirmado (origen → `productos` en FarmaSystem)

| Columna en `productos` | Origen | Transformación |
|---|---|---|
| `nombre` | `description` | tal cual (verificar ≤ 200 caracteres, columna `VARCHAR(200)`) |
| `codigo` | `barcode` | tal cual (columna `UNIQUE NOT NULL` — vigilar barcodes vacíos/duplicados) |
| `codigo_barras` | `barcode` | mismo valor que `codigo` (confirmado por el cliente — necesario para que el lector HID del POS funcione) |
| `codigo_interno` | `company_code` | tal cual (el cliente lo llama "sku", pero la columna real se llama `codigo_interno`) |
| `categoria_id` | `linea_name` | resolver contra `public.categorias` (tabla **global**, compartida por TODAS las sucursales y TODOS los tenants del sistema — ver `migration_21_categorias_global.sql`). Si `linea_name` es `null` → categoría `"Sin categoria"`. Si el nombre no existe en `public.categorias`, **crearla** y anotarla en el reporte de carga (ver sección Reporte). Confirmado por el cliente: categorías como "SECCIÓN BEBE." son productos reales que la botica también vende (no es basura de datos), así que no se deben excluir ni fusionar arbitrariamente con otra categoría. |
| `unidad` | — | fijo: `'UND'` |
| `stock` | `stock` | string decimal (`"130.00"`) → convertir a entero (columna `INTEGER`) |
| `precio_venta` | `sale_price` | quitar prefijo `"S/ "` → decimal (columna `DECIMAL(10,2)`) |
| `precio_compra` | `purchase_price` | quitar prefijo `"S/ "` → decimal. Columna ampliada a `DECIMAL(10,3)` (ver abajo) porque algunos precios de compra vienen con 3 decimales (ej. `"S/ 0.234"`) y se perdía precisión al redondear a 2. |
| `fecha_vencimiento` | — | siempre `NULL` (el origen no trae esta info) |

### Campos de `productos` sin dato de origen → quedan en su default del sistema

`descripcion` (TEXT largo, distinto de `nombre`), `laboratorio`, `presentacion`,
`requiere_receta` (`false`), `activo` (`true`), `favorito` (`false`),
`codigo_sunat` (`'00000000'`), `unidad_id`, `unidad_codigo` (`'NIU'`),
`afectacion_igv_id`, `afectacion_igv_codigo` (`'10'`), `porcentaje_igv` (`18.00`),
`incluye_igv` (`true`), `icbper_activo` (`false`), `factor_icbper` (`0`),
`product_type` (`'product'`).

Regla general del cliente: *"Debes registrar todos los datos que en nuestro
sistema acepta, lo que no acepta ignóralos"* — es decir, solo se pueblan
columnas con equivalente real en el origen; el resto se deja en su default,
sin inventar valores.

## Reporte de categorías nuevas (pendiente de implementar en el SQL)

Cuando se genere el SQL de carga, además del `INSERT INTO productos`, se debe
producir un listado de qué categorías se crearon nuevas en `public.categorias`
(nombre + en qué archivo/sucursal aparecieron primero), para que el cliente las
revise — dado que es una tabla global y una categoría mal escrita ahí queda
visible para todo el sistema, no solo para esa sucursal.

## Cambios de esquema ya aplicados durante este entrenamiento

- **`migration_33_precio_compra_3_decimales.sql`**: `productos.precio_compra`
  `DECIMAL(10,2)` → `DECIMAL(10,3)`. Aplicado en local (ambos schemas). **Falta
  aplicar en el VPS** antes de cargar datos reales de producción.
- `database/schema_sucursal.sql` actualizado para que sucursales nuevas ya
  nazcan con `precio_compra DECIMAL(10,3)`.

## Estado de las bases de datos (a la fecha de este documento)

- **Local**: ambos schemas de sucursal (`generic_pharma_jr_alonso_de_alvarado`,
  `generic_pharma_jr_lima_tambo`) están sin productos ni ventas de prueba —
  listos para la carga nueva.
- **Producción (VPS)**: los dos schemas de generycpharma —
  `generyc_pharma_alonso_de_alvarado` y `generyc_pharma_jr_lima_tambo`
  (⚠️ nombres de schema con ortografía distinta a local: "generyc" vs "generic")
  — también están sin productos (992 borrados en `generyc_pharma_jr_lima_tambo`,
  0 en `generyc_pharma_alonso_de_alvarado`). El tenant generycpharma tiene 2
  sucursales; hay otras 2 sucursales en el mismo VPS (`steifer_jr_lima`,
  `steifer_jr_san_martin`) que pertenecen a otro tenant (Steifer, usado por el
  cliente solo para pruebas) — **no tocar esos schemas** en esta carga.
- Se borraron los scripts SQL de inserción de productos obsoletos que existían
  en el repo (`insert_productos_limatambo_bloque_*.sql`,
  `insert_productos_vps.sql`, `seed_productos_test.sql`) — referenciaban
  nombres de schema de una carga anterior, ya no aplican.

## Siguiente paso

Seguir recibiendo ejemplos/archivos de productos del cliente para terminar de
validar el mapeo contra casos borde (nombres largos, precios con más
decimales, categorías nuevas, barcodes vacíos o duplicados, etc.). Recién
cuando el cliente lo pida explícitamente, generar el script SQL real de carga
(por sucursal, con manejo de categorías nuevas + reporte).
