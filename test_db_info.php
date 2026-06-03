<?php
require __DIR__ . '/config/database.php';
$db = getDB();
try {
    $row = $db->query("SELECT current_schema(), current_setting('search_path')")->fetch();
    echo "current_schema: " . ($row['current_schema'] ?? '') . "\n";
    echo "search_path: " . ($row['current_setting'] ?? '') . "\n\n";

    $schemas = $db->query("SELECT nspname FROM pg_namespace WHERE nspname NOT LIKE 'pg_%' AND nspname <> 'information_schema' ORDER BY nspname")->fetchAll();
    echo "Schemas:\n";
    foreach ($schemas as $s) echo " - " . $s['nspname'] . "\n";

    echo "\nTables (first 200):\n";
    $tables = $db->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_schema NOT LIKE 'pg_%' AND table_schema <> 'information_schema' ORDER BY table_schema, table_name LIMIT 200")->fetchAll();
    foreach ($tables as $t) echo " - {$t['table_schema']}.{$t['table_name']}\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
