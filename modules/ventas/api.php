<?php
// ============================================================
// ARCHIVO: farmacia/modules/ventas/api.php
// DESCRIPCION: API REST para el modulo de Ventas
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth();

$action = $_GET['action'] ?? '';
$db     = getDB();

function ventasMetaComprobante(string $tipo): array
{
    return match ($tipo) {
        'factura' => ['codigo' => '01', 'descripcion' => 'Factura Electronica'],
        'boleta' => ['codigo' => '03', 'descripcion' => 'Boleta Electronica'],
        default => ['codigo' => '02', 'descripcion' => 'Nota de venta'],
    };
}

function ventasNormalizarAfectacionTipo(?string $tipo, ?string $codigo): string
{
    $tipo = strtoupper(trim((string) $tipo));
    if ($tipo !== '') {
        return $tipo;
    }

    return match (trim((string) $codigo)) {
        '20', '21' => 'EXO',
        '30', '31', '32', '33', '34', '35', '36' => 'INA',
        '40' => 'EXP',
        default => 'GRAV',
    };
}

function ventasEsClienteVarios(?array $cliente): bool
{
    if (!$cliente) {
        return false;
    }

    $documento = trim((string) ($cliente['numero_documento'] ?? $cliente['dni'] ?? $cliente['ruc'] ?? ''));
    $nombre = strtoupper(trim((string) ($cliente['razon_social'] ?? $cliente['nombre_completo'] ?? trim(($cliente['nombres'] ?? '') . ' ' . ($cliente['apellidos'] ?? '')))));

    return $documento === '00000000' || str_contains($nombre, 'CLIENTES VARIOS');
}

function ventasPrecioUnitarioFinal(array $producto): float
{
    $precio = round((float) ($producto['precio_venta'] ?? 0), 2);
    $tipoAfectacion = ventasNormalizarAfectacionTipo($producto['afectacion_tipo'] ?? null, $producto['afectacion_igv_codigo'] ?? null);
    $porcentajeIgv = max(0, (float) ($producto['porcentaje_igv'] ?? 0));
    $incluyeIgv = filter_var($producto['incluye_igv'] ?? false, FILTER_VALIDATE_BOOL);

    if ($tipoAfectacion === 'GRAV' && !$incluyeIgv) {
        return round($precio * (1 + ($porcentajeIgv / 100)), 2);
    }

    return $precio;
}

function ventasCalcularDetalleTributario(array $producto, float $cantidad): array
{
    $cantidad = round(max(0.01, $cantidad), 2);
    $precioBase = round((float) ($producto['precio_venta'] ?? 0), 2);
    $tipoAfectacion = ventasNormalizarAfectacionTipo($producto['afectacion_tipo'] ?? null, $producto['afectacion_igv_codigo'] ?? null);
    $afectacionCodigo = trim((string) ($producto['afectacion_igv_codigo'] ?? '10')) ?: '10';
    $porcentajeIgv = $tipoAfectacion === 'GRAV'
        ? round(max(0, (float) ($producto['porcentaje_igv'] ?? 18)), 2)
        : 0.0;
    $incluyeIgv = $tipoAfectacion === 'GRAV'
        ? filter_var($producto['incluye_igv'] ?? false, FILTER_VALIDATE_BOOL)
        : false;
    $factorIcbper = filter_var($producto['icbper_activo'] ?? false, FILTER_VALIDATE_BOOL)
        ? round(max(0, (float) ($producto['factor_icbper'] ?? 0)), 4)
        : 0.0;

    if ($tipoAfectacion === 'GRAV') {
        if ($incluyeIgv) {
            $valorUnitario = round($precioBase / (1 + ($porcentajeIgv / 100)), 10);
            $precioUnitario = $precioBase;
            $igvUnitario = round($precioUnitario - $valorUnitario, 10);
        } else {
            $valorUnitario = round($precioBase, 10);
            $igvUnitario = round($valorUnitario * ($porcentajeIgv / 100), 10);
            $precioUnitario = round($valorUnitario + $igvUnitario, 10);
        }
    } else {
        $valorUnitario = round($precioBase, 10);
        $precioUnitario = round($precioBase, 10);
        $igvUnitario = 0.0;
    }

    $valorTotal = round($valorUnitario * $cantidad, 2);
    $igv = round($igvUnitario * $cantidad, 2);
    $icbper = round($factorIcbper * $cantidad, 2);
    $precioTotal = round(($precioUnitario * $cantidad) + $icbper, 2);

    return [
        'cantidad' => $cantidad,
        'precio_unitario' => round($precioUnitario, 2),
        'valor_unitario' => $valorUnitario,
        'valor_total' => $valorTotal,
        'igv' => $igv,
        'icbper' => $icbper,
        'precio_total' => $precioTotal,
        'afectacion_codigo' => $afectacionCodigo,
        'afectacion_tipo' => $tipoAfectacion,
        'porcentaje_igv' => $porcentajeIgv,
        'incluye_igv' => $incluyeIgv,
        'unidad_codigo' => trim((string) ($producto['unidad_codigo'] ?? 'NIU')) ?: 'NIU',
        'unidad_id' => !empty($producto['unidad_id']) ? (int) $producto['unidad_id'] : null,
        'afectacion_id' => !empty($producto['afectacion_igv_id']) ? (int) $producto['afectacion_igv_id'] : null,
        'codigo_sunat' => trim((string) ($producto['codigo_sunat'] ?? '00000000')) ?: '00000000',
        'codigo_interno' => trim((string) ($producto['codigo_interno'] ?? $producto['codigo'] ?? '')),
        'descripcion' => trim((string) ($producto['nombre'] ?? 'Producto')),
        'factor_icbper' => $factorIcbper,
    ];
}

function ventasResumenDesdeDetalles(array $detalles): array
{
    $gravada = 0.0;
    $exonerada = 0.0;
    $inafecta = 0.0;
    $igv = 0.0;
    $icbper = 0.0;
    $total = 0.0;

    foreach ($detalles as $detalle) {
        $valorTotal = (float) ($detalle['valor_total'] ?? 0);
        $lineIgv = (float) ($detalle['igv'] ?? 0);
        $lineIcbper = (float) ($detalle['icbper'] ?? 0);
        $tipoAfectacion = ventasNormalizarAfectacionTipo($detalle['afectacion_tipo'] ?? null, $detalle['afectacion_codigo'] ?? null);

        if ($tipoAfectacion === 'GRAV') {
            $gravada += $valorTotal;
        } elseif ($tipoAfectacion === 'EXO') {
            $exonerada += $valorTotal;
        } else {
            $inafecta += $valorTotal;
        }

        $igv += $lineIgv;
        $icbper += $lineIcbper;
        $total += (float) ($detalle['precio_total'] ?? 0);
    }

    $subtotal = round($gravada + $exonerada + $inafecta, 2);

    return [
        'subtotal' => $subtotal,
        'gravada' => round($gravada, 2),
        'exonerada' => round($exonerada, 2),
        'inafecta' => round($inafecta, 2),
        'igv' => round($igv, 2),
        'icbper' => round($icbper, 2),
        'total' => round($total, 2),
    ];
}

function ventasAplicarDescuentoGlobal(array $detalles, float $descuento): array
{
    $descuento = round(max(0, $descuento), 2);
    if ($descuento <= 0 || empty($detalles)) {
        return [$detalles, 0.0];
    }

    $grossTotal = 0.0;
    foreach ($detalles as $detalle) {
        $grossTotal += (float) ($detalle['precio_total'] ?? 0);
    }
    $grossTotal = round($grossTotal, 2);

    if ($grossTotal <= 0) {
        return [$detalles, 0.0];
    }

    if ($descuento > $grossTotal + 0.01) {
        throw new Exception('El descuento no puede ser mayor al importe de la venta.');
    }

    $remaining = $descuento;
    $adjusted = [];
    $lastIndex = count($detalles) - 1;

    foreach ($detalles as $index => $detalle) {
        $lineGross = round((float) ($detalle['precio_total'] ?? 0), 2);
        $lineDiscount = $index === $lastIndex
            ? round(max(0, $remaining), 2)
            : round($descuento > 0 ? ($lineGross / $grossTotal) * $descuento : 0, 2);

        $lineDiscount = min($lineDiscount, $lineGross);
        $remaining = round($remaining - $lineDiscount, 2);

        $lineBase = round((float) ($detalle['valor_total'] ?? 0), 2);
        $lineIgv = round((float) ($detalle['igv'] ?? 0), 2);
        $lineRatio = $lineGross > 0 ? ($lineBase / $lineGross) : 1.0;
        $baseDiscount = round($lineDiscount * $lineRatio, 2);
        $igvDiscount = round($lineDiscount - $baseDiscount, 2);

        $detalle['descuento'] = $lineDiscount;
        $detalle['valor_total'] = max(0, round($lineBase - $baseDiscount, 2));
        $detalle['igv'] = max(0, round($lineIgv - $igvDiscount, 2));
        $detalle['precio_total'] = max(0, round($lineGross - $lineDiscount, 2));
        $detalle['subtotal'] = $detalle['precio_total'];

        $adjusted[] = $detalle;
    }

    return [$adjusted, $descuento];
}

function ventasResolverFormaPagoId(PDO $db, string $tipoPago): ?int
{
    static $cache = [];

    $tipoPago = strtolower(trim($tipoPago));
    if (isset($cache[$tipoPago])) {
        return $cache[$tipoPago];
    }

    $descripcion = match ($tipoPago) {
        'tarjeta' => 'Tarjeta de credito',
        'transferencia' => 'Transferencia',
        'yape' => 'Yape',
        'plin' => 'Plin',
        default => 'Efectivo',
    };

    $stmt = $db->prepare("
        SELECT id
        FROM public.fe_formas_pago
        WHERE LOWER(descripcion) = LOWER(:descripcion)
        LIMIT 1
    ");
    $stmt->execute([':descripcion' => $descripcion]);

    return $cache[$tipoPago] = (($row = $stmt->fetch()) ? (int) $row['id'] : null);
}

switch ($action) {

    case 'productos':
        $stmt = $db->query("
            SELECT
                p.id, p.codigo, p.codigo_interno, p.codigo_barras, p.codigo_sunat,
                p.nombre, p.precio_venta, p.stock, p.stock_minimo,
                p.laboratorio, p.presentacion, p.categoria_id, p.favorito,
                p.unidad_id, p.unidad_codigo, p.afectacion_igv_id, p.afectacion_igv_codigo,
                p.porcentaje_igv, p.incluye_igv, p.icbper_activo, p.factor_icbper,
                a.tipo AS afectacion_tipo, a.descripcion AS afectacion_descripcion,
                c.nombre AS categoria
            FROM productos p
            LEFT JOIN public.categorias c ON c.id = p.categoria_id
            LEFT JOIN public.fe_tipos_afectacion_igv a ON a.id = p.afectacion_igv_id
            WHERE p.activo = TRUE
            ORDER BY p.favorito DESC, p.nombre ASC
        ");
        echo json_encode($stmt->fetchAll());
        break;

    case 'series_disponibles':
        $stmt = $db->query("
            SELECT
                tipo,
                serie,
                COALESCE(codigo_tipo_documento, CASE tipo
                    WHEN 'factura' THEN '01'
                    WHEN 'boleta' THEN '03'
                    ELSE '02'
                END) AS codigo_tipo_documento,
                COALESCE(descripcion, CASE tipo
                    WHEN 'factura' THEN 'Factura'
                    WHEN 'boleta' THEN 'Boleta'
                    ELSE 'Nota de venta'
                END) AS descripcion
            FROM series_comprobantes
            WHERE COALESCE(activo, TRUE) = TRUE
            ORDER BY tipo, serie
        ");

        $series = [
            'boleta' => [],
            'factura' => [],
            'ticket' => [],
        ];

        foreach ($stmt->fetchAll() as $row) {
            $tipo = trim((string) ($row['tipo'] ?? ''));
            if (!isset($series[$tipo])) {
                $series[$tipo] = [];
            }
            $series[$tipo][] = $row;
        }

        echo json_encode($series);
        break;

    case 'crear_cliente':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $nombres = trim($data['nombres'] ?? '');
        if (!$nombres) {
            jsonResponse(['error' => true, 'message' => 'El nombre es obligatorio'], 400);
        }

        $stmt = $db->prepare("
            INSERT INTO clientes (nombres, apellidos, dni, ruc, telefono, email, direccion, activo)
            VALUES (:nombres, :apellidos, NULLIF(:dni,''), NULLIF(:ruc,''), NULLIF(:telefono,''), NULLIF(:email,''), NULLIF(:direccion,''), TRUE)
            RETURNING id, nombres, apellidos, dni, ruc, telefono, email, direccion
        ");
        $stmt->execute([
            ':nombres'   => $nombres,
            ':apellidos' => trim($data['apellidos'] ?? ''),
            ':dni'       => trim($data['dni']       ?? ''),
            ':ruc'       => trim($data['ruc']       ?? ''),
            ':telefono'  => trim($data['telefono']  ?? ''),
            ':email'     => trim($data['email']     ?? ''),
            ':direccion' => trim($data['direccion'] ?? ''),
        ]);
        $cliente = $stmt->fetch();
        echo json_encode(['error' => false, 'cliente' => $cliente]);
        break;

    case 'default_cliente':
        $stmt = $db->query("
            SELECT
                id, nombres, apellidos, dni, ruc, telefono, email, direccion,
                tipo_documento_id, tipo_documento_codigo, numero_documento,
                nombre_completo, razon_social
            FROM clientes
            WHERE activo = TRUE
              AND (
                    id = 1
                    OR COALESCE(numero_documento, '') = '00000000'
                    OR UPPER(COALESCE(razon_social, '')) = 'CLIENTES VARIOS'
                  )
            ORDER BY CASE
                WHEN id = 1 THEN 0
                WHEN COALESCE(numero_documento, '') = '00000000' THEN 1
                ELSE 2
            END
            LIMIT 1
        ");
        $row = $stmt->fetch();
        echo json_encode($row ?: null);
        break;

    case 'buscar_cliente':
        $q = '%' . trim($_GET['q'] ?? '') . '%';
        $stmt = $db->prepare("
            SELECT
                id, nombres, apellidos, dni, ruc, telefono, email, direccion,
                tipo_documento_id, tipo_documento_codigo, numero_documento,
                nombre_completo, razon_social
            FROM clientes
            WHERE activo = TRUE
              AND (
                    nombres ILIKE :q
                    OR apellidos ILIKE :q
                    OR COALESCE(nombre_completo,'') ILIKE :q
                    OR COALESCE(razon_social,'') ILIKE :q
                    OR dni ILIKE :q
                    OR COALESCE(ruc,'') ILIKE :q
                    OR COALESCE(numero_documento,'') ILIKE :q
                  )
              AND COALESCE(numero_documento, '') <> '00000000'
            LIMIT 1
        ");
        $stmt->execute([':q' => $q]);
        $row = $stmt->fetch();
        echo json_encode($row ?: null);
        break;

    case 'registrar_venta':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || empty($data['items'])) {
            jsonResponse(['error' => true, 'message' => 'Datos invalidos'], 400);
        }

        $cajaStmt = $db->query("SELECT id FROM cajas WHERE estado = 'abierta' LIMIT 1");
        $caja = $cajaStmt->fetch();
        if (!$caja) {
            jsonResponse(['error' => true, 'message' => 'No hay una caja abierta. Debes aperturar la caja antes de registrar ventas.', 'caja_cerrada' => true], 422);
        }
        $cajaId = $caja['id'];

        $tipoComp = trim((string) ($data['tipo_comprobante'] ?? 'ticket')) ?: 'ticket';
        $tipoPago = trim((string) ($data['tipo_pago'] ?? 'efectivo')) ?: 'efectivo';
        $serieSolicitada = trim((string) ($data['serie'] ?? ''));
        $esCredito = !empty($data['es_credito']);
        if ($esCredito) {
            $tipoPago = 'credito';
        }
        $montoRecibido = round((float) ($data['monto_recibido'] ?? 0), 2);
        $observaciones = trim((string) ($data['observaciones'] ?? ''));
        $cuotas = [];
        $paymentBreakdown = [];
        if (!empty($data['cuotas']) && is_array($data['cuotas'])) {
            foreach ($data['cuotas'] as $cuota) {
                $amount = round((float) ($cuota['amount'] ?? 0), 2);
                $dueDate = trim((string) ($cuota['due_date'] ?? ''));
                if ($amount <= 0 || $dueDate === '') {
                    continue;
                }
                $cuotas[] = [
                    'amount' => $amount,
                    'due_date' => $dueDate,
                ];
            }
        }
        if (!empty($data['payment_breakdown']) && is_array($data['payment_breakdown'])) {
            foreach ($data['payment_breakdown'] as $payment) {
                $method = strtolower(trim((string) ($payment['method'] ?? '')));
                $amount = round((float) ($payment['amount'] ?? 0), 2);
                $accountId = isset($payment['account_id']) && $payment['account_id'] !== ''
                    ? (int) $payment['account_id']
                    : null;

                if ($method === '' || $amount <= 0) {
                    continue;
                }

                $paymentBreakdown[] = [
                    'method' => $method,
                    'amount' => $amount,
                    'account_id' => $accountId,
                ];
            }
        }

        $db->beginTransaction();
        try {
            $productosInfo = [];
            $detallesVenta = [];

            foreach ($data['items'] as $item) {
                $productoId = (int) ($item['producto_id'] ?? 0);
                $cantidad = round((float) ($item['cantidad'] ?? 0), 2);
                if ($productoId <= 0 || $cantidad <= 0) {
                    throw new Exception('Hay productos invalidos en la venta.');
                }

                $stockStmt = $db->prepare("
                    SELECT
                        p.id, p.stock, p.nombre, p.codigo, p.codigo_interno, p.codigo_sunat,
                        p.precio_venta, p.unidad_id, p.unidad_codigo, p.afectacion_igv_id,
                        p.afectacion_igv_codigo, p.porcentaje_igv, p.incluye_igv,
                        p.icbper_activo, p.factor_icbper,
                        a.tipo AS afectacion_tipo
                    FROM productos p
                    LEFT JOIN public.fe_tipos_afectacion_igv a ON a.id = p.afectacion_igv_id
                    WHERE p.id = :id AND p.activo = TRUE
                ");
                $stockStmt->execute([':id' => $productoId]);
                $producto = $stockStmt->fetch();

                if (!$producto) {
                    throw new Exception("Producto ID {$productoId} no encontrado");
                }
                if ((float) $producto['stock'] < $cantidad) {
                    throw new Exception("Stock insuficiente para: {$producto['nombre']} (disponible: {$producto['stock']})");
                }

                $detalle = ventasCalcularDetalleTributario($producto, $cantidad);
                $detalle['producto_id'] = $productoId;
                $detalle['nombre'] = $producto['nombre'];
                $detalle['codigo'] = $producto['codigo'];

                $productosInfo[$productoId] = $detalle;
                $detallesVenta[] = $detalle;
            }

            $descuento = round((float) ($data['descuento'] ?? 0), 2);
            [$detallesVenta, $descuentoAplicado] = ventasAplicarDescuentoGlobal($detallesVenta, $descuento);
            $resumen = ventasResumenDesdeDetalles($detallesVenta);
            $subtotal = $resumen['subtotal'];
            $descuento = $descuentoAplicado;
            $igv = $resumen['igv'];
            $total = $resumen['total'];

            if ($esCredito) {
                if (empty($cuotas)) {
                    throw new Exception('Debes registrar al menos una cuota para la venta a crédito.');
                }

                $creditTotal = round(array_sum(array_map(fn ($row) => (float) ($row['amount'] ?? 0), $cuotas)), 2);
                if (abs($creditTotal - $total) > 0.01) {
                    throw new Exception('La suma de las cuotas debe coincidir con el total de la venta.');
                }

                $paymentBreakdown = [];
                $montoRecibido = 0.0;
                $vuelto = 0.0;
                $tipoPago = 'credito';
            } elseif ($paymentBreakdown) {
                $paymentTotal = round(array_sum(array_map(fn ($payment) => (float) ($payment['amount'] ?? 0), $paymentBreakdown)), 2);
                if (abs($paymentTotal - $total) > 0.01) {
                    throw new Exception('La suma de los métodos de pago debe coincidir con el total de la venta.');
                }

                $cashReceived = round(array_sum(array_map(fn ($payment) => strtolower((string) ($payment['method'] ?? '')) === 'efectivo'
                    ? (float) ($payment['amount'] ?? 0)
                    : 0, $paymentBreakdown)), 2);
                $montoRecibido = $cashReceived;
            } else {
                $paymentBreakdown = [[
                    'method' => $tipoPago,
                    'amount' => $total,
                    'account_id' => $tipoPago === 'efectivo' ? null : ((int) ($data['payment_account_id'] ?? 0) ?: null),
                ]];
                $montoRecibido = $montoRecibido > 0 ? $montoRecibido : $total;
            }

            $vuelto = $tipoPago === 'efectivo' ? round(max(0, $montoRecibido - $total), 2) : 0.0;

            $clienteInfo = null;
            if (!empty($data['cliente_id'])) {
                $cliStmt = $db->prepare("
                    SELECT
                        id, nombres, apellidos, dni, ruc, telefono, email, direccion,
                        tipo_documento_id, tipo_documento_codigo, numero_documento,
                        nombre_completo, razon_social, ubigeo
                    FROM clientes
                    WHERE id = :id
                ");
                $cliStmt->execute([':id' => $data['cliente_id']]);
                $clienteInfo = $cliStmt->fetch() ?: null;
            }

            if ($tipoComp === 'factura' && (!$clienteInfo || empty($clienteInfo['ruc']))) {
                throw new Exception('Para emitir factura debe seleccionar un cliente con RUC registrado');
            }

            if ($esCredito && (!$clienteInfo || ventasEsClienteVarios($clienteInfo))) {
                throw new Exception('Para vender a crédito debes seleccionar un cliente real, no Clientes Varios.');
            }

            $comprobanteMeta = ventasMetaComprobante($tipoComp);
            $formaPagoId = $esCredito ? null : ventasResolverFormaPagoId($db, $tipoPago);
            $sunatFormaPago = $esCredito ? 'Credito' : 'Contado';

            $serie = null;
            $correlativo = null;
            $tipoDocumentoId = null;
            if (in_array($tipoComp, ['boleta', 'factura', 'ticket'], true)) {
                $seriesSql = "
                    UPDATE series_comprobantes
                    SET ultimo_numero = COALESCE(ultimo_numero, 0) + 1
                    WHERE tipo = :tipo
                      AND COALESCE(activo, TRUE) = TRUE
                ";
                $seriesParams = [':tipo' => $tipoComp];

                if ($serieSolicitada !== '') {
                    $seriesSql .= " AND serie = :serie";
                    $seriesParams[':serie'] = $serieSolicitada;
                }

                $seriesSql .= "
                    RETURNING serie,
                              ultimo_numero,
                              COALESCE(codigo_tipo_documento, :codigo_tipo_documento) AS codigo_tipo_documento,
                              tipo_documento_id,
                              COALESCE(descripcion, :descripcion) AS descripcion
                ";
                $seriesParams[':codigo_tipo_documento'] = $comprobanteMeta['codigo'];
                $seriesParams[':descripcion'] = $comprobanteMeta['descripcion'];

                $seriesStmt = $db->prepare($seriesSql);
                $seriesStmt->execute($seriesParams);
                $serieRow = $seriesStmt->fetch();

                if (!$serieRow) {
                    throw new Exception("No hay serie configurada para {$comprobanteMeta['descripcion']}.");
                }

                $serie = $serieRow['serie'];
                $correlativo = str_pad((string) intval($serieRow['ultimo_numero']), 8, '0', STR_PAD_LEFT);
                $tipoDocumentoId = $serieRow['tipo_documento_id'] ?? null;
            }

            $numeroVenta = generarNumeroVenta($db);
            $ventaStmt = $db->prepare("
                INSERT INTO ventas (
                    numero_venta, cliente_id, caja_id, subtotal, descuento, igv, total,
                    tipo_pago, tipo_comprobante, estado, vendedor, observaciones,
                    fecha_emision, fecha_vencimiento, hora_emision, moneda_codigo,
                    tipo_documento_id, codigo_tipo_documento, serie, correlativo,
                    operacion_tipo_codigo, forma_pago_id, sunat_forma_pago,
                    exonerada, inafecta, gravada, gratuita, anticipo, otros_cargos,
                    icbper, monto_credito, vuelto, cuotas, payment_breakdown
                )
                VALUES (
                    :numero_venta, :cliente_id, :caja_id, :subtotal, :descuento, :igv, :total,
                    :tipo_pago, :tipo_comprobante, 'completada', 'Administrador', :observaciones,
                    CURRENT_DATE, CURRENT_DATE, CURRENT_TIME, 'PEN',
                    :tipo_documento_id, :codigo_tipo_documento, :serie, :correlativo,
                    '0101', :forma_pago_id, :sunat_forma_pago,
                    :exonerada, :inafecta, :gravada, 0, 0, 0,
                    :icbper, :monto_credito, :vuelto, :cuotas, :payment_breakdown
                )
                RETURNING id
            ");
            $ventaStmt->execute([
                ':numero_venta' => $numeroVenta,
                ':cliente_id' => $data['cliente_id'] ?: null,
                ':caja_id' => $cajaId,
                ':subtotal' => $subtotal,
                ':descuento' => $descuento,
                ':igv' => $igv,
                ':total' => $total,
                ':tipo_pago' => $tipoPago,
                ':tipo_comprobante' => $tipoComp,
                ':observaciones' => $observaciones !== '' ? $observaciones : null,
                ':tipo_documento_id' => $tipoDocumentoId,
                ':codigo_tipo_documento' => $comprobanteMeta['codigo'],
                ':serie' => $serie,
                ':correlativo' => $correlativo,
                ':forma_pago_id' => $formaPagoId,
                ':sunat_forma_pago' => $sunatFormaPago,
                ':exonerada' => $resumen['exonerada'],
                ':inafecta' => $resumen['inafecta'],
                ':gravada' => $resumen['gravada'],
                ':icbper' => $resumen['icbper'],
                ':monto_credito' => $esCredito ? $total : 0,
                ':vuelto' => $vuelto,
                ':cuotas' => json_encode($cuotas, JSON_UNESCAPED_UNICODE),
                ':payment_breakdown' => json_encode($paymentBreakdown, JSON_UNESCAPED_UNICODE),
            ]);
            $ventaId = $ventaStmt->fetch()['id'];

            foreach ($detallesVenta as $detalle) {
                $detalleStmt = $db->prepare("
                    INSERT INTO venta_detalles (
                        venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal,
                        unidad_id, unidad_codigo, afectacion_igv_id, afectacion_igv_codigo,
                        descripcion, codigo_interno, codigo_sunat, igv, valor_unitario,
                        valor_total, precio_total, icbper, factor_icbper
                    )
                    VALUES (
                        :venta_id, :producto_id, :cantidad, :precio_unitario, :descuento, :subtotal,
                        :unidad_id, :unidad_codigo, :afectacion_id, :afectacion_codigo,
                        :descripcion, :codigo_interno, :codigo_sunat, :igv, :valor_unitario,
                        :valor_total, :precio_total, :icbper, :factor_icbper
                    )
                ");
                $detalleStmt->execute([
                    ':venta_id' => $ventaId,
                    ':producto_id' => $detalle['producto_id'],
                    ':cantidad' => $detalle['cantidad'],
                    ':precio_unitario' => $detalle['precio_unitario'],
                    ':subtotal' => $detalle['precio_total'],
                    ':descuento' => $detalle['descuento'] ?? 0,
                    ':unidad_id' => $detalle['unidad_id'],
                    ':unidad_codigo' => $detalle['unidad_codigo'],
                    ':afectacion_id' => $detalle['afectacion_id'],
                    ':afectacion_codigo' => $detalle['afectacion_codigo'],
                    ':descripcion' => $detalle['descripcion'],
                    ':codigo_interno' => $detalle['codigo_interno'],
                    ':codigo_sunat' => $detalle['codigo_sunat'],
                    ':igv' => $detalle['igv'],
                    ':valor_unitario' => $detalle['valor_unitario'],
                    ':valor_total' => $detalle['valor_total'],
                    ':precio_total' => $detalle['precio_total'],
                    ':icbper' => $detalle['icbper'],
                    ':factor_icbper' => $detalle['factor_icbper'],
                ]);

                $db->prepare("
                    UPDATE productos
                    SET stock = stock - :cantidad,
                        total_vendido = total_vendido + :cantidad,
                        updated_at = NOW()
                    WHERE id = :id
                ")->execute([
                    ':cantidad' => $detalle['cantidad'],
                    ':id' => $detalle['producto_id'],
                ]);
            }

            $db->prepare("UPDATE cajas SET saldo_actual = saldo_actual + :total WHERE id = :id")
                ->execute([':total' => $total, ':id' => $cajaId]);

            $compData = null;
            if (in_array($tipoComp, ['boleta', 'factura'], true)) {
                $numeroCompleto = $serie . '-' . $correlativo;

                $cpeStmt = $db->prepare("
                    INSERT INTO comprobantes_electronicos (
                        venta_id, tipo, serie, numero, numero_completo, estado_sunat,
                        codigo_tipo_documento, fecha_emision
                    )
                    VALUES (
                        :venta_id, :tipo, :serie, :numero, :numero_completo, 'pendiente',
                        :codigo_tipo_documento, CURRENT_DATE
                    )
                    RETURNING id
                ");
                $cpeStmt->execute([
                    ':venta_id' => $ventaId,
                    ':tipo' => $tipoComp,
                    ':serie' => $serie,
                    ':numero' => intval($correlativo),
                    ':numero_completo' => $numeroCompleto,
                    ':codigo_tipo_documento' => $comprobanteMeta['codigo'],
                ]);

                $compData = [
                    'id' => $cpeStmt->fetch()['id'],
                    'tipo' => $tipoComp,
                    'serie' => $serie,
                    'numero' => intval($correlativo),
                    'numero_completo' => $numeroCompleto,
                    'codigo_tipo_documento' => $comprobanteMeta['codigo'],
                ];
            }

            $db->commit();

            $comprobanteResult = null;
            if ($compData) {
                require_once '../../config/sunat.php';
                $itemsSunat = array_values($productosInfo);
                $ventaInfo = [
                    'numero_venta' => $numeroVenta,
                    'tipo_pago' => $tipoPago,
                    'sunat_forma_pago' => $sunatFormaPago,
                    'fecha_emision' => date('Y-m-d'),
                    'hora_emision' => date('H:i:s'),
                    'moneda' => 'PEN',
                    'totales' => $resumen,
                ];
                $comprobanteResult = enviar_sunat($db, $compData, $clienteInfo, $itemsSunat, $ventaInfo);
            }

            echo json_encode([
                'error' => false,
                'numero_venta' => $numeroVenta,
                'venta_id' => $ventaId,
                'subtotal' => $subtotal,
                'gravada' => $resumen['gravada'],
                'exonerada' => $resumen['exonerada'],
                'inafecta' => $resumen['inafecta'],
                'igv' => $igv,
                'icbper' => $resumen['icbper'],
                'descuento' => $descuento,
                'total' => $total,
                'tipo_pago' => $tipoPago,
                'tipo_comprobante' => $tipoComp,
                'serie' => $serie,
                'correlativo' => $correlativo,
                'monto_recibido' => $esCredito ? 0 : ($montoRecibido > 0 ? $montoRecibido : $total),
                'vuelto' => $vuelto,
                'cuotas' => $cuotas,
                'payment_breakdown' => $paymentBreakdown,
                'items' => array_map(fn($i) => [
                    'producto_id' => $i['producto_id'],
                    'nombre' => $i['nombre'],
                    'codigo' => $i['codigo'],
                    'cantidad' => $i['cantidad'],
                    'qty' => $i['cantidad'],
                    'precio' => $i['precio_unitario'],
                    'valor_unitario' => $i['valor_unitario'],
                    'valor_total' => $i['valor_total'],
                    'precio_total' => $i['precio_total'],
                    'igv' => $i['igv'],
                    'afectacion_igv_codigo' => $i['afectacion_codigo'],
                    'afectacion_tipo' => $i['afectacion_tipo'],
                    'unidad_codigo' => $i['unidad_codigo'],
                ], $detallesVenta),
                'comprobante' => $comprobanteResult,
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    case 'historial':
        $desde  = $_GET['desde']  ?? date('Y-m-d', strtotime('-30 days'));
        $hasta  = $_GET['hasta']  ?? date('Y-m-d');
        $estado = $_GET['estado'] ?? '';
        $q      = '%' . ($_GET['q'] ?? '') . '%';

        $params = [':desde' => $desde, ':hasta' => $hasta . ' 23:59:59', ':q' => $q];
        $estFilter = $estado ? "AND v.estado = :estado" : '';
        if ($estado) {
            $params[':estado'] = $estado;
        }

        $stmt = $db->prepare("
            SELECT
                v.id, v.numero_venta, v.total, v.subtotal, v.descuento, v.igv,
                v.tipo_pago, v.tipo_comprobante, v.estado, v.vendedor, v.created_at,
                COALESCE(c.razon_social, c.nombre_completo, c.nombres || ' ' || COALESCE(c.apellidos,''), 'CLIENTES VARIOS') AS cliente,
                COUNT(vd.id) AS num_items
            FROM ventas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            LEFT JOIN venta_detalles vd ON vd.venta_id = v.id
            WHERE v.created_at BETWEEN :desde AND :hasta
              AND (
                    v.numero_venta ILIKE :q
                    OR COALESCE(c.nombres,'') ILIKE :q
                    OR COALESCE(c.razon_social,'') ILIKE :q
                  )
              $estFilter
            GROUP BY v.id, c.razon_social, c.nombre_completo, c.nombres, c.apellidos
            ORDER BY v.created_at DESC
            LIMIT 200
        ");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    case 'detalle_venta':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $db->prepare("
            SELECT vd.*, p.nombre AS producto_nombre, p.codigo
            FROM venta_detalles vd
            JOIN productos p ON p.id = vd.producto_id
            WHERE vd.venta_id = :id
        ");
        $stmt->execute([':id' => $id]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'anular_venta':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $id   = intval($data['id'] ?? 0);

        $db->beginTransaction();
        try {
            $check = $db->prepare("SELECT estado, total, caja_id FROM ventas WHERE id = :id");
            $check->execute([':id' => $id]);
            $venta = $check->fetch();

            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }
            if ($venta['estado'] === 'anulada') {
                throw new Exception('La venta ya esta anulada');
            }

            $detalles = $db->prepare("SELECT producto_id, cantidad FROM venta_detalles WHERE venta_id = :id");
            $detalles->execute([':id' => $id]);
            foreach ($detalles->fetchAll() as $det) {
                $db->prepare("UPDATE productos SET stock = stock + :qty WHERE id = :pid")
                    ->execute([':qty' => $det['cantidad'], ':pid' => $det['producto_id']]);
            }

            $db->prepare("UPDATE ventas SET estado = 'anulada' WHERE id = :id")
                ->execute([':id' => $id]);

            if ($venta['caja_id']) {
                $db->prepare("UPDATE cajas SET saldo_actual = saldo_actual - :total WHERE id = :cid")
                    ->execute([':total' => $venta['total'], ':cid' => $venta['caja_id']]);
            }

            $db->commit();
            echo json_encode(['error' => false, 'message' => 'Venta anulada correctamente']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    case 'stats_dia':
        $hoy = date('Y-m-d');
        $row = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE estado = 'completada') AS total_ventas,
                COALESCE(SUM(total) FILTER (WHERE estado = 'completada'), 0) AS ingresos,
                COALESCE(AVG(total) FILTER (WHERE estado = 'completada'), 0) AS ticket_promedio,
                COUNT(*) FILTER (WHERE estado = 'anulada') AS anuladas
            FROM ventas
            WHERE DATE(created_at) = '$hoy'
        ")->fetch();
        echo json_encode($row);
        break;

    case 'top_vendidos':
        $limite = intval($_GET['limite'] ?? 10);
        $stmt = $db->prepare("
            SELECT
                p.id, p.nombre, p.laboratorio, p.total_vendido, p.precio_venta,
                p.favorito, c.nombre AS categoria
            FROM productos p
            LEFT JOIN public.categorias c ON c.id = p.categoria_id
            WHERE p.activo = TRUE AND p.total_vendido > 0
            ORDER BY p.total_vendido DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetchAll());
        break;

    case 'favoritos':
        $stmt = $db->query("
            SELECT
                p.id, p.nombre, p.laboratorio, p.total_vendido, p.precio_venta, p.stock,
                c.nombre AS categoria
            FROM productos p
            LEFT JOIN public.categorias c ON c.id = p.categoria_id
            WHERE p.activo = TRUE AND p.favorito = TRUE
            ORDER BY p.total_vendido DESC
        ");
        echo json_encode($stmt->fetchAll());
        break;

    case 'toggle_favorito':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $id   = intval($data['id'] ?? 0);
        $stmt = $db->prepare("UPDATE productos SET favorito = NOT favorito WHERE id = :id RETURNING favorito");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        echo json_encode(['error' => false, 'favorito' => $row['favorito']]);
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Accion no valida'], 404);
}
