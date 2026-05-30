<?php
// ============================================================
// ARCHIVO: farmacia/modules/admin/api.php
// DESCRIPCIÓN: API para gestión de usuarios y sucursales
//              Solo accesible por rol 'admin'
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin']);

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ================================================================
    // USUARIOS
    // ================================================================

    case 'usuarios_listar':
        $stmt = $db->prepare("
            SELECT u.id, u.nombre, u.apellido, u.username, u.activo, u.created_at,
                   COALESCE(
                       json_agg(
                           json_build_object('sucursal_id', s.id, 'sucursal', s.nombre, 'rol', us.rol, 'activo', us.activo)
                           ORDER BY s.nombre
                       ) FILTER (WHERE s.id IS NOT NULL),
                       '[]'
                   ) AS accesos
            FROM public.usuarios u
            LEFT JOIN public.usuario_sucursal us ON us.usuario_id = u.id
            LEFT JOIN public.sucursales s        ON s.id = us.sucursal_id AND s.tenant_id = :tid
            WHERE u.tenant_id = :tid
            GROUP BY u.id
            ORDER BY u.nombre ASC
        ");
        $stmt->execute([':tid' => sesionTenantId()]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'usuario_crear':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['nombre']) || empty($d['username']) || empty($d['password'])) {
            jsonResponse(['error' => true, 'message' => 'Nombre, usuario y contraseña son requeridos'], 400);
        }
        // Verificar username único
        $ck = $db->prepare("SELECT id FROM public.usuarios WHERE username = :u");
        $ck->execute([':u' => trim($d['username'])]);
        if ($ck->fetch()) {
            jsonResponse(['error' => true, 'message' => 'El nombre de usuario ya existe'], 409);
        }
        $stmt = $db->prepare("
            INSERT INTO public.usuarios (tenant_id, nombre, apellido, username, password_hash)
            VALUES (:tid, :n, :a, :u, :h)
            RETURNING id
        ");
        $stmt->execute([
            ':tid' => sesionTenantId(),
            ':n'   => trim($d['nombre']),
            ':a'   => trim($d['apellido'] ?? ''),
            ':u'   => trim($d['username']),
            ':h'   => password_hash($d['password'], PASSWORD_BCRYPT),
        ]);
        $nuevo_id = $stmt->fetch()['id'];

        // Asignar sucursal + rol si se envían
        if (!empty($d['sucursal_id']) && !empty($d['rol'])) {
            $db->prepare("
                INSERT INTO public.usuario_sucursal (usuario_id, sucursal_id, rol)
                VALUES (:uid, :sid, :rol)
                ON CONFLICT (usuario_id, sucursal_id) DO UPDATE SET rol = EXCLUDED.rol, activo = TRUE
            ")->execute([':uid' => $nuevo_id, ':sid' => intval($d['sucursal_id']), ':rol' => $d['rol']]);
        }
        jsonResponse(['error' => false, 'message' => 'Usuario creado correctamente', 'id' => $nuevo_id]);

    case 'usuario_actualizar':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = intval($d['id'] ?? 0);
        if (!$id || empty($d['nombre']) || empty($d['username'])) {
            jsonResponse(['error' => true, 'message' => 'Datos incompletos'], 400);
        }
        // Verificar username único (excluyendo el mismo usuario)
        $ck = $db->prepare("SELECT id FROM public.usuarios WHERE username = :u AND id <> :id");
        $ck->execute([':u' => trim($d['username']), ':id' => $id]);
        if ($ck->fetch()) {
            jsonResponse(['error' => true, 'message' => 'El nombre de usuario ya existe'], 409);
        }
        $db->prepare("
            UPDATE public.usuarios SET nombre = :n, apellido = :a, username = :u
            WHERE id = :id AND tenant_id = :tid
        ")->execute([':n' => trim($d['nombre']), ':a' => trim($d['apellido'] ?? ''), ':u' => trim($d['username']), ':id' => $id, ':tid' => sesionTenantId()]);
        jsonResponse(['error' => false, 'message' => 'Usuario actualizado']);

    case 'usuario_cambiar_password':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = intval($d['id'] ?? 0);
        if (!$id || empty($d['password']) || strlen($d['password']) < 4) {
            jsonResponse(['error' => true, 'message' => 'La contraseña debe tener al menos 4 caracteres'], 400);
        }
        $db->prepare("UPDATE public.usuarios SET password_hash = :h WHERE id = :id")
           ->execute([':h' => password_hash($d['password'], PASSWORD_BCRYPT), ':id' => $id]);
        jsonResponse(['error' => false, 'message' => 'Contraseña actualizada']);

    case 'usuario_toggle_activo':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = intval($d['id'] ?? 0);
        // No permitir desactivar al propio usuario
        if ($id === sesionId()) {
            jsonResponse(['error' => true, 'message' => 'No puedes desactivar tu propia cuenta'], 400);
        }
        $stmt = $db->prepare("UPDATE public.usuarios SET activo = NOT activo WHERE id = :id AND tenant_id = :tid RETURNING activo");
        $stmt->execute([':id' => $id, ':tid' => sesionTenantId()]);
        $row = $stmt->fetch();
        jsonResponse(['error' => false, 'activo' => $row['activo']]);

    case 'asignar_acceso':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['usuario_id']) || empty($d['sucursal_id']) || empty($d['rol'])) {
            jsonResponse(['error' => true, 'message' => 'Datos incompletos'], 400);
        }
        $db->prepare("
            INSERT INTO public.usuario_sucursal (usuario_id, sucursal_id, rol, activo)
            VALUES (:uid, :sid, :rol, TRUE)
            ON CONFLICT (usuario_id, sucursal_id)
            DO UPDATE SET rol = EXCLUDED.rol, activo = TRUE
        ")->execute([':uid' => intval($d['usuario_id']), ':sid' => intval($d['sucursal_id']), ':rol' => $d['rol']]);
        jsonResponse(['error' => false, 'message' => 'Acceso asignado']);

    case 'revocar_acceso':
        $d = json_decode(file_get_contents('php://input'), true);
        $db->prepare("
            UPDATE public.usuario_sucursal SET activo = FALSE
            WHERE usuario_id = :uid AND sucursal_id = :sid
        ")->execute([':uid' => intval($d['usuario_id']), ':sid' => intval($d['sucursal_id'])]);
        jsonResponse(['error' => false, 'message' => 'Acceso revocado']);

    // ================================================================
    // SUCURSALES
    // ================================================================

    case 'sucursales_listar':
        $stmt = $db->prepare("
            SELECT s.id, s.nombre, s.schema_name, s.direccion, s.telefono, s.activo, s.created_at,
                   COUNT(us.id) AS total_usuarios
            FROM public.sucursales s
            LEFT JOIN public.usuario_sucursal us ON us.sucursal_id = s.id AND us.activo = TRUE
            WHERE s.tenant_id = :tid
            GROUP BY s.id
            ORDER BY s.nombre ASC
        ");
        $stmt->execute([':tid' => sesionTenantId()]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'sucursal_crear':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['nombre'])) {
            jsonResponse(['error' => true, 'message' => 'El nombre es requerido'], 400);
        }

        // Generar slug de la sucursal desde el nombre
        $suc_slug = strtolower(trim($d['nombre']));
        $suc_slug = iconv('UTF-8', 'ASCII//TRANSLIT', $suc_slug) ?: $suc_slug;
        $suc_slug = preg_replace('/[^a-z0-9]+/', '_', $suc_slug);
        $suc_slug = trim($suc_slug, '_');
        $suc_slug = substr($suc_slug, 0, 20) ?: 'suc';

        // Prefijo del tenant para garantizar aislamiento entre tenants
        $tenant_slug = sesionTenantSlug();
        $schema      = $tenant_slug . '_' . $suc_slug;
        $schema      = substr($schema, 0, 55); // PostgreSQL max identifier 63 chars

        // Verificar unicidad del schema_name
        $ck = $db->prepare("SELECT id FROM public.sucursales WHERE schema_name = :s");
        $ck->execute([':s' => $schema]);
        if ($ck->fetch()) {
            $schema .= '_' . (time() % 1000);
        }

        $sql_file = __DIR__ . '/../../database/schema_sucursal.sql';
        if (!file_exists($sql_file)) {
            jsonResponse(['error' => true, 'message' => 'No se encontró schema_sucursal.sql'], 500);
        }

        try {
            // ---- DDL fuera de transacción ----
            $db->exec("CREATE SCHEMA IF NOT EXISTS \"{$schema}\"");
            $db->exec("SET search_path TO \"{$schema}\", public");

            $sql_raw = file_get_contents($sql_file);
            $sql_raw = preg_replace('/--[^\n]*/', '', $sql_raw);
            foreach (array_filter(array_map('trim', explode(';', $sql_raw))) as $stmt_sql) {
                $db->exec($stmt_sql);
            }

            // Restaurar search_path de la sesión actual
            $mySchema = preg_replace('/[^a-z0-9_]/', '', strtolower($_SESSION['sucursal_schema'] ?? ''));
            $db->exec($mySchema ? "SET search_path TO \"{$mySchema}\", public" : "SET search_path TO public");

            // ---- INSERT en transacción ----
            $db->beginTransaction();
            $ins = $db->prepare("
                INSERT INTO public.sucursales (tenant_id, nombre, schema_name, direccion, telefono)
                VALUES (:tid, :n, :s, :d, :t) RETURNING id
            ");
            $ins->execute([
                ':tid' => sesionTenantId(),
                ':n'   => trim($d['nombre']),
                ':s'   => $schema,
                ':d'   => trim($d['direccion'] ?? ''),
                ':t'   => trim($d['telefono']  ?? ''),
            ]);
            $nueva_id = $ins->fetch()['id'];
            $db->commit();

            jsonResponse(['error' => false, 'message' => 'Sucursal creada correctamente', 'id' => $nueva_id, 'schema_name' => $schema]);

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            try { $db->exec("DROP SCHEMA IF EXISTS \"{$schema}\" CASCADE"); } catch (Exception $ignored) {}
            jsonResponse(['error' => true, 'message' => 'Error al crear sucursal: ' . $e->getMessage()], 500);
        }

    case 'sucursal_actualizar':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = intval($d['id'] ?? 0);
        if (!$id || empty($d['nombre'])) {
            jsonResponse(['error' => true, 'message' => 'Datos incompletos'], 400);
        }
        $db->prepare("
            UPDATE public.sucursales SET nombre = :n, direccion = :d, telefono = :t
            WHERE id = :id AND tenant_id = :tid
        ")->execute([':n' => trim($d['nombre']), ':d' => trim($d['direccion'] ?? ''), ':t' => trim($d['telefono'] ?? ''), ':id' => $id, ':tid' => sesionTenantId()]);
        jsonResponse(['error' => false, 'message' => 'Sucursal actualizada']);

    case 'sucursal_toggle_activo':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = intval($d['id'] ?? 0);
        // No permitir desactivar la sucursal de la sesión activa
        if ($id === sesionSucursalId()) {
            jsonResponse(['error' => true, 'message' => 'No puedes desactivar tu sucursal activa'], 400);
        }
        $stmt = $db->prepare("UPDATE public.sucursales SET activo = NOT activo WHERE id = :id AND tenant_id = :tid RETURNING activo");
        $stmt->execute([':id' => $id, ':tid' => sesionTenantId()]);
        jsonResponse(['error' => false, 'activo' => $stmt->fetch()['activo']]);

    // ================================================================
    // CONFIGURACIÓN DE MARCA
    // ================================================================

    case 'config_get':
        $stmt = $db->prepare(
            "SELECT nombre_sistema, logo_path FROM public.tenant_config WHERE tenant_id = :tid"
        );
        $stmt->execute([':tid' => sesionTenantId()]);
        $row = $stmt->fetch() ?: ['nombre_sistema' => 'FarmaSystem', 'logo_path' => null];
        echo json_encode($row);
        break;

    case 'config_guardar':
        $d      = json_decode(file_get_contents('php://input'), true);
        $nombre = trim($d['nombre_sistema'] ?? '');
        if ($nombre === '') {
            jsonResponse(['error' => true, 'message' => 'El nombre del sistema es requerido'], 400);
        }
        if (mb_strlen($nombre) > 100) {
            jsonResponse(['error' => true, 'message' => 'El nombre no puede superar 100 caracteres'], 400);
        }
        $db->prepare("
            INSERT INTO public.tenant_config (tenant_id, nombre_sistema, updated_at)
            VALUES (:tid, :n, NOW())
            ON CONFLICT (tenant_id) DO UPDATE SET nombre_sistema = EXCLUDED.nombre_sistema, updated_at = NOW()
        ")->execute([':tid' => sesionTenantId(), ':n' => $nombre]);
        jsonResponse(['error' => false, 'message' => 'Nombre guardado correctamente']);

    case 'logo_subir':
        if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $err = $_FILES['logo']['error'] ?? -1;
            $msg = $err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE
                ? 'El archivo supera el tamaño máximo permitido (2 MB)'
                : 'No se recibió ningún archivo';
            jsonResponse(['error' => true, 'message' => $msg], 400);
        }
        $file    = $_FILES['logo'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($file['type'], $allowed, true)) {
            jsonResponse(['error' => true, 'message' => 'Tipo no permitido. Use JPG, PNG, GIF, WEBP o SVG'], 400);
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            jsonResponse(['error' => true, 'message' => 'El logo no puede superar 2 MB'], 400);
        }
        $ext_map = [
            'image/jpeg'    => 'jpg',
            'image/png'     => 'png',
            'image/gif'     => 'gif',
            'image/webp'    => 'webp',
            'image/svg+xml' => 'svg',
        ];
        $ext = $ext_map[$file['type']] ?? 'png';
        $tid = sesionTenantId();
        $dir = __DIR__ . '/../../assets/img/logos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        // Eliminar logo anterior
        $old_stmt = $db->prepare("SELECT logo_path FROM public.tenant_config WHERE tenant_id = :tid");
        $old_stmt->execute([':tid' => $tid]);
        $old = $old_stmt->fetch();
        if ($old && $old['logo_path']) {
            $old_file = __DIR__ . '/../../' . $old['logo_path'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        $filename  = "logo_{$tid}.{$ext}";
        $dest      = $dir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            jsonResponse(['error' => true, 'message' => 'Error al guardar el archivo en el servidor'], 500);
        }
        $logo_path = "assets/img/logos/{$filename}";
        $db->prepare("
            INSERT INTO public.tenant_config (tenant_id, logo_path, updated_at)
            VALUES (:tid, :p, NOW())
            ON CONFLICT (tenant_id) DO UPDATE SET logo_path = EXCLUDED.logo_path, updated_at = NOW()
        ")->execute([':tid' => $tid, ':p' => $logo_path]);
        jsonResponse(['error' => false, 'message' => 'Logo actualizado correctamente', 'logo_path' => $logo_path]);

    case 'logo_eliminar':
        $tid      = sesionTenantId();
        $old_stmt = $db->prepare("SELECT logo_path FROM public.tenant_config WHERE tenant_id = :tid");
        $old_stmt->execute([':tid' => $tid]);
        $old = $old_stmt->fetch();
        if ($old && $old['logo_path']) {
            $old_file = __DIR__ . '/../../' . $old['logo_path'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        $db->prepare("
            UPDATE public.tenant_config SET logo_path = NULL, updated_at = NOW()
            WHERE tenant_id = :tid
        ")->execute([':tid' => $tid]);
        jsonResponse(['error' => false, 'message' => 'Logo eliminado']);

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
