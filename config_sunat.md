# Configuración SUNAT — Notas de implementación

## Estado: FUNCIONANDO EN PRODUCCIÓN (confirmado 2026-07-12)
Boleta `B001-00000003` (PETRAM CO SAC, RUC 20616086465) enviada y aceptada por SUNAT en el ambiente de Producción, de punta a punta: certificado `.pfx`, credenciales SOL, RUC/razón social correctos, permiso "Servicio de Envío de Documentos Electrónicos" asignado, y URL del XML autoadaptada al VPS. Ver bugs resueltos en `CLAUDE.md` (commits `7d95761` y anteriores) y detalle abajo.

## Certificado digital
- Formato requerido por el sistema: `.pfx` (renombrar si viene como `.p12` de SUNAT — es el mismo formato PKCS#12, solo cambia la extensión).
- Se sube desde **Admin → Configuración**. Se guarda en `facturacion/certs/<RUC>.pfx` en el VPS.
- Requiere permisos de escritura para `www-data` en `facturacion/certs/`, `facturacion/storage/xml/`, `facturacion/storage/cdr/` (ver `config_vps.md`).

## Datos obligatorios para poder enviar comprobantes (Admin → Configuración)
Validados en `config/sunat.php` (`sunat_profile_for_current_tenant()`) — sin todos estos, el envío falla:
- RUC
- Razón social
- Usuario SOL
- Clave SOL
- Certificado `.pfx` (subido)
- Contraseña del certificado

Selector adicional: **Servidor SUNAT** — `Beta` (sandbox, no aparece en el portal SUNAT real) o `Producción` (comprobante fiscal real y válido).

Los campos "GRE Client ID/Secret" son para Guías de Remisión Electrónica (trámite aparte) — no se necesitan para boletas/facturas normales.

## Error: "No tiene el perfil para enviar comprobantes electrónicos" / "Rejected by policy"
No es un error del sistema — es un permiso faltante del **Usuario SOL secundario** en el propio portal de SUNAT. Se corrige así:

1. Entrar a SUNAT con la **Clave SOL del RUC principal** (no la del usuario secundario).
2. **Administración de Usuarios Secundarios** → seleccionar el usuario (el mismo configurado como `sunat_username` en el sistema) → botón **"Modificar Programas"**.
   - Nota: el botón "Asignar Roles" de esa misma pantalla es para otro tipo de permisos (VUCE, comercio exterior, B2B) — **no es por ahí**.
3. En el árbol de opciones, expandir **TRIBUTARIOS**.
4. Dentro de Tributarios, expandir **"Comprobantes de pago"**.
5. Dentro de ahí hay varias sub-carpetas — **ojo, no todas sirven**:
   - **"SEE - SOL"** → esto es para usar el *Facturador SUNAT* manualmente desde el portal web (llenar boletas/facturas a mano en la página de SUNAT). **No es esto** si tu sistema envía automático por servicio web.
   - **"SEE - Del Contribuyente y Envío de Documentos"** → **esta es la correcta** para un sistema propio (como FarmaSystem) que envía comprobantes directo a SUNAT por servicio web/SOAP, sin pasar por un OSE intermediario. Expandir esta.
6. Dentro de "SEE - Del Contribuyente y Envío de Documentos" hay 3 opciones — marcar las **3**:
   - **Servicio de Envío de Documentos Electrónicos** ← la esencial, resuelve el error "Rejected by policy".
   - **Consultar Envíos de CPE** — consultar estado de comprobantes ya enviados.
   - **Certificado Digital** — gestión del certificado vía servicio web.
7. Clic en **Siguiente** → confirmar la asignación.

Después de guardar el permiso en SUNAT, no hace falta tocar nada del sistema — solo reintentar el envío desde Facturación (opción "Reenviar a SUNAT" en comprobantes pendientes/rechazados).

## Notas para replicar esto con otras empresas/tenants
- Esta configuración es **por RUC/empresa**, no global al sistema — cada tenant nuevo necesita su propio Usuario SOL con este mismo permiso asignado, además de su propio certificado `.pfx` y credenciales SOL en Admin → Configuración.
- El camino en el portal SUNAT (Administración de Usuarios Secundarios → Modificar Programas → Tributarios → Comprobantes de pago → SEE - Del Contribuyente y Envío de Documentos) debería ser el mismo para cualquier RUC, ya que es la estructura de menú de SUNAT, no algo específico de esta empresa.
- Si en el futuro se usa un **OSE** (operador externo) en vez de envío directo, el permiso a asignar sería otro distinto (bajo "Operador de Servicios Electrónicos - OSE" o "Proveedor de Servicios Electrónicos-PSE") — no aplica a este sistema, que usa envío directo (`config/sunat.php`).
