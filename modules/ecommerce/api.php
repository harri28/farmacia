<?php
// ============================================================
// FARMACIA — API pública para Ecommerce Selvadigital
// Endpoint: /farmacia/modules/ecommerce/api.php
// No requiere autenticación (solo lectura de catálogo/stock)
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/database.php';

$action = $_GET['action'] ?? '';
$schema = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['schema'] ?? '');

if (!$schema) {
    echo json_encode(['success' => false, 'error' => 'Schema requerido']);
    exit;
}

try {
    $pdo = getDB();
    // Validar que el schema existe
    $schemaExists = $pdo->prepare("SELECT 1 FROM information_schema.schemata WHERE schema_name = ?");
    $schemaExists->execute([$schema]);
    if (!$schemaExists->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Schema no encontrado']);
        exit;
    }

    switch ($action) {

        // ── CATEGORÍAS ──────────────────────────────────────
        case 'categorias':
            $stmt = $pdo->query("SELECT id, nombre FROM public.categorias WHERE activo = TRUE ORDER BY nombre ASC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        // ── PRODUCTOS ───────────────────────────────────────
        case 'productos':
            $stmt = $pdo->query("
                SELECT
                    p.id,
                    p.nombre,
                    p.codigo,
                    p.precio_venta   AS precio,
                    p.stock,
                    p.laboratorio,
                    p.presentacion,
                    c.id             AS categoria_id,
                    c.nombre         AS categoria,
                    CASE WHEN p.imagen_path IS NOT NULL AND p.imagen_path != ''
                         THEN CONCAT('/farmacia/', p.imagen_path)
                         ELSE NULL
                    END AS imagen
                FROM {$schema}.productos p
                LEFT JOIN public.categorias c ON c.id = p.categoria_id
                WHERE p.activo = TRUE AND p.stock > 0
                ORDER BY p.nombre ASC
            ");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        // ── STOCK DE UN PRODUCTO ─────────────────────────────
        case 'stock':
            $id = intval($_GET['producto_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT stock FROM {$schema}.productos WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            echo json_encode(['success' => true, 'stock' => $row ? intval($row['stock']) : 0]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
