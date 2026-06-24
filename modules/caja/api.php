<?php
// ============================================================
// ARCHIVO: farmacia/modules/caja/api.php
// DESCRIPCIÓN: API REST para el módulo de Caja
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth();

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ---- GET: Estado actual de la caja ----
    case 'estado':
        $caja = $db->query("
            SELECT * FROM cajas WHERE estado = 'abierta'
            ORDER BY apertura_at DESC LIMIT 1
        ")->fetch();

        if (!$caja) {
            echo json_encode(['abierta' => false]);
            break;
        }

        // Ventas del turno
        $v = $db->prepare("
            SELECT COUNT(*) AS count, COALESCE(SUM(total), 0) AS total
            FROM ventas
            WHERE caja_id = :id AND estado = 'completada'
        ");
        $v->execute([':id' => $caja['id']]);
        $ventas = $v->fetch();

        // Movimientos adicionales
        $m = $db->prepare("
            SELECT tipo, COALESCE(SUM(monto), 0) AS total
            FROM caja_movimientos
            WHERE caja_id = :id
            GROUP BY tipo
        ");
        $m->execute([':id' => $caja['id']]);
        $ingresos = 0;
        $egresos  = 0;
        foreach ($m->fetchAll() as $row) {
            if ($row['tipo'] === 'ingreso') $ingresos = floatval($row['total']);
            else                            $egresos  = floatval($row['total']);
        }

        $saldo_esperado = floatval($caja['saldo_inicial'])
                        + floatval($ventas['total'])
                        + $ingresos
                        - $egresos;

        echo json_encode([
            'abierta'              => true,
            'caja'                 => $caja,
            'ventas_count'         => intval($ventas['count']),
            'ventas_total'         => floatval($ventas['total']),
            'ingresos_adicionales' => $ingresos,
            'egresos'              => $egresos,
            'saldo_esperado'       => $saldo_esperado,
        ]);
        break;

    // ---- GET: Usuarios sugeridos (de aperturas anteriores) ----
    case 'usuarios':
        $rows = $db->query("
            SELECT DISTINCT usuario_apertura AS nombre
            FROM cajas
            WHERE usuario_apertura IS NOT NULL AND usuario_apertura != ''
            ORDER BY usuario_apertura
        ")->fetchAll();
        echo json_encode(array_column($rows, 'nombre'));
        break;

    // ---- POST: Aperturar caja ----
    case 'aperturar':
        $data    = json_decode(file_get_contents('php://input'), true);
        $usuario = trim($data['usuario'] ?? '');
        $monto   = floatval($data['monto_inicial'] ?? 0);

        if (!$usuario) jsonResponse(['error' => true, 'message' => 'El nombre del usuario es requerido'], 400);

        // Verificar que no haya caja abierta en esta sucursal
        $abierta = $db->query("SELECT id FROM cajas WHERE estado = 'abierta' LIMIT 1")->fetch();
        if ($abierta) jsonResponse(['error' => true, 'message' => 'Ya hay una caja abierta en esta sucursal'], 409);

        // Verificar que el usuario no tenga caja abierta en OTRA sucursal
        $uid_sesion = (int)sesionId();
        if ($uid_sesion > 0) {
            try {
                $suc_chk = $db->prepare("
                    SELECT nombre, schema_name FROM public.sucursales
                    WHERE tenant_id = :tid AND activo = TRUE AND schema_name IS NOT NULL
                ");
                $suc_chk->execute([':tid' => sesionTenantId()]);
                $cur_schema = sesionSchema();

                foreach ($suc_chk->fetchAll() as $_os) {
                    if ($_os['schema_name'] === $cur_schema) continue;
                    $sq = str_replace('"', '""', $_os['schema_name']);
                    $chk = $db->query(
                        "SELECT id FROM \"{$sq}\".cajas WHERE estado = 'abierta' AND usuario_id = {$uid_sesion} LIMIT 1"
                    )->fetch();
                    if ($chk) {
                        jsonResponse([
                            'error'   => true,
                            'message' => 'Ya tienes una caja abierta en "' . $_os['nombre'] . '". Ciérrala antes de aperturar en otra sucursal.'
                        ], 409);
                    }
                }
            } catch (Throwable $_te) {}
        }

        $stmt = $db->prepare("
            INSERT INTO cajas (nombre, saldo_inicial, saldo_actual, estado, apertura_at, usuario_apertura, usuario_id)
            VALUES ('Caja Principal', :monto, :monto, 'abierta', NOW(), :usuario, :uid)
            RETURNING id
        ");
        $stmt->execute([':monto' => $monto, ':usuario' => $usuario, ':uid' => $uid_sesion ?: null]);
        $id = $stmt->fetch()['id'];
        jsonResponse(['error' => false, 'message' => 'Caja aperturada correctamente', 'id' => $id]);

    // ---- POST: Cerrar caja ----
    case 'cerrar':
        $data       = json_decode(file_get_contents('php://input'), true);
        $caja_id    = intval($data['caja_id'] ?? 0);
        $monto_real = floatval($data['monto_real'] ?? 0); // monto contado físicamente

        if (!$caja_id) jsonResponse(['error' => true, 'message' => 'ID de caja inválido'], 400);

        $caja = $db->prepare("SELECT * FROM cajas WHERE id = :id AND estado = 'abierta'");
        $caja->execute([':id' => $caja_id]);
        if (!$caja->fetch()) jsonResponse(['error' => true, 'message' => 'Caja no encontrada o ya cerrada'], 404);

        $stmt = $db->prepare("
            UPDATE cajas
            SET estado = 'cerrada', cierre_at = NOW(), saldo_actual = :monto
            WHERE id = :id
        ");
        $stmt->execute([':monto' => $monto_real, ':id' => $caja_id]);
        jsonResponse(['error' => false, 'message' => 'Caja cerrada correctamente']);

    // ---- POST: Registrar movimiento (ingreso/egreso) ----
    case 'registrar_movimiento':
        $data    = json_decode(file_get_contents('php://input'), true);
        $caja_id = intval($data['caja_id'] ?? 0);
        $tipo    = $data['tipo']    ?? '';
        $monto   = floatval($data['monto'] ?? 0);
        $concepto = trim($data['concepto'] ?? '');
        $usuario  = trim($data['usuario']  ?? 'Administrador');

        if (!$caja_id || !in_array($tipo, ['ingreso', 'egreso']) || $monto <= 0) {
            jsonResponse(['error' => true, 'message' => 'Datos inválidos'], 400);
        }
        if (!$concepto) jsonResponse(['error' => true, 'message' => 'El concepto es requerido'], 400);

        // Verificar que la caja esté abierta
        $ck = $db->prepare("SELECT id FROM cajas WHERE id = :id AND estado = 'abierta'");
        $ck->execute([':id' => $caja_id]);
        if (!$ck->fetch()) jsonResponse(['error' => true, 'message' => 'La caja no está abierta'], 409);

        $stmt = $db->prepare("
            INSERT INTO caja_movimientos (caja_id, tipo, monto, concepto, usuario)
            VALUES (:cid, :tipo, :monto, :concepto, :usuario)
        ");
        $stmt->execute([
            ':cid'     => $caja_id,
            ':tipo'    => $tipo,
            ':monto'   => $monto,
            ':concepto'=> $concepto,
            ':usuario' => $usuario,
        ]);

        // Actualizar saldo_actual en cajas
        $delta = $tipo === 'ingreso' ? $monto : -$monto;
        $db->prepare("UPDATE cajas SET saldo_actual = saldo_actual + :delta WHERE id = :id")
           ->execute([':delta' => $delta, ':id' => $caja_id]);

        jsonResponse(['error' => false, 'message' => 'Movimiento registrado']);

    // ---- GET: Movimientos de la caja actual ----
    case 'movimientos':
        $caja_id = intval($_GET['caja_id'] ?? 0);
        if (!$caja_id) { echo json_encode([]); break; }

        $stmt = $db->prepare("
            SELECT id, tipo, monto, concepto, usuario, created_at
            FROM caja_movimientos
            WHERE caja_id = :id
            ORDER BY created_at DESC
        ");
        $stmt->execute([':id' => $caja_id]);
        echo json_encode($stmt->fetchAll());
        break;

    // ---- GET: Historial de sesiones de caja ----
    case 'historial':
        $rows = $db->query("
            SELECT c.id, c.nombre, c.saldo_inicial, c.saldo_actual, c.estado,
                   c.apertura_at, c.cierre_at, c.usuario_apertura,
                   COUNT(DISTINCT v.id)    AS total_ventas,
                   COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada'), 0) AS ingresos_ventas
            FROM cajas c
            LEFT JOIN ventas v ON v.caja_id = c.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
            LIMIT 30
        ")->fetchAll();
        echo json_encode($rows);
        break;

    // ---- POST: Registrar gasto operativo ----
    case 'registrar_gasto':
        $data = json_decode(file_get_contents('php://input'), true);
        $descripcion = trim($data['descripcion'] ?? '');
        $monto       = floatval($data['monto'] ?? 0);

        if (!$descripcion) jsonResponse(['error' => true, 'message' => 'La descripción es requerida'], 400);
        if ($monto <= 0)   jsonResponse(['error' => true, 'message' => 'El monto debe ser mayor a 0'], 400);

        $caja_id = intval($data['caja_id'] ?? 0) ?: null;

        $db->beginTransaction();
        try {
            // Insertar gasto
            $stmt = $db->prepare("
                INSERT INTO gastos
                    (caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id)
                VALUES (:cid, :desc, :prov, :ncomp, :monto, :mp, :uid)
                RETURNING id
            ");
            $stmt->execute([
                ':cid'   => $caja_id,
                ':desc'  => $descripcion,
                ':prov'  => trim($data['proveedor']          ?? ''),
                ':ncomp' => trim($data['numero_comprobante'] ?? ''),
                ':monto' => $monto,
                ':mp'    => trim($data['metodo_pago'] ?? 'efectivo'),
                ':uid'   => sesionId(),
            ]);
            $gasto_id = $stmt->fetch()['id'];

            // Si hay caja abierta, registrar como egreso en caja_movimientos
            if ($caja_id) {
                $ck = $db->prepare("SELECT id FROM cajas WHERE id = :id AND estado = 'abierta'");
                $ck->execute([':id' => $caja_id]);
                if ($ck->fetch()) {
                    $concepto = "[Gasto] {$descripcion}";
                    if (!empty($data['numero_comprobante'])) {
                        $concepto .= ' · ' . $data['numero_comprobante'];
                    }
                    $db->prepare("
                        INSERT INTO caja_movimientos (caja_id, tipo, monto, concepto, usuario_id)
                        VALUES (:cid, 'egreso', :monto, :concepto, :uid)
                    ")->execute([
                        ':cid'     => $caja_id,
                        ':monto'   => $monto,
                        ':concepto'=> $concepto,
                        ':uid'     => sesionId(),
                    ]);
                    $db->prepare("UPDATE cajas SET saldo_actual = saldo_actual - :monto WHERE id = :id")
                       ->execute([':monto' => $monto, ':id' => $caja_id]);
                }
            }

            $db->commit();
            jsonResponse(['error' => false, 'message' => 'Gasto registrado correctamente', 'id' => $gasto_id]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }

    // ---- GET: Listar gastos ----
    case 'gastos_listar':
        $desde   = $_GET['desde']   ?? date('Y-m-d');
        $hasta   = $_GET['hasta']   ?? date('Y-m-d');
        $caja_id = intval($_GET['caja_id'] ?? 0);

        $where  = ['DATE(g.created_at) BETWEEN :desde AND :hasta'];
        $params = [':desde' => $desde, ':hasta' => $hasta];

        if ($caja_id) {
            $where[] = 'g.caja_id = :cid';
            $params[':cid'] = $caja_id;
        }

        $stmt = $db->prepare("
            SELECT g.id, g.descripcion, g.proveedor,
                   g.numero_comprobante, g.monto, g.metodo_pago, g.created_at
            FROM gastos g
            WHERE " . implode(' AND ', $where) . "
            ORDER BY g.created_at DESC
        ");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    // ---- GET: Resumen de gastos por categoría ----
    case 'gastos_resumen':
        $desde   = $_GET['desde']   ?? date('Y-m-01');
        $hasta   = $_GET['hasta']   ?? date('Y-m-d');

        $stmt = $db->prepare("
            SELECT COUNT(*) AS cantidad,
                   SUM(monto) AS total
            FROM gastos
            WHERE DATE(created_at) BETWEEN :desde AND :hasta
        ");
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);
        echo json_encode($stmt->fetchAll());
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
