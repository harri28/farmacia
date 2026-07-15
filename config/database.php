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

function registrarAuditoria(string $accion, string $modulo = '', string $detalle = ''): void
{
    try {
        $db = getDB();
        $db->prepare("
            INSERT INTO public.audit_log
                (tenant_id, sucursal_id, usuario_id, username, nombre_usuario, rol, accion, modulo, detalle, ip_address)
            VALUES
                (:tid, :sid, :uid, :uname, :nombre, :rol, :accion, :modulo, :detalle, :ip)
        ")->execute([
            ':tid'    => $_SESSION['tenant_id']    ?? null,
            ':sid'    => $_SESSION['sucursal_id']  ?? null,
            ':uid'    => $_SESSION['usuario_id']   ?? null,
            ':uname'  => $_SESSION['username']     ?? null,
            ':nombre' => $_SESSION['nombre']       ?? null,
            ':rol'    => $_SESSION['rol']          ?? null,
            ':accion' => $accion,
            ':modulo' => $modulo,
            ':detalle'=> $detalle,
            ':ip'     => $_SERVER['REMOTE_ADDR']   ?? null,
        ]);
    } catch (Throwable $e) {
        // No interrumpir el flujo si falla el log
    }
}

function formatMoney(float $amount): string
{
    return 'S/ ' . number_format($amount, 2);
}

function generarNumeroVenta(PDO $db): string
{
    $prefijo = 'V' . date('Ymd') . '-';
    $stmt = $db->prepare("
        SELECT COALESCE(MAX(CAST(SUBSTRING(numero_venta FROM '[0-9]+$') AS INTEGER)), 0) AS ultimo
        FROM ventas
        WHERE numero_venta LIKE :prefijo
    ");
    $stmt->execute([':prefijo' => $prefijo . '%']);
    $ultimo = (int) ($stmt->fetch()['ultimo'] ?? 0);

    return $prefijo . str_pad((string) ($ultimo + 1), 4, '0', STR_PAD_LEFT);
}

function generarNumeroIngreso(PDO $db): string
{
    $prefijo = 'I' . date('Ymd') . '-';
    $stmt = $db->prepare("
        SELECT COALESCE(MAX(CAST(SUBSTRING(numero_ingreso FROM '[0-9]+$') AS INTEGER)), 0) AS ultimo
        FROM ingresos
        WHERE numero_ingreso LIKE :prefijo
    ");
    $stmt->execute([':prefijo' => $prefijo . '%']);
    $ultimo = (int) ($stmt->fetch()['ultimo'] ?? 0);

    return $prefijo . str_pad((string) ($ultimo + 1), 4, '0', STR_PAD_LEFT);
}

function generarNumeroSalida(PDO $db): string
{
    $prefijo = 'S' . date('Ymd') . '-';
    $stmt = $db->prepare("
        SELECT COALESCE(MAX(CAST(SUBSTRING(numero_salida FROM '[0-9]+$') AS INTEGER)), 0) AS ultimo
        FROM salidas
        WHERE numero_salida LIKE :prefijo
    ");
    $stmt->execute([':prefijo' => $prefijo . '%']);
    $ultimo = (int) ($stmt->fetch()['ultimo'] ?? 0);

    return $prefijo . str_pad((string) ($ultimo + 1), 4, '0', STR_PAD_LEFT);
}

function app_public_prefix(): string
{
    // Igual idea que sunat_public_base_url() en config/sunat.php: en el VPS
    // el DocumentRoot de Apache ES la raíz de la app (sin subcarpeta), pero
    // en XAMPP local la app vive bajo htdocs/farmacia. Se calcula el prefijo
    // comparando la carpeta real del proyecto contra el DocumentRoot, para
    // que las URLs públicas (ej. el enlace de "Mi Comprobante") funcionen en
    // ambos entornos sin hardcodear ninguno de los dos.
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $appRoot = rtrim(str_replace('\\', '/', __DIR__ . '/..'), '/');

    if ($docRoot === '' || strpos($appRoot, $docRoot) !== 0) {
        return '';
    }

    return substr($appRoot, strlen($docRoot));
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
