<?php
require __DIR__ . '/../config/database.php';
try {
    $db = getDB();
    $rows = $db->query('SELECT id, nombre, schema_name FROM public.sucursales ORDER BY id LIMIT 100')->fetchAll();
    echo "SUCURSALES:\n";
    foreach ($rows as $r) echo "- {$r['id']} | {$r['nombre']} | {$r['schema_name']}\n";
} catch (Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
