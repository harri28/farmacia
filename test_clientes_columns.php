<?php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['sucursal_schema'] = 'generic_pharma_jr_lima_tambo_408';

require __DIR__ . '/config/database.php';
$db = getDB();

// Lista las columnas de clientes
$cols = $db->query("
    SELECT column_name, data_type 
    FROM information_schema.columns 
    WHERE table_name = 'clientes' 
    ORDER BY ordinal_position
")->fetchAll(PDO::FETCH_COLUMN);

echo "Clientes table columns:\n";
foreach ($cols as $col) {
    echo "- $col\n";
}

// Contar clientes
$count = $db->query("SELECT COUNT(*) FROM clientes WHERE activo = TRUE")->fetchColumn();
echo "\nActive clientes: $count\n";

// Ver sample
if ($count > 0) {
    $sample = $db->query("SELECT * FROM clientes LIMIT 1")->fetch();
    echo "\nSample cliente: " . json_encode($sample, JSON_PRETTY_PRINT) . "\n";
}
?>
