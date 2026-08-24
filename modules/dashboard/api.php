<?php
// ============================================================
// ARCHIVO: farmacia/modules/dashboard/api.php
// DESCRIPCIÓN: API REST para el módulo de Dashboard
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin', 'gerente', 'cajero']);

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ---- GET: Resumen general del día ----
    case 'resumen':
        $hoy = date('Y-m-d');

        // Stats ventas hoy
        $ventas = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE estado = 'completada') AS total_ventas,
                COALESCE(SUM(total) FILTER (WHERE estado = 'completada'), 0) AS ingresos,
                COALESCE(AVG(total) FILTER (WHERE estado = 'completada'), 0) AS ticket_promedio,
                COUNT(*) FILTER (WHERE estado = 'anulada') AS anuladas
            FROM ventas
            WHERE DATE(created_at) = '$hoy'
        ")->fetch();

        // Stats inventario
        $inventario = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE activo = TRUE)                                         AS total_activos,
                COUNT(*) FILTER (WHERE activo = TRUE AND stock = 0)                           AS agotados,
                COUNT(*) FILTER (WHERE activo = TRUE AND stock > 0 AND stock <= stock_minimo) AS stock_bajo,
                COALESCE(SUM(stock * precio_compra) FILTER (WHERE activo = TRUE), 0)          AS valor_inventario
            FROM productos
        ")->fetch();

        // Stats almacén (mes actual)
        $mes_inicio = date('Y-m-01');
        $mes_fin    = date('Y-m-d');
        $almacen = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE estado = 'completado'
                    AND DATE(created_at) BETWEEN '$mes_inicio' AND '$mes_fin') AS ingresos_mes,
                COALESCE(SUM(total) FILTER (WHERE estado = 'completado'
                    AND DATE(created_at) BETWEEN '$mes_inicio' AND '$mes_fin'), 0) AS valor_mes,
                (SELECT COUNT(*) FROM proveedores WHERE activo = TRUE) AS proveedores_activos
            FROM ingresos
        ")->fetch();

        // Estado caja (del usuario en sesión)
        $caja = $db->prepare("SELECT * FROM cajas WHERE estado = 'abierta' AND usuario_id = :uid ORDER BY apertura_at DESC LIMIT 1");
        $caja->execute([':uid' => (int) sesionId()]);
        $caja = $caja->fetch();
        $caja_info = ['abierta' => false];
        if ($caja) {
            $v = $db->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(total), 0) AS total FROM ventas WHERE caja_id = :id AND estado = 'completada'");
            $v->execute([':id' => $caja['id']]);
            $cv = $v->fetch();
            $caja_info = [
                'abierta'        => true,
                'ventas_count'   => intval($cv['cnt']),
                'ventas_total'   => floatval($cv['total']),
                'saldo_inicial'  => floatval($caja['saldo_inicial']),
                'saldo_esperado' => floatval($caja['saldo_inicial']) + floatval($cv['total']),
                'apertura_at'    => $caja['apertura_at'],
                'responsable'    => $caja['usuario_apertura'],
            ];
        }

        echo json_encode([
            'ventas'     => $ventas,
            'inventario' => $inventario,
            'almacen'    => $almacen,
            'caja'       => $caja_info,
        ]);
        break;

    // ---- GET: Ventas agrupadas por día (últimos N días) ----
    case 'ventas_semana':
        $dias   = min(intval($_GET['dias'] ?? 7), 30);
        $result = $db->query("
            SELECT
                DATE(created_at) AS fecha,
                COUNT(*) FILTER (WHERE estado = 'completada') AS total_ventas,
                COALESCE(SUM(total) FILTER (WHERE estado = 'completada'), 0) AS ingresos
            FROM ventas
            WHERE DATE(created_at) >= CURRENT_DATE - INTERVAL '$dias days'
            GROUP BY DATE(created_at)
            ORDER BY fecha ASC
        ")->fetchAll();

        // Fill missing days with zeros
        $map = [];
        foreach ($result as $r) $map[$r['fecha']] = $r;
        $series = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $series[] = [
                'fecha'        => $fecha,
                'total_ventas' => intval($map[$fecha]['total_ventas'] ?? 0),
                'ingresos'     => floatval($map[$fecha]['ingresos']   ?? 0),
            ];
        }
        echo json_encode($series);
        break;

    // ---- GET: Ventas por método de pago (hoy o semana) ----
    case 'ventas_metodo_pago':
        $periodo = $_GET['periodo'] ?? 'hoy';
        $where   = $periodo === 'semana'
            ? "DATE(created_at) >= CURRENT_DATE - INTERVAL '7 days'"
            : "DATE(created_at) = CURRENT_DATE";

        $rows = $db->query("
            SELECT tipo_pago, COUNT(*) AS total_ventas, COALESCE(SUM(total), 0) AS monto
            FROM ventas
            WHERE estado = 'completada' AND $where
            GROUP BY tipo_pago
            ORDER BY monto DESC
        ")->fetchAll();
        echo json_encode($rows);
        break;

    // ---- GET: Productos con stock bajo o agotado ----
    case 'stock_alertas':
        $rows = $db->query("
            SELECT p.id, p.codigo, p.nombre, p.laboratorio,
                   p.stock, p.stock_minimo, c.nombre AS categoria,
                   CASE
                       WHEN p.stock = 0 THEN 'agotado'
                       ELSE 'bajo'
                   END AS alerta
            FROM productos p
            LEFT JOIN public.categorias c ON c.id = p.categoria_id
            WHERE p.activo = TRUE AND p.stock <= p.stock_minimo
            ORDER BY p.stock ASC, p.nombre ASC
            LIMIT 20
        ")->fetchAll();
        echo json_encode($rows);
        break;

    // ---- GET: Top 5 productos más vendidos ----
    case 'top_vendidos':
        $rows = $db->query("
            SELECT p.id, p.nombre, p.laboratorio, p.total_vendido,
                   p.precio_venta, c.nombre AS categoria
            FROM productos p
            LEFT JOIN public.categorias c ON c.id = p.categoria_id
            WHERE p.activo = TRUE AND p.total_vendido > 0
            ORDER BY p.total_vendido DESC
            LIMIT 5
        ")->fetchAll();
        echo json_encode($rows);
        break;

    // ---- GET: Últimas ventas del día ----
    case 'ultimas_ventas':
        $rows = $db->query("
            SELECT v.numero_venta, v.total, v.tipo_pago, v.tipo_comprobante, v.estado, v.created_at,
                   COALESCE(c.nombres || ' ' || COALESCE(c.apellidos,''), 'Cliente General') AS cliente
            FROM ventas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            WHERE DATE(v.created_at) = CURRENT_DATE
            ORDER BY v.created_at DESC
            LIMIT 8
        ")->fetchAll();
        echo json_encode($rows);
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
