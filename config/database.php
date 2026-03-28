<?php
// ============================================================
// ARCHIVO: farmacia/config/database.php
// DESCRIPCIÓN: Configuración de conexión a PostgreSQL
// ============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'farmacia');
define('DB_USER', 'postgres');
define('DB_PASS', '1234');  // Cambia por tu contraseña

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode([
                'error' => true,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ]));
        }
    }
    return $pdo;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function formatMoney($amount) {
    return 'S/ ' . number_format($amount, 2);
}

function generarNumeroVenta($db) {
    $stmt = $db->query("SELECT COUNT(*) as total FROM ventas WHERE DATE(created_at) = CURRENT_DATE");
    $row = $stmt->fetch();
    $num = str_pad(($row['total'] + 1), 4, '0', STR_PAD_LEFT);
    return 'V' . date('Ymd') . '-' . $num;
}

function generarNumeroIngreso($db) {
    $stmt = $db->query("SELECT COUNT(*) as total FROM ingresos WHERE DATE(created_at) = CURRENT_DATE");
    $row = $stmt->fetch();
    $num = str_pad(($row['total'] + 1), 4, '0', STR_PAD_LEFT);
    return 'I' . date('Ymd') . '-' . $num;
}