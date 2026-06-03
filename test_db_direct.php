<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=farmacia', 'postgres', '123456', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "DIRECT DB connection OK\n";
} catch (PDOException $e) {
    echo "DIRECT DB connection ERROR: " . $e->getMessage() . "\n";
}
