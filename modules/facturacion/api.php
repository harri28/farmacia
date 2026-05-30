<?php
// ============================================================
// ARCHIVO: farmacia/modules/facturacion/api.php
// DESCRIPCIÓN: API REST para el módulo de Facturación / Reportes
// ============================================================

require_once '../../config/database.php';
requireApiAuth(['admin']);

$action = $_GET['action'] ?? '';
$db     = getDB();

// ---- Parámetros de filtro comunes ----
$desde     = $_GET['desde']     ?? date('Y-m-d');
$hasta     = $_GET['hasta']     ?? date('Y-m-d');
$estado    = $_GET['estado']    ?? '';
$tipo_comp = $_GET['tipo_comp'] ?? '';
$tipo_pago = $_GET['tipo_pago'] ?? '';
$vendedor    = $_GET['vendedor']    ?? '';
$categoria_id = intval($_GET['categoria_id'] ?? 0);
$q           = '%' . trim($_GET['q'] ?? '') . '%';

$hasta_dt = $hasta . ' 23:59:59';

switch ($action) {

    // ---- GET: Estadísticas del período ----
    case 'stats':
        header('Content-Type: application/json; charset=UTF-8');

        $params = [':desde' => $desde, ':hasta' => $hasta_dt];

        $where_extra = '';
        if ($estado)    { $where_extra .= " AND v.estado = :estado";              $params[':estado']    = $estado; }
        if ($tipo_comp) { $where_extra .= " AND v.tipo_comprobante = :tipo_comp"; $params[':tipo_comp'] = $tipo_comp; }
        if ($tipo_pago) { $where_extra .= " AND v.tipo_pago = :tipo_pago";        $params[':tipo_pago'] = $tipo_pago; }
        if ($vendedor)  { $where_extra .= " AND v.vendedor = :vendedor";          $params[':vendedor']  = $vendedor; }

        $stmt = $db->prepare("
            SELECT
                COUNT(*) FILTER (WHERE v.estado = 'completada')  AS total_completadas,
                COUNT(*) FILTER (WHERE v.estado = 'anulada')     AS total_anuladas,
                COALESCE(SUM(v.total)    FILTER (WHERE v.estado = 'completada'), 0) AS total_ingresos,
                COALESCE(SUM(v.igv)      FILTER (WHERE v.estado = 'completada'), 0) AS total_igv,
                COALESCE(SUM(v.descuento)FILTER (WHERE v.estado = 'completada'), 0) AS total_descuentos,
                COALESCE(AVG(v.total)    FILTER (WHERE v.estado = 'completada'), 0) AS ticket_promedio,
                COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'efectivo'),     0) AS pago_efectivo,
                COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'yape'),         0) AS pago_yape,
                COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'plin'),         0) AS pago_plin,
                COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'tarjeta'),      0) AS pago_tarjeta,
                COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'transferencia'),0) AS pago_transferencia,
                COUNT(*) FILTER (WHERE v.estado = 'completada' AND v.tipo_comprobante = 'ticket')  AS comp_ticket,
                COUNT(*) FILTER (WHERE v.estado = 'completada' AND v.tipo_comprobante = 'boleta')  AS comp_boleta,
                COUNT(*) FILTER (WHERE v.estado = 'completada' AND v.tipo_comprobante = 'factura') AS comp_factura
            FROM ventas v
            WHERE v.created_at BETWEEN :desde AND :hasta
            $where_extra
        ");
        $stmt->execute($params);
        echo json_encode($stmt->fetch());
        break;

    // ---- GET: Stats por vendedor (comparativa) ----
    case 'stats_usuario':
        header('Content-Type: application/json; charset=UTF-8');

        $params = [':desde' => $desde, ':hasta' => $hasta_dt];
        $where_extra = '';
        if ($estado)    { $where_extra .= " AND v.estado = :estado";              $params[':estado']    = $estado; }
        if ($tipo_comp) { $where_extra .= " AND v.tipo_comprobante = :tipo_comp"; $params[':tipo_comp'] = $tipo_comp; }
        if ($tipo_pago) { $where_extra .= " AND v.tipo_pago = :tipo_pago";        $params[':tipo_pago'] = $tipo_pago; }
        if ($vendedor)  { $where_extra .= " AND v.vendedor = :vendedor";          $params[':vendedor']  = $vendedor; }

        try {
            $stmt = $db->prepare("
                SELECT
                    COALESCE(v.vendedor, 'Sin asignar')                                     AS vendedor,
                    COUNT(*) FILTER (WHERE v.estado = 'completada')                         AS total_ventas,
                    COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada'), 0)        AS total_ingresos,
                    COALESCE(SUM(v.igv)   FILTER (WHERE v.estado = 'completada'), 0)        AS total_igv,
                    COALESCE(AVG(v.total) FILTER (WHERE v.estado = 'completada'), 0)        AS ticket_promedio,
                    COUNT(*) FILTER (WHERE v.estado = 'anulada')                            AS total_anuladas,
                    COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'efectivo'),     0) AS pago_efectivo,
                    COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'yape'),         0) AS pago_yape,
                    COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'plin'),         0) AS pago_plin,
                    COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'tarjeta'),      0) AS pago_tarjeta,
                    COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada' AND v.tipo_pago = 'transferencia'),0) AS pago_transferencia
                FROM ventas v
                WHERE v.created_at BETWEEN :desde AND :hasta
                $where_extra
                GROUP BY COALESCE(v.vendedor, 'Sin asignar')
                ORDER BY total_ingresos DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    // ---- GET: Lista de vendedores para el filtro ----
    case 'usuarios_lista':
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $rows = $db->query("
                SELECT DISTINCT COALESCE(vendedor, 'Sin asignar') AS vendedor
                FROM ventas
                WHERE vendedor IS NOT NULL AND vendedor != ''
                ORDER BY vendedor
            ")->fetchAll();
            echo json_encode($rows);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    // ---- GET: Reporte de ventas con filtros ----
    case 'reporte':
        header('Content-Type: application/json; charset=UTF-8');

        $params = [':desde' => $desde, ':hasta' => $hasta_dt, ':q' => $q];
        $where_extra = '';
        if ($estado)    { $where_extra .= " AND v.estado = :estado";              $params[':estado']    = $estado; }
        if ($tipo_comp) { $where_extra .= " AND v.tipo_comprobante = :tipo_comp"; $params[':tipo_comp'] = $tipo_comp; }
        if ($tipo_pago) { $where_extra .= " AND v.tipo_pago = :tipo_pago";        $params[':tipo_pago'] = $tipo_pago; }
        if ($vendedor)  { $where_extra .= " AND v.vendedor = :vendedor";          $params[':vendedor']  = $vendedor; }

        // Detectar si existe la tabla comprobantes_electronicos
        $tiene_ce = false;
        try {
            $db->query("SELECT 1 FROM comprobantes_electronicos LIMIT 0");
            $tiene_ce = true;
        } catch (Exception $e) {}

        $join_ce  = $tiene_ce ? "LEFT JOIN comprobantes_electronicos ce ON ce.venta_id = v.id" : "";
        $sel_ce   = $tiene_ce
            ? "COALESCE(ce.numero_completo, '') AS comprobante_numero,
               COALESCE(ce.estado_sunat, '')    AS estado_sunat,
               COALESCE(ce.enlace_del_pdf, '')  AS enlace_pdf"
            : "'' AS comprobante_numero, '' AS estado_sunat, '' AS enlace_pdf";
        $group_ce = $tiene_ce ? ", ce.numero_completo, ce.estado_sunat, ce.enlace_del_pdf" : "";

        try {
            $stmt = $db->prepare("
                SELECT
                    v.id,
                    v.numero_venta,
                    v.created_at,
                    v.subtotal,
                    v.descuento,
                    v.igv,
                    v.total,
                    v.tipo_pago,
                    v.tipo_comprobante,
                    v.estado,
                    COALESCE(v.vendedor, '—')                                                  AS vendedor,
                    COALESCE(c.nombres || ' ' || COALESCE(c.apellidos, ''), 'Cliente General') AS cliente,
                    COALESCE(c.dni, '') AS dni,
                    COALESCE(c.ruc, '') AS ruc,
                    COUNT(vd.id)        AS num_items,
                    {$sel_ce}
                FROM ventas v
                LEFT JOIN clientes c        ON c.id = v.cliente_id
                LEFT JOIN venta_detalles vd ON vd.venta_id = v.id
                {$join_ce}
                WHERE v.created_at BETWEEN :desde AND :hasta
                  AND (v.numero_venta ILIKE :q
                       OR COALESCE(c.nombres,  '') ILIKE :q
                       OR COALESCE(c.apellidos,'') ILIKE :q
                       OR COALESCE(c.dni,      '') ILIKE :q
                       OR COALESCE(c.ruc,      '') ILIKE :q
                       OR COALESCE(v.vendedor, '') ILIKE :q)
                  {$where_extra}
                GROUP BY v.id, v.vendedor, c.nombres, c.apellidos, c.dni, c.ruc
                         {$group_ce}
                ORDER BY v.created_at DESC
                LIMIT 1000
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Error al cargar reporte: ' . $e->getMessage()], 500);
        }
        break;

    // ---- GET: Exportar a CSV (Excel) ----
    case 'exportar':
        $params = [':desde' => $desde, ':hasta' => $hasta_dt, ':q' => $q];
        $where_extra = '';
        if ($estado)    { $where_extra .= " AND v.estado = :estado";              $params[':estado']    = $estado; }
        if ($tipo_comp) { $where_extra .= " AND v.tipo_comprobante = :tipo_comp"; $params[':tipo_comp'] = $tipo_comp; }
        if ($tipo_pago) { $where_extra .= " AND v.tipo_pago = :tipo_pago";        $params[':tipo_pago'] = $tipo_pago; }
        if ($vendedor)  { $where_extra .= " AND v.vendedor = :vendedor";          $params[':vendedor']  = $vendedor; }

        $tiene_ce_exp = false;
        try {
            $db->query("SELECT 1 FROM comprobantes_electronicos LIMIT 0");
            $tiene_ce_exp = true;
        } catch (Exception $e) {}

        $join_ce_exp  = $tiene_ce_exp ? "LEFT JOIN comprobantes_electronicos ce ON ce.venta_id = v.id" : "";
        $sel_ce_exp   = $tiene_ce_exp
            ? "COALESCE(ce.numero_completo, '') AS \"N° Comprobante\",
               COALESCE(ce.estado_sunat, '')    AS \"Estado SUNAT\","
            : "'' AS \"N° Comprobante\", '' AS \"Estado SUNAT\",";
        $group_ce_exp = $tiene_ce_exp ? ", ce.numero_completo, ce.estado_sunat" : "";

        $stmt = $db->prepare("
            SELECT
                v.numero_venta                                                              AS \"N° Venta\",
                TO_CHAR(v.created_at, 'DD/MM/YYYY')                                        AS \"Fecha\",
                TO_CHAR(v.created_at, 'HH24:MI')                                           AS \"Hora\",
                COALESCE(v.vendedor, 'Sin asignar')                                        AS \"Vendedor\",
                COALESCE(c.nombres || ' ' || COALESCE(c.apellidos,''), 'Cliente General')  AS \"Cliente\",
                COALESCE(c.dni, '')                                                         AS \"DNI\",
                COALESCE(c.ruc, '')                                                         AS \"RUC\",
                COUNT(vd.id)                                                                AS \"N° Items\",
                UPPER(v.tipo_pago)                                                          AS \"Método Pago\",
                UPPER(v.tipo_comprobante)                                                   AS \"Comprobante\",
                {$sel_ce_exp}
                ROUND(v.subtotal::numeric, 2)                                               AS \"Subtotal\",
                ROUND(v.descuento::numeric, 2)                                              AS \"Descuento\",
                ROUND(v.igv::numeric, 2)                                                    AS \"IGV\",
                ROUND(v.total::numeric, 2)                                                  AS \"Total\",
                UPPER(v.estado)                                                             AS \"Estado\"
            FROM ventas v
            LEFT JOIN clientes c        ON c.id = v.cliente_id
            LEFT JOIN venta_detalles vd ON vd.venta_id = v.id
            {$join_ce_exp}
            WHERE v.created_at BETWEEN :desde AND :hasta
              AND (v.numero_venta ILIKE :q
                   OR COALESCE(c.nombres,  '') ILIKE :q
                   OR COALESCE(c.apellidos,'') ILIKE :q
                   OR COALESCE(c.dni,      '') ILIKE :q
                   OR COALESCE(c.ruc,      '') ILIKE :q
                   OR COALESCE(v.vendedor, '') ILIKE :q)
              {$where_extra}
            GROUP BY v.id, v.vendedor, c.nombres, c.apellidos, c.dni, c.ruc
                     {$group_ce_exp}
            ORDER BY v.created_at ASC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = 'ventas_' . str_replace('-', '', $desde) . '_' . str_replace('-', '', $hasta) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]), ';');
            foreach ($rows as $row) {
                fputcsv($out, array_values($row), ';');
            }
        } else {
            fputcsv($out, ['Sin resultados para los filtros aplicados'], ';');
        }

        fclose($out);
        exit;

    // ---- GET: Categorías para filtro ----
    case 'categorias_lista':
        header('Content-Type: application/json; charset=UTF-8');
        try {
            echo json_encode($db->query(
                "SELECT id, nombre FROM categorias WHERE activo = TRUE ORDER BY nombre"
            )->fetchAll());
        } catch (Exception $e) { echo json_encode([]); }
        break;

    // ---- GET: Stats globales de rentabilidad ----
    case 'rentabilidad_stats':
        header('Content-Type: application/json; charset=UTF-8');

        $params = [':desde' => $desde, ':hasta' => $hasta_dt];
        $where  = '';
        if ($vendedor)     { $where .= " AND v.vendedor = :vendedor";       $params[':vendedor']     = $vendedor; }
        if ($categoria_id) { $where .= " AND p.categoria_id = :cat_id";    $params[':cat_id']       = $categoria_id; }

        try {
            $stmt = $db->prepare("
                SELECT
                    COALESCE(SUM(vd.subtotal), 0)                                                       AS total_ingresos,
                    COALESCE(SUM(vd.cantidad * p.precio_compra), 0)                                     AS total_costo,
                    COALESCE(SUM(vd.subtotal - vd.cantidad * p.precio_compra), 0)                       AS ganancia_bruta,
                    COALESCE(SUM(vd.cantidad), 0)                                                       AS total_unidades,
                    COUNT(DISTINCT v.id)                                                                AS total_ventas,
                    ROUND(COALESCE(
                        SUM(vd.subtotal - vd.cantidad * p.precio_compra) / NULLIF(SUM(vd.subtotal), 0) * 100
                    , 0)::numeric, 2)                                                                   AS margen_pct,
                    ROUND(COALESCE(
                        SUM(vd.subtotal - vd.cantidad * p.precio_compra) / NULLIF(SUM(vd.cantidad * p.precio_compra), 0) * 100
                    , 0)::numeric, 2)                                                                   AS roi_pct
                FROM ventas v
                JOIN venta_detalles vd ON vd.venta_id = v.id
                JOIN productos p       ON p.id = vd.producto_id
                WHERE v.estado = 'completada'
                  AND v.created_at BETWEEN :desde AND :hasta
                $where
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetch());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    // ---- GET: Rentabilidad por categoría ----
    case 'rentabilidad_categorias':
        header('Content-Type: application/json; charset=UTF-8');

        $params = [':desde' => $desde, ':hasta' => $hasta_dt];
        $where  = '';
        if ($vendedor)     { $where .= " AND v.vendedor = :vendedor";    $params[':vendedor'] = $vendedor; }
        if ($categoria_id) { $where .= " AND p.categoria_id = :cat_id"; $params[':cat_id']   = $categoria_id; }

        try {
            $stmt = $db->prepare("
                SELECT
                    COALESCE(cat.nombre, 'Sin categoría')                                               AS categoria,
                    COUNT(DISTINCT v.id)                                                                AS num_ventas,
                    COALESCE(SUM(vd.cantidad), 0)                                                       AS unidades,
                    ROUND(COALESCE(SUM(vd.subtotal), 0)::numeric, 2)                                    AS ingresos,
                    ROUND(COALESCE(SUM(vd.cantidad * p.precio_compra), 0)::numeric, 2)                  AS costo,
                    ROUND(COALESCE(SUM(vd.subtotal - vd.cantidad * p.precio_compra), 0)::numeric, 2)    AS ganancia,
                    ROUND(COALESCE(
                        SUM(vd.subtotal - vd.cantidad * p.precio_compra) / NULLIF(SUM(vd.subtotal), 0) * 100
                    , 0)::numeric, 2)                                                                   AS margen_pct
                FROM ventas v
                JOIN venta_detalles vd ON vd.venta_id = v.id
                JOIN productos p       ON p.id = vd.producto_id
                LEFT JOIN categorias cat ON cat.id = p.categoria_id
                WHERE v.estado = 'completada'
                  AND v.created_at BETWEEN :desde AND :hasta
                $where
                GROUP BY COALESCE(cat.nombre, 'Sin categoría')
                ORDER BY ganancia DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    // ---- GET: Rentabilidad por producto ----
    case 'rentabilidad_productos':
        header('Content-Type: application/json; charset=UTF-8');

        $params = [':desde' => $desde, ':hasta' => $hasta_dt];
        $where  = '';
        if ($vendedor)     { $where .= " AND v.vendedor = :vendedor";    $params[':vendedor'] = $vendedor; }
        if ($categoria_id) { $where .= " AND p.categoria_id = :cat_id"; $params[':cat_id']   = $categoria_id; }

        try {
            $stmt = $db->prepare("
                SELECT
                    p.id,
                    p.nombre                                                                            AS producto,
                    p.codigo,
                    p.precio_compra                                                                     AS costo_unitario,
                    COALESCE(cat.nombre, 'Sin categoría')                                               AS categoria,
                    COALESCE(SUM(vd.cantidad), 0)                                                       AS unidades,
                    ROUND(COALESCE(SUM(vd.subtotal), 0)::numeric, 2)                                    AS ingresos,
                    ROUND(COALESCE(SUM(vd.cantidad * p.precio_compra), 0)::numeric, 2)                  AS costo,
                    ROUND(COALESCE(SUM(vd.subtotal - vd.cantidad * p.precio_compra), 0)::numeric, 2)    AS ganancia,
                    ROUND(COALESCE(
                        SUM(vd.subtotal - vd.cantidad * p.precio_compra) / NULLIF(SUM(vd.subtotal), 0) * 100
                    , 0)::numeric, 2)                                                                   AS margen_pct
                FROM ventas v
                JOIN venta_detalles vd ON vd.venta_id = v.id
                JOIN productos p       ON p.id = vd.producto_id
                LEFT JOIN categorias cat ON cat.id = p.categoria_id
                WHERE v.estado = 'completada'
                  AND v.created_at BETWEEN :desde AND :hasta
                $where
                GROUP BY p.id, p.nombre, p.codigo, p.precio_compra, cat.nombre
                ORDER BY ganancia DESC
                LIMIT 500
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    // ---- GET: Tendencia diaria de ganancia ----
    case 'rentabilidad_tendencia':
        header('Content-Type: application/json; charset=UTF-8');

        $params = [':desde' => $desde, ':hasta' => $hasta_dt];
        $where  = '';
        if ($vendedor)     { $where .= " AND v.vendedor = :vendedor";    $params[':vendedor'] = $vendedor; }
        if ($categoria_id) { $where .= " AND p.categoria_id = :cat_id"; $params[':cat_id']   = $categoria_id; }

        // Agrupar por día o semana según el rango
        $dias = max(1, (strtotime($hasta) - strtotime($desde)) / 86400);
        $trunc = $dias > 62 ? 'week' : 'day';

        try {
            $stmt = $db->prepare("
                SELECT
                    DATE_TRUNC('$trunc', v.created_at)::date                                            AS fecha,
                    ROUND(SUM(vd.subtotal)::numeric, 2)                                                 AS ingresos,
                    ROUND(SUM(vd.cantidad * p.precio_compra)::numeric, 2)                               AS costo,
                    ROUND(SUM(vd.subtotal - vd.cantidad * p.precio_compra)::numeric, 2)                 AS ganancia
                FROM ventas v
                JOIN venta_detalles vd ON vd.venta_id = v.id
                JOIN productos p       ON p.id = vd.producto_id
                WHERE v.estado = 'completada'
                  AND v.created_at BETWEEN :desde AND :hasta
                $where
                GROUP BY DATE_TRUNC('$trunc', v.created_at)::date
                ORDER BY fecha ASC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    default:
        header('Content-Type: application/json; charset=UTF-8');
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
