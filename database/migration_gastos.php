<?php
// Ejecutar: http://localhost/farmacia/database/migration_gastos.php
require_once __DIR__ . '/../config/database.php';
requireApiAuth(['admin', 'superadmin']);

$db     = getDB();
$sql    = file_get_contents(__DIR__ . '/migration_gastos.sql');
$sql    = preg_replace('/--[^\n]*/', '', $sql);
$errors = [];
$ok     = [];

$schema = preg_replace('/[^a-z0-9_]/', '', strtolower($_SESSION['sucursal_schema'] ?? ''));
if (!$schema) { echo json_encode(['error' => true, 'message' => 'Sin schema de sesión']); exit; }

$db->exec("SET search_path TO \"{$schema}\", public");
foreach (array_filter(array_map('trim', explode(';', $sql))) as $s) {
    try { $db->exec($s); $ok[] = substr($s, 0, 60) . '…'; }
    catch (Exception $e) { $errors[] = $e->getMessage(); }
}
echo json_encode(['ok' => $ok, 'errors' => $errors]);
