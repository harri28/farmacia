<?php
// ============================================================
// ARCHIVO: farmacia/modules/auth/logout.php
// ============================================================

require_once __DIR__ . '/../../config/auth.php';

// Capturar datos de sesión antes de destruirla
$_uid    = $_SESSION['usuario_id']      ?? null;
$_uname  = $_SESSION['username']        ?? null;
$_nombre = $_SESSION['nombre']          ?? null;
$_rol    = $_SESSION['rol']             ?? null;
$_tid    = $_SESSION['tenant_id']       ?? null;
$_sid    = $_SESSION['sucursal_id']     ?? null;
$_suc    = $_SESSION['sucursal_nombre'] ?? null;

if ($_uid) {
    require_once __DIR__ . '/../../config/database.php';
    try {
        $db = getDB();
        $db->prepare("INSERT INTO public.audit_log
            (tenant_id, sucursal_id, usuario_id, username, nombre_usuario, rol, accion, modulo, detalle, ip_address)
            VALUES (:tid, :sid, :uid, :uname, :nombre, :rol, 'logout', 'auth', :detalle, :ip)")
           ->execute([
               ':tid'    => $_tid,
               ':sid'    => $_sid,
               ':uid'    => $_uid,
               ':uname'  => $_uname,
               ':nombre' => $_nombre,
               ':rol'    => $_rol,
               ':detalle'=> 'Cerró sesión' . ($_suc ? " — $_suc" : ''),
               ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
           ]);
    } catch (Throwable $e) {}
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: login.php');
exit;
