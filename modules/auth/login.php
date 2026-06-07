<?php
// ============================================================
// ARCHIVO: farmacia/modules/auth/login.php
// Flujo: Empresa → Sucursal → Credenciales
//        (superadmin accede directamente con credenciales)
// ============================================================

require_once __DIR__ . '/../../config/auth.php';

if (!empty($_SESSION['usuario_id'])) {
    if (isSuperadmin()) {
        header('Location: ../superadmin/index.php');
    } elseif (isAdmin()) {
        header('Location: ../dashboard/index.php');
    } else {
        header('Location: ../ventas/index.php');
    }
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$error = '';

// ---- Cargar datos para el selector (renderizado server-side) ----
try {
    $db = getDB();

    $tenants = $db->query("
        SELECT id, nombre, slug FROM public.tenants WHERE activo = TRUE ORDER BY nombre ASC
    ")->fetchAll();

    $sucursales_raw = $db->query("
        SELECT id, tenant_id, nombre, direccion, telefono
        FROM public.sucursales WHERE activo = TRUE ORDER BY nombre ASC
    ")->fetchAll();

    // Agrupar sucursales por tenant para filtrado en JS
    $sucursales_por_tenant = [];
    foreach ($sucursales_raw as $s) {
        $sucursales_por_tenant[$s['tenant_id']][] = $s;
    }

} catch (Exception $e) {
    $tenants = [];
    $sucursales_por_tenant = [];
    $error = 'No se pudo conectar con la base de datos.';
}

// ---- POST: Procesar login ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {

    $is_superadmin = !empty($_POST['is_superadmin']);
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Ingresa usuario y contraseña.';
    } else {
        try {
            if ($is_superadmin) {
                // ---- Login superadmin (tabla exclusiva) ----
                $stmt = $db->prepare("
                    SELECT id, nombre, apellido, username, password_hash
                    FROM public.superadmins WHERE username = :u AND activo = TRUE
                ");
                $stmt->execute([':u' => $username]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $error = 'Usuario o contraseña incorrectos.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']    = $user['id'];
                    $_SESSION['nombre']        = trim($user['nombre'] . ' ' . ($user['apellido'] ?? ''));
                    $_SESSION['username']      = $user['username'];
                    $_SESSION['rol']           = 'superadmin';
                    $_SESSION['tenant_id']     = null;
                    $_SESSION['tenant_nombre'] = 'Sistema';
                    $_SESSION['tenant_slug']   = null;
                    header('Location: ../superadmin/index.php');
                    exit;
                }

            } else {
                // ---- Login usuario de empresa/sucursal ----
                $sucursal_id = intval($_POST['sucursal_id'] ?? 0);
                $tenant_id   = intval($_POST['tenant_id']   ?? 0);

                if (!$sucursal_id || !$tenant_id) {
                    $error = 'Selecciona una empresa y una sucursal.';
                } else {
                    $stmt = $db->prepare("
                        SELECT id, nombre, apellido, username, password_hash, tenant_id
                        FROM public.usuarios WHERE username = :u AND activo = TRUE
                    ");
                    $stmt->execute([':u' => $username]);
                    $user = $stmt->fetch();

                    if (!$user || !password_verify($password, $user['password_hash'])) {
                        $error = 'Usuario o contraseña incorrectos.';
                        try {
                            $db->prepare("INSERT INTO public.audit_log
                                (tenant_id, username, accion, modulo, detalle, ip_address)
                                VALUES (:tid, :uname, 'login_fallido', 'auth', :detalle, :ip)")
                               ->execute([
                                   ':tid'    => $tenant_id ?: null,
                                   ':uname'  => $username,
                                   ':detalle'=> 'Credenciales incorrectas',
                                   ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
                               ]);
                        } catch (Throwable $e) {}
                    } elseif ((int)$user['tenant_id'] !== $tenant_id) {
                        $error = 'No tienes acceso a esta empresa.';
                    } else {
                        $stmt2 = $db->prepare("
                            SELECT us.rol, s.nombre AS sucursal_nombre, s.schema_name,
                                   t.nombre AS tenant_nombre, t.slug AS tenant_slug
                            FROM public.usuario_sucursal us
                            JOIN public.sucursales s ON s.id = us.sucursal_id
                            JOIN public.tenants    t ON t.id = s.tenant_id
                            WHERE us.usuario_id = :uid AND us.sucursal_id = :sid
                              AND us.activo = TRUE AND s.activo = TRUE AND t.activo = TRUE
                        ");
                        $stmt2->execute([':uid' => $user['id'], ':sid' => $sucursal_id]);
                        $acceso = $stmt2->fetch();

                        if (!$acceso) {
                            $error = 'No tienes acceso asignado a esta sucursal.';
                        } else {
                            session_regenerate_id(true);
                            $_SESSION['usuario_id']      = $user['id'];
                            $_SESSION['nombre']          = trim($user['nombre'] . ' ' . ($user['apellido'] ?? ''));
                            $_SESSION['username']        = $user['username'];
                            $_SESSION['rol']             = $acceso['rol'];
                            $_SESSION['tenant_id']       = $tenant_id;
                            $_SESSION['tenant_nombre']   = $acceso['tenant_nombre'];
                            $_SESSION['tenant_slug']     = $acceso['tenant_slug'];
                            $_SESSION['sucursal_id']     = $sucursal_id;
                            $_SESSION['sucursal_nombre'] = $acceso['sucursal_nombre'];
                            $_SESSION['sucursal_schema'] = $acceso['schema_name'];
                            try {
                                $db->prepare("INSERT INTO public.audit_log
                                    (tenant_id, sucursal_id, usuario_id, username, nombre_usuario, rol, accion, modulo, detalle, ip_address)
                                    VALUES (:tid, :sid, :uid, :uname, :nombre, :rol, 'login', 'auth', :detalle, :ip)")
                                   ->execute([
                                       ':tid'    => $tenant_id,
                                       ':sid'    => $sucursal_id,
                                       ':uid'    => $user['id'],
                                       ':uname'  => $user['username'],
                                       ':nombre' => trim($user['nombre'] . ' ' . ($user['apellido'] ?? '')),
                                       ':rol'    => $acceso['rol'],
                                       ':detalle'=> 'Ingresó a ' . $acceso['sucursal_nombre'],
                                       ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
                                   ]);
                            } catch (Throwable $e) {}
                            if (in_array($acceso['rol'], ['admin', 'gerente'], true)) {
                                header('Location: ../dashboard/index.php');
                            } else {
                                header('Location: ../ventas/index.php');
                            }
                            exit;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Error al procesar el inicio de sesión.';
        }
    }
}

// Valores previos para repoblar el form en caso de error
$prev_tenant_id   = intval($_POST['tenant_id']   ?? 0);
$prev_sucursal_id = intval($_POST['sucursal_id'] ?? 0);
$prev_username    = htmlspecialchars($_POST['username'] ?? '');
$prev_superadmin  = !empty($_POST['is_superadmin']);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — FarmaSystem</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,.4);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            transition: max-width .3s ease;
        }
        .card.wide { max-width: 680px; }
        .card-header {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            padding: 26px 32px 22px;
            text-align: center;
            color: #fff;
        }
        .brand-icon { width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 10px; }
        .brand-name { font-size:1.4rem;font-weight:700; }
        .brand-sub  { font-size:.8rem;opacity:.75;margin-top:2px; }
        .card-body  { padding: 28px 32px; }
        .card-footer { border-top:1px solid #f1f5f9;padding:12px 32px;text-align:center;font-size:.75rem;color:#94a3b8; }

        /* Steps */
        .step { display:none; }
        .step.active { display:block;animation:fadeIn .2s ease; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

        .step-title { font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:4px; }
        .step-sub   { font-size:.82rem;color:#64748b;margin-bottom:20px; }

        /* Selector cards (tenants + branches) */
        .sel-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:10px;margin-bottom:4px; }
        .sel-card {
            border:2px solid #e2e8f0;border-radius:11px;padding:14px 12px;cursor:pointer;
            transition:border-color .15s,background .15s,transform .1s;
        }
        .sel-card:hover { border-color:#6366f1;background:#fafafe;transform:translateY(-1px); }
        .sel-card.selected { border-color:#6366f1;background:#eef2ff; }
        .sel-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;margin-bottom:9px; }
        .sel-icon.tenant  { background:#eef2ff;color:#6366f1; }
        .sel-icon.branch  { background:#f0fdf4;color:#16a34a; }
        .sel-nombre  { font-size:.88rem;font-weight:700;color:#1e293b; }
        .sel-sub     { font-size:.73rem;color:#64748b;margin-top:3px;line-height:1.3; }

        .empty-state { text-align:center;padding:28px 0;color:#94a3b8;font-size:.85rem; }

        /* Badge de selección */
        .sel-badge {
            display:flex;align-items:center;gap:10px;
            background:#f8fafc;border:1px solid #e2e8f0;
            border-radius:10px;padding:9px 13px;margin-bottom:18px;
        }
        .sel-badge i { color:#6366f1;font-size:.85rem;width:16px;text-align:center; }
        .sel-badge .sel-badge-text { flex:1; }
        .sel-badge .sel-badge-nombre { font-size:.85rem;font-weight:600;color:#1e293b; }
        .sel-badge .sel-badge-sub    { font-size:.73rem;color:#64748b; }
        .btn-cambiar { background:none;border:none;cursor:pointer;color:#6366f1;font-size:.75rem;font-weight:600;text-decoration:underline; }
        .btn-cambiar:hover { color:#4f46e5; }

        /* Formulario */
        .form-group { margin-bottom:16px; }
        .form-label { display:block;font-size:.78rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.04rem;margin-bottom:6px; }
        .input-wrap { position:relative; }
        .input-icon { position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.88rem; }
        .form-input { width:100%;padding:11px 14px 11px 37px;border:2px solid #e2e8f0;border-radius:9px;font-size:.93rem;font-family:inherit;outline:none;transition:border-color .15s;color:#1e293b; }
        .form-input:focus { border-color:#6366f1; }
        .form-input.with-toggle { padding-right:42px; }
        .toggle-pass { position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8; }
        .toggle-pass:hover { color:#475569; }
        .btn-submit { width:100%;padding:13px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;border-radius:9px;font-size:1rem;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s; }
        .btn-submit:hover { opacity:.92; }

        /* Alert */
        .alert { background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:9px;padding:10px 14px;font-size:.83rem;margin-bottom:16px;display:flex;align-items:center;gap:8px; }

        /* Superadmin link */
        .sa-link { text-align:center;margin-top:20px; }
        .sa-link button { background:none;border:none;cursor:pointer;font-size:.75rem;color:#94a3b8;font-family:inherit; }
        .sa-link button:hover { color:#475569; }

        /* Nav pills (back) */
        .back-btn { display:inline-flex;align-items:center;gap:6px;background:none;border:none;cursor:pointer;font-size:.8rem;color:#64748b;font-family:inherit;margin-bottom:16px;padding:0; }
        .back-btn:hover { color:#1e293b; }
    </style>
</head>
<body>
<div class="card <?= (count($tenants) > 2 && !$prev_superadmin) ? 'wide' : '' ?>" id="login-card">

    <div class="card-header">
        <div class="brand-icon"><i class="fas fa-pills"></i></div>
        <div class="brand-name">FarmaSystem</div>
        <div class="brand-sub">Sistema de Gestión de Farmacia</div>
    </div>

    <div class="card-body">

        <?php if (($_GET['reset'] ?? '') === 'ok'): ?>
        <div class="alert" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <i class="fas fa-check-circle"></i> Contraseña actualizada correctamente. Ya puedes iniciar sesión.
        </div>
        <?php elseif ($error && (!$prev_tenant_id || $prev_superadmin)): ?>
        <div class="alert"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- ============================================================
             PASO 1: Selección de empresa (tenant)
             ============================================================ -->
        <div class="step <?= (!$prev_tenant_id && !$prev_superadmin) ? 'active' : '' ?>" id="step-tenant">
            <div class="step-title">¿Con qué empresa vas a trabajar?</div>
            <div class="step-sub">Selecciona tu empresa para continuar</div>

            <?php if (empty($tenants)): ?>
            <div class="empty-state">
                <i class="fas fa-building" style="font-size:2rem;display:block;margin-bottom:10px"></i>
                No hay empresas registradas.<br>Ejecuta el setup inicial del sistema.
            </div>
            <?php else: ?>
            <div class="sel-grid" id="grid-tenants">
                <?php foreach ($tenants as $t): ?>
                <div class="sel-card" onclick="selectTenant(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['nombre'])) ?>', '<?= htmlspecialchars(addslashes($t['slug'])) ?>')">
                    <div class="sel-icon tenant"><i class="fas fa-building"></i></div>
                    <div class="sel-nombre"><?= htmlspecialchars($t['nombre']) ?></div>
                    <div class="sel-sub"><?= htmlspecialchars($t['slug']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="sa-link">
                <button type="button" onclick="showSuperadminLogin()">
                    <i class="fas fa-lock" style="font-size:.7rem"></i> Acceso de sistema
                </button>
            </div>
        </div>

        <!-- ============================================================
             PASO 2: Selección de sucursal
             ============================================================ -->
        <div class="step <?= ($prev_tenant_id && !$prev_sucursal_id && !$prev_superadmin) ? 'active' : '' ?>" id="step-branch">
            <button type="button" class="back-btn" onclick="goTo('step-tenant')">
                <i class="fas fa-arrow-left"></i> Cambiar empresa
            </button>

            <div class="sel-badge" id="badge-tenant">
                <i class="fas fa-building"></i>
                <div class="sel-badge-text">
                    <div class="sel-badge-nombre" id="badge-tenant-nombre"></div>
                    <div class="sel-badge-sub">Empresa seleccionada</div>
                </div>
            </div>

            <div class="step-title">Selecciona tu sucursal</div>
            <div class="step-sub">Elige el local en el que trabajarás hoy</div>
            <div class="sel-grid" id="grid-branches"></div>
        </div>

        <!-- ============================================================
             PASO 3: Credenciales
             ============================================================ -->
        <div class="step <?= (($prev_sucursal_id && !$prev_superadmin) || ($prev_superadmin && $error)) ? 'active' : '' ?>" id="step-creds">

            <?php if (!$prev_superadmin): ?>
            <button type="button" class="back-btn" onclick="goTo('step-branch')">
                <i class="fas fa-arrow-left"></i> Cambiar sucursal
            </button>
            <div class="sel-badge" id="badge-branch">
                <i class="fas fa-store"></i>
                <div class="sel-badge-text">
                    <div class="sel-badge-nombre" id="badge-branch-nombre"></div>
                    <div class="sel-badge-sub" id="badge-branch-sub"></div>
                </div>
            </div>
            <?php else: ?>
            <div class="sel-badge" style="background:#fef2f2;border-color:#fecaca">
                <i class="fas fa-shield-alt" style="color:#6366f1"></i>
                <div class="sel-badge-text">
                    <div class="sel-badge-nombre" style="color:#4f46e5">Acceso de sistema</div>
                    <div class="sel-badge-sub">Solo para administradores del sistema</div>
                </div>
                <button type="button" class="btn-cambiar" onclick="goTo('step-tenant')">Cancelar</button>
            </div>
            <?php endif; ?>

            <?php if ($error && ($prev_sucursal_id || $prev_superadmin)): ?>
            <div class="alert"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="form-login">
                <input type="hidden" name="tenant_id"      id="h-tenant-id"    value="<?= $prev_tenant_id ?>">
                <input type="hidden" name="sucursal_id"    id="h-sucursal-id"  value="<?= $prev_sucursal_id ?>">
                <input type="hidden" name="is_superadmin"  id="h-superadmin"   value="<?= $prev_superadmin ? '1' : '' ?>">

                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <div class="input-wrap">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" id="inp-username" class="form-input"
                            value="<?= $prev_username ?>"
                            placeholder="Tu nombre de usuario" autocomplete="username">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="inp-password" class="form-input with-toggle"
                            placeholder="••••••••" autocomplete="current-password">
                        <button type="button" class="toggle-pass" onclick="togglePass()">
                            <i class="fas fa-eye" id="icon-pass"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>
                <div style="text-align:center;margin-top:14px">
                    <a href="recuperar.php" style="font-size:.78rem;color:#6366f1;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
                        <i class="fas fa-key" style="font-size:.7rem"></i> ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </form>
        </div>

    </div>

    <div class="card-footer">FarmaSystem &copy; <?= date('Y') ?> · v1.0</div>
</div>

<script>
const card          = document.getElementById('login-card');
const SUCURSALES    = <?= json_encode($sucursales_por_tenant) ?>;
const HAS_MANY_TEN  = <?= count($tenants) > 2 ? 'true' : 'false' ?>;

let selectedTenantId   = <?= $prev_tenant_id   ?: 'null' ?>;
let selectedTenantName = '';
let selectedBranchId   = <?= $prev_sucursal_id ?: 'null' ?>;
let selectedBranchName = '';

// ---- Repoblar badges si hay errores de vuelta ----
<?php if ($prev_tenant_id && !$prev_superadmin): ?>
(function(){
    <?php foreach ($tenants as $t): if ((int)$t['id'] === $prev_tenant_id): ?>
    selectedTenantName = '<?= htmlspecialchars(addslashes($t['nombre'])) ?>';
    document.getElementById('badge-tenant-nombre').textContent = selectedTenantName;
    renderBranches(<?= $prev_tenant_id ?>);
    <?php endif; endforeach; ?>
    <?php if ($prev_sucursal_id): ?>
    <?php foreach ($sucursales_raw as $s): if ((int)$s['id'] === $prev_sucursal_id): ?>
    selectedBranchName = '<?= htmlspecialchars(addslashes($s['nombre'])) ?>';
    document.getElementById('badge-branch-nombre').textContent = selectedBranchName;
    document.getElementById('badge-branch-sub').textContent    = '<?= htmlspecialchars(addslashes($s['direccion'] ?? '')) ?>';
    <?php endif; endforeach; ?>
    <?php endif; ?>
})();
<?php endif; ?>

function goTo(stepId) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById(stepId).classList.add('active');
    card.classList.toggle('wide', stepId === 'step-tenant' && HAS_MANY_TEN);
    if (stepId === 'step-creds') setTimeout(() => document.getElementById('inp-username').focus(), 80);
}

function selectTenant(id, nombre, slug) {
    selectedTenantId = id;
    selectedTenantName = nombre;
    document.getElementById('h-tenant-id').value  = id;
    document.getElementById('h-superadmin').value = '';
    document.getElementById('badge-tenant-nombre').textContent = nombre;
    renderBranches(id);
    goTo('step-branch');
}

function renderBranches(tenantId) {
    const branches = SUCURSALES[tenantId] || [];
    const grid = document.getElementById('grid-branches');
    if (!branches.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-store-slash" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>No hay sucursales disponibles para esta empresa.</div>';
        return;
    }
    grid.innerHTML = branches.map(b => `
        <div class="sel-card" onclick="selectBranch(${b.id},'${esc(b.nombre)}','${esc(b.direccion||'')}')">
            <div class="sel-icon branch"><i class="fas fa-store"></i></div>
            <div class="sel-nombre">${esc(b.nombre)}</div>
            ${b.direccion ? `<div class="sel-sub">${esc(b.direccion)}</div>` : ''}
        </div>`).join('');
}

function selectBranch(id, nombre, dir) {
    selectedBranchId = id;
    selectedBranchName = nombre;
    document.getElementById('h-sucursal-id').value = id;
    document.getElementById('badge-branch-nombre').textContent = nombre;
    document.getElementById('badge-branch-sub').textContent    = dir || selectedTenantName;
    goTo('step-creds');
}

function showSuperadminLogin() {
    document.getElementById('h-tenant-id').value   = '';
    document.getElementById('h-sucursal-id').value = '';
    document.getElementById('h-superadmin').value  = '1';
    goTo('step-creds');
}

function togglePass() {
    const inp  = document.getElementById('inp-password');
    const icon = document.getElementById('icon-pass');
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>
</body>
</html>
