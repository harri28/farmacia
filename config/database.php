<?php
// ============================================================
// ARCHIVO: farmacia/config/database.php
// DESCRIPCIÓN: Conexión PDO a PostgreSQL + helpers globales
// ============================================================

require_once __DIR__ . '/auth.php';   // inicia sesión y carga helpers

define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'farmacia');
define('DB_USER', 'postgres');
define('DB_PASS', '1234');

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Apuntar al schema de la sucursal activa (si hay sesión)
            $schema = preg_replace('/[^a-z0-9_]/', '', strtolower($_SESSION['sucursal_schema'] ?? ''));
            if ($schema !== '') {
                $pdo->exec("SET search_path TO {$schema}, public");
            }

        } catch (PDOException $e) {
            die(json_encode([
                'error'   => true,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ]));
        }
    }

    return $pdo;
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function formatMoney(float $amount): string
{
    return 'S/ ' . number_format($amount, 2);
}

function generarNumeroVenta(PDO $db): string
{
    $stmt = $db->query("SELECT COUNT(*) AS total FROM ventas WHERE DATE(created_at) = CURRENT_DATE");
    $num  = str_pad(($stmt->fetch()['total'] + 1), 4, '0', STR_PAD_LEFT);
    return 'V' . date('Ymd') . '-' . $num;
}

function generarNumeroIngreso(PDO $db): string
{
    $stmt = $db->query("SELECT COUNT(*) AS total FROM ingresos WHERE DATE(created_at) = CURRENT_DATE");
    $num  = str_pad(($stmt->fetch()['total'] + 1), 4, '0', STR_PAD_LEFT);
    return 'I' . date('Ymd') . '-' . $num;
}

function getTenantConfig(): array
{
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $tid = sesionTenantId();
    if (!$tid) {
        return $cfg = ['nombre_sistema' => 'FarmaSystem', 'logo_path' => null];
    }
    try {
        $stmt = getDB()->prepare(
            "SELECT nombre_sistema, logo_path FROM public.tenant_config WHERE tenant_id = :tid"
        );
        $stmt->execute([':tid' => $tid]);
        $row = $stmt->fetch();
        $cfg = $row ?: ['nombre_sistema' => 'FarmaSystem', 'logo_path' => null];
    } catch (Exception $e) {
        $cfg = ['nombre_sistema' => 'FarmaSystem', 'logo_path' => null];
    }
    return $cfg;
}
