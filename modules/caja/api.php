<?php
// ============================================================
// ARCHIVO: farmacia/modules/caja/api.php
// DESCRIPCIÓN: API REST para el módulo de Caja
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';

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

        // Verificar que no haya caja abierta
        $abierta = $db->query("SELECT id FROM cajas WHERE estado = 'abierta' LIMIT 1")->fetch();
        if ($abierta) jsonResponse(['error' => true, 'message' => 'Ya hay una caja abierta'], 409);

        $stmt = $db->prepare("
            INSERT INTO cajas (nombre, saldo_inicial, saldo_actual, estado, apertura_at, usuario_apertura)
            VALUES ('Caja Principal', :monto, :monto, 'abierta', NOW(), :usuario)
            RETURNING id
        ");
        $stmt->execute([':monto' => $monto, ':usuario' => $usuario]);
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

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
