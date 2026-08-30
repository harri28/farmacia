<?php
// ============================================================
// ARCHIVO: farmacia/modules/superadmin/login.php
// Acceso exclusivo para superadministradores del sistema.
// URL directa — no enlazada desde el login de empresas.
// ============================================================

require_once __DIR__ . '/../../config/auth.php';

if (!empty($_SESSION['usuario_id']) && isSuperadmin()) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Ingresa usuario y contraseña.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare("
                SELECT id, nombre, apellido, username, password_hash
                FROM public.superadmins
                WHERE username = :u AND activo = TRUE
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
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Error al conectar con la base de datos.';
        }
    }
}

$prev_username = htmlspecialchars($_POST['username'] ?? '');
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e1b4b">
    <title>Panel de Control — FarmaSystem</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,.5);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #4f46e5, #3730a3);
            padding: 28px 32px 24px;
            text-align: center;
            color: #fff;
        }
        .brand-icon {
            width: 54px; height: 54px;
            background: rgba(255,255,255,.15);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 12px;
        }
        .brand-name { font-size: 1.25rem; font-weight: 800; letter-spacing: -.01em; }
        .brand-sub  { font-size: .78rem; opacity: .7; margin-top: 3px; }
        .card-body  { padding: 28px 32px; }
        .card-footer {
            border-top: 1px solid #f1f5f9;
            padding: 12px 32px;
            text-align: center;
            font-size: .73rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .alert {
            background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
            border-radius: 9px; padding: 10px 14px; font-size: .83rem;
            margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
        }

        .badge-admin {
            display: flex; align-items: center; gap: 10px;
            background: #eef2ff; border: 1px solid #c7d2fe;
            border-radius: 10px; padding: 10px 14px; margin-bottom: 22px;
        }
        .badge-admin i { color: #4f46e5; font-size: .9rem; }
        .badge-admin-text .title { font-size: .85rem; font-weight: 700; color: #3730a3; }
        .badge-admin-text .sub   { font-size: .72rem; color: #6366f1; margin-top: 1px; }

        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block; font-size: .76rem; font-weight: 700;
            color: #475569; text-transform: uppercase;
            letter-spacing: .05em; margin-bottom: 6px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%); color: #94a3b8; font-size: .88rem;
        }
        .form-input {
            width: 100%; padding: 11px 14px 11px 38px;
            border: 2px solid #e2e8f0; border-radius: 9px;
            font-size: .93rem; font-family: inherit;
            outline: none; transition: border-color .15s; color: #1e293b;
        }
        .form-input:focus { border-color: #4f46e5; }
        .form-input.with-toggle { padding-right: 42px; }
        .toggle-pass {
            position: absolute; right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #94a3b8;
        }
        .toggle-pass:hover { color: #475569; }

        .btn-submit {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #4f46e5, #3730a3);
            color: #fff; border: none; border-radius: 9px;
            font-size: .95rem; font-weight: 700;
            font-family: inherit; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity .15s;
        }
        .btn-submit:hover { opacity: .9; }

        .back-link {
            display: block; text-align: center; margin-top: 18px;
            font-size: .78rem; color: #94a3b8; text-decoration: none;
        }
        .back-link:hover { color: #475569; }
    </style>
</head>
<body>
<div class="card">

    <div class="card-header">
        <div class="brand-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="brand-name">Panel de Control</div>
        <div class="brand-sub">FarmaSystem — Acceso restringido</div>
    </div>

    <div class="card-body">

        <?php if ($error): ?>
        <div class="alert"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="badge-admin">
            <i class="fas fa-lock"></i>
            <div class="badge-admin-text">
                <div class="title">Superadministrador</div>
                <div class="sub">Solo personal autorizado del sistema</div>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Usuario</label>
                <div class="input-wrap">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" class="form-input"
                        value="<?= $prev_username ?>"
                        placeholder="Usuario del sistema"
                        autocomplete="username" autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div class="input-wrap">
                    <i class="fas fa-key input-icon"></i>
                    <input type="password" name="password" id="inp-pass" class="form-input with-toggle"
                        placeholder="••••••••" autocomplete="current-password">
                    <button type="button" class="toggle-pass" onclick="togglePass()">
                        <i class="fas fa-eye" id="icon-pass"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Acceder al panel
            </button>
        </form>

        <a href="../auth/login.php" class="back-link">
            <i class="fas fa-arrow-left" style="font-size:.7rem"></i> Volver al inicio de sesión
        </a>

    </div>

    <div class="card-footer">
        <i class="fas fa-shield-alt"></i>
        Acceso de sistema &mdash; FarmaSystem &copy; <?= date('Y') ?>
    </div>

</div>
<script>
function togglePass() {
    const inp  = document.getElementById('inp-pass');
    const icon = document.getElementById('icon-pass');
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
