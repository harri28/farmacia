<?php
// ============================================================
// ARCHIVO: farmacia/modules/auth/api.php
// API del login en dos pasos: credenciales primero, sucursal después.
// No expone ninguna sucursal hasta validar usuario/contraseña.
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../config/database.php';

$db = getDB();

// El tenant se determina siempre por la cuenta del usuario que se loguea
// (usuarios.tenant_id), nunca por el subdominio/URL usada para llegar aquí
// -- el subdominio es solo cosmético. La única regla ligada a la URL es en
// login.php: un subdominio que no corresponde a NINGÚN tenant registrado
// muestra "empresa no encontrada" antes de llegar siquiera a este endpoint.

$action = $_GET['action'] ?? '';
$data   = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($action) {

    // ---- Paso 1: valida usuario/contraseña, devuelve SUS sucursales ----
    case 'validar_credenciales':
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            jsonResponse(['error' => true, 'message' => 'Ingresa usuario y contraseña.'], 400);
        }

        $stmt = $db->prepare("
            SELECT id, nombre, apellido, username, password_hash, tenant_id
            FROM public.usuarios WHERE username = :u AND activo = TRUE
        ");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            try {
                $db->prepare("INSERT INTO public.audit_log
                    (tenant_id, username, accion, modulo, detalle, ip_address)
                    VALUES (:tid, :uname, 'login_fallido', 'auth', :detalle, :ip)")
                   ->execute([
                       ':tid'    => $user['tenant_id'] ?? null,
                       ':uname'  => $username,
                       ':detalle'=> 'Credenciales incorrectas',
                       ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
                   ]);
            } catch (Throwable $e) {}
            jsonResponse(['error' => true, 'message' => 'Usuario o contraseña incorrectos.'], 401);
        }

        // Se muestran TODAS las sucursales activas de la empresa del usuario,
        // sin importar si el admin lo asignó individualmente a cada una (eso
        // se sigue validando después, en confirmar_sucursal, para decidir su
        // rol ahí -- ver ese caso más abajo).
        $stmt2 = $db->prepare("
            SELECT s.id, s.nombre, s.direccion
            FROM public.sucursales s
            JOIN public.tenants t ON t.id = s.tenant_id
            WHERE s.tenant_id = :tid AND s.activo = TRUE AND t.activo = TRUE
            ORDER BY s.nombre ASC
        ");
        $stmt2->execute([':tid' => $user['tenant_id']]);
        $sucursales = $stmt2->fetchAll();

        if (!$sucursales) {
            jsonResponse(['error' => true, 'message' => 'Tu empresa no tiene sucursales activas. Contacta al administrador.'], 403);
        }

        // Estado temporal: ya se validaron credenciales, falta elegir sucursal.
        // No se otorga ningún acceso real todavía (requireAuth sigue rechazando).
        $_SESSION['pending_login_user_id'] = $user['id'];

        jsonResponse(['error' => false, 'sucursales' => $sucursales]);

    // ---- Paso 2: confirma sucursal y recién ahí crea la sesión completa ----
    case 'confirmar_sucursal':
        $sucursal_id = intval($data['sucursal_id'] ?? 0);
        $pending_id  = intval($_SESSION['pending_login_user_id'] ?? 0);

        if (!$pending_id) {
            jsonResponse(['error' => true, 'message' => 'Tu sesión expiró, vuelve a ingresar tus credenciales.'], 440);
        }
        if (!$sucursal_id) {
            jsonResponse(['error' => true, 'message' => 'Selecciona una sucursal.'], 400);
        }

        $stmt = $db->prepare("SELECT id, nombre, apellido, username, tenant_id FROM public.usuarios WHERE id = :id AND activo = TRUE");
        $stmt->execute([':id' => $pending_id]);
        $user = $stmt->fetch();

        if (!$user) {
            unset($_SESSION['pending_login_user_id']);
            jsonResponse(['error' => true, 'message' => 'Tu sesión expiró, vuelve a ingresar tus credenciales.'], 440);
        }

        // La sucursal debe pertenecer a la MISMA empresa del usuario y estar
        // activa -- se vuelve a verificar server-side, nunca se confía en lo
        // que mandó el navegador. Ya no se exige una fila usuario_sucursal
        // para esta sucursal en particular: cualquier usuario de la empresa
        // puede entrar a cualquiera de sus sucursales.
        $stmt2 = $db->prepare("
            SELECT s.nombre AS sucursal_nombre, s.schema_name,
                   t.id AS tenant_id, t.nombre AS tenant_nombre, t.slug AS tenant_slug
            FROM public.sucursales s
            JOIN public.tenants t ON t.id = s.tenant_id
            WHERE s.id = :sid AND s.tenant_id = :tid AND s.activo = TRUE AND t.activo = TRUE
        ");
        $stmt2->execute([':sid' => $sucursal_id, ':tid' => $user['tenant_id']]);
        $sucursal = $stmt2->fetch();

        if (!$sucursal) {
            jsonResponse(['error' => true, 'message' => 'Sucursal inválida.'], 400);
        }

        // El rol es el mismo en toda la empresa: se toma de cualquier fila
        // activa que el usuario tenga en usuario_sucursal (no se filtra por
        // esta sucursal en particular).
        $stmt3 = $db->prepare("
            SELECT rol FROM public.usuario_sucursal
            WHERE usuario_id = :uid AND activo = TRUE
            ORDER BY id ASC LIMIT 1
        ");
        $stmt3->execute([':uid' => $user['id']]);
        $rolRow = $stmt3->fetch();

        if (!$rolRow) {
            jsonResponse(['error' => true, 'message' => 'Tu usuario no tiene un rol asignado. Contacta al administrador.'], 403);
        }
        $rol = $rolRow['rol'];

        unset($_SESSION['pending_login_user_id']);
        session_regenerate_id(true);
        $_SESSION['usuario_id']      = $user['id'];
        $_SESSION['nombre']          = trim($user['nombre'] . ' ' . ($user['apellido'] ?? ''));
        $_SESSION['username']        = $user['username'];
        $_SESSION['rol']             = $rol;
        $_SESSION['tenant_id']       = $sucursal['tenant_id'];
        $_SESSION['tenant_nombre']   = $sucursal['tenant_nombre'];
        $_SESSION['tenant_slug']     = $sucursal['tenant_slug'];
        $_SESSION['sucursal_id']     = $sucursal_id;
        $_SESSION['sucursal_nombre'] = $sucursal['sucursal_nombre'];
        $_SESSION['sucursal_schema'] = $sucursal['schema_name'];

        try {
            $db->prepare("INSERT INTO public.audit_log
                (tenant_id, sucursal_id, usuario_id, username, nombre_usuario, rol, accion, modulo, detalle, ip_address)
                VALUES (:tid, :sid, :uid, :uname, :nombre, :rol, 'login', 'auth', :detalle, :ip)")
               ->execute([
                   ':tid'    => $sucursal['tenant_id'],
                   ':sid'    => $sucursal_id,
                   ':uid'    => $user['id'],
                   ':uname'  => $user['username'],
                   ':nombre' => trim($user['nombre'] . ' ' . ($user['apellido'] ?? '')),
                   ':rol'    => $rol,
                   ':detalle'=> 'Ingresó a ' . $sucursal['sucursal_nombre'],
                   ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
               ]);
        } catch (Throwable $e) {}

        $redirect = in_array($rol, ['admin', 'gerente'], true)
            ? '../dashboard/index.php'
            : '../ventas/index.php';

        jsonResponse(['error' => false, 'redirect' => $redirect]);

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no reconocida.'], 400);
}
