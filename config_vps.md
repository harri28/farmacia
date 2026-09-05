# Configuración del VPS

## Acceso
- Usuario/host: `root@sv-O8EUkgxLPR3n9lhvWIep`
- Ruta del proyecto: `/var/www/farmacia`

## Despliegue
- El proyecto se despliega vía git, rama `main`, sincronizado con `origin/main`.
- Flujo de actualización típico:
  ```bash
  cd /var/www/farmacia
  git status
  git pull origin main
  ```

## PHP
- **Local (XAMPP, este repo en Windows)**: PHP 8.2.12 (`php.exe -v`).
- **VPS — Apache/mod_php (sirve FarmaSystem en producción)**: **PHP 7.x** — confirmado el 2026-07-12 por el log de error (`[php7:error]`) al desplegar `str_ends_with()` (función exclusiva de PHP 8+) en `login.php`, que tumbó el login en producción con un 500 hasta reemplazarla por un equivalente compatible con PHP 7. Versión exacta (7.0/7.4/etc.) sin confirmar — para verificar: `apache2ctl -M | grep php` o `php7 -v`.
- **Corrección de una nota anterior**: la actualización a PHP 8.2 que se hizo en el VPS fue para `conexion_sunat/` (app Laravel aparte, que exige PHP 8.2+), **no** para el Apache que sirve el sitio principal. PHP 7 sigue siendo el que efectivamente atiende `genpharma.cloud` y sus subdominios de tenant.
- **Importante para todo código nuevo de FarmaSystem**: no usar sintaxis exclusiva de PHP 8+ (`str_ends_with`, `str_starts_with`, `str_contains`, `match`, `enum`, operador `?->`, etc.) mientras Apache siga sirviendo con PHP 7 — rompe en producción sin previo aviso.
- **Ya NO es seguro desinstalar PHP 7 del servidor** (la nota anterior lo daba por pendiente/seguro de borrar, pero es justamente el que está sirviendo el sitio). Antes de tocarlo hay que migrar Apache a mod_php 8.2 (o PHP-FPM) y probar a fondo.

## Servidor web
- Apache 2.4.41 (Ubuntu). No hay Nginx instalado.
- Vhost de FarmaSystem: `/etc/apache2/sites-available/genpharma.cloud.conf` (+ `genpharma.cloud-le-ssl.conf` para HTTPS, gestionado por Certbot/Let's Encrypt).
- `ServerName genpharma.cloud`, `ServerAlias www.genpharma.cloud` + `*.genpharma.cloud` (wildcard agregado — ver sección "Subdominios por tenant").
- Redirección forzada HTTP → HTTPS vía `mod_rewrite`, sin depender de `SERVER_NAME` exacto (cubre cualquier subdominio).
- El VPS aloja además otros sitios no relacionados: `sistemmg.com`, `ssgestion` (mismo servidor, vhosts separados) — tenerlo en cuenta antes de un `restart` de Apache.

**Reiniciar Apache:**
```bash
# Recomendado — recarga config sin cortar conexiones activas
systemctl reload apache2

# Reinicio completo — corta todas las conexiones activas de TODOS los sitios
# del VPS (incluye sistemmg.com y ssgestion), no solo FarmaSystem. Usar solo
# si reload no resuelve el problema.
systemctl restart apache2
```

## DNS (Hostinger) — genpharma.cloud
- `CNAME www → genpharma.cloud`
- `A * → 161.132.51.134` (wildcard, ya creado — cubre subdominios de tenant)
- `A @ → 161.132.51.134`
- IP del VPS confirmada: `161.132.51.134`

## Certificado SSL wildcard — EMITIDO
- `*.genpharma.cloud` + `genpharma.cloud` cubiertos. Emitido con Certbot DNS-01 manual, `--cert-name genpharma.cloud` (misma ruta de siempre, no requiere cambios de ruta en el vhost).
- Vence: **2026-10-10**. Guardado en `/etc/letsencrypt/live/genpharma.cloud/fullchain.pem` y `privkey.pem` (igual que antes).
- **Importante**: validación DNS manual → Certbot **no lo renueva solo**. Repetir el proceso manual (o armar un hook con la API de Hostinger) antes del **2026-10-10**.
- Los registros TXT `_acme-challenge` en Hostinger ya no son necesarios (se pueden borrar, no molestan si se dejan).

## Subdominios por tenant — LISTO Y FUNCIONANDO
- `ServerAlias *.genpharma.cloud` agregado en `genpharma.cloud.conf` y `genpharma.cloud-le-ssl.conf`.
- Vhost HTTP (`genpharma.cloud.conf`) simplificado: el redirect a HTTPS ya no depende de `SERVER_NAME` exacto (`RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [END,NE,R=permanent]`), así cubre cualquier subdominio.
- Verificado con `prueba.genpharma.cloud`: HTTP → 301 → HTTPS, certificado válido, Apache sirve `/var/www/farmacia`.
- **Pendiente**: crear el tenant con slug `generycparham` (sin guion bajo) desde el panel superadmin para que `generycparham.genpharma.cloud` muestre solo sus sucursales.

## Datos de empresa (tenant) — RUC y razón social, dos campos que se confunden
`public.tenants` tiene **varios** campos parecidos que se editan desde pantallas distintas — confirmado el 2026-07-12 al encontrar un boleta emitida con la identidad equivocada:

| Campo (columna en `tenants`) | Dónde se edita | Para qué sirve |
|---|---|---|
| `nombre` | Superadmin → Empresas → Editar empresa → "Nombre de la empresa" | Branding/visualización general del sistema (login, sidebar) |
| `business_name` | Admin → Configuración → Datos de la botica → **"Razón social para emitir"** | **Es el que se manda a SUNAT** en el XML del comprobante (`AccountingSupplierParty`) |
| `trade_name` | Admin → Configuración → "Nombre del negocio" | Nombre comercial, informativo |
| `ruc` | **Ambas pantallas** (Superadmin y Admin → Configuración) editan la **misma columna** — corregirlo en cualquiera de las dos alcanza |

**Bug real encontrado**: para el tenant PETRAM CO SAC (RUC certificado: `20616086465`), tanto `ruc` como `business_name` estaban mal cargados — `ruc = '10734630549'` (parece un RUC de persona natural derivado del DNI del usuario que configuró el sistema, `73463054`) y `business_name = 'steifer'`, en vez del RUC/razón social real de la empresa. SUNAT rechaza el envío si el RUC del XML no coincide con el RUC del certificado que firma — **quedaba pendiente corregir ambos campos** (RUC → `20616086465`, razón social → nombre legal exacto registrado en SUNAT) antes de que el envío a producción funcione.

**Para futuros tenants**: al dar de alta una empresa nueva, verificar explícitamente que `ruc` y `business_name` (no solo `nombre`) queden con los datos reales de la empresa — es fácil dejarlos con datos de prueba/placeholder sin darse cuenta, ya que "Nombre de la empresa" (Superadmin) no es el mismo campo que alimenta el comprobante SUNAT.

## Base de datos de producción — CONFIRMADO 2026-08-30
Usa exactamente las mismas credenciales que local (`config/database.php` en el VPS es idéntico al de XAMPP): host `localhost`, puerto `5432`, db `farmacia`, usuario `postgres`, password `1234`. Para consultar directo desde el VPS (ya en `/var/www/farmacia`):
```bash
PGPASSWORD='1234' psql -U postgres -d farmacia -c "..."
```

## Bug conocido: `schema_name` duplicado en una sucursal (encontrado 2026-08-30)
La sucursal **Grupo Tapullima & Manayalle SAC** (id 7, tenant_id 5) quedó con `schema_name = grupo_tapullima_manayalle_grupo_tapullima_mana` — el slug parece haberse concatenado dos veces y truncado (posiblemente por el límite de 63 caracteres de PostgreSQL para identificadores). Confirmado consultando `public.sucursales` directo, ya que el schema "esperado" (`grupo_tapullima_manayalle`, sin duplicar) existe pero está vacío — no es el que usa la app en runtime. Pendiente: revisar el código que genera `schema_name` al crear una sucursal nueva (probablemente en `modules/admin/api.php`, acción `sucursal_crear`) para evitar que se repita con futuras sucursales.

## Bug resuelto: `https://sistemmg.com` mostraba la landing de FarmaSystem (2026-09-04)
El VPS aloja varios sitios no relacionados en la misma IP (ver sección "Servidor web" arriba: `sistemmg.com`, `ssgestion`/`corfiemsistem.com`, `kallparoom.cloud`, `trazabcafe.cloud`, además de `genpharma.cloud`). `genpharma.cloud` quedó registrado como **`default server`** de Apache tanto en `*:80` como en `*:443` (por ser el primer vhost cargado — confirmado con `apache2ctl -S`).

**Síntoma:** entrar a `https://sistemmg.com` mostraba la landing page de FarmaSystem, con el navegador marcando "No es seguro".

**Causa:** `sistemmg.com` tenía vhost propio en `:80` (`sistemmg.com.conf`) pero **nunca tuvo un vhost SSL** (`sistemmg.com-le-ssl.conf` no existía). Al conectar por HTTPS, el SNI `sistemmg.com` no matcheaba ningún vhost `:443`, así que Apache caía al **default server** (`genpharma.cloud-le-ssl.conf`) — sirviendo el certificado de genpharma (de ahí el "No es seguro", cert no cubre `sistemmg.com`) y el docroot de FarmaSystem. Una vez ahí, el propio `index.php` de FarmaSystem miraba el `Host` real (`sistemmg.com`, solo 2 labels) y renderizaba la landing page (ver regla de routing por hostname en este mismo archivo).

**Fix aplicado:**
```bash
certbot --apache -d sistemmg.com -d www.sistemmg.com
# opción 2 (Redirect) cuando pregunta por forzar HTTPS
```
Certbot creó `sistemmg.com-le-ssl.conf`, lo habilitó, y desplegó el certificado. Confirmado con `apache2ctl -S`: `sistemmg.com` ya aparece como `namevhost` propio en `*:443` (ya no cae en el default). Certificado vence **2026-12-04**.

**Pendiente relacionado — `corfiemsistem.com` (ssgestion) tiene el mismo problema sin resolver:** el mismo comando (`certbot --apache -d corfiemsistem.com -d www.corfiemsistem.com`) falló con `no valid A records found` / `NXDOMAIN` — ese dominio **no tiene DNS apuntando a este VPS en absoluto** (ni siquiera el apex resuelve). Hay que agregar los registros `A` (y `www`) en el proveedor de DNS de `corfiemsistem.com` antes de poder reintentar el certbot. Mientras tanto sigue expuesto al mismo riesgo (`https://corfiemsistem.com` caería en el default `genpharma.cloud` si alguien llegara a resolverlo por otra vía).

**Nota para el futuro:** cualquier dominio nuevo que se agregue a este VPS sin su propio vhost SSL va a caer en `genpharma.cloud` (FarmaSystem) por ser el default server — repetir este mismo fix (`certbot --apache -d <dominio> -d www.<dominio>`) apenas se dé de alta un sitio nuevo, no dejarlo solo con vhost `:80`.

## Pendiente de completar
- Rutas de logs
