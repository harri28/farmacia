# Resumen de sesión — 2026-07-12

Contexto para retomar donde quedamos si se compacta la conversación. Para detalle profundo de cada tema, ver `config_vps.md` (infra del VPS), `config_sunat.md` (SUNAT), `reglas del negocio.md` (reglas de negocio del sistema).

## Qué se hizo hoy (en orden)

1. **CLAUDE.md** — se corrió `/init` y se actualizó con hallazgos reales del código (rutas de ruteo por subdominio, módulo Banco/Ecommerce, migración 30, caveat de PHP 7 en producción).
2. **Subdominios por tenant** — se armó de cero: DNS wildcard en Hostinger, certificado SSL wildcard (Certbot DNS-01 manual, vence **2026-10-10**, hay que renovarlo a mano), `ServerAlias *.genpharma.cloud` en Apache, y lógica en `index.php`/`login.php` para: tenant válido → login normal; subdominio sin tenant → página "Empresa no encontrada"; dominio raíz/www/localhost → landing pública nueva (`includes/landing.php`).
3. **Incidente de producción (resuelto)** — se usó `str_ends_with()` (PHP 8+) en `login.php` y tumbó el login con 500, porque el VPS sirve con **PHP 7**, no 8.2 como se creía (la actualización a 8.2 fue solo para `conexion_sunat/`, la app Laravel aparte). Corregido y documentado en `config_vps.md` — **no usar sintaxis PHP 8+ en este proyecto**.
4. **Rediseño del login** — antes mostraba TODAS las sucursales de TODOS los tenants sin autenticar (hueco de seguridad). Ahora es credenciales-primero: `modules/auth/api.php` (nuevo) con `validar_credenciales` + `confirmar_sucursal`. Diseño final acordado con el usuario: el subdominio ya NO restringe login (es solo cosmético), el tenant se determina por `usuarios.tenant_id`, y **cualquier usuario de una empresa puede entrar a cualquier sucursal de esa misma empresa** (sin necesitar fila explícita en `usuario_sucursal` — eso solo se usa para sacar el `rol`, tomado de cualquier fila activa del usuario).
5. **Bugs de permisos de archivos en el VPS** — `facturacion/certs/`, `facturacion/storage/xml/`, `facturacion/storage/cdr/`, `modules/banco/img/` eran de `root:root` (creados por `git pull` como root) y Apache (`www-data`) no podía escribir ahí. Corregido con `chown -R www-data:www-data`.
6. **Bug grave: `assets/vendor/` nunca se subía a git** — `.gitignore` tenía `vendor/` sin ruta completa, lo que también ignoraba jQuery/Select2/DataTables (no solo `conexion_sunat/vendor/` de Composer). Por eso Select2 nunca cargó en producción. Corregido: `.gitignore` ahora dice `conexion_sunat/vendor/` explícito, y se subieron los 6 archivos que faltaban.
7. **Bugs menores de UI en ventas** — cliente seleccionado desaparecía al reabrir el desplegable (evento `change.select2` en vez de `change`), campo "Efectivo recibido" venía pre-llenado con el total (cambiado a vacío), color de resaltado de Select2 no usaba `var(--primary)` dinámico por sucursal.
8. **Migraciones de columnas faltantes** — `codigo_interno` en `productos` (usó `migration_26`) y `anticipo`/`otros_cargos`/`cuotas`/`payment_breakdown`/`cdr`/`estado_cpe` en `ventas` (nueva `migration_30_ventas_campos_faltantes.sql`) faltaban en los schemas de sucursal existentes porque `migration_22` no las cubrió todas. **Pendiente aparte, no resuelto hoy**: `schema_sucursal.sql` (define sucursales nuevas) tampoco tiene estas columnas — una sucursal nueva hoy tendría el mismo problema.
9. **SUNAT — integración de punta a punta, en progreso**:
   - Certificado `.p12` → renombrado a `.pfx`, subido (tras corregir permisos del punto 5).
   - RUC/razón social mal configurados (`10734630549` / "steifer", datos personales) → corregidos a `20616086465` / "PETRAM CO SAC" (los reales, coinciden con el certificado).
   - URL del XML rota en producción (`sunat_public_base_url()` tenía `/farmacia/` hardcodeado, asumiendo XAMPP local) → corregida para autoadaptarse según `DOCUMENT_ROOT`.
   - Bug de UX: `reenviar_sunat` siempre decía "enviado correctamente" sin importar si SUNAT lo rechazaba → corregido para revisar `error_nubefact`.
   - Bug técnico real: al sobre SOAP le faltaba `Type="...#PasswordText"` en `wsse:Password` (estándar WS-Security) → corregido.
   - **Pendiente ahora mismo**: las 5 boletas de prueba (`B001-00000001` a `-05`) siguen rechazadas por SUNAT con `fault_code 0111` ("No tiene el perfil... Rejected by policy"), incluso con todo lo anterior corregido y los permisos del Usuario SOL secundario confirmados como asignados en el portal. Ya no es algo que se arregle desde el código — es demora de propagación de SUNAT o afiliación al SEE pendiente del lado de la cuenta. **Se está esperando** — reintentar en unas horas, o llamar a SUNAT (0-801-12-100) con el RUC y el código 0111.

## Estado actual — qué falta

- [ ] **SUNAT**: confirmar si el error 0111 se resuelve solo (propagación) o si requiere gestión con SUNAT (afiliación al SEE). Ver `config_sunat.md` para el detalle y el catálogo de códigos de error.
- [ ] **`schema_sucursal.sql`**: no tiene ninguna columna de facturación electrónica — una sucursal nueva creada hoy tendría los mismos errores de "columna no existe" que tuvimos que parchear. No se tocó hoy a propósito (merece revisión aparte, con cuidado).
- [ ] Renovar el certificado SSL wildcard antes del **2026-10-10** (validación manual, no se renueva solo).
- [ ] `reglas del negocio.md` no se actualizó con el nuevo modelo de acceso "cualquier usuario de la empresa entra a cualquier sucursal" — podría valer la pena reflejarlo ahí.

## Archivos de referencia creados esta sesión
- `config_vps.md` — infraestructura del VPS (Apache, DNS, SSL, PHP, permisos). **Gitignored** (tiene datos operativos sensibles).
- `config_sunat.md` — todo lo de SUNAT: certificado, datos obligatorios, catálogo de errores, cómo asignar permisos SOL, cómo ver la respuesta cruda.
- `reglas del negocio.md` — reglas de negocio generales del sistema (no actualizado hoy).
