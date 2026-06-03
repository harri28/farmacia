<?php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['sucursal_schema'] = 'generic_pharma_jr_lima_tambo_408';

require __DIR__ . '/config/database.php';
$db = getDB();

// Lista las columnas de productos
$cols = $db->query("
    SELECT column_name, data_type 
    FROM information_schema.columns 
    WHERE table_name = 'productos' 
    ORDER BY ordinal_position
")->fetchAll();

echo "Productos table columns:\n";
foreach ($cols as $col) {
    echo "- {$col['column_name']} ({$col['data_type']})\n";
}
?>
