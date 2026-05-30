<?php
// Ejecutar: http://localhost/farmacia/database/migration_compras.php
require_once __DIR__ . '/../config/database.php';
requireApiAuth(['admin', 'superadmin']);

$db     = getDB();
$sql    = file_get_contents(__DIR__ . '/migration_compras.sql');
$sql    = preg_replace('/--[^\n]*/', '', $sql);
$errors = [];
$ok     = [];

$schema = preg_replace('/[^a-z0-9_]/', '', strtolower($_SESSION['sucursal_schema'] ?? ''));
if (!$schema) {
    // Schema no está en sesión: ejecutar en public (instalación simple)
    $schema = 'public';
}

$db->exec("SET search_path TO {$schema}, public");
foreach (array_filter(array_map('trim', explode(';', $sql))) as $s) {
    try { $db->exec($s); $ok[] = substr($s, 0, 80) . '…'; }
    catch (Exception $e) { $errors[] = $e->getMessage(); }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Migración Compras</title>
<style>body{font-family:monospace;padding:24px;background:#f8fafc} h2{color:#1e293b} .ok{color:#15803d} .err{color:#dc2626} pre{background:#fff;border:1px solid #e2e8f0;padding:12px;border-radius:8px;white-space:pre-wrap;word-break:break-all}</style>
</head><body>
<h2>Migración: Módulo de Compras</h2>
<p>Schema: <strong><?= htmlspecialchars($schema) ?></strong></p>
<?php if ($errors): ?>
    <p class="err"><strong><?= count($errors) ?> error(es):</strong></p>
    <?php foreach ($errors as $e): ?><pre class="err"><?= htmlspecialchars($e) ?></pre><?php endforeach; ?>
<?php else: ?>
    <p class="ok"><strong>✓ Migración completada sin errores</strong></p>
<?php endif; ?>
<p class="ok"><?= count($ok) ?> sentencia(s) ejecutada(s):</p>
<?php foreach ($ok as $s): ?><pre class="ok"><?= htmlspecialchars($s) ?></pre><?php endforeach; ?>
<p><a href="../modules/compras/index.php">← Ir al módulo de Compras</a></p>
</body></html>
