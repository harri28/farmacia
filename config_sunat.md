# Configuración SUNAT — Notas de implementación

## ✅ CAUSA RAÍZ ENCONTRADA Y RESUELTA (2026-09-14) — leer esto primero

Después de días descartando hipótesis (RUC mal guardado, afiliación SEE, permisos idénticos byte a byte, certificados, endpoints alternativos, correo registrado, `anexo_sucursal`), la causa real del rechazo `0111 "No tiene el perfil para enviar comprobantes electronicos - Detalle: Rejected by policy"` en Producción para cuentas nuevas fue:

**Un Usuario SOL secundario al que se le modificó el mismo permiso varias veces en distintas sesiones (crear → asignar → volver a confirmar días después → agregarle más permisos encima) queda en un estado inconsistente del lado de SUNAT que no se arregla volviendo a marcar las mismas casillas.** La solución fue crear un **Usuario SOL secundario nuevo, nunca antes tocado**, y asignarle **todos los permisos necesarios de una sola vez, en una sola sesión** — no en pasos separados a lo largo de varios días.

**Receta confirmada que funciona** (validada con Grupo Tapullima & Manayalle SAC, usuario `HARRILUZ`, 2026-09-14):

1. En el portal SOL (con la Clave SOL del RUC **principal**), crear un **usuario secundario nuevo** — no reusar uno que ya se haya tocado antes. Usuario en **mayúsculas** (dato de la comunidad Greenter, no confirmado como obligatorio pero es gratis cumplirlo).
2. En una sola pasada de "Modificar/Asignar Programas", marcar **ambos** grupos de permisos (no solo uno):
   - **TRIBUTARIOS → Comprobantes de pago → SEE - Del Contribuyente y Envío de Documentos**: Servicio de Envío de Documentos Electrónicos, Certificado Digital, Consultar Envíos de CPE.
   - **TRIBUTARIOS → Comprobantes de pago → SEE - SOL**: Factura Electrónica → Emitir Factura (+ Nota de Crédito/Débito, Consultar), Boleta de Venta Electrónica → Emitir Boleta de Venta (+ Nota de Crédito/Débito, Consultar). **Este segundo grupo es el que faltaba** — la documentación previa de este mismo archivo decía que "SEE - SOL" no aplicaba para envío por servicio web (ver nota de corrección más abajo), y eso era incompleto: aunque el envío en sí es por servicio web, SUNAT parece exigir también el perfil base de emisión de SEE-SOL como prerequisito.
3. Completar el asistente hasta el final y confirmar que aparece el mensaje explícito **"El Usuario Secundario y sus Opciones han sido registrados satisfactoriamente."** — no basta con ver las casillas marcadas en el árbol, hay que llegar a esa confirmación.
4. Configurar ese usuario/clave nuevo en Admin → Configuración → SUNAT y certificado de FarmaSystem (el RUC y el certificado **no cambian**, siguen siendo los mismos ya validados).
5. Esperar **algunas horas** (no necesariamente 24-48h completas) antes de que la aceptación sea consistente — es normal ver 1-2 rechazos `0111` intermitentes en las primeras horas mientras SUNAT termina de propagar el nuevo usuario en todos sus servidores; después de eso, aceptación estable. Confirmado con el Reporte de Ventas de Grupo Tapullima: todos los envíos desde las ~2 horas después de crear `HARRILUZ` en adelante salieron "Aceptado" (con 2 rechazos sueltos intermedios, coherente con propagación desigual, no con un problema real).

**Corrección a una nota anterior de este archivo**: en la sección "Error: No tiene el perfil..." más abajo se decía que "SEE - SOL" era irrelevante para un sistema que envía por servicio web como este. Esa afirmación **era incompleta** — SUNAT sí parece requerir (al menos en la práctica, sin que quede claro en su documentación pública) que el usuario tenga también el perfil de emisión de SEE-SOL, aunque el canal real de envío sea el servicio web de "SEE del Contribuyente". Dejar el punto 2 de la receta de arriba como la guía correcta a seguir de ahora en adelante.

**Pendiente de aplicar la misma receta** (usuario nuevo + ambos grupos de permisos en una sola sesión) a: `generycpharma` (RUC `20611023457`, actualmente con `User1237`, tocado varias veces — crear un usuario nuevo en vez de seguir insistiendo con ese) y al RUC natural de prueba (`10734630549`, `PETRAM73`, mismo caso).

## Estado actual de los tenants configurados con SUNAT (actualizado 2026-09-14)

| Tenant | RUC | Usuario SOL | Rol | Estado actual |
|---|---|---|---|---|
| **PETRAM CO SAC** | `20616086465` | `HARRIS28` | Empresa propia del usuario del sistema — **la usa para hacer pruebas**, no es cliente. | ✅ Envío a SUNAT **Aceptado consistente** desde 2026-07-12 (Producción). Es el caso de referencia "funcionando" que se usa para comparar cuando otro tenant falla. |
| **Generic Pharma** (razón social real: Grupo Tapullima & Manayalle SAC) | `20616306139` | ~~`petram26`~~ → **`HARRILUZ`** (usuario nuevo, 2026-09-14) | Cliente real. | ✅ **Resuelto 2026-09-14.** El usuario original `petram26` nunca llegó a funcionar; se creó `HARRILUZ` desde cero con ambos grupos de permisos (SEE-Del Contribuyente + SEE-SOL) en una sola sesión — Aceptado consistente unas horas después. Ver "Causa raíz" arriba. |
| **generycpharma** (razón social real: GRUPO OLAZABAL MUÑOZ S.A.C.) | `20611023457` | `User1237` | Cliente real. | 🔴 **Sigue bloqueado.** Se corrigió el `ruc` (tenía un placeholder) y se confirmó afiliación al SEE, pero `User1237` fue tocado/re-guardado varias veces en distintas sesiones — candidato principal a estar en el mismo estado inconsistente que tenía `petram26`. **Siguiente paso: aplicar la receta de la sección "Causa raíz" (usuario nuevo, no reusar `User1237`).** |

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
| **0111** | "No tiene el perfil para enviar comprobantes electronicos - Detalle: Rejected by policy." | El Usuario SOL usado para el envío (o el RUC mismo) no tiene habilitado el perfil de emisor electrónico en el sistema de SUNAT — puede ser el permiso del usuario secundario, o que el RUC no esté afiliado al SEE. | Asignar el permiso "Servicio de Envío de Documentos Electrónicos" (ver sección de arriba). **RESUELTO 2026-07-12** para Steifer/PETRAM (RUC 20616086465): dejó de aparecer una vez asignado el permiso + corregido el `Type` faltante en `wsse:Password` — el envío avanzó a un error distinto (0102), confirmando que 0111 ya no ocurre. **REAPARECIÓ 2026-07-27/28** para **Generyc Pharma (RUC 20611023457, usuario SOL `User1237`)** — mismo código exacto (`soap-env:Client.0111`), confirmado con el XML crudo de respuesta de SUNAT (sin `<detail>` adicional, solo faltcode+faultstring). Es la confirmación práctica de que el permiso es **por RUC**, no global: cada empresa nueva necesita repetir el mismo trámite de la sección de arriba, aunque ya se haya hecho para otro RUC en este mismo sistema. Pendiente de que el cliente lo resuelva con Mesa de Ayuda de SUNAT / el portal para este RUC específico. **NUEVO HALLAZGO 2026-08-30** — ver sección "Propagación del permiso SOL no es inmediata" más abajo: el permiso recién asignado puede tardar horas en propagarse de forma pareja en los servidores de SUNAT, causando rechazos **intermitentes** (unos comprobantes pasan, otros no, con la misma configuración exacta) durante ese período — no confundir con un permiso mal asignado. **REAPARECIÓ 2026-09-11** para **Generic Pharma / Grupo Tapullima & Manayalle SAC (RUC 20616306139, usuario SOL `petram26`)**, probado en local después de arreglar dos bugs de entorno que lo tapaban (ver "Bugs de entorno resueltos" más abajo) — mismo `fault_string` exacto. Tercera confirmación de que el permiso es por RUC: pendiente que se asigne para este RUC específico. **CAUSA DISTINTA encontrada 2026-09-11 en `generycpharma` (producción)**: el usuario `User1237` SÍ tenía el permiso completo desde julio (confirmado revisando las 3 casillas en el portal SUNAT), pero el rechazo seguía apareciendo un mes después — no era propagación ni permiso faltante. La causa real: `public.tenants.ruc` de ese tenant estaba guardado como `20123456789` (RUC de prueba/placeholder), mientras que `User1237` y el certificado `.pfx` real pertenecen al RUC `20611023457` (`GRUPO OLAZABAL MUÑOZ S.A.C.`, confirmado con `openssl x509 -subject` sobre el certificado). El sistema armaba el SOAP con `RUC-de-prueba + User1237`, una combinación que SUNAT nunca reconoce — de ahí el "Rejected by policy" aunque el permiso estuviera perfecto. **Lección: si el usuario SOL tiene el permiso confirmado y aun así rechaza con 0111, comparar `ruc` guardado en `public.tenants` contra el RUC real del certificado (`openssl pkcs12 -in <archivo>.pfx -clcerts -nokeys -passin pass:<clave> \| openssl x509 -noout -subject`) antes de sospechar de SUNAT.** Ver también la sección de `config_vps.md` sobre `ruc`/`business_name` confundidos entre pantallas — mismo patrón de bug, tercera vez que aparece. |
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

## Bugs de entorno resueltos (2026-09-11) — tapaban el error real de SUNAT en local
Al probar el reenvío a SUNAT del tenant Generic Pharma / Grupo Tapullima & Manayalle SAC (RUC `20616306139`) en el XAMPP local, aparecían dos errores de **entorno** (no de configuración SUNAT) que impedían siquiera llegar a ver la respuesta real de SUNAT:

1. **`facturacion/signature.php` y `facturacion/cacert.pem` borrados del disco** (sin commitear, quedaban como `D` en `git status` desde hace semanas) — `enviar_sunat()` hace `require_once` de `signature.php` sin poder capturarlo con try/catch (un `require` de archivo inexistente es un error fatal de compilación en PHP, no una `Exception`), así que el envío cortaba a medias e imprimía el fatal error como HTML en vez de JSON → en el navegador se veía como `Unexpected token '<', "..." is not valid JSON`. **Fix**: `git restore facturacion/signature.php facturacion/cacert.pem facturacion/README.md` (estaban en el último commit que los tocó, `520d745`).
2. **Certificado `.pfx` cifrado con RC2-40-CBC, incompatible con OpenSSL 3.x**: una vez restaurado `signature.php`, el envío fallaba con `Failure Signing Data: error:0308010C:digital envelope routines::unsupported`. Confirmado con `openssl pkcs12 -in <archivo>.pfx -info -noout -passin pass:<clave>`: el certificado usa `pbeWithSHA1And40BitRC2-CBC`, un cifrado PKCS#12 que OpenSSL 3.x deshabilita por defecto ("legacy provider"). El XAMPP local corre PHP 8.2.12 con OpenSSL 3.0.11 — el VPS de producción corre PHP 7.x, casi seguro con OpenSSL 1.0/1.1, donde este algoritmo nunca estuvo restringido (por eso este bug es específico del entorno local, no se espera en producción). **Fix aplicado solo al `.pfx` local** (se guardó el original como `<archivo>.pfx.bak_legacy` antes de tocarlo):
   ```bash
   openssl pkcs12 -in <archivo>.pfx -legacy -nodes -out temp.pem -passin pass:<clave>
   openssl pkcs12 -export -in temp.pem -out <archivo>.pfx -passout pass:<clave>
   rm temp.pem   # contiene la llave privada sin cifrar, no dejarlo tirado
   ```
   Mismo certificado y llave privada, solo cambia el algoritmo de cifrado del contenedor `.pfx` (a AES-256-CBC/PBKDF2) — no afecta el RUC/Usuario SOL/Clave SOL configurados en Admin → Configuración, esos siguen apuntando al mismo archivo.

Con ambos resueltos, el envío llegó limpio hasta SUNAT y devolvió el rechazo real `0111 Rejected by policy` (ver fila de la tabla de arriba) — o sea, estos dos bugs de entorno eran justamente lo que estaba enmascarando el diagnóstico real.

## Caso `generycpharma` (RUC 20611023457) — RUC de prueba guardado en producción (2026-09-11)
Otro hallazgo del mismo día, en el tenant de producción `generycpharma` (usuario SOL `User1237`): llevaba **más de un mes** rechazando con `0111 Rejected by policy` a pesar de que el permiso de `User1237` ya estaba asignado desde julio (confirmado revisando las 3 casillas en el portal SUNAT) — no era propagación, era otra cosa.

**Causa real:** `public.tenants.ruc` de ese tenant estaba guardado como `20123456789` (RUC de prueba/placeholder, números correlativos) en vez del RUC real `20611023457`. El `business_name` ("GRUPO OLAZABAL MUÑOZ S.A.C.") sí estaba correcto — coincide con el `subject` real del certificado (confirmado con `openssl x509 -subject`) y con el nombre que muestra el propio portal SUNAT al loguearse con esa empresa. O sea: el sistema armaba el sobre SOAP con `RUC-de-prueba (20123456789) + Usuario SOL (User1237)` — una combinación que SUNAT nunca puede reconocer, porque `User1237` es un usuario secundario del RUC `20611023457`, no de `20123456789`. De ahí el "Rejected by policy" constante, sin importar cuántas veces se revisara el permiso del usuario (se estaba revisando el permiso del RUC correcto, pero el sistema enviaba con el RUC equivocado).

**Cómo se detectó — método de comparación con un caso funcionando:** en vez de seguir asumiendo que era el permiso SOL, se comparó campo por campo contra un tenant que sí envía bien (PETRAM CO SAC, RUC `20616086465`, ver tabla de la sección anterior — "Aceptado consistente" desde 2026-07-12):
```bash
# Datos del tenant
PGPASSWORD='1234' psql -U postgres -d farmacia -c "SELECT id, nombre, business_name, ruc, sunat_username, sunat_server, certificate_path, activo FROM public.tenants WHERE ruc = '<RUC>' OR nombre ILIKE '%<nombre>%';"

# Detalle del certificado (a quién pertenece realmente)
PASS=$(PGPASSWORD='1234' psql -U postgres -d farmacia -tA -c "SELECT certificate_password FROM public.tenants WHERE ruc = '<RUC>';")
openssl pkcs12 -in <certificate_path> -clcerts -nokeys -passin pass:$PASS 2>/dev/null | openssl x509 -noout -subject -issuer -dates
```
La comparación mostró que ambos certificados son CDT gratuitos de RENIEC, de antigüedad similar (PETRAM emitido 12-jul, Generyc Pharma 6-jul) — descartando que el tipo de certificado o su antigüedad fuera la diferencia. Eso apuntó directo al único dato que sí difería de forma sospechosa: el `ruc` guardado.

**Fix:** corregir `public.tenants.ruc` al valor real (`UPDATE public.tenants SET ruc = '20611023457' WHERE id = <id>;`, hecho vía Admin → Configuración en este caso). Confirmado con Beta: el siguiente envío (`B001-00002127`) salió **Aceptado**. Reenviar el mismo comprobante en **Producción** (`B001-00002126`) siguió rechazando con el mismo `0111` exacto (mismo `fault_code`, mismo `fault_string`, confirmado con el `nubefact_response` crudo) — pendiente de resolver, ver sección de abajo.

**Lección general:** si un usuario SOL tiene el permiso "Servicio de Envío de Documentos Electrónicos" confirmado en el portal y aun así rechaza con `0111`, **no asumir que es propagación o que hay que volver a asignar el permiso** — primero comparar el `ruc` guardado en `public.tenants` contra el RUC real del certificado (`openssl x509 -subject`, buscar `organizationIdentifier=NTRPE-<RUC>`) y contra el RUC bajo el cual se le dio el permiso al usuario en el portal SUNAT. Si tienes otro tenant que sí funciona, compararlo campo por campo (tenant + certificado) es la forma más rápida de aislar qué es distinto.

## Conclusión (2026-09-11) — `generycpharma` sigue rechazado tras agotar todo lo verificable; escalado a Mesa de Ayuda SUNAT
Con el RUC ya corregido, Beta aceptó el envío (`B001-00002127`) pero Producción siguió rechazando `0111 Rejected by policy` en cada reintento, incluso después de esperar varias horas (descarta propagación) y de volver a completar el asistente de permisos de `User1237` hasta el final ("Siguiente → Grabar", no solo dejarlo en el árbol marcado). Se agotaron, en orden, las 3 hipótesis que quedaban:

1. ~~El asistente no se había confirmado de verdad~~ — descartado: se repitió completo hasta grabar, mismo resultado.
2. ~~El RUC no está afiliado al SEE~~ — descartado: **la emisión manual de comprobantes desde el propio portal SUNAT (Comprobantes de pago → SEE - SOL → Emitir Comprobante de Pago) funciona sin problema** para este RUC. Esto confirma que el RUC sí está afiliado al SEE y puede emitir electrónicamente — el problema es específico del canal de **envío por servicio web** (el que usa FarmaSystem), no una afiliación general.
3. **Comparación exhaustiva contra `HARRIS28` (PETRAM, caso que sí funciona)** — se revisó el mismo árbol de permisos ("Modificar Programas") nodo por nodo para ambos usuarios: **resultado idéntico**. Mismas 3 opciones marcadas, mismos sub-ítems de "Consultar Envíos de CPE", y hasta la misma línea duplicada "Servicio de Envío de Documentos Electrónicos por Servicio Web" apareciendo dos veces en el resumen de ambos usuarios (confirmado que esa duplicación es cosmética de SUNAT, no una anomalía). No hay ninguna diferencia de configuración entre el usuario que funciona y el que no.

**Estado final:** con RUC, razón social, certificado, afiliación SEE y permisos del usuario secundario todos verificados y correctos (idénticos a un tenant que sí funciona), el rechazo `0111` para `generycpharma`/`User1237` no tiene explicación diagnosticable desde el sistema ni desde el portal SUNAT — apunta a algo específico de esa cuenta en el backend de SUNAT. **Se escala a Mesa de Ayuda de SUNAT** con estos datos:
- RUC: `20611023457` · Usuario SOL: `User1237`
- Error: `soap-env:Client.0111` — "No tiene el perfil para enviar comprobantes electronicos - Detalle: Rejected by policy."
- Contexto para darle a SUNAT: permiso "Servicio de Envío de Documentos Electrónicos" asignado y confirmado hace más de un mes; emisión manual (SEE-SOL) funciona; envío por servicio web (SEE del Contribuyente) rechazado consistentemente.

Nota aparte: que Beta acepte y Producción rechace con la misma configuración exacta sugiere que Beta no valida el permiso real del usuario secundario con el mismo rigor que Producción — no tomar un "Aceptado" en Beta como confirmación de que el permiso está bien en Producción, en ningún tenant futuro.

## Diagnóstico adicional (2026-09-11) — entrando al portal SUNAT como el propio usuario secundario
Para seguir descartando causas antes de esperar la respuesta de Mesa de Ayuda, se probó entrar al portal SOL **directamente con las credenciales del usuario secundario** (`User1237` / la Clave SOL guardada en Admin → Configuración de FarmaSystem), en vez de con la Clave SOL del RUC principal. Se comparó cada dato contra lo guardado en el sistema antes de intentar el login — coincidieron los 3 (RUC, Usuario SOL, Clave SOL) exactamente.

**Resultado:**
- **Login exitoso** — descarta de plano cualquier duda sobre la clave SOL guardada en el sistema estar mal o desactualizada (si estuviera mal, SUNAT habría dado error de "usuario o contraseña incorrectos" al intentar loguearse, igual que el código 0102 documentado arriba).
- Ya logueado como `User1237` (no como administrador viendo sus permisos desde afuera), el menú **"Empresas → Comprobantes de pago"** muestra exactamente los 2 accesos esperados: "Comprobantes de Pago" y "SEE - Del Contribuyente y Envío de Documentos" — confirma el permiso también desde la perspectiva del propio usuario, no solo desde la pantalla de administración.
- Dentro de eso, **Consultar Envíos de CPE → Consultar Envío de Comprobante de Pago Electrónicos**, buscando por la fecha de hoy (11/09/2026, día en que se hicieron varios reintentos de envío): **"Ningún dato disponible en esta tabla"** — SUNAT no tiene registrado NINGÚN intento de envío de hoy, a pesar de que FarmaSystem sí los mandó y recibió el rechazo `0111` en cada uno.

**Interpretación:** esto es una pista fuerte de que el rechazo `0111 Rejected by policy` está ocurriendo en una **capa de autenticación/gateway de SUNAT, antes** de que la solicitud llegue al sistema donde se registran los envíos de comprobantes — no es que SUNAT reciba el documento y lo rechace por falta de permiso a nivel de negocio, es que ni siquiera lo deja pasar de la puerta de entrada. Es coherente con un problema de **caché de permisos desactualizada** en la infraestructura de SUNAT para este RUC/usuario específico (algo que no se puede forzar a refrescar ni desde el portal ni desde el sistema, solo Mesa de Ayuda tiene acceso a esa capa).

**Reporte formal solicitado a SUNAT:** se marcó el checkbox "Envíos Rechazados por Período" en la misma pantalla, lo que cambia la consulta a un **reporte asíncrono** (no instantáneo): `Tipo de Servicio = Facturas`, `Período de Envío = 202609`, `Tipo de Consulta = Rechazos`. Quedó **"Registrado"** (fecha de inicio 11/09/2026 23:38:58) — SUNAT indica que tarda **~8 horas aproximadamente** en generar el archivo descargable con el detalle oficial de los rechazos de ese período. Pendiente: volver a esa misma pantalla en ~8 horas y descargar el archivo cuando el estado cambie a "Terminado" — puede traer más detalle que el `fault_string` genérico que ya tenemos.

**Este hallazgo refuerza (no reemplaza) la recomendación de escalar a Mesa de Ayuda** — ahora con un dato más concreto para darles: "no aparece ningún registro de mis envíos de hoy en Consultar Envíos de CPE, a pesar de que mi sistema recibió rechazo `0111` en cada intento", que apunta más directo a un problema de su lado (caché/gateway) que a algo del lado del contribuyente.

## Notas para replicar esto con otras empresas/tenants
- Esta configuración es **por RUC/empresa**, no global al sistema — cada tenant nuevo necesita su propio Usuario SOL con este mismo permiso asignado, además de su propio certificado `.pfx` y credenciales SOL en Admin → Configuración.
- El camino en el portal SUNAT (Administración de Usuarios Secundarios → Modificar Programas → Tributarios → Comprobantes de pago → SEE - Del Contribuyente y Envío de Documentos) debería ser el mismo para cualquier RUC, ya que es la estructura de menú de SUNAT, no algo específico de esta empresa.
- Si en el futuro se usa un **OSE** (operador externo) en vez de envío directo, el permiso a asignar sería otro distinto (bajo "Operador de Servicios Electrónicos - OSE" o "Proveedor de Servicios Electrónicos-PSE") — no aplica a este sistema, que usa envío directo (`config/sunat.php`).
