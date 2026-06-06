<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin', 'gerente']);

$action = $_GET['action'] ?? '';
$db = getDB();

function trasladoSchemaSeguro(string $schema): string
{
    $safe = preg_replace('/[^a-z0-9_]/', '', strtolower($schema));
    if ($safe === '') {
        throw new Exception('Schema de sucursal inválido.');
    }
    return $safe;
}

function trasladoSucursalActual(PDO $db): array
{
    $stmt = $db->prepare("
        SELECT id, tenant_id, nombre, schema_name
        FROM public.sucursales
        WHERE id = :id AND tenant_id = :tid
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => sesionSucursalId(),
        ':tid' => sesionTenantId(),
    ]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new Exception('No se encontró la sucursal activa.');
    }
    return $row;
}

function trasladoSucursalPorId(PDO $db, int $id): ?array
{
    $stmt = $db->prepare("
        SELECT id, tenant_id, nombre, schema_name, activo
        FROM public.sucursales
        WHERE id = :id AND tenant_id = :tid
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => $id,
        ':tid' => sesionTenantId(),
    ]);
    return $stmt->fetch() ?: null;
}

function trasladoGenerarNumero(PDO $db): string
{
    $prefijo = 'TR' . date('Ymd') . '-';
    $stmt = $db->prepare("
        SELECT COALESCE(MAX(CAST(SUBSTRING(numero_traslado FROM '[0-9]+$') AS INTEGER)), 0) AS ultimo
        FROM public.ordenes_traslado
        WHERE tenant_id = :tid
          AND numero_traslado LIKE :prefijo
    ");
    $stmt->execute([
        ':tid' => sesionTenantId(),
        ':prefijo' => $prefijo . '%',
    ]);
    $ultimo = (int) ($stmt->fetch()['ultimo'] ?? 0);
    return $prefijo . str_pad((string) ($ultimo + 1), 4, '0', STR_PAD_LEFT);
}

function trasladoCargarCabecera(PDO $db, int $id): array
{
    $stmt = $db->prepare("
        SELECT
            ot.*,
            so.nombre AS sucursal_origen,
            so.schema_name AS schema_origen,
            sd.nombre AS sucursal_destino,
            sd.schema_name AS schema_destino,
            us.nombre AS usuario_solicita_nombre,
            ue.nombre AS usuario_envia_nombre,
            ur.nombre AS usuario_recibe_nombre
        FROM public.ordenes_traslado ot
        INNER JOIN public.sucursales so ON so.id = ot.sucursal_origen_id
        INNER JOIN public.sucursales sd ON sd.id = ot.sucursal_destino_id
        LEFT JOIN public.usuarios us ON us.id = ot.usuario_solicita_id
        LEFT JOIN public.usuarios ue ON ue.id = ot.usuario_envia_id
        LEFT JOIN public.usuarios ur ON ur.id = ot.usuario_recibe_id
        WHERE ot.id = :id
          AND ot.tenant_id = :tid
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => $id,
        ':tid' => sesionTenantId(),
    ]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new Exception('Traslado no encontrado.');
    }
    return $row;
}

function trasladoCargarDetalles(PDO $db, int $trasladoId): array
{
    $stmt = $db->prepare("
        SELECT *
        FROM public.orden_traslado_detalles
        WHERE traslado_id = :id
        ORDER BY id ASC
    ");
    $stmt->execute([':id' => $trasladoId]);
    return $stmt->fetchAll();
}

function trasladoProductoPorCodigo(PDO $db, string $schema, string $codigo): ?array
{
    $schema = trasladoSchemaSeguro($schema);
    $stmt = $db->prepare("
        SELECT id, codigo, nombre, stock, precio_compra, activo
        FROM {$schema}.productos
        WHERE codigo = :codigo
        LIMIT 1
    ");
    $stmt->execute([':codigo' => $codigo]);
    return $stmt->fetch() ?: null;
}

switch ($action) {
    case 'sucursales':
        $stmt = $db->prepare("
            SELECT id, nombre, schema_name, activo
            FROM public.sucursales
            WHERE tenant_id = :tid
            ORDER BY nombre ASC
        ");
        $stmt->execute([':tid' => sesionTenantId()]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'buscar_producto':
        try {
            $actual = trasladoSucursalActual($db);
            $q = '%' . trim($_GET['q'] ?? '') . '%';
            $schema = trasladoSchemaSeguro($actual['schema_name']);
            $stmt = $db->prepare("
                SELECT id, codigo, nombre, stock, precio_compra, precio_venta
                FROM {$schema}.productos
                WHERE activo = TRUE
                  AND (codigo ILIKE :q OR nombre ILIKE :q)
                ORDER BY nombre ASC
                LIMIT 12
            ");
            $stmt->execute([':q' => $q]);
            echo json_encode($stmt->fetchAll());
        } catch (Throwable $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'listar':
        try {
            $estado = trim($_GET['estado'] ?? '');
            $q = '%' . trim($_GET['q'] ?? '') . '%';
            $where = ["ot.tenant_id = :tid", "(ot.numero_traslado ILIKE :q OR so.nombre ILIKE :q OR sd.nombre ILIKE :q)"];
            $params = [
                ':tid' => sesionTenantId(),
                ':q' => $q,
            ];
            if ($estado !== '') {
                $where[] = "ot.estado = :estado";
                $params[':estado'] = $estado;
            }

            $stmt = $db->prepare("
                SELECT
                    ot.*,
                    so.nombre AS sucursal_origen,
                    sd.nombre AS sucursal_destino,
                    COUNT(d.id) AS total_items,
                    COALESCE(SUM(d.cantidad), 0) AS total_unidades
                FROM public.ordenes_traslado ot
                INNER JOIN public.sucursales so ON so.id = ot.sucursal_origen_id
                INNER JOIN public.sucursales sd ON sd.id = ot.sucursal_destino_id
                LEFT JOIN public.orden_traslado_detalles d ON d.traslado_id = ot.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY ot.id, so.nombre, sd.nombre
                ORDER BY ot.created_at DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Throwable $e) {
            jsonResponse(['error' => true, 'message' => 'Error al listar traslados: ' . $e->getMessage()], 500);
        }
        break;

    case 'detalle':
        try {
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(['error' => true, 'message' => 'Traslado inválido.'], 400);
            }
            $cab = trasladoCargarCabecera($db, $id);
            $cab['items'] = trasladoCargarDetalles($db, $id);
            echo json_encode($cab);
        } catch (Throwable $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
        }
        break;

    case 'crear':
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        try {
            $actual = trasladoSucursalActual($db);
            $destinoId = (int) ($data['sucursal_destino_id'] ?? 0);
            $observaciones = trim((string) ($data['observaciones'] ?? ''));
            $items = $data['items'] ?? [];

            if ($destinoId <= 0) {
                jsonResponse(['error' => true, 'message' => 'Selecciona la sucursal destino.'], 400);
            }
            if (empty($items) || !is_array($items)) {
                jsonResponse(['error' => true, 'message' => 'Debes agregar al menos un producto.'], 400);
            }

            $destino = trasladoSucursalPorId($db, $destinoId);
            if (!$destino) {
                jsonResponse(['error' => true, 'message' => 'La sucursal destino no existe.'], 404);
            }
            if ((int) $destino['id'] === (int) $actual['id']) {
                jsonResponse(['error' => true, 'message' => 'La sucursal destino debe ser distinta a la sucursal origen.'], 400);
            }

            $schemaOrigen = trasladoSchemaSeguro($actual['schema_name']);
            $schemaDestino = trasladoSchemaSeguro($destino['schema_name']);

            $db->beginTransaction();
            $numero = trasladoGenerarNumero($db);
            $stmt = $db->prepare("
                INSERT INTO public.ordenes_traslado (
                    tenant_id, numero_traslado, sucursal_origen_id, sucursal_destino_id,
                    estado, observaciones, usuario_solicita_id, fecha_solicitud, created_at, updated_at
                ) VALUES (
                    :tid, :numero, :origen, :destino,
                    'borrador', :obs, :uid, NOW(), NOW(), NOW()
                )
                RETURNING id
            ");
            $stmt->execute([
                ':tid' => sesionTenantId(),
                ':numero' => $numero,
                ':origen' => $actual['id'],
                ':destino' => $destino['id'],
                ':obs' => $observaciones ?: null,
                ':uid' => sesionId() ?: null,
            ]);
            $trasladoId = (int) $stmt->fetch()['id'];

            $itemStmt = $db->prepare("
                INSERT INTO public.orden_traslado_detalles (
                    traslado_id, producto_codigo, producto_nombre, cantidad, costo_unitario,
                    stock_origen_snapshot, stock_destino_snapshot, observaciones, created_at
                ) VALUES (
                    :traslado_id, :codigo, :nombre, :cantidad, :costo,
                    :stock_origen, :stock_destino, :observaciones, NOW()
                )
            ");

            foreach ($items as $item) {
                $codigo = trim((string) ($item['codigo'] ?? ''));
                $cantidad = (int) ($item['cantidad'] ?? 0);
                $costo = round((float) ($item['costo_unitario'] ?? 0), 2);
                if ($codigo === '' || $cantidad <= 0) {
                    throw new Exception('Todos los productos deben tener código y cantidad válida.');
                }

                $productoOrigen = trasladoProductoPorCodigo($db, $schemaOrigen, $codigo);
                $activoOrigen = $productoOrigen && (
                    $productoOrigen['activo'] === true
                    || $productoOrigen['activo'] === 't'
                    || $productoOrigen['activo'] === '1'
                    || $productoOrigen['activo'] === 1
                );
                if (!$activoOrigen) {
                    throw new Exception("El producto {$codigo} no existe o está inactivo en la sucursal origen.");
                }

                $productoDestino = trasladoProductoPorCodigo($db, $schemaDestino, $codigo);
                $itemStmt->execute([
                    ':traslado_id' => $trasladoId,
                    ':codigo' => $codigo,
                    ':nombre' => $productoOrigen['nombre'],
                    ':cantidad' => $cantidad,
                    ':costo' => $costo > 0 ? $costo : (float) ($productoOrigen['precio_compra'] ?? 0),
                    ':stock_origen' => (int) ($productoOrigen['stock'] ?? 0),
                    ':stock_destino' => (int) ($productoDestino['stock'] ?? 0),
                    ':observaciones' => trim((string) ($item['observaciones'] ?? '')) ?: null,
                ]);
            }

            $db->commit();
            jsonResponse([
                'error' => false,
                'message' => 'Traslado creado correctamente.',
                'id' => $trasladoId,
                'numero_traslado' => $numero,
            ]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    case 'enviar':
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        try {
            $id = (int) ($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(['error' => true, 'message' => 'Traslado inválido.'], 400);
            }

            $actual = trasladoSucursalActual($db);
            $cab = trasladoCargarCabecera($db, $id);
            if ((int) $cab['sucursal_origen_id'] !== (int) $actual['id']) {
                jsonResponse(['error' => true, 'message' => 'Solo la sucursal origen puede enviar el traslado.'], 403);
            }
            if ($cab['estado'] !== 'borrador') {
                jsonResponse(['error' => true, 'message' => 'Solo se pueden enviar traslados en borrador.'], 422);
            }

            $items = trasladoCargarDetalles($db, $id);
            if (!$items) {
                jsonResponse(['error' => true, 'message' => 'El traslado no tiene productos.'], 422);
            }

            $schemaOrigen = trasladoSchemaSeguro($cab['schema_origen']);
            $db->beginTransaction();
            foreach ($items as $item) {
                $producto = trasladoProductoPorCodigo($db, $schemaOrigen, $item['producto_codigo']);
                if (!$producto) {
                    throw new Exception("No se encontró el producto {$item['producto_codigo']} en origen.");
                }
                if ((int) $producto['stock'] < (int) $item['cantidad']) {
                    throw new Exception("Stock insuficiente para {$item['producto_nombre']} (disponible: {$producto['stock']}).");
                }

                $upd = $db->prepare("
                    UPDATE {$schemaOrigen}.productos
                    SET stock = stock - :cantidad,
                        updated_at = NOW()
                    WHERE codigo = :codigo
                ");
                $upd->execute([
                    ':cantidad' => (int) $item['cantidad'],
                    ':codigo' => $item['producto_codigo'],
                ]);
            }

            $db->prepare("
                UPDATE public.ordenes_traslado
                SET estado = 'enviado',
                    usuario_envia_id = :uid,
                    fecha_envio = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ")->execute([
                ':uid' => sesionId() ?: null,
                ':id' => $id,
            ]);
            $db->commit();
            jsonResponse(['error' => false, 'message' => 'Traslado enviado correctamente.']);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    case 'recibir':
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        try {
            $id = (int) ($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(['error' => true, 'message' => 'Traslado inválido.'], 400);
            }

            $actual = trasladoSucursalActual($db);
            $cab = trasladoCargarCabecera($db, $id);
            if ((int) $cab['sucursal_destino_id'] !== (int) $actual['id']) {
                jsonResponse(['error' => true, 'message' => 'Solo la sucursal destino puede recibir el traslado.'], 403);
            }
            if ($cab['estado'] !== 'enviado') {
                jsonResponse(['error' => true, 'message' => 'Solo se pueden recibir traslados enviados.'], 422);
            }

            $items = trasladoCargarDetalles($db, $id);
            $schemaDestino = trasladoSchemaSeguro($cab['schema_destino']);

            $db->beginTransaction();
            foreach ($items as $item) {
                $producto = trasladoProductoPorCodigo($db, $schemaDestino, $item['producto_codigo']);
                if (!$producto) {
                    throw new Exception("El producto {$item['producto_codigo']} no existe en la sucursal destino.");
                }

                $upd = $db->prepare("
                    UPDATE {$schemaDestino}.productos
                    SET stock = stock + :cantidad,
                        precio_compra = CASE
                            WHEN CAST(:costo AS NUMERIC(10,2)) > 0
                                THEN CAST(:costo AS NUMERIC(10,2))
                            ELSE precio_compra
                        END,
                        updated_at = NOW()
                    WHERE codigo = :codigo
                ");
                $upd->execute([
                    ':cantidad' => (int) $item['cantidad'],
                    ':costo' => round((float) ($item['costo_unitario'] ?? 0), 2),
                    ':codigo' => $item['producto_codigo'],
                ]);
            }

            $db->prepare("
                UPDATE public.ordenes_traslado
                SET estado = 'recibido',
                    usuario_recibe_id = :uid,
                    fecha_recepcion = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ")->execute([
                ':uid' => sesionId() ?: null,
                ':id' => $id,
            ]);
            $db->commit();
            jsonResponse(['error' => false, 'message' => 'Traslado recibido correctamente.']);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    case 'anular':
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        try {
            $id = (int) ($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(['error' => true, 'message' => 'Traslado inválido.'], 400);
            }
            $actual = trasladoSucursalActual($db);
            $cab = trasladoCargarCabecera($db, $id);
            if ((int) $cab['sucursal_origen_id'] !== (int) $actual['id']) {
                jsonResponse(['error' => true, 'message' => 'Solo la sucursal origen puede anular el traslado.'], 403);
            }
            if ($cab['estado'] === 'recibido') {
                jsonResponse(['error' => true, 'message' => 'No se puede anular un traslado ya recibido.'], 422);
            }
            if ($cab['estado'] === 'anulado') {
                jsonResponse(['error' => true, 'message' => 'El traslado ya está anulado.'], 422);
            }

            $items = trasladoCargarDetalles($db, $id);
            $schemaOrigen = trasladoSchemaSeguro($cab['schema_origen']);

            $db->beginTransaction();
            if ($cab['estado'] === 'enviado') {
                foreach ($items as $item) {
                    $db->prepare("
                        UPDATE {$schemaOrigen}.productos
                        SET stock = stock + :cantidad,
                            updated_at = NOW()
                        WHERE codigo = :codigo
                    ")->execute([
                        ':cantidad' => (int) $item['cantidad'],
                        ':codigo' => $item['producto_codigo'],
                    ]);
                }
            }

            $db->prepare("
                UPDATE public.ordenes_traslado
                SET estado = 'anulado',
                    updated_at = NOW()
                WHERE id = :id
            ")->execute([':id' => $id]);
            $db->commit();
            jsonResponse(['error' => false, 'message' => 'Traslado anulado correctamente.']);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
