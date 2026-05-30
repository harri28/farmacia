<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin']);

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ----------------------------------------------------------------
    // STATS
    // ----------------------------------------------------------------
    case 'stats':
        try {
            $row = $db->query("
                SELECT
                    COUNT(*)                                                                       AS total,
                    COUNT(*) FILTER (WHERE activo = TRUE)                                         AS activos,
                    COUNT(*) FILTER (WHERE activo = FALSE)                                        AS inactivos,
                    COUNT(*) FILTER (WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)) AS nuevos_mes
                FROM clientes
                WHERE id != 1
            ")->fetch();
            $ven = $db->query("
                SELECT COALESCE(SUM(total), 0) AS total_facturado
                FROM ventas
                WHERE cliente_id IS NOT NULL AND cliente_id != 1 AND estado = 'completada'
            ")->fetch();
            echo json_encode(array_merge($row, $ven));
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // LISTAR
    // ----------------------------------------------------------------
    case 'listar':
        try {
            $q      = '%' . trim($_GET['q'] ?? '') . '%';
            $estado = $_GET['estado'] ?? '';

            $where  = [
                "c.id != 1",
                "(c.nombres ILIKE :q OR COALESCE(c.apellidos,'') ILIKE :q OR COALESCE(c.dni,'') ILIKE :q OR COALESCE(c.ruc,'') ILIKE :q OR COALESCE(c.telefono,'') ILIKE :q OR COALESCE(c.email,'') ILIKE :q)",
            ];
            $params = [':q' => $q];

            if ($estado === 'activo')   { $where[] = 'c.activo = TRUE'; }
            elseif ($estado === 'inactivo') { $where[] = 'c.activo = FALSE'; }

            $stmt = $db->prepare("
                SELECT c.id, c.nombres, c.apellidos, c.dni, c.ruc, c.telefono, c.email,
                       c.direccion, c.activo, c.created_at,
                       COUNT(v.id)    FILTER (WHERE v.estado = 'completada')           AS total_compras,
                       COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada'), 0) AS total_gastado,
                       MAX(v.created_at) FILTER (WHERE v.estado = 'completada')        AS ultima_compra
                FROM clientes c
                LEFT JOIN ventas v ON v.cliente_id = c.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY c.id
                ORDER BY c.nombres ASC, c.apellidos ASC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // CREAR
    // ----------------------------------------------------------------
    case 'crear':
        $d       = json_decode(file_get_contents('php://input'), true);
        $nombres = trim($d['nombres'] ?? '');
        if (!$nombres) jsonResponse(['error' => true, 'message' => 'El nombre es obligatorio'], 400);

        try {
            $stmt = $db->prepare("
                INSERT INTO clientes (nombres, apellidos, dni, ruc, telefono, email, direccion)
                VALUES (:nombres, :apellidos, NULLIF(:dni,''), NULLIF(:ruc,''), NULLIF(:telefono,''), NULLIF(:email,''), NULLIF(:direccion,''))
                RETURNING id
            ");
            $stmt->execute([
                ':nombres'   => $nombres,
                ':apellidos' => trim($d['apellidos'] ?? ''),
                ':dni'       => trim($d['dni']       ?? ''),
                ':ruc'       => trim($d['ruc']       ?? ''),
                ':telefono'  => trim($d['telefono']  ?? ''),
                ':email'     => trim($d['email']     ?? ''),
                ':direccion' => trim($d['direccion'] ?? ''),
            ]);
            jsonResponse(['error' => false, 'message' => 'Cliente registrado', 'id' => $stmt->fetch()['id']]);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // ACTUALIZAR
    // ----------------------------------------------------------------
    case 'actualizar':
        $d       = json_decode(file_get_contents('php://input'), true);
        $id      = intval($d['id'] ?? 0);
        $nombres = trim($d['nombres'] ?? '');
        if (!$id || !$nombres) jsonResponse(['error' => true, 'message' => 'Datos incompletos'], 400);

        try {
            $db->prepare("
                UPDATE clientes SET
                    nombres   = :nombres,
                    apellidos = :apellidos,
                    dni       = NULLIF(:dni,''),
                    ruc       = NULLIF(:ruc,''),
                    telefono  = NULLIF(:telefono,''),
                    email     = NULLIF(:email,''),
                    direccion = NULLIF(:direccion,'')
                WHERE id = :id
            ")->execute([
                ':id'        => $id,
                ':nombres'   => $nombres,
                ':apellidos' => trim($d['apellidos'] ?? ''),
                ':dni'       => trim($d['dni']       ?? ''),
                ':ruc'       => trim($d['ruc']       ?? ''),
                ':telefono'  => trim($d['telefono']  ?? ''),
                ':email'     => trim($d['email']     ?? ''),
                ':direccion' => trim($d['direccion'] ?? ''),
            ]);
            jsonResponse(['error' => false, 'message' => 'Cliente actualizado']);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // TOGGLE ACTIVO
    // ----------------------------------------------------------------
    case 'toggle_activo':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = intval($d['id'] ?? 0);
        if (!$id) jsonResponse(['error' => true, 'message' => 'ID inválido'], 400);

        try {
            $db->prepare("UPDATE clientes SET activo = NOT activo WHERE id = :id")->execute([':id' => $id]);
            $row = $db->prepare("SELECT activo FROM clientes WHERE id = :id");
            $row->execute([':id' => $id]);
            $activo = $row->fetchColumn();
            $activo = ($activo === true || $activo === 't' || $activo === '1');
            jsonResponse(['error' => false, 'activo' => $activo, 'message' => $activo ? 'Cliente activado' : 'Cliente desactivado']);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // HISTORIAL DE COMPRAS
    // ----------------------------------------------------------------
    case 'historial':
        $id = intval($_GET['id'] ?? 0);
        if (!$id) jsonResponse(['error' => true, 'message' => 'ID inválido'], 400);

        try {
            $cStmt = $db->prepare("SELECT id, nombres, apellidos, dni, ruc, telefono, email, direccion FROM clientes WHERE id = :id");
            $cStmt->execute([':id' => $id]);
            $cliente = $cStmt->fetch();
            if (!$cliente) jsonResponse(['error' => true, 'message' => 'Cliente no encontrado'], 404);

            $vStmt = $db->prepare("
                SELECT v.id, v.numero_venta, v.total, v.estado, v.metodo_pago, v.created_at,
                       COUNT(d.id) AS num_items
                FROM ventas v
                LEFT JOIN venta_detalles d ON d.venta_id = v.id
                WHERE v.cliente_id = :id
                GROUP BY v.id
                ORDER BY v.created_at DESC
                LIMIT 50
            ");
            $vStmt->execute([':id' => $id]);
            $cliente['ventas'] = $vStmt->fetchAll();
            echo json_encode($cliente);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
