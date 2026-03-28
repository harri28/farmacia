<?php
// ============================================================
// ARCHIVO: farmacia/modules/almacen/api.php
// DESCRIPCIÓN: API REST para el módulo de Almacén
// Entidades: proveedores, ingresos de stock
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ================================================================
    // PROVEEDORES
    // ================================================================

    case 'proveedores_listar':
        $q      = '%' . trim($_GET['q'] ?? '') . '%';
        $solo   = $_GET['activos'] ?? '';
        $where  = "(p.razon_social ILIKE :q OR COALESCE(p.ruc,'') ILIKE :q OR COALESCE(p.nombre_comercial,'') ILIKE :q)";
        $params = [':q' => $q];
        if ($solo === '1') { $where .= ' AND p.activo = TRUE'; }

        $stmt = $db->prepare("
            SELECT p.id, p.ruc, p.razon_social, p.nombre_comercial, p.telefono,
                   p.email, p.contacto_nombre, p.direccion, p.activo,
                   (SELECT COUNT(*) FROM ingresos WHERE proveedor_id = p.id) AS total_ingresos
            FROM proveedores p
            WHERE $where
            ORDER BY p.razon_social ASC
        ");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    case 'proveedor_crear':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['razon_social'])) {
            jsonResponse(['error' => true, 'message' => 'La razón social es requerida'], 400);
        }
        if (!empty($data['ruc'])) {
            $ck = $db->prepare("SELECT id FROM proveedores WHERE ruc = :ruc");
            $ck->execute([':ruc' => trim($data['ruc'])]);
            if ($ck->fetch()) jsonResponse(['error' => true, 'message' => 'Ya existe un proveedor con ese RUC'], 409);
        }
        $stmt = $db->prepare("
            INSERT INTO proveedores (ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre)
            VALUES (:ruc, :rs, :nc, :tel, :email, :dir, :ct)
            RETURNING id
        ");
        $stmt->execute([
            ':ruc'   => trim($data['ruc']   ?? '') ?: null,
            ':rs'    => trim($data['razon_social']),
            ':nc'    => trim($data['nombre_comercial'] ?? ''),
            ':tel'   => trim($data['telefono']  ?? ''),
            ':email' => trim($data['email']     ?? ''),
            ':dir'   => trim($data['direccion'] ?? ''),
            ':ct'    => trim($data['contacto_nombre'] ?? ''),
        ]);
        $id = $stmt->fetch()['id'];
        jsonResponse(['error' => false, 'message' => 'Proveedor creado correctamente', 'id' => $id]);

    case 'proveedor_actualizar':
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id || empty($data['razon_social'])) {
            jsonResponse(['error' => true, 'message' => 'Datos inválidos'], 400);
        }
        if (!empty($data['ruc'])) {
            $ck = $db->prepare("SELECT id FROM proveedores WHERE ruc = :ruc AND id != :id");
            $ck->execute([':ruc' => trim($data['ruc']), ':id' => $id]);
            if ($ck->fetch()) jsonResponse(['error' => true, 'message' => 'Otro proveedor ya usa ese RUC'], 409);
        }
        $stmt = $db->prepare("
            UPDATE proveedores SET
                ruc              = :ruc,
                razon_social     = :rs,
                nombre_comercial = :nc,
                telefono         = :tel,
                email            = :email,
                direccion        = :dir,
                contacto_nombre  = :ct
            WHERE id = :id
        ");
        $stmt->execute([
            ':id'    => $id,
            ':ruc'   => trim($data['ruc']   ?? '') ?: null,
            ':rs'    => trim($data['razon_social']),
            ':nc'    => trim($data['nombre_comercial'] ?? ''),
            ':tel'   => trim($data['telefono']  ?? ''),
            ':email' => trim($data['email']     ?? ''),
            ':dir'   => trim($data['direccion'] ?? ''),
            ':ct'    => trim($data['contacto_nombre'] ?? ''),
        ]);
        jsonResponse(['error' => false, 'message' => 'Proveedor actualizado correctamente']);

    case 'proveedor_toggle_activo':
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) jsonResponse(['error' => true, 'message' => 'ID inválido'], 400);
        $stmt = $db->prepare("UPDATE proveedores SET activo = NOT activo WHERE id = :id RETURNING activo");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        jsonResponse(['error' => false, 'activo' => $row['activo']]);

    // ================================================================
    // INGRESOS
    // ================================================================

    case 'stats_almacen':
        $mes = date('Y-m');
        $row = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE estado = 'completado' AND TO_CHAR(created_at,'YYYY-MM') = '$mes') AS ingresos_mes,
                COALESCE(SUM(total) FILTER (WHERE estado = 'completado' AND TO_CHAR(created_at,'YYYY-MM') = '$mes'), 0) AS valor_mes,
                COUNT(*) FILTER (WHERE estado = 'anulado'    AND TO_CHAR(created_at,'YYYY-MM') = '$mes') AS anulados_mes,
                (SELECT COUNT(*) FROM proveedores WHERE activo = TRUE) AS proveedores_activos
            FROM ingresos
        ")->fetch();
        echo json_encode($row);
        break;

    case 'ingresos_listar':
        $desde     = $_GET['desde']       ?? date('Y-m-d', strtotime('-30 days'));
        $hasta     = $_GET['hasta']        ?? date('Y-m-d');
        $estado    = $_GET['estado']       ?? '';
        $prov_id   = intval($_GET['proveedor_id'] ?? 0);
        $q         = '%' . ($_GET['q'] ?? '') . '%';

        $where  = ['i.created_at BETWEEN :desde AND :hasta', '(i.numero_ingreso ILIKE :q OR COALESCE(p.razon_social,\'\') ILIKE :q)'];
        $params = [':desde' => $desde, ':hasta' => $hasta . ' 23:59:59', ':q' => $q];

        if ($estado)  { $where[] = 'i.estado = :estado';    $params[':estado']  = $estado;  }
        if ($prov_id) { $where[] = 'i.proveedor_id = :pid'; $params[':pid']     = $prov_id; }

        $stmt = $db->prepare("
            SELECT i.id, i.numero_ingreso, i.numero_factura, i.fecha_factura,
                   i.subtotal, i.igv, i.total, i.estado, i.usuario, i.created_at,
                   COALESCE(p.nombre_comercial, p.razon_social, 'Sin proveedor') AS proveedor,
                   COUNT(d.id) AS num_items
            FROM ingresos i
            LEFT JOIN proveedores p ON p.id = i.proveedor_id
            LEFT JOIN ingreso_detalles d ON d.ingreso_id = i.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY i.id, p.nombre_comercial, p.razon_social
            ORDER BY i.created_at DESC
            LIMIT 200
        ");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    case 'ingreso_detalle':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $db->prepare("
            SELECT d.cantidad, d.precio_unitario, d.subtotal,
                   p.nombre AS producto_nombre, p.codigo
            FROM ingreso_detalles d
            JOIN productos p ON p.id = d.producto_id
            WHERE d.ingreso_id = :id
            ORDER BY p.nombre
        ");
        $stmt->execute([':id' => $id]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'buscar_producto':
        $q = '%' . trim($_GET['q'] ?? '') . '%';
        $stmt = $db->prepare("
            SELECT id, codigo, nombre, precio_compra, precio_venta, stock, presentacion, laboratorio
            FROM productos
            WHERE activo = TRUE AND (nombre ILIKE :q OR codigo ILIKE :q)
            ORDER BY nombre ASC
            LIMIT 10
        ");
        $stmt->execute([':q' => $q]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'registrar_ingreso':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['items'])) {
            jsonResponse(['error' => true, 'message' => 'Debe agregar al menos un producto'], 400);
        }

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += floatval($item['cantidad']) * floatval($item['precio_unitario']);
        }
        $igv   = round($subtotal * 0.18, 2);
        $total = $subtotal;

        $db->beginTransaction();
        try {
            $numero = generarNumeroIngreso($db);
            $stmt = $db->prepare("
                INSERT INTO ingresos
                    (numero_ingreso, proveedor_id, numero_factura, fecha_factura,
                     subtotal, igv, total, observaciones, estado)
                VALUES
                    (:num, :prov, :factura, :fecha, :sub, :igv, :total, :obs, 'completado')
                RETURNING id
            ");
            $stmt->execute([
                ':num'     => $numero,
                ':prov'    => $data['proveedor_id'] ?: null,
                ':factura' => trim($data['numero_factura'] ?? ''),
                ':fecha'   => $data['fecha_factura'] ?: date('Y-m-d'),
                ':sub'     => $subtotal,
                ':igv'     => $igv,
                ':total'   => $total,
                ':obs'     => trim($data['observaciones'] ?? ''),
            ]);
            $ingresoId = $stmt->fetch()['id'];

            foreach ($data['items'] as $item) {
                $qty    = intval($item['cantidad']);
                $precio = floatval($item['precio_unitario']);
                $sub    = $qty * $precio;
                $pid    = intval($item['producto_id']);

                // Verificar que el producto existe
                $ck = $db->prepare("SELECT id FROM productos WHERE id = :id AND activo = TRUE");
                $ck->execute([':id' => $pid]);
                if (!$ck->fetch()) throw new Exception("Producto ID $pid no encontrado");

                $db->prepare("
                    INSERT INTO ingreso_detalles (ingreso_id, producto_id, cantidad, precio_unitario, subtotal)
                    VALUES (:iid, :pid, :qty, :precio, :sub)
                ")->execute([':iid' => $ingresoId, ':pid' => $pid, ':qty' => $qty, ':precio' => $precio, ':sub' => $sub]);

                // Actualizar stock
                $db->prepare("UPDATE productos SET stock = stock + :qty, updated_at = NOW() WHERE id = :id")
                   ->execute([':qty' => $qty, ':id' => $pid]);

                // Actualizar precio de compra con el último precio recibido
                if ($precio > 0) {
                    $db->prepare("UPDATE productos SET precio_compra = :precio, updated_at = NOW() WHERE id = :id")
                       ->execute([':precio' => $precio, ':id' => $pid]);
                }
            }

            $db->commit();
            jsonResponse(['error' => false, 'numero_ingreso' => $numero, 'total' => $total]);

        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }

    case 'anular_ingreso':
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);

        $db->beginTransaction();
        try {
            $ing = $db->prepare("SELECT estado FROM ingresos WHERE id = :id");
            $ing->execute([':id' => $id]);
            $row = $ing->fetch();
            if (!$row)                         throw new Exception('Ingreso no encontrado');
            if ($row['estado'] === 'anulado')  throw new Exception('El ingreso ya está anulado');

            // Verificar que revertir stock no deje valores negativos
            $dets = $db->prepare("SELECT producto_id, cantidad FROM ingreso_detalles WHERE ingreso_id = :id");
            $dets->execute([':id' => $id]);
            foreach ($dets->fetchAll() as $det) {
                $prod = $db->prepare("SELECT stock, nombre FROM productos WHERE id = :pid");
                $prod->execute([':pid' => $det['producto_id']]);
                $p = $prod->fetch();
                if ($p && ($p['stock'] - $det['cantidad']) < 0) {
                    throw new Exception("No se puede anular: stock insuficiente en '{$p['nombre']}' (quedaría negativo)");
                }
                // Revertir stock
                $db->prepare("UPDATE productos SET stock = stock - :qty, updated_at = NOW() WHERE id = :pid")
                   ->execute([':qty' => $det['cantidad'], ':pid' => $det['producto_id']]);
            }

            $db->prepare("UPDATE ingresos SET estado = 'anulado' WHERE id = :id")->execute([':id' => $id]);
            $db->commit();
            jsonResponse(['error' => false, 'message' => 'Ingreso anulado correctamente']);

        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
