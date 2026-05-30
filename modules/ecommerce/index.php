<?php
$base_path      = '../../';
$current_module = 'ecommerce';
$current_page   = 'config';
$page_title     = 'Configuración E-commerce';
$breadcrumb     = 'E-commerce / Configuración';
$required_roles = [];

require_once $base_path . 'config/database.php';
require_once $base_path . 'includes/header.php';

// Obtener schemas disponibles
$pdo = getDB();
$schemas = $pdo->query("
    SELECT schema_name FROM information_schema.schemata
    WHERE schema_name NOT IN ('public','information_schema','pg_catalog','pg_toast')
    AND schema_name NOT LIKE 'pg_%'
    ORDER BY schema_name
")->fetchAll(PDO::FETCH_COLUMN);

// Test de conexión
$testResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_schema'])) {
    $ts = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['test_schema'] ?? '');
    try {
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM {$ts}.productos WHERE activo = TRUE");
        $count = $stmt->fetchColumn();
        $testResult = ['ok' => true, 'msg' => "Conexión exitosa — $count productos activos en schema '$ts'"];
    } catch (Exception $e) {
        $testResult = ['ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
    }
}
?>

<div style="max-width:800px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
        <div style="width:44px;height:44px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem">
            <i class="fas fa-store"></i>
        </div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:700">Módulo E-commerce</h2>
            <p style="color:var(--text-muted);font-size:.88rem">Configuración de conexión con Selvadigital</p>
        </div>
    </div>

    <!-- Info -->
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius);padding:16px;margin-bottom:24px;font-size:.9rem">
        <strong><i class="fas fa-info-circle" style="color:#3b82f6"></i> API Pública</strong><br>
        Esta API expone el catálogo de productos, categorías y stock al sistema e-commerce Selvadigital.<br>
        <code style="background:#dbeafe;padding:3px 8px;border-radius:4px;font-size:.82rem;margin-top:6px;display:inline-block">
            <?= htmlspecialchars('http://localhost/farmacia/modules/ecommerce/api.php?action=productos&schema={schema}') ?>
        </code>
    </div>

    <!-- Schemas disponibles -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fas fa-database"></i> Schemas disponibles</div>
        <?php if ($schemas): ?>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
            <?php foreach ($schemas as $s): ?>
            <span style="background:var(--surface-3);border:1px solid var(--border);padding:4px 12px;border-radius:20px;font-family:monospace;font-size:.85rem"><?= htmlspecialchars($s) ?></span>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:var(--text-muted)">No se encontraron schemas.</p>
        <?php endif; ?>

        <!-- Test conexión -->
        <form method="POST" style="display:flex;gap:10px;align-items:flex-end">
            <div style="flex:1">
                <label class="form-label">Probar schema</label>
                <input type="text" name="test_schema" class="form-control" placeholder="nombre_schema" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plug"></i> Probar</button>
        </form>

        <?php if ($testResult): ?>
        <div class="alert <?= $testResult['ok'] ? 'alert-success' : 'alert-error' ?>" style="margin-top:12px;margin-bottom:0">
            <?= htmlspecialchars($testResult['msg']) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Endpoints disponibles -->
    <div class="card">
        <div class="card-title"><i class="fas fa-code"></i> Endpoints disponibles</div>
        <?php
        $endpoints = [
            ['GET', '?action=categorias&schema=X', 'Lista de categorías'],
            ['GET', '?action=productos&schema=X',  'Catálogo de productos activos con stock > 0'],
            ['GET', '?action=stock&schema=X&producto_id=Y', 'Stock actual de un producto'],
        ];
        ?>
        <table style="width:100%;border-collapse:collapse;font-size:.85rem">
            <thead><tr style="border-bottom:1px solid var(--border)">
                <th style="padding:8px;text-align:left;color:var(--text-muted)">Método</th>
                <th style="padding:8px;text-align:left;color:var(--text-muted)">Endpoint</th>
                <th style="padding:8px;text-align:left;color:var(--text-muted)">Descripción</th>
            </tr></thead>
            <tbody>
            <?php foreach ($endpoints as $ep): ?>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:8px"><span style="background:var(--primary-bg);color:var(--primary);padding:2px 8px;border-radius:4px;font-weight:700;font-size:.78rem"><?= $ep[0] ?></span></td>
                <td style="padding:8px;font-family:monospace;font-size:.8rem"><?= htmlspecialchars($ep[1]) ?></td>
                <td style="padding:8px;color:var(--text-muted)"><?= $ep[2] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:16px;text-align:center">
            <a href="/ecomerce/admin/config.php" target="_blank" class="btn btn-outline">
                <i class="fas fa-external-link-alt"></i> Abrir panel Selvadigital
            </a>
        </div>
    </div>
</div>

<?php require_once $base_path . 'includes/footer.php'; ?>
