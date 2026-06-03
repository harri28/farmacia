<?php
session_start();
// Simular sesión de usuario logueado en una sucursal
$_SESSION['usuario_id'] = 1;
$_SESSION['sucursal_id'] = 1;
$_SESSION['sucursal_schema'] = 'generic_pharma_jr_lima_tambo_408'; // primera sucursal

require __DIR__ . '/config/database.php';
$db = getDB();

echo "Current schema: " . $db->query("SELECT current_schema()")->fetchColumn() . "\n";
echo "Search path: " . $db->query("SELECT current_setting('search_path')")->fetchColumn() . "\n\n";

// Intenta la query de productos
echo "Testing productos query...\n";
try {
    $stmt = $db->query("
        SELECT
            p.id, p.codigo, p.nombre, 
            p.precio_venta, p.stock, p.stock_minimo,
            p.laboratorio, p.presentacion, p.categoria_id, p.favorito,
            c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON c.id = p.categoria_id
        WHERE p.activo = TRUE
        ORDER BY p.favorito DESC, p.nombre ASC
    ");
    $results = $stmt->fetchAll();
    echo "SUCCESS: Retrieved " . count($results) . " productos\n";
    if (count($results) > 0) {
        echo "First product: " . json_encode($results[0]) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
