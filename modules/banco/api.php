<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin', 'gerente', 'cajero']);

$db     = getDB();
$action = $_GET['action'] ?? '';

switch ($action) {

    // ── Listar cuentas del tenant ────────────────────────────────
    case 'cuentas_listar':
        $stmt = $db->prepare("
            SELECT id, nombre_cuenta, banco, tipo_cuenta, numero_cuenta, saldo, activo, imagen_path
            FROM public.cuentas_banco
            WHERE tenant_id = :tid AND activo = TRUE
            ORDER BY nombre_cuenta ASC
        ");
        $stmt->execute([':tid' => sesionTenantId()]);
        echo json_encode($stmt->fetchAll());
        break;

    // ── Crear cuenta ─────────────────────────────────────────────
    case 'cuenta_crear':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre_cuenta'])) {
            jsonResponse(['error' => true, 'message' => 'El nombre de la cuenta es obligatorio'], 400);
        }
        $stmt = $db->prepare("
            INSERT INTO public.cuentas_banco (tenant_id, nombre_cuenta, banco, tipo_cuenta, numero_cuenta, saldo)
            VALUES (:tid, :nombre, :banco, :tipo, :numero, :saldo)
            RETURNING id
        ");
        $stmt->execute([
            ':tid'    => sesionTenantId(),
            ':nombre' => trim($data['nombre_cuenta']),
            ':banco'  => trim($data['banco'] ?? ''),
            ':tipo'   => $data['tipo_cuenta'] ?? 'ahorros',
            ':numero' => trim($data['numero_cuenta'] ?? ''),
            ':saldo'  => floatval($data['saldo_inicial'] ?? 0),
        ]);
        $id = $stmt->fetchColumn();
        if (!empty($data['saldo_inicial']) && floatval($data['saldo_inicial']) > 0) {
            $db->prepare("
                INSERT INTO public.banco_movimientos (cuenta_banco_id, sucursal_id, fecha, beneficiario, tipo, concepto, monto)
                VALUES (:cid, :sid, CURRENT_DATE, :ben, 'ingreso', 'Saldo inicial', :monto)
            ")->execute([
                ':cid'   => $id,
                ':sid'   => sesionSucursalId(),
                ':ben'   => trim($data['nombre_cuenta']),
                ':monto' => floatval($data['saldo_inicial']),
            ]);
        }
        jsonResponse(['success' => true, 'id' => $id]);
        break;

    // ── Actualizar cuenta ────────────────────────────────────────
    case 'cuenta_actualizar':
        $data = json_decode(file_get_contents('php://input'), true);
        $cid  = intval($data['id'] ?? 0);
        if (!$cid || empty($data['nombre_cuenta'])) {
            jsonResponse(['error' => true, 'message' => 'Datos incompletos'], 400);
        }
        $db->prepare("
            UPDATE public.cuentas_banco
            SET nombre_cuenta = :nombre,
                banco         = :banco,
                tipo_cuenta   = :tipo,
                numero_cuenta = :numero
            WHERE id = :id AND tenant_id = :tid
        ")->execute([
            ':nombre' => trim($data['nombre_cuenta']),
            ':banco'  => trim($data['banco']         ?? ''),
            ':tipo'   => $data['tipo_cuenta']         ?? 'ahorros',
            ':numero' => trim($data['numero_cuenta']  ?? ''),
            ':id'     => $cid,
            ':tid'    => sesionTenantId(),
        ]);
        jsonResponse(['success' => true]);
        break;

    // ── Subir imagen de cuenta ───────────────────────────────────
    case 'cuenta_imagen_subir':
        $cid = intval($_POST['cuenta_id'] ?? 0);
        if (!$cid || empty($_FILES['imagen']['tmp_name'])) {
            jsonResponse(['error' => true, 'message' => 'Datos incompletos'], 400);
        }
        $file         = $_FILES['imagen'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowedMimes)) {
            jsonResponse(['error' => true, 'message' => 'Tipo de archivo no permitido (usa PNG, JPG o WebP)'], 400);
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            jsonResponse(['error' => true, 'message' => 'La imagen no debe superar 2 MB'], 400);
        }
        $dir = __DIR__ . '/img/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'banco_' . $cid . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            jsonResponse(['error' => true, 'message' => 'No se pudo guardar la imagen'], 500);
        }
        $path = 'modules/banco/img/' . $filename;
        $db->prepare("UPDATE public.cuentas_banco SET imagen_path = :p WHERE id = :id AND tenant_id = :tid")
           ->execute([':p' => $path, ':id' => $cid, ':tid' => sesionTenantId()]);
        jsonResponse(['success' => true, 'path' => $path]);
        break;

    // ── Detalle de cuenta ────────────────────────────────────────
    case 'cuenta_detalle':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $db->prepare("
            SELECT id, nombre_cuenta, banco, tipo_cuenta, numero_cuenta, saldo, imagen_path
            FROM public.cuentas_banco
            WHERE id = :id AND tenant_id = :tid
        ");
        $stmt->execute([':id' => $id, ':tid' => sesionTenantId()]);
        $cuenta = $stmt->fetch();
        if (!$cuenta) jsonResponse(['error' => true, 'message' => 'Cuenta no encontrada'], 404);

        // Balance del mes actual
        $bal = $db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) AS total_ingresos,
                COALESCE(SUM(CASE WHEN tipo = 'egreso'  THEN monto ELSE 0 END), 0) AS total_egresos
            FROM public.banco_movimientos
            WHERE cuenta_banco_id = :cid
              AND DATE_TRUNC('month', fecha) = DATE_TRUNC('month', CURRENT_DATE)
        ");
        $bal->execute([':cid' => $id]);
        $balance = $bal->fetch();
        echo json_encode(array_merge($cuenta, $balance));
        break;

    // ── Movimientos de una cuenta ────────────────────────────────
    case 'movimientos_listar':
        $id     = intval($_GET['id'] ?? 0);
        $desde  = $_GET['desde']  ?? date('Y-m-01');
        $hasta  = $_GET['hasta']  ?? date('Y-m-d');
        $tipo   = $_GET['tipo']   ?? '';

        $where  = ['m.cuenta_banco_id = :cid', 'm.fecha BETWEEN :desde AND :hasta'];
        $params = [':cid' => $id, ':desde' => $desde, ':hasta' => $hasta];

        if ($tipo) { $where[] = 'm.tipo = :tipo'; $params[':tipo'] = $tipo; }

        $stmt = $db->prepare("
            SELECT m.id, m.fecha, m.beneficiario, m.numero_comprobante,
                   m.tipo, m.concepto, m.monto, s.nombre AS sucursal
            FROM public.banco_movimientos m
            LEFT JOIN public.sucursales s ON s.id = m.sucursal_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY m.fecha DESC, m.id DESC
        ");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    // ── Gráfico mensual (últimos 6 meses) ───────────────────────
    case 'grafico_mensual':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $db->prepare("
            SELECT
                TO_CHAR(DATE_TRUNC('month', fecha), 'Mon') AS mes,
                COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) AS ingresos,
                COALESCE(SUM(CASE WHEN tipo = 'egreso'  THEN monto ELSE 0 END), 0) AS egresos
            FROM public.banco_movimientos
            WHERE cuenta_banco_id = :cid
              AND fecha >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '5 months'
            GROUP BY DATE_TRUNC('month', fecha)
            ORDER BY DATE_TRUNC('month', fecha) ASC
        ");
        $stmt->execute([':cid' => $id]);
        echo json_encode($stmt->fetchAll());
        break;

    // ── Registrar movimiento ─────────────────────────────────────
    case 'movimiento_crear':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['cuenta_banco_id']) || empty($data['monto']) || empty($data['tipo'])) {
            jsonResponse(['error' => true, 'message' => 'Datos incompletos'], 400);
        }
        $cid   = intval($data['cuenta_banco_id']);
        $monto = floatval($data['monto']);
        $tipo  = $data['tipo'] === 'egreso' ? 'egreso' : 'ingreso';

        $db->beginTransaction();
        try {
            $db->prepare("
                INSERT INTO public.banco_movimientos
                    (cuenta_banco_id, sucursal_id, fecha, beneficiario, numero_comprobante, tipo, concepto, monto)
                VALUES (:cid, :sid, :fecha, :ben, :nro, :tipo, :concepto, :monto)
            ")->execute([
                ':cid'     => $cid,
                ':sid'     => sesionSucursalId(),
                ':fecha'   => $data['fecha']               ?? date('Y-m-d'),
                ':ben'     => trim($data['beneficiario']   ?? ''),
                ':nro'     => trim($data['numero_comprobante'] ?? ''),
                ':tipo'    => $tipo,
                ':concepto'=> trim($data['concepto']       ?? ''),
                ':monto'   => $monto,
            ]);
            $delta = $tipo === 'ingreso' ? $monto : -$monto;
            $db->prepare("
                UPDATE public.cuentas_banco SET saldo = saldo + :delta WHERE id = :id
            ")->execute([':delta' => $delta, ':id' => $cid]);
            $db->commit();
            jsonResponse(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 400);
}
