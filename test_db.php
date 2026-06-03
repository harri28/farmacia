<?php
echo "TEST_SCRIPT_START\n";
require __DIR__ . '/config/database.php';
try {
    $db = getDB();
    echo "DB connection OK\n";
} catch (Throwable $e) {
    echo "DB connection ERROR: " . $e->getMessage() . "\n";
}
