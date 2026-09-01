<?php
// ============================================================
// ARCHIVO: farmacia/modules/reportes/api.php
// DESCRIPCION: API REST para el modulo de Reportes (solo admin/gerente)
//              Costos de compras, ventas por periodo/vendedor,
//              valorizacion de inventario y movimientos de caja.
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin', 'gerente']);

$action = $_GET['action'] ?? '';
$db     = getDB();

function reportesRangoFechas(): array
{
    $desde = $_GET['desde'] ?? date('Y-m-01');
    $hasta = $_GET['hasta'] ?? date('Y-m-d');
    return [$desde, $hasta . ' 23:59:59'];
}

function reportesCostosComprasFiltro(): array
{
    [$desde, $hastaDt] = reportesRangoFechas();
    $proveedorId = intval($_GET['proveedor_id'] ?? 0);
    $estado      = trim((string) ($_GET['estado'] ?? ''));

    $where  = "WHERE oc.created_at BETWEEN :desde AND :hasta";
    $params = [':desde' => $desde, ':hasta' => $hastaDt];
    if ($proveedorId) { $where .= " AND oc.proveedor_id = :proveedor_id"; $params[':proveedor_id'] = $proveedorId; }
    if ($estado !== '') { $where .= " AND oc.estado = :estado"; $params[':estado'] = $estado; }

    return [$where, $params];
}

function reportesCajaMovimientosFiltro(): array
{
    [$desde, $hastaDt] = reportesRangoFechas();
    $tipo = trim((string) ($_GET['tipo'] ?? ''));

    $where  = "WHERE cm.created_at BETWEEN :desde AND :hasta";
    $params = [':desde' => $desde, ':hasta' => $hastaDt];
    if ($tipo !== '') { $where .= " AND cm.tipo = :tipo"; $params[':tipo'] = $tipo; }

    return [$where, $params];
}

function reportesVentasAgrupacion(string $agrupar): array
{
    switch ($agrupar) {
        case 'dia':
            return ["TO_CHAR(v.created_at, 'YYYY-MM-DD')", 'Fecha'];
        case 'comprobante':
            return ['v.tipo_comprobante', 'Comprobante'];
        default:
            return ["COALESCE(v.vendedor, 'Sin asignar')", 'Vendedor'];
    }
}

function reportesInventarioFiltro(): array
{
    $categoriaId = intval($_GET['categoria_id'] ?? 0);
    $soloActivos = ($_GET['solo_activos'] ?? '1') !== '0';
    $q           = '%' . trim((string) ($_GET['q'] ?? '')) . '%';

    $where  = "WHERE (p.nombre ILIKE :q OR p.codigo ILIKE :q OR COALESCE(p.codigo_interno,'') ILIKE :q)";
    $params = [':q' => $q];
    if ($categoriaId) { $where .= " AND p.categoria_id = :categoria_id"; $params[':categoria_id'] = $categoriaId; }
    if ($soloActivos) { $where .= " AND p.activo = TRUE"; }

    return [$where, $params];
}

function reportesCsvOutput(string $filenameBase, array $rows): void
{
    $filename = $filenameBase . '_' . date('Ymd_His') . '.csv';

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
}

switch ($action) {

    // ----------------------------------------------------------------
    // COSTOS DE COMPRAS
    // ----------------------------------------------------------------
    case 'costos_compras':
        try {
            [$where, $params] = reportesCostosComprasFiltro();
            $stmt = $db->prepare("
                SELECT oc.id, oc.numero_orden, oc.estado, oc.created_at,
                       oc.subtotal, oc.igv, oc.costo_envio, oc.total,
                       COALESCE(p.razon_social, 'Sin proveedor') AS proveedor
                FROM ordenes_compra oc
                LEFT JOIN proveedores p ON p.id = oc.proveedor_id
                $where
                ORDER BY oc.created_at DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Error al cargar el reporte: ' . $e->getMessage()], 500);
        }
        break;

    case 'costos_compras_stats':
        try {
            [$where, $params] = reportesCostosComprasFiltro();
            $stmt = $db->prepare("
                SELECT
                    COUNT(*)                          AS total_ordenes,
                    COALESCE(SUM(oc.subtotal), 0)     AS total_subtotal,
                    COALESCE(SUM(oc.igv), 0)          AS total_igv,
                    COALESCE(SUM(oc.costo_envio), 0)  AS total_envio,
                    COALESCE(SUM(oc.total), 0)        AS total_general
                FROM ordenes_compra oc
                $where
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetch());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'costos_compras_exportar':
        [$where, $params] = reportesCostosComprasFiltro();
        $stmt = $db->prepare("
            SELECT
                oc.numero_orden                                  AS \"N° Orden\",
                TO_CHAR(oc.created_at, 'DD/MM/YYYY')            AS \"Fecha\",
                COALESCE(p.razon_social, 'Sin proveedor')        AS \"Proveedor\",
                UPPER(oc.estado)                                 AS \"Estado\",
                ROUND(oc.subtotal::numeric, 2)                   AS \"Subtotal\",
                ROUND(oc.igv::numeric, 2)                        AS \"IGV\",
                ROUND(oc.costo_envio::numeric, 2)                AS \"Costo Envío\",
                ROUND(oc.total::numeric, 2)                      AS \"Total\"
            FROM ordenes_compra oc
            LEFT JOIN proveedores p ON p.id = oc.proveedor_id
            $where
            ORDER BY oc.created_at ASC
        ");
        $stmt->execute($params);
        reportesCsvOutput('costos_compras', $stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'proveedores_lista':
        try {
            $rows = $db->query("
                SELECT id, razon_social
                FROM proveedores
                WHERE activo = TRUE
                ORDER BY razon_social
            ")->fetchAll();
            echo json_encode($rows);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    // ----------------------------------------------------------------
    // VENTAS POR PERIODO / VENDEDOR
    // ----------------------------------------------------------------
    case 'ventas_reporte':
        try {
            [$desde, $hastaDt] = reportesRangoFechas();
            $agrupar = trim((string) ($_GET['agrupar'] ?? 'vendedor'));
            [$groupExpr, ] = reportesVentasAgrupacion($agrupar);

            $stmt = $db->prepare("
                SELECT
                    $groupExpr                                                          AS etiqueta,
                    COUNT(*) FILTER (WHERE v.estado = 'completada')                     AS total_ventas,
                    COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada'), 0)    AS total_ingresos,
                    COALESCE(SUM(v.igv)   FILTER (WHERE v.estado = 'completada'), 0)    AS total_igv,
                    COALESCE(AVG(v.total) FILTER (WHERE v.estado = 'completada'), 0)    AS ticket_promedio,
                    COUNT(*) FILTER (WHERE v.estado = 'anulada')                        AS total_anuladas
                FROM ventas v
                WHERE v.created_at BETWEEN :desde AND :hasta
                GROUP BY $groupExpr
                ORDER BY total_ingresos DESC
            ");
            $stmt->execute([':desde' => $desde, ':hasta' => $hastaDt]);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'ventas_exportar':
        [$desde, $hastaDt] = reportesRangoFechas();
        $agrupar = trim((string) ($_GET['agrupar'] ?? 'vendedor'));
        [$groupExpr, $groupLabel] = reportesVentasAgrupacion($agrupar);

        $stmt = $db->prepare("
            SELECT
                $groupExpr                                                                    AS \"$groupLabel\",
                COUNT(*) FILTER (WHERE v.estado = 'completada')                               AS \"N° Ventas\",
                ROUND(COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada'), 0)::numeric, 2) AS \"Total Ingresos\",
                ROUND(COALESCE(SUM(v.igv)   FILTER (WHERE v.estado = 'completada'), 0)::numeric, 2) AS \"Total IGV\",
                ROUND(COALESCE(AVG(v.total) FILTER (WHERE v.estado = 'completada'), 0)::numeric, 2) AS \"Ticket Promedio\",
                COUNT(*) FILTER (WHERE v.estado = 'anulada')                                  AS \"Anuladas\"
            FROM ventas v
            WHERE v.created_at BETWEEN :desde AND :hasta
            GROUP BY $groupExpr
            ORDER BY \"Total Ingresos\" DESC
        ");
        $stmt->execute([':desde' => $desde, ':hasta' => $hastaDt]);
        reportesCsvOutput('ventas', $stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ----------------------------------------------------------------
    // VALORIZACION DE INVENTARIO
    // ----------------------------------------------------------------
    case 'inventario_valorizacion':
        try {
            [$where, $params] = reportesInventarioFiltro();
            $stmt = $db->prepare("
                SELECT p.id, p.codigo, p.nombre, COALESCE(cat.nombre, 'Sin categoría') AS categoria,
                       p.stock, p.stock_minimo,
                       COALESCE(p.precio_compra, 0) AS precio_compra,
                       p.precio_venta,
                       ROUND((p.stock * COALESCE(p.precio_compra, 0))::numeric, 2) AS valor_inventario,
                       p.activo
                FROM productos p
                LEFT JOIN categorias cat ON cat.id = p.categoria_id
                $where
                ORDER BY valor_inventario DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'inventario_valorizacion_stats':
        try {
            [$where, $params] = reportesInventarioFiltro();
            $stmt = $db->prepare("
                SELECT
                    COUNT(*)                                                              AS total_productos,
                    COALESCE(SUM(p.stock * COALESCE(p.precio_compra, 0)), 0)               AS valor_total_compra,
                    COALESCE(SUM(p.stock * p.precio_venta), 0)                             AS valor_total_venta,
                    COUNT(*) FILTER (WHERE p.stock = 0)                                     AS agotados,
                    COUNT(*) FILTER (WHERE p.stock > 0 AND p.stock <= p.stock_minimo)       AS stock_bajo
                FROM productos p
                $where
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetch());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'inventario_valorizacion_exportar':
        [$where, $params] = reportesInventarioFiltro();
        $stmt = $db->prepare("
            SELECT
                p.codigo                                                          AS \"Código\",
                p.nombre                                                          AS \"Producto\",
                COALESCE(cat.nombre, 'Sin categoría')                             AS \"Categoría\",
                p.stock                                                           AS \"Stock\",
                ROUND(COALESCE(p.precio_compra, 0)::numeric, 2)                   AS \"Precio Compra\",
                ROUND(p.precio_venta::numeric, 2)                                 AS \"Precio Venta\",
                ROUND((p.stock * COALESCE(p.precio_compra, 0))::numeric, 2)       AS \"Valor Inventario\",
                CASE WHEN p.activo THEN 'Activo' ELSE 'Inactivo' END              AS \"Estado\"
            FROM productos p
            LEFT JOIN categorias cat ON cat.id = p.categoria_id
            $where
            ORDER BY \"Valor Inventario\" DESC
        ");
        $stmt->execute($params);
        reportesCsvOutput('valorizacion_inventario', $stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'inventario_nombre_stock_exportar':
        [$where, $params] = reportesInventarioFiltro();
        $stmt = $db->prepare("
            SELECT
                p.nombre AS \"Producto\",
                p.stock  AS \"Stock\"
            FROM productos p
            $where
            ORDER BY p.nombre ASC
        ");
        $stmt->execute($params);
        reportesCsvOutput('productos_stock', $stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'categorias_lista':
        try {
            $rows = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();
            echo json_encode($rows);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    // ----------------------------------------------------------------
    // MOVIMIENTOS DE CAJA
    // ----------------------------------------------------------------
    case 'caja_movimientos':
        try {
            [$where, $params] = reportesCajaMovimientosFiltro();
            $stmt = $db->prepare("
                SELECT cm.id, cm.tipo, cm.monto, cm.concepto, cm.usuario, cm.created_at,
                       c.nombre AS caja_nombre
                FROM caja_movimientos cm
                JOIN cajas c ON c.id = cm.caja_id
                $where
                ORDER BY cm.created_at DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'caja_movimientos_stats':
        try {
            [$where, $params] = reportesCajaMovimientosFiltro();
            $stmt = $db->prepare("
                SELECT
                    COUNT(*)                                                            AS total_movimientos,
                    COALESCE(SUM(cm.monto) FILTER (WHERE cm.tipo = 'ingreso'), 0)       AS total_ingresos,
                    COALESCE(SUM(cm.monto) FILTER (WHERE cm.tipo = 'egreso'), 0)        AS total_egresos
                FROM caja_movimientos cm
                $where
            ");
            $stmt->execute($params);
            $row = $stmt->fetch();
            $row['neto'] = (float) $row['total_ingresos'] - (float) $row['total_egresos'];
            echo json_encode($row);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'caja_movimientos_exportar':
        [$where, $params] = reportesCajaMovimientosFiltro();
        $stmt = $db->prepare("
            SELECT
                TO_CHAR(cm.created_at, 'DD/MM/YYYY HH24:MI')  AS \"Fecha\",
                c.nombre                                       AS \"Caja\",
                UPPER(cm.tipo)                                 AS \"Tipo\",
                COALESCE(cm.concepto, '')                      AS \"Concepto\",
                COALESCE(cm.usuario, '')                        AS \"Usuario\",
                ROUND(cm.monto::numeric, 2)                    AS \"Monto\"
            FROM caja_movimientos cm
            JOIN cajas c ON c.id = cm.caja_id
            $where
            ORDER BY cm.created_at ASC
        ");
        $stmt->execute($params);
        reportesCsvOutput('movimientos_caja', $stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 400);
}
