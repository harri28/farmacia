Facturacion interna del proyecto.

Contenido:
- `signature.php`: firma XML.
- `api_signature/`: librerias usadas por la firma.
- `cacert.pem`: bundle CA para conexiones SOAP/cURL.
- `certs/`: certificados digitales.
- `storage/xml/`: XML generados.
- `storage/cdr/`: respuestas CDR.
- `storage/soap/`: requests/responses SOAP de soporte.

Esta carpeta existe para que la emision electronica sea autocontenida
dentro de `farmacia` y no dependa de `C:\laragon\www\apiSunat2027`.

Pendiente para configuracion real:
- credenciales SOL
- RUC y datos del emisor
- certificado digital definitivo
- endpoints beta/produccion
