<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

requireAuth([]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['sucursal_id'])) {
    header('Location: ../ventas/index.php'); exit;
}

$sucursal_id = (int)$_POST['sucursal_id'];

if ($sucursal_id === sesionSucursalId()) {
    header('Location: ../ventas/index.php'); exit;
}

// Bloquear cambio de sucursal si el usuario tiene una caja abierta en la sucursal actual
$cur_schema = sesionSchema();
$uid_sw     = (int)sesionId();
if ($cur_schema && $uid_sw > 0) {
    try {
        $sq_cur  = str_replace('"', '""', $cur_schema);
        $caja_sw = getDB()->query(
            "SELECT id FROM \"{$sq_cur}\".cajas WHERE estado = 'abierta' AND usuario_id = {$uid_sw} LIMIT 1"
        )->fetch();
        if ($caja_sw) {
            $_SESSION['flash_error'] = 'Tienes una caja abierta en ' . sesionSucursal() . '. Ciérrala antes de cambiar de sucursal.';
            header('Location: ../caja/index.php'); exit;
        }
    } catch (Throwable $_esw) {}
}

try {
    $db = getDB();

    if (isAdmin()) {
        // Admin/gerente puede cambiar a cualquier sucursal activa del tenant
        $stmt = $db->prepare("
            SELECT s.nombre AS sucursal_nombre, s.schema_name
            FROM public.sucursales s
            WHERE s.id = :sid AND s.tenant_id = :tid AND s.activo = TRUE
        ");
        $stmt->execute([':sid' => $sucursal_id, ':tid' => sesionTenantId()]);
        $suc = $stmt->fetch();

        if (!$suc) { header('Location: ../ventas/index.php'); exit; }

        $_SESSION['sucursal_id']     = $sucursal_id;
        $_SESSION['sucursal_nombre'] = $suc['sucursal_nombre'];
        $_SESSION['sucursal_schema'] = $suc['schema_name'];
        // rol se mantiene igual
    } else {
        // Cajero: solo sucursales asignadas
        $stmt = $db->prepare("
            SELECT us.rol, s.nombre AS sucursal_nombre, s.schema_name
            FROM public.usuario_sucursal us
            JOIN public.sucursales s ON s.id = us.sucursal_id
            WHERE us.usuario_id = :uid AND us.sucursal_id = :sid
              AND s.tenant_id = :tid
              AND us.activo = TRUE AND s.activo = TRUE
        ");
        $stmt->execute([':uid' => sesionId(), ':sid' => $sucursal_id, ':tid' => sesionTenantId()]);
        $acceso = $stmt->fetch();

        if (!$acceso) { header('Location: ../ventas/index.php'); exit; }

        $_SESSION['sucursal_id']     = $sucursal_id;
        $_SESSION['sucursal_nombre'] = $acceso['sucursal_nombre'];
        $_SESSION['sucursal_schema'] = $acceso['schema_name'];
        $_SESSION['rol']             = $acceso['rol'];
    }

    header('Location: ../ventas/index.php'); exit;

} catch (Throwable $e) {
    header('Location: ../ventas/index.php'); exit;
}
