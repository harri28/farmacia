<?php
// ============================================================
// ARCHIVO: farmacia/config/sunat.php
// DESCRIPCION: Adaptador SUNAT SOAP usando la carpeta interna
//              facturacion del proyecto.
// ============================================================

function sunat_base_dir(): string
{
    return realpath(__DIR__ . '/../facturacion') ?: (__DIR__ . '/../facturacion');
}

function sunat_public_base_url(): string
{
    // No hardcodear "/farmacia" -- en el VPS el DocumentRoot de Apache ES la
    // raíz de la app (sin subcarpeta), pero en XAMPP local la app vive bajo
    // htdocs/farmacia. Se calcula el prefijo comparando la carpeta real del
    // proyecto contra el DocumentRoot, para que funcione en ambos entornos.
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $appRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

    $prefix = '';
    if ($docRoot !== '' && strpos($appRoot, $docRoot) === 0) {
        $prefix = substr($appRoot, strlen($docRoot));
    }

    return $prefix . '/facturacion/storage';
}

function sunat_estado_db(string $estado): string
{
    $estado = trim($estado);
    if (function_exists('mb_substr')) {
        return mb_substr($estado, 0, 100);
    }

    return substr($estado, 0, 100);
}

function sunat_resolve_certificate_path(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) || strpos($path, '/') === 0 || strpos($path, '\\') === 0) {
        return $path;
    }

    $rootPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    if (file_exists($rootPath)) {
        return $rootPath;
    }

    $facturacionPath = sunat_base_dir() . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    return $facturacionPath;
}

function sunat_endpoint_for_server(string $serverCode): string
{
    return $serverCode === '1'
        ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'
        : 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
}

function sunat_codigo_documento_por_tipo(string $tipo): string
{
    switch (trim(strtolower($tipo))) {
        case 'factura':      return '01';
        case 'boleta':       return '03';
        case 'nota_credito': return '07';
        case 'nota_debito':  return '08';
        default:             return '03';
    }
}

function sunat_profile_for_current_tenant(PDO $db): array
{
    $tenantId = sesionTenantId();
    if (!$tenantId) {
        throw new RuntimeException('No hay tenant activo en la sesion.');
    }

    $stmt = $db->prepare("
        SELECT
            nombre,
            ruc,
            business_name,
            trade_name,
            direccion,
            ubigeo,
            departamento,
            provincia,
            distrito,
            sunat_username,
            sunat_password,
            certificate_path,
            certificate_password,
            sunat_server
        FROM public.tenants
        WHERE id = :id
    ");
    $stmt->execute([':id' => $tenantId]);
    $tenant = $stmt->fetch() ?: [];

    if (!$tenant) {
        throw new RuntimeException('No se encontro la configuracion del tenant.');
    }

    $ruc = trim((string) ($tenant['ruc'] ?? ''));
    $razonSocial = trim((string) ($tenant['business_name'] ?? $tenant['nombre'] ?? ''));
    $nombreComercial = trim((string) ($tenant['trade_name'] ?? $tenant['business_name'] ?? $tenant['nombre'] ?? ''));
    $certPath = sunat_resolve_certificate_path((string) ($tenant['certificate_path'] ?? ''));
    $serverCode = trim((string) ($tenant['sunat_server'] ?? '3'));

    if ($ruc === '' || $razonSocial === '') {
        throw new RuntimeException('Faltan datos base de empresa para emitir a SUNAT.');
    }
    if (trim((string) ($tenant['sunat_username'] ?? '')) === '' || trim((string) ($tenant['sunat_password'] ?? '')) === '') {
        throw new RuntimeException('Faltan credenciales SOL en la configuracion de empresa.');
    }
    if ($certPath === '' || !file_exists($certPath)) {
        throw new RuntimeException('No se encontro el certificado configurado para la empresa.');
    }
    if (trim((string) ($tenant['certificate_password'] ?? '')) === '') {
        throw new RuntimeException('Falta la clave del certificado en la configuracion de empresa.');
    }

    return [
        'emisor' => [
            'tipo_documento' => '6',
            'ruc' => $ruc,
            'razon_social' => $razonSocial,
            'nombre_comercial' => $nombreComercial,
            'departamento' => trim((string) ($tenant['departamento'] ?? '')),
            'provincia' => trim((string) ($tenant['provincia'] ?? '')),
            'distrito' => trim((string) ($tenant['distrito'] ?? '')),
            'direccion' => trim((string) ($tenant['direccion'] ?? '')),
            'ubigeo' => trim((string) ($tenant['ubigeo'] ?? '')),
            'anexo_sucursal' => '0000',
        ],
        'sol' => [
            'usuario' => trim((string) ($tenant['sunat_username'] ?? '')),
            'clave' => trim((string) ($tenant['sunat_password'] ?? '')),
        ],
        'certificado' => [
            'ruta' => $certPath,
            'clave' => trim((string) ($tenant['certificate_password'] ?? '')),
        ],
        'endpoint' => sunat_endpoint_for_server($serverCode),
    ];
}

function enviar_sunat(PDO $db, array $comp, ?array $cliente, array $items, array $venta): array
{
    try {
        $profile = sunat_profile_for_current_tenant($db);
        $baseDir = sunat_base_dir();
        if (!is_dir($baseDir)) {
            throw new RuntimeException('No se encontro la carpeta interna de facturacion.');
        }

        $tipoDocumento = trim((string) ($comp['tipo'] ?? 'boleta'));
        $customer = sunat_build_customer($cliente, $tipoDocumento);
        $invoiceItems = sunat_build_invoice_items($items);
        $totals = sunat_build_totals($invoiceItems);
        $docType = sunat_codigo_documento_por_tipo($tipoDocumento);
        $correlativo = str_pad((string) intval($comp['numero']), 8, '0', STR_PAD_LEFT);
        $fileBase = $profile['emisor']['ruc'] . '-' . $docType . '-' . $comp['serie'] . '-' . $correlativo;

        $xmlDir = $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'xml';
        $cdrDir = $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cdr';
        if (!is_dir($xmlDir)) {
            mkdir($xmlDir, 0775, true);
        }
        if (!is_dir($cdrDir)) {
            mkdir($cdrDir, 0775, true);
        }

        $xmlPath = $xmlDir . DIRECTORY_SEPARATOR . $fileBase . '.XML';
        $zipPath = $xmlDir . DIRECTORY_SEPARATOR . $fileBase . '.ZIP';
        $cdrZipPath = $cdrDir . DIRECTORY_SEPARATOR . 'R-' . $fileBase . '.ZIP';
        $cdrExtractDir = $cdrDir . DIRECTORY_SEPARATOR . 'R-' . $fileBase;

        $cabecera = [
            'tipo_comprobante' => $docType,
            'serie' => $comp['serie'],
            'correlativo' => $correlativo,
            'fecha_emision' => $venta['fecha_emision'] ?? date('Y-m-d'),
            'hora_emision' => $venta['hora_emision'] ?? date('H:i:s'),
            'fecha_vencimiento' => $venta['fecha_emision'] ?? date('Y-m-d'),
            'moneda' => $venta['moneda'] ?? 'PEN',
            'forma_pago' => $venta['sunat_forma_pago'] ?? 'Contado',
            'monto_credito' => $venta['sunat_forma_pago'] === 'Credito' ? (string) ($venta['totales']['total'] ?? '0.00') : '0.00',
            'anexo_sucursal' => $profile['emisor']['anexo_sucursal'] ?? '0000',
            'documento_modificado_tipo_documento_codigo' => $venta['documento_modificado_tipo_documento_codigo'] ?? null,
            'documento_modificado_numero_completo' => $venta['documento_modificado_numero_completo'] ?? null,
            'motivo_codigo' => $venta['motivo_codigo'] ?? null,
            'motivo_descripcion' => $venta['motivo_descripcion'] ?? null,
        ];

        $xmlContent = $tipoDocumento === 'nota_credito'
            ? sunat_build_credit_note_xml($profile['emisor'], $customer, $cabecera, $invoiceItems, $totals)
            : sunat_build_invoice_xml($profile['emisor'], $customer, $cabecera, $invoiceItems, $totals);

        file_put_contents($xmlPath, $xmlContent);

        require_once $baseDir . DIRECTORY_SEPARATOR . 'signature.php';
        $signer = new Signature();
        $signResult = $signer->signature_xml('0', $xmlPath, $profile['certificado']['ruta'], $profile['certificado']['clave']);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo generar el ZIP del comprobante.');
        }
        $zip->addFile($xmlPath, $fileBase . '.XML');
        $zip->close();

        $soapEnvelope = sunat_build_send_bill_envelope(
            $profile['emisor']['ruc'],
            $profile['sol']['usuario'],
            $profile['sol']['clave'],
            $fileBase . '.ZIP',
            base64_encode((string) file_get_contents($zipPath))
        );

        $soapResult = sunat_send_bill($profile['endpoint'], $soapEnvelope, $baseDir . DIRECTORY_SEPARATOR . 'cacert.pem');
        $xmlUrl = sunat_public_base_url() . '/xml/' . rawurlencode($fileBase . '.XML');

        $responsePayload = [
            'hash_cpe' => $signResult['hash_cpe'] ?? null,
            'firma_cpe' => $signResult['firma_cpe'] ?? null,
            'soap' => $soapResult,
        ];

        if (!($soapResult['ok'] ?? false)) {
            $estado = trim((string) ($soapResult['fault_string'] ?? 'Error al enviar a SUNAT'));
            $estadoDb = sunat_estado_db($estado);
            $db->prepare("
                UPDATE comprobantes_electronicos
                SET estado_sunat = :estado,
                    enlace_del_xml = :xml,
                    nubefact_response = :resp
                WHERE id = :id
            ")->execute([
                ':estado' => $estadoDb,
                ':xml' => $xmlUrl,
                ':resp' => json_encode($responsePayload, JSON_UNESCAPED_UNICODE),
                ':id' => $comp['id'],
            ]);

            return [
                'numero_completo' => $comp['numero_completo'],
                'tipo' => $comp['tipo'],
                'estado_sunat' => $estado,
                'response_code' => null,
                'cdr_description' => null,
                'enlace_del_pdf' => null,
                'enlace_del_xml' => $xmlUrl,
                'enlace_del_cdr' => null,
                'cadena_qr' => $signResult['hash_cpe'] ?? null,
                'error_nubefact' => true,
                'mensaje' => $estado,
            ];
        }

        file_put_contents($cdrZipPath, base64_decode((string) $soapResult['cdr_zip_base64']));
        if (!is_dir($cdrExtractDir)) {
            mkdir($cdrExtractDir, 0775, true);
        }

        $cdrZip = new ZipArchive();
        if ($cdrZip->open($cdrZipPath) === true) {
            $cdrZip->extractTo($cdrExtractDir);
            $cdrZip->close();
        }

        $cdrParse = sunat_parse_cdr($cdrExtractDir);
        $estado = $cdrParse['description'] ?: 'Enviado a SUNAT';
        $estadoDb = sunat_estado_db($estado);
        $cdrUrl = sunat_public_base_url() . '/cdr/' . rawurlencode('R-' . $fileBase . '.ZIP');
        $responsePayload['cdr'] = $cdrParse;

        $db->prepare("
            UPDATE comprobantes_electronicos
            SET estado_sunat = :estado,
                enlace_del_xml = :xml,
                enlace_del_cdr = :cdr,
                cadena_para_codigo_qr = :qr,
                nubefact_response = :resp
            WHERE id = :id
        ")->execute([
            ':estado' => $estadoDb,
            ':xml' => $xmlUrl,
            ':cdr' => $cdrUrl,
            ':qr' => $signResult['hash_cpe'] ?? '',
            ':resp' => json_encode($responsePayload, JSON_UNESCAPED_UNICODE),
            ':id' => $comp['id'],
        ]);

        return [
            'numero_completo' => $comp['numero_completo'],
            'tipo' => $comp['tipo'],
            'estado_sunat' => $estado,
            'response_code' => $cdrParse['response_code'] ?? null,
            'cdr_description' => $cdrParse['description'] ?? null,
            'enlace_del_pdf' => null,
            'enlace_del_xml' => $xmlUrl,
            'enlace_del_cdr' => $cdrUrl,
            'cadena_qr' => $signResult['hash_cpe'] ?? null,
            'error_nubefact' => false,
            'mensaje' => null,
        ];
    } catch (Throwable $e) {
        $estado = 'Error SUNAT: ' . $e->getMessage();
        $estadoDb = sunat_estado_db($estado);
        $db->prepare("UPDATE comprobantes_electronicos SET estado_sunat = :estado WHERE id = :id")
            ->execute([
                ':estado' => $estadoDb,
                ':id' => $comp['id'],
            ]);

        return [
            'numero_completo' => $comp['numero_completo'],
            'tipo' => $comp['tipo'],
            'estado_sunat' => $estado,
            'response_code' => null,
            'cdr_description' => null,
            'enlace_del_pdf' => null,
            'enlace_del_xml' => null,
            'enlace_del_cdr' => null,
            'cadena_qr' => null,
            'error_nubefact' => true,
            'mensaje' => $estado,
        ];
    }
}

function sunat_build_customer(?array $cliente, string $tipoComprobante): array
{
    $nombre = strtoupper(trim((string) ($cliente['razon_social'] ?? $cliente['nombre_completo'] ?? trim(($cliente['nombres'] ?? '') . ' ' . ($cliente['apellidos'] ?? '')))));
    $direccion = trim((string) ($cliente['direccion'] ?? ''));
    $ruc = trim((string) ($cliente['ruc'] ?? ''));
    $dni = trim((string) ($cliente['dni'] ?? ''));
    $numeroDocumento = trim((string) ($cliente['numero_documento'] ?? $ruc ?: $dni));

    if ($tipoComprobante === 'factura') {
        if (!$cliente || $ruc === '') {
            throw new RuntimeException('Para emitir factura electronica el cliente debe tener RUC.');
        }

        return [
            'tipo_documento' => '6',
            'numero_documento' => $ruc,
            'razon_social' => $nombre,
            'direccion' => $direccion,
        ];
    }

    if (!$cliente) {
        throw new RuntimeException('Para emitir boleta electronica debe seleccionar un cliente.');
    }

    if ($ruc !== '') {
        return [
            'tipo_documento' => '6',
            'numero_documento' => $ruc,
            'razon_social' => $nombre,
            'direccion' => $direccion,
        ];
    }

    if ($dni !== '' && preg_match('/^\d{8}$/', $dni)) {
        return [
            'tipo_documento' => '1',
            'numero_documento' => $dni,
            'razon_social' => $nombre,
            'direccion' => $direccion,
        ];
    }

    if ($numeroDocumento === '00000000' || strpos($nombre, 'CLIENTES VARIOS') !== false) {
        return [
            'tipo_documento' => '0',
            'numero_documento' => '00000000',
            'razon_social' => $nombre !== '' ? $nombre : 'CLIENTES VARIOS',
            'direccion' => $direccion !== '' ? $direccion : '-',
        ];
    }

    return [
        'tipo_documento' => trim((string) ($cliente['tipo_documento_codigo'] ?? '0')) ?: '0',
        'numero_documento' => $numeroDocumento,
        'razon_social' => $nombre,
        'direccion' => $direccion,
    ];
}

function sunat_build_invoice_items(array $items): array
{
    $result = [];

    foreach ($items as $index => $item) {
        $cantidad = max(1, (float) ($item['cantidad'] ?? 0));
        $afectacionCodigo = trim((string) ($item['afectacion_codigo'] ?? $item['afectacion_igv_codigo'] ?? '10')) ?: '10';
        $afectacionTipo = strtoupper(trim((string) ($item['afectacion_tipo'] ?? '')));
        if ($afectacionTipo === '') {
            switch ($afectacionCodigo) {
                case '20': case '21': $afectacionTipo = 'EXO'; break;
                case '30': case '31': case '32': case '33': case '34': case '35': case '36': $afectacionTipo = 'INA'; break;
                case '40': $afectacionTipo = 'EXP'; break;
                default:   $afectacionTipo = 'GRAV'; break;
            }
        }

        switch ($afectacionCodigo) {
            case '20': case '21': $categoriaId = 'E'; $taxSchemeId = '9997'; $taxName = 'EXO'; $taxTypeCode = 'VAT'; break;
            case '30': case '31': case '32': case '33': case '34': case '35': case '36': $categoriaId = 'O'; $taxSchemeId = '9998'; $taxName = 'INA'; $taxTypeCode = 'FRE'; break;
            default: $categoriaId = 'S'; $taxSchemeId = '1000'; $taxName = 'IGV'; $taxTypeCode = 'VAT'; break;
        }

        $result[] = [
            'item' => $index + 1,
            'cantidad' => $cantidad,
            'unidad' => trim((string) ($item['unidad_codigo'] ?? $item['unidad'] ?? 'NIU')) ?: 'NIU',
            'nombre' => trim((string) ($item['descripcion'] ?? $item['nombre'] ?? 'Producto')),
            'codigo' => trim((string) ($item['codigo_interno'] ?? $item['codigo'] ?? $item['producto_id'] ?? '')),
            'valor_unitario' => round((float) ($item['valor_unitario'] ?? 0), 6),
            'descuento' => round((float) ($item['descuento'] ?? 0), 2),
            'precio_lista' => round((float) ($item['precio_unitario'] ?? $item['precio'] ?? 0), 2),
            'valor_total' => round((float) ($item['valor_total'] ?? 0), 2),
            'igv' => round((float) ($item['igv'] ?? 0), 2),
            'icbper' => round((float) ($item['icbper'] ?? 0), 2),
            'total_antes_impuestos' => round((float) ($item['valor_total'] ?? 0), 2),
            'total_impuestos' => round((float) ($item['igv'] ?? 0), 2),
            'porcentaje_igv' => 18.0,
            'codigos' => [$categoriaId, $afectacionCodigo, $taxSchemeId, $taxName, $taxTypeCode],
        ];
    }

    return $result;
}

function sunat_build_totals(array $items): array
{
    $gravadas = 0.0;
    $exoneradas = 0.0;
    $inafectas = 0.0;
    $igv = 0.0;

    foreach ($items as $item) {
        $codigo = trim((string) ($item['codigos'][1] ?? '10'));
        if (in_array($codigo, ['20', '21'], true)) {
            $exoneradas += (float) $item['valor_total'];
        } elseif (in_array($codigo, ['30', '31', '32', '33', '34', '35', '36'], true)) {
            $inafectas += (float) $item['valor_total'];
        } else {
            $gravadas += (float) $item['valor_total'];
        }
        $igv += (float) $item['igv'];
    }

    $gravadas = round($gravadas, 2);
    $exoneradas = round($exoneradas, 2);
    $inafectas = round($inafectas, 2);
    $igv = round($igv, 2);
    $base = round($gravadas + $exoneradas + $inafectas, 2);
    $total = round($base + $igv, 2);

    return [
        'total_op_gravadas' => $gravadas,
        'total_op_exoneradas' => $exoneradas,
        'total_op_inafectas' => $inafectas,
        'total_antes_impuestos' => $base,
        'igv' => $igv,
        'total_impuestos' => $igv,
        'total_despues_impuestos' => $total,
        'total_a_pagar' => $total,
    ];
}

function sunat_xml_escape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function sunat_cdata(string $value): string
{
    $safe = str_replace(']]>', ']]]]><![CDATA[>', $value);
    return '<![CDATA[' . $safe . ']]>';
}

function sunat_build_invoice_xml(array $emisor, array $cliente, array $cabecera, array $items, array $totals): string
{
    $xml = '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= '<Invoice xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">';
    $xml .= '<ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>';
    $xml .= '<cbc:UBLVersionID>2.1</cbc:UBLVersionID>';
    $xml .= '<cbc:CustomizationID schemeAgencyName="PE:SUNAT">2.0</cbc:CustomizationID>';
    $xml .= '<cbc:ProfileID schemeName="Tipo de Operacion" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo17">0101</cbc:ProfileID>';
    $xml .= '<cbc:ID>' . sunat_xml_escape($cabecera['serie'] . '-' . $cabecera['correlativo']) . '</cbc:ID>';
    $xml .= '<cbc:IssueDate>' . sunat_xml_escape($cabecera['fecha_emision']) . '</cbc:IssueDate>';
    $xml .= '<cbc:IssueTime>' . sunat_xml_escape($cabecera['hora_emision']) . '</cbc:IssueTime>';
    $xml .= '<cbc:DueDate>' . sunat_xml_escape($cabecera['fecha_vencimiento']) . '</cbc:DueDate>';
    $xml .= '<cbc:InvoiceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" listID="0101" name="Tipo de Operacion">' . sunat_xml_escape($cabecera['tipo_comprobante']) . '</cbc:InvoiceTypeCode>';
    $xml .= '<cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . sunat_xml_escape($cabecera['moneda']) . '</cbc:DocumentCurrencyCode>';
    $xml .= '<cbc:LineCountNumeric>' . count($items) . '</cbc:LineCountNumeric>';
    $xml .= '<cac:Signature><cbc:ID>' . sunat_xml_escape($cabecera['serie'] . '-' . $cabecera['correlativo']) . '</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>' . sunat_xml_escape($emisor['ruc']) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name>' . sunat_cdata($emisor['razon_social']) . '</cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#SignatureSP</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature>';
    $xml .= '<cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="' . sunat_xml_escape((string) $emisor['tipo_documento']) . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($emisor['ruc']) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name>' . sunat_cdata($emisor['razon_social']) . '</cbc:Name></cac:PartyName><cac:PartyTaxScheme><cbc:RegistrationName>' . sunat_cdata($emisor['razon_social']) . '</cbc:RegistrationName><cbc:CompanyID schemeID="' . sunat_xml_escape((string) $emisor['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($emisor['ruc']) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID schemeID="' . sunat_xml_escape((string) $emisor['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($emisor['ruc']) . '</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme><cac:PartyLegalEntity><cbc:RegistrationName>' . sunat_cdata($emisor['razon_social']) . '</cbc:RegistrationName><cac:RegistrationAddress><cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">' . sunat_xml_escape((string) ($emisor['ubigeo'] ?? '')) . '</cbc:ID><cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">' . sunat_xml_escape((string) ($cabecera['anexo_sucursal'] ?? '0000')) . '</cbc:AddressTypeCode><cbc:CityName>' . sunat_cdata((string) ($emisor['provincia'] ?? '')) . '</cbc:CityName><cbc:CountrySubentity>' . sunat_cdata((string) ($emisor['departamento'] ?? '')) . '</cbc:CountrySubentity><cbc:District>' . sunat_cdata((string) ($emisor['distrito'] ?? '')) . '</cbc:District><cac:AddressLine><cbc:Line>' . sunat_cdata((string) ($emisor['direccion'] ?? '')) . '</cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty>';
    $xml .= '<cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="' . sunat_xml_escape($cliente['tipo_documento']) . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($cliente['numero_documento']) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name>' . sunat_cdata($cliente['razon_social']) . '</cbc:Name></cac:PartyName><cac:PartyTaxScheme><cbc:RegistrationName>' . sunat_cdata($cliente['razon_social']) . '</cbc:RegistrationName><cbc:CompanyID schemeID="' . sunat_xml_escape($cliente['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($cliente['numero_documento']) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID schemeID="' . sunat_xml_escape($cliente['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($cliente['numero_documento']) . '</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme><cac:PartyLegalEntity><cbc:RegistrationName>' . sunat_cdata($cliente['razon_social']) . '</cbc:RegistrationName><cac:RegistrationAddress><cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI"/><cbc:CityName><![CDATA[]]></cbc:CityName><cbc:CountrySubentity><![CDATA[]]></cbc:CountrySubentity><cbc:District><![CDATA[]]></cbc:District><cac:AddressLine><cbc:Line>' . sunat_cdata((string) $cliente['direccion']) . '</cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country"/></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty>';
    $xml .= '<cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID><cbc:PaymentMeansID>' . sunat_xml_escape($cabecera['forma_pago']) . '</cbc:PaymentMeansID><cbc:Amount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $cabecera['monto_credito'], 2, '.', '') . '</cbc:Amount></cac:PaymentTerms>';
    $xml .= '<cac:TaxTotal><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['igv'], 2, '.', '') . '</cbc:TaxAmount>';
    if ((float) ($totals['total_op_gravadas'] ?? 0) > 0) {
        $xml .= '<cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_op_gravadas'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['igv'], 2, '.', '') . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">S</cbc:ID><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
    }
    if ((float) ($totals['total_op_exoneradas'] ?? 0) > 0) {
        $xml .= '<cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_op_exoneradas'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">0.00</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9997</cbc:ID><cbc:Name>EXO</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
    }
    if ((float) ($totals['total_op_inafectas'] ?? 0) > 0) {
        $xml .= '<cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_op_inafectas'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">0.00</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">O</cbc:ID><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9998</cbc:ID><cbc:Name>INA</cbc:Name><cbc:TaxTypeCode>FRE</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
    }
    $xml .= '</cac:TaxTotal>';
    $xml .= '<cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_antes_impuestos'], 2, '.', '') . '</cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_despues_impuestos'], 2, '.', '') . '</cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_a_pagar'], 2, '.', '') . '</cbc:PayableAmount></cac:LegalMonetaryTotal>';

    foreach ($items as $item) {
        $xml .= '<cac:InvoiceLine>';
        $xml .= '<cbc:ID>' . intval($item['item']) . '</cbc:ID>';
        $xml .= '<cbc:InvoicedQuantity unitCode="' . sunat_xml_escape($item['unidad']) . '" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">' . number_format((float) $item['cantidad'], 2, '.', '') . '</cbc:InvoicedQuantity>';
        $xml .= '<cbc:LineExtensionAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['valor_total'], 2, '.', '') . '</cbc:LineExtensionAmount>';
        $xml .= '<cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['precio_lista'], 2, '.', '') . '</cbc:PriceAmount><cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference>';
        $xml .= '<cac:TaxTotal><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['igv'], 2, '.', '') . '</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['valor_total'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['igv'], 2, '.', '') . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . sunat_xml_escape($item['codigos'][0]) . '</cbc:ID><cbc:Percent>18.00</cbc:Percent><cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . sunat_xml_escape($item['codigos'][1]) . '</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT">' . sunat_xml_escape($item['codigos'][2]) . '</cbc:ID><cbc:Name>' . sunat_xml_escape($item['codigos'][3]) . '</cbc:Name><cbc:TaxTypeCode>' . sunat_xml_escape($item['codigos'][4]) . '</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal>';
        $xml .= '<cac:Item><cbc:Description>' . sunat_cdata($item['nombre']) . '</cbc:Description><cac:SellersItemIdentification><cbc:ID>' . sunat_cdata($item['codigo']) . '</cbc:ID></cac:SellersItemIdentification></cac:Item>';
        $xml .= '<cac:Price><cbc:PriceAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['valor_unitario'], 6, '.', '') . '</cbc:PriceAmount></cac:Price>';
        $xml .= '</cac:InvoiceLine>';
    }

    $xml .= '</Invoice>';
    return $xml;
}

function sunat_build_credit_note_xml(array $emisor, array $cliente, array $cabecera, array $items, array $totals): string
{
    $tipoDocModificado = trim((string) ($cabecera['documento_modificado_tipo_documento_codigo'] ?? '03')) ?: '03';
    $numeroDocModificado = trim((string) ($cabecera['documento_modificado_numero_completo'] ?? ''));
    $motivoCodigo = trim((string) ($cabecera['motivo_codigo'] ?? '01')) ?: '01';
    $motivoDescripcion = trim((string) ($cabecera['motivo_descripcion'] ?? 'ANULACION DE LA OPERACION')) ?: 'ANULACION DE LA OPERACION';

    $xml = '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= '<CreditNote xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2">';
    $xml .= '<ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>';
    $xml .= '<cbc:UBLVersionID>2.1</cbc:UBLVersionID>';
    $xml .= '<cbc:CustomizationID schemeAgencyName="PE:SUNAT">2.0</cbc:CustomizationID>';
    $xml .= '<cbc:ID>' . sunat_xml_escape($cabecera['serie'] . '-' . $cabecera['correlativo']) . '</cbc:ID>';
    $xml .= '<cbc:IssueDate>' . sunat_xml_escape($cabecera['fecha_emision']) . '</cbc:IssueDate>';
    $xml .= '<cbc:IssueTime>' . sunat_xml_escape($cabecera['hora_emision']) . '</cbc:IssueTime>';
    $xml .= '<cbc:Note languageLocaleID="1000">' . sunat_cdata($motivoDescripcion) . '</cbc:Note>';
    $xml .= '<cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . sunat_xml_escape($cabecera['moneda']) . '</cbc:DocumentCurrencyCode>';
    $xml .= '<cac:DiscrepancyResponse><cbc:ReferenceID>' . sunat_xml_escape($numeroDocModificado) . '</cbc:ReferenceID><cbc:ResponseCode>' . sunat_xml_escape($motivoCodigo) . '</cbc:ResponseCode><cbc:Description>' . sunat_cdata($motivoDescripcion) . '</cbc:Description></cac:DiscrepancyResponse>';
    $xml .= '<cac:BillingReference><cac:InvoiceDocumentReference><cbc:ID>' . sunat_xml_escape($numeroDocModificado) . '</cbc:ID><cbc:DocumentTypeCode>' . sunat_xml_escape($tipoDocModificado) . '</cbc:DocumentTypeCode></cac:InvoiceDocumentReference></cac:BillingReference>';
    $xml .= '<cac:Signature><cbc:ID>' . sunat_xml_escape($cabecera['serie'] . '-' . $cabecera['correlativo']) . '</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>' . sunat_xml_escape($emisor['ruc']) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name>' . sunat_cdata($emisor['razon_social']) . '</cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#SignatureSP</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature>';
    $xml .= '<cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="' . sunat_xml_escape((string) $emisor['tipo_documento']) . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($emisor['ruc']) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name>' . sunat_cdata($emisor['razon_social']) . '</cbc:Name></cac:PartyName><cac:PartyTaxScheme><cbc:RegistrationName>' . sunat_cdata($emisor['razon_social']) . '</cbc:RegistrationName><cbc:CompanyID schemeID="' . sunat_xml_escape((string) $emisor['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($emisor['ruc']) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID schemeID="' . sunat_xml_escape((string) $emisor['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($emisor['ruc']) . '</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme><cac:PartyLegalEntity><cbc:RegistrationName>' . sunat_cdata($emisor['razon_social']) . '</cbc:RegistrationName><cac:RegistrationAddress><cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">' . sunat_xml_escape((string) ($emisor['ubigeo'] ?? '')) . '</cbc:ID><cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">' . sunat_xml_escape((string) ($cabecera['anexo_sucursal'] ?? '0000')) . '</cbc:AddressTypeCode><cbc:CityName>' . sunat_cdata((string) ($emisor['provincia'] ?? '')) . '</cbc:CityName><cbc:CountrySubentity>' . sunat_cdata((string) ($emisor['departamento'] ?? '')) . '</cbc:CountrySubentity><cbc:District>' . sunat_cdata((string) ($emisor['distrito'] ?? '')) . '</cbc:District><cac:AddressLine><cbc:Line>' . sunat_cdata((string) ($emisor['direccion'] ?? '')) . '</cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty>';
    $xml .= '<cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="' . sunat_xml_escape($cliente['tipo_documento']) . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($cliente['numero_documento']) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name>' . sunat_cdata($cliente['razon_social']) . '</cbc:Name></cac:PartyName><cac:PartyTaxScheme><cbc:RegistrationName>' . sunat_cdata($cliente['razon_social']) . '</cbc:RegistrationName><cbc:CompanyID schemeID="' . sunat_xml_escape($cliente['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($cliente['numero_documento']) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID schemeID="' . sunat_xml_escape($cliente['tipo_documento']) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . sunat_xml_escape($cliente['numero_documento']) . '</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme><cac:PartyLegalEntity><cbc:RegistrationName>' . sunat_cdata($cliente['razon_social']) . '</cbc:RegistrationName><cac:RegistrationAddress><cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI"/><cbc:CityName><![CDATA[]]></cbc:CityName><cbc:CountrySubentity><![CDATA[]]></cbc:CountrySubentity><cbc:District><![CDATA[]]></cbc:District><cac:AddressLine><cbc:Line>' . sunat_cdata((string) $cliente['direccion']) . '</cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country"/></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty>';
    $xml .= '<cac:TaxTotal><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['igv'], 2, '.', '') . '</cbc:TaxAmount>';
    if ((float) ($totals['total_op_gravadas'] ?? 0) > 0) {
        $xml .= '<cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_op_gravadas'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['igv'], 2, '.', '') . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">S</cbc:ID><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyName="PE:SUNAT">1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
    }
    if ((float) ($totals['total_op_exoneradas'] ?? 0) > 0) {
        $xml .= '<cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_op_exoneradas'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">0.00</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyName="PE:SUNAT">9997</cbc:ID><cbc:Name>EXO</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
    }
    if ((float) ($totals['total_op_inafectas'] ?? 0) > 0) {
        $xml .= '<cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_op_inafectas'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">0.00</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">O</cbc:ID><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyName="PE:SUNAT">9998</cbc:ID><cbc:Name>INA</cbc:Name><cbc:TaxTypeCode>FRE</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
    }
    $xml .= '</cac:TaxTotal>';
    $xml .= '<cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_antes_impuestos'], 2, '.', '') . '</cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_despues_impuestos'], 2, '.', '') . '</cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $totals['total_a_pagar'], 2, '.', '') . '</cbc:PayableAmount></cac:LegalMonetaryTotal>';

    foreach ($items as $item) {
        $xml .= '<cac:CreditNoteLine>';
        $xml .= '<cbc:ID>' . intval($item['item']) . '</cbc:ID>';
        $xml .= '<cbc:CreditedQuantity unitCode="' . sunat_xml_escape($item['unidad']) . '" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">' . number_format((float) $item['cantidad'], 2, '.', '') . '</cbc:CreditedQuantity>';
        $xml .= '<cbc:LineExtensionAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['valor_total'], 2, '.', '') . '</cbc:LineExtensionAmount>';
        $xml .= '<cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['precio_lista'], 2, '.', '') . '</cbc:PriceAmount><cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference>';
        $xml .= '<cac:TaxTotal><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['igv'], 2, '.', '') . '</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['valor_total'], 2, '.', '') . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['igv'], 2, '.', '') . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . sunat_xml_escape($item['codigos'][0]) . '</cbc:ID><cbc:Percent>18.00</cbc:Percent><cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . sunat_xml_escape($item['codigos'][1]) . '</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT">' . sunat_xml_escape($item['codigos'][2]) . '</cbc:ID><cbc:Name>' . sunat_xml_escape($item['codigos'][3]) . '</cbc:Name><cbc:TaxTypeCode>' . sunat_xml_escape($item['codigos'][4]) . '</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal>';
        $xml .= '<cac:Item><cbc:Description>' . sunat_cdata($item['nombre']) . '</cbc:Description><cac:SellersItemIdentification><cbc:ID>' . sunat_cdata($item['codigo']) . '</cbc:ID></cac:SellersItemIdentification></cac:Item>';
        $xml .= '<cac:Price><cbc:PriceAmount currencyID="' . sunat_xml_escape($cabecera['moneda']) . '">' . number_format((float) $item['valor_unitario'], 6, '.', '') . '</cbc:PriceAmount></cac:Price>';
        $xml .= '</cac:CreditNoteLine>';
    }

    $xml .= '</CreditNote>';
    return $xml;
}

function sunat_build_send_bill_envelope(string $ruc, string $solUser, string $solPassword, string $fileName, string $zipContentBase64): string
{
    return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">'
        . '<soapenv:Header><wsse:Security><wsse:UsernameToken><wsse:Username>' . sunat_xml_escape($ruc . $solUser) . '</wsse:Username><wsse:Password>' . sunat_xml_escape($solPassword) . '</wsse:Password></wsse:UsernameToken></wsse:Security></soapenv:Header>'
        . '<soapenv:Body><ser:sendBill><fileName>' . sunat_xml_escape($fileName) . '</fileName><contentFile>' . $zipContentBase64 . '</contentFile></ser:sendBill></soapenv:Body>'
        . '</soapenv:Envelope>';
}

function sunat_send_bill(string $endpoint, string $soapEnvelope, string $caInfoPath): array
{
    $headers = [
        'Content-type: text/xml; charset="utf-8"',
        'Accept: text/xml',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'SOAPAction: ',
        'Content-length: ' . strlen($soapEnvelope),
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl, CURLOPT_URL, $endpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $soapEnvelope);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    if (is_file($caInfoPath)) {
        curl_setopt($curl, CURLOPT_CAINFO, $caInfoPath);
    }

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response === false || $curlError !== '') {
        return [
            'ok' => false,
            'http_code' => $httpCode,
            'fault_string' => 'Error CURL: ' . $curlError,
            'response_xml' => '',
        ];
    }

    $document = new DOMDocument();
    if (!@$document->loadXML($response)) {
        return [
            'ok' => false,
            'http_code' => $httpCode,
            'fault_string' => 'SUNAT devolvio una respuesta XML invalida.',
            'response_xml' => $response,
        ];
    }

    $applicationResponse = sunat_dom_value($document, 'applicationResponse');
    if ($httpCode === 200 && $applicationResponse !== null) {
        return [
            'ok' => true,
            'http_code' => $httpCode,
            'cdr_zip_base64' => $applicationResponse,
            'response_xml' => $response,
        ];
    }

    return [
        'ok' => false,
        'http_code' => $httpCode,
        'fault_code' => sunat_dom_value($document, 'faultcode'),
        'fault_string' => sunat_dom_value($document, 'faultstring') ?: 'SUNAT no devolvio applicationResponse.',
        'response_xml' => $response,
    ];
}

function sunat_dom_value(DOMDocument $document, string $tagName): ?string
{
    $node = $document->getElementsByTagName($tagName)->item(0);
    return $node ? $node->nodeValue : null;
}

function sunat_parse_cdr(string $extractDir): array
{
    $files = glob($extractDir . DIRECTORY_SEPARATOR . '*.XML') ?: [];
    if (!$files) {
        return ['response_code' => null, 'description' => 'CDR recibido sin XML legible.', 'xml_path' => null];
    }

    $document = new DOMDocument();
    if (!@$document->load($files[0])) {
        return ['response_code' => null, 'description' => 'No se pudo leer el XML del CDR.', 'xml_path' => $files[0]];
    }

    return [
        'response_code' => sunat_dom_value($document, 'ResponseCode'),
        'description' => trim((string) sunat_dom_value($document, 'Description')),
        'xml_path' => $files[0],
    ];
}

function sunat_normalize_cdr_status(?string $responseCode, ?string $description = null): array
{
    $code = trim((string) $responseCode);
    $desc = trim((string) $description);

    if ($code === '0') {
        return [
            'label' => 'Aceptado',
            'raw' => $desc !== '' ? $desc : 'CDR con respuesta 0',
            'code' => $code,
        ];
    }

    if ($code === '1') {
        return [
            'label' => 'Observado',
            'raw' => $desc !== '' ? $desc : 'CDR con observaciones',
            'code' => $code,
        ];
    }

    if ($desc !== '') {
        $lower = strtolower($desc);
        if (strpos($lower, 'acept') !== false) {
            return ['label' => 'Aceptado', 'raw' => $desc, 'code' => $code];
        }
        if (strpos($lower, 'observ') !== false) {
            return ['label' => 'Observado', 'raw' => $desc, 'code' => $code];
        }
        if (strpos($lower, 'pend') !== false) {
            return ['label' => 'Pendiente', 'raw' => $desc, 'code' => $code];
        }
    }

    return [
        'label' => $desc !== '' ? $desc : 'Sin estado',
        'raw' => $desc,
        'code' => $code,
    ];
}
