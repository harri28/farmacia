# Configuración SUNAT — Notas de implementación

## Estado: EN PROGRESO — envío técnicamente correcto, SUNAT sigue rechazando (2026-07-12)
Corrección: la nota anterior decía "funcionando" basada en un toast de éxito de `B001-00000003` que resultó ser **falso** (bug de `reenviar_sunat`, ya corregido — ver abajo). Las 5 boletas emitidas hasta ahora (`B001-00000001` a `B001-00000005`) fueron rechazadas por SUNAT con el mismo error, incluso después de:
- Corregir RUC/razón social (certificado, XML y perfil ahora coinciden: `20616086465`, PETRAM CO SAC).
- Asignar los 3 permisos del Usuario SOL secundario (confirmado con screenshot que quedaron guardados).
- Corregir el sobre SOAP: faltaba `Type="...#PasswordText"` en `wsse:Password` (fix real, pero no resolvió esto solo).

**Error exacto** (sacado de `nubefact_response` en la BD, columna JSON con la respuesta cruda de SUNAT):
```
fault_code:   soap-env:Client.0111
fault_string: No tiene el perfil para enviar comprobantes electronicos - Detalle: Rejected by policy.
```
Código **0111** es un código de error oficial/documentado de SUNAT para su servicio `sendBill`. Con el sobre SOAP ya técnicamente correcto y los permisos del portal ya asignados, lo que queda pendiente es 100% del lado de la cuenta SUNAT, no del código:
1. **Demora de propagación** de los permisos recién asignados (puede tardar horas en activarse en el backend de SUNAT aunque el portal ya los muestre guardados) — probar de nuevo más tarde.
2. **RUC no afiliado al SEE** (Sistema de Emisión Electrónica) — trámite/registro de la empresa como emisor electrónico, separado de los permisos del usuario secundario. Se verifica/gestiona contactando a SUNAT directamente (Central de Consultas 0-801-12-100), dando el RUC y el código 0111.

Para saber si esto se resuelve solo: reintentar el envío de un comprobante pendiente (o generar una venta nueva) unas horas después, y revisar `nubefact_response` con el query de abajo.

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

## Cómo ver la respuesta cruda de SUNAT (para diagnosticar cualquier rechazo)
La tabla de Facturación solo muestra el mensaje recortado. Para ver el `fault_code`/`fault_string` completo y el XML de respuesta, consultar directo en la BD (ajustar el schema de la sucursal):
```bash
psql -U postgres -d farmacia -c "
SET search_path TO steifer_jr_lima, public;
SELECT id, numero_completo, estado_sunat, nubefact_response
FROM comprobantes_electronicos
ORDER BY id DESC
LIMIT 1;
"
```

## Catálogo de códigos de error SUNAT (se va llenando con lo que encontremos)
Cada vez que un envío falle, se saca el `fault_code`/`fault_string` real con el query de la sección anterior y se agrega aquí — así queda un catálogo propio, explicado en español simple, en vez de tener que volver a investigar el mismo código dos veces.

| Código | `fault_string` textual de SUNAT | Qué significa en la práctica | Cómo se resuelve |
|---|---|---|---|
| **0111** | "No tiene el perfil para enviar comprobantes electronicos - Detalle: Rejected by policy." | El Usuario SOL usado para el envío (o el RUC mismo) no tiene habilitado el perfil de emisor electrónico en el sistema de SUNAT — puede ser el permiso del usuario secundario, o que el RUC no esté afiliado al SEE. | Asignar el permiso "Servicio de Envío de Documentos Electrónicos" (ver sección de arriba) y/o esperar propagación / consultar con SUNAT si el RUC está afiliado al SEE. Visto el 2026-07-12, **aún sin confirmar resuelto**. |

*(Tabla en construcción — se agrega una fila nueva cada vez que aparezca un código distinto. No hay que memorizar catálogos genéricos de internet: solo se documentan los que realmente nos salieron, con el contexto real de qué se probó y qué funcionó.)*

## Notas para replicar esto con otras empresas/tenants
- Esta configuración es **por RUC/empresa**, no global al sistema — cada tenant nuevo necesita su propio Usuario SOL con este mismo permiso asignado, además de su propio certificado `.pfx` y credenciales SOL en Admin → Configuración.
- El camino en el portal SUNAT (Administración de Usuarios Secundarios → Modificar Programas → Tributarios → Comprobantes de pago → SEE - Del Contribuyente y Envío de Documentos) debería ser el mismo para cualquier RUC, ya que es la estructura de menú de SUNAT, no algo específico de esta empresa.
- Si en el futuro se usa un **OSE** (operador externo) en vez de envío directo, el permiso a asignar sería otro distinto (bajo "Operador de Servicios Electrónicos - OSE" o "Proveedor de Servicios Electrónicos-PSE") — no aplica a este sistema, que usa envío directo (`config/sunat.php`).
