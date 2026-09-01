# Configuración SUNAT — Notas de implementación

## Estado: EN PROGRESO — se pasó el error 0111, ahora falla por credenciales SOL (2026-07-12)
Avance real: después de asignar los permisos del Usuario SOL y corregir el sobre SOAP, el error `0111 "Rejected by policy"` **dejó de aparecer** — la boleta `B001-00000006` avanzó a un error distinto:
```
fault_code:   soap-env:Client.0102
fault_string: Usuario o contrasena incorrectos - Detalle:
```
Esto confirma que el sobre SOAP y los permisos ya están bien — ahora el bloqueo es que el **Usuario SOL / Clave SOL** guardados en Admin → Configuración no están siendo aceptados por SUNAT. Pendiente: revisar que "Usuario SOL" tenga *solo* `HARRIS28` (sin el RUC delante, el sistema ya lo antepone) y volver a escribir la Clave SOL a mano. Ver catálogo de códigos abajo para el detalle completo de ambos errores.

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
| **0111** | "No tiene el perfil para enviar comprobantes electronicos - Detalle: Rejected by policy." | El Usuario SOL usado para el envío (o el RUC mismo) no tiene habilitado el perfil de emisor electrónico en el sistema de SUNAT — puede ser el permiso del usuario secundario, o que el RUC no esté afiliado al SEE. | Asignar el permiso "Servicio de Envío de Documentos Electrónicos" (ver sección de arriba). **RESUELTO 2026-07-12** para Steifer/PETRAM (RUC 20616086465): dejó de aparecer una vez asignado el permiso + corregido el `Type` faltante en `wsse:Password` — el envío avanzó a un error distinto (0102), confirmando que 0111 ya no ocurre. **REAPARECIÓ 2026-07-27/28** para **Generyc Pharma (RUC 20611023457, usuario SOL `User1237`)** — mismo código exacto (`soap-env:Client.0111`), confirmado con el XML crudo de respuesta de SUNAT (sin `<detail>` adicional, solo faltcode+faultstring). Es la confirmación práctica de que el permiso es **por RUC**, no global: cada empresa nueva necesita repetir el mismo trámite de la sección de arriba, aunque ya se haya hecho para otro RUC en este mismo sistema. Pendiente de que el cliente lo resuelva con Mesa de Ayuda de SUNAT / el portal para este RUC específico. **NUEVO HALLAZGO 2026-08-30** — ver sección "Propagación del permiso SOL no es inmediata" más abajo: el permiso recién asignado puede tardar horas en propagarse de forma pareja en los servidores de SUNAT, causando rechazos **intermitentes** (unos comprobantes pasan, otros no, con la misma configuración exacta) durante ese período — no confundir con un permiso mal asignado. |
| **0102** | "Usuario o contrasena incorrectos - Detalle: " (sin más detalle) | Las credenciales SOL (Usuario SOL + Clave SOL) que se están enviando no son válidas para SUNAT. Puede ser: la Clave SOL guardada está mal/desactualizada, o el campo "Usuario SOL" quedó con el RUC duplicado (el sistema ya antepone el RUC automáticamente — el campo debe tener *solo* el usuario, ej. `HARRIS28`, no `20616086465HARRIS28`). | Revisar/re-escribir a mano (no copiar/pegar) Usuario SOL y Clave SOL en Admin → Configuración. Para confirmar si la clave en sí es correcta, probar loguearse directo en SUNAT con ese usuario secundario. Visto el 2026-07-12, **aún sin confirmar resuelto**. |

*(Tabla en construcción — se agrega una fila nueva cada vez que aparezca un código distinto. No hay que memorizar catálogos genéricos de internet: solo se documentan los que realmente nos salieron, con el contexto real de qué se probó y qué funcionó.)*

## Cómo obtener el Certificado Digital Tributario (CDT) gratuito de RENIEC/SUNAT
Para un RUC que nunca ha emitido comprobantes electrónicos y no tiene certificado propio comprado, SUNAT ofrece uno gratuito (el CDT). Flujo completo probado el 2026-08-30 con Grupo Tapullima & Manayalle SAC (RUC 20616306139):

1. **Registrar correo** (requisito previo): logueado con la Clave SOL del usuario que va a emitir (puede ser el usuario secundario si ya tiene el permiso "Certificado Digital", o el principal), ir a **Comprobantes de pago → SEE - Del Contribuyente y Envío de Documentos → Certificado Digital → Registro y Mantenimiento de Correo y Certificados Digitales**. Ahí, si no hay correo registrado, clic en "Registrar correo electrónico", ingresar un correo válido → llega un código de verificación al correo → se ingresa en la misma pantalla para confirmar.
2. **Solicitar el CDT**: ir a **Comprobantes de pago → Certificado Digital Tributario - CDT → Solicitar Certificado Digital Tributario**. Confirma los datos del contribuyente (RUC, razón social, correo, celular), aceptar términos y condiciones, "Enviar Solicitud".
3. SUNAT responde con un **número de solicitud** y un **enlace de descarga** (`https://cdt.reniec.gob.pe/...`), válido por unos **30 días** desde la emisión (pasado ese plazo hay que solicitarlo de nuevo). El mismo enlace se reenvía al Buzón Electrónico SOL.
4. Al abrir el enlace, RENIEC entrega el archivo del certificado directamente (confirmación "¡Felicidades! Has descargado tu Certificado Digital Tributario").
5. **La clave (PIN) de instalación del certificado NO se muestra en esa pantalla** — llega aparte, como un mensaje distinto en el **Buzón Electrónico SUNAT** (asunto "Envío de clave (PIN) de instalación del CDT - Número de Solicitud ..."), con un número de solicitud consecutivo al de la emisión (ej. si la emisión fue `...402`, el PIN llega en `...403`). SUNAT sugiere no borrar ese mensaje, sirve para recuperación futura.
6. Subir el certificado descargado en **Admin → Configuración → SUNAT y certificado**: campo "Clave certificado" = el PIN del paso 5, "Certificado digital (.pfx)" = el archivo descargado (si viene como `.p12`, el sistema lo acepta igual, es el mismo formato PKCS#12).

## Propagación del permiso SOL no es inmediata (hallazgo 2026-08-30)
Comparando dos tenants configurados en este sistema:

| | PETRAM CO SAC (RUC 20616086465) | Grupo Tapullima & Manayalle (RUC 20616306139) |
|---|---|---|
| Permiso SOL asignado | 2026-07-12 (~7 semanas antes de esta prueba) | 2026-08-30 (mismo día) |
| Certificado | Ya establecido desde antes | CDT recién emitido el mismo día |
| Entorno | Producción | Beta |
| Resultado al enviar | Aceptado consistente | **Intermitente**: reenviando la misma boleta varias veces seguidas, algunas quedan "Aceptado" y otras siguen rechazando con el mismo `0111 Rejected by policy`, sin ningún cambio de configuración entre intentos |

**Conclusión**: cuando se asigna el permiso "Servicio de Envío de Documentos Electrónicos" a un usuario SOL (o se emite un certificado nuevo) el mismo día que se necesita usarlo, es normal ver rechazos 0111 intermitentes durante varias horas mientras SUNAT propaga el cambio en todos sus servidores — no es necesariamente un error de configuración. Antes de sospechar de RUC/usuario/certificado mal puestos, **reintentar el envío (botón "Reenviar a SUNAT") varias veces a lo largo del día**; si sigue fallando al 100% después de 24 horas, ahí sí revisar la configuración a fondo.

## Notas para replicar esto con otras empresas/tenants
- Esta configuración es **por RUC/empresa**, no global al sistema — cada tenant nuevo necesita su propio Usuario SOL con este mismo permiso asignado, además de su propio certificado `.pfx` y credenciales SOL en Admin → Configuración.
- El camino en el portal SUNAT (Administración de Usuarios Secundarios → Modificar Programas → Tributarios → Comprobantes de pago → SEE - Del Contribuyente y Envío de Documentos) debería ser el mismo para cualquier RUC, ya que es la estructura de menú de SUNAT, no algo específico de esta empresa.
- Si en el futuro se usa un **OSE** (operador externo) en vez de envío directo, el permiso a asignar sería otro distinto (bajo "Operador de Servicios Electrónicos - OSE" o "Proveedor de Servicios Electrónicos-PSE") — no aplica a este sistema, que usa envío directo (`config/sunat.php`).
