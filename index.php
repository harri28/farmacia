<?php

require_once __DIR__ . '/config/database.php';

$host = strtolower(
    preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '')
);

if ($host === 'admin.genpharma.cloud') {
    header('Location: /modules/superadmin/login.php');
    exit;
}

// Solo los subdominios que coinciden con el slug de un tenant activo van al
// login normal. Dominio raíz, "www" y subdominios desconocidos van al panel
// superadmin en vez de exponer el selector combinado de todas las empresas.
$host_parts   = explode('.', $host);
$tenant_found = false;

if (count($host_parts) >= 3 && $host_parts[0] !== 'www') {
    try {
        // Conexión propia (no getDB()): si la BD está caída, getDB() mata la
        // página con un JSON crudo en vez de una excepción capturable. Aquí
        // basta con degradar a "tenant no encontrado" y redirigir igual.
        $pdo = new PDO(
            'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $stmt = $pdo->prepare('SELECT 1 FROM public.tenants WHERE slug = :s AND activo = TRUE');
        $stmt->execute([':s' => $host_parts[0]]);
        $tenant_found = (bool) $stmt->fetch();
    } catch (Throwable $e) {
        $tenant_found = false;
    }
}

header('Location: ' . ($tenant_found ? '/modules/auth/login.php' : '/modules/superadmin/login.php'));
exit;
