<?php
$base_path      = '../../';
$current_module = 'ecommerce';
$current_page   = 'config';
$page_title     = 'E-commerce API';
$breadcrumb     = 'E-commerce / API';
$required_roles = ['admin', 'gerente'];

require_once $base_path . 'config/database.php';
require_once $base_path . 'includes/header.php';

$schema     = sesionSchema();
$base_url   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . $_SERVER['HTTP_HOST'];
$api_url    = $base_url . '/farmacia/modules/ecommerce/api.php';
?>

<style>
.api-explorer { max-width: 960px; }
.api-hero { display:flex; align-items:center; gap:14px; margin-bottom:24px; }
.api-hero-icon { width:48px;height:48px;background:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0; }
.endpoint-btns { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.ep-btn { display:flex;align-items:center;gap:7px;padding:9px 16px;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;font-size:.84rem;font-weight:600;color:var(--text);transition:all .15s; }
.ep-btn:hover, .ep-btn.active { border-color:var(--primary);color:var(--primary);background:var(--primary-bg); }
.ep-btn i { font-size:.9rem; }
.url-bar { display:flex;align-items:center;gap:8px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:.8rem;font-family:monospace;overflow-x:auto; }
.url-bar span { color:var(--text-muted);white-space:nowrap;flex:1; }
.url-bar button { flex-shrink:0;background:none;border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:.75rem;cursor:pointer;color:var(--text-light); }
.url-bar button:hover { border-color:var(--primary);color:var(--primary); }
.stock-row { display:flex;align-items:center;gap:8px;margin-bottom:12px; }
.stock-row input { flex:1;max-width:200px; }
.json-viewer { background:#1e1e1e;color:#d4d4d4;border-radius:10px;padding:18px;font-family:'Courier New',monospace;font-size:.82rem;line-height:1.6;overflow:auto;max-height:520px;min-height:120px;white-space:pre; }
.json-viewer .jk { color:#9cdcfe; }   /* key     */
.json-viewer .js { color:#ce9178; }   /* string  */
.json-viewer .jn { color:#b5cea8; }   /* number  */
.json-viewer .jb { color:#569cd6; }   /* boolean */
.json-viewer .jnull { color:#808080; }/* null    */
.json-meta { display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;font-size:.78rem;color:var(--text-muted); }
.json-meta .badge { padding:2px 8px;border-radius:12px;font-weight:600;font-size:.74rem; }
.badge-ok  { background:#dcfce7;color:#16a34a; }
.badge-err { background:#fee2e2;color:#dc2626; }
</style>

<div class="api-explorer">

    <div class="api-hero">
        <div class="api-hero-icon"><i class="fas fa-store"></i></div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:700;margin:0">E-commerce API</h2>
            <p style="color:var(--text-muted);font-size:.85rem;margin:2px 0 0">
                Expone el inventario de <strong><?= htmlspecialchars($schema) ?></strong> al sistema externo
            </p>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px">
        <div class="card-title" style="margin-bottom:14px"><i class="fas fa-terminal"></i> Explorador de endpoints</div>

        <!-- Botones de endpoint -->
        <div class="endpoint-btns" id="ep-btns">
            <button class="ep-btn active" data-action="productos" onclick="selectEndpoint(this)">
                <i class="fas fa-boxes"></i> Productos
            </button>
            <button class="ep-btn" data-action="categorias" onclick="selectEndpoint(this)">
                <i class="fas fa-tags"></i> Categorías
            </button>
            <button class="ep-btn" data-action="stock" onclick="selectEndpoint(this)">
                <i class="fas fa-chart-bar"></i> Stock producto
            </button>
        </div>

        <!-- Input producto_id solo para stock -->
        <div class="stock-row" id="stock-row" style="display:none">
            <input type="number" id="producto-id" class="form-control" placeholder="ID del producto" min="1">
        </div>

        <!-- URL generada -->
        <div class="url-bar">
            <span id="url-display"><?= htmlspecialchars($api_url) ?>?action=productos&amp;schema=<?= htmlspecialchars($schema) ?></span>
            <button onclick="copyUrl()"><i class="fas fa-copy"></i> Copiar</button>
            <button onclick="runQuery()" id="btn-run" style="border-color:var(--primary);color:var(--primary);font-weight:700">
                <i class="fas fa-play"></i> Ejecutar
            </button>
        </div>

        <!-- Meta: estado y conteo -->
        <div class="json-meta" id="json-meta" style="display:none">
            <span id="json-status"></span>
            <span id="json-count"></span>
        </div>

        <!-- Visor JSON -->
        <div class="json-viewer" id="json-viewer">Selecciona un endpoint y haz clic en <strong style="color:#9cdcfe">Ejecutar</strong> para ver la respuesta.</div>
    </div>

    <!-- Referencia de endpoints -->
    <div class="card">
        <div class="card-title"><i class="fas fa-book"></i> Referencia de la API</div>
        <table style="width:100%;border-collapse:collapse;font-size:.84rem">
            <thead>
                <tr style="border-bottom:2px solid var(--border)">
                    <th style="padding:8px 10px;text-align:left;color:var(--text-muted);font-weight:600">Endpoint</th>
                    <th style="padding:8px 10px;text-align:left;color:var(--text-muted);font-weight:600">Parámetros</th>
                    <th style="padding:8px 10px;text-align:left;color:var(--text-muted);font-weight:600">Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:8px 10px;font-family:monospace;color:var(--primary)">?action=productos</td>
                    <td style="padding:8px 10px;font-family:monospace;font-size:.78rem;color:var(--text-muted)">schema</td>
                    <td style="padding:8px 10px;color:var(--text-muted)">Catálogo completo con precio, stock, categoría e imagen</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:8px 10px;font-family:monospace;color:var(--primary)">?action=categorias</td>
                    <td style="padding:8px 10px;font-family:monospace;font-size:.78rem;color:var(--text-muted)">schema</td>
                    <td style="padding:8px 10px;color:var(--text-muted)">Lista de categorías activas</td>
                </tr>
                <tr>
                    <td style="padding:8px 10px;font-family:monospace;color:var(--primary)">?action=stock</td>
                    <td style="padding:8px 10px;font-family:monospace;font-size:.78rem;color:var(--text-muted)">schema, producto_id</td>
                    <td style="padding:8px 10px;color:var(--text-muted)">Stock actual de un producto específico</td>
                </tr>
            </tbody>
        </table>
        <div style="margin-top:14px;padding:12px;background:var(--surface-2);border-radius:8px;font-size:.82rem;color:var(--text-muted)">
            <i class="fas fa-lock-open" style="color:var(--primary)"></i>
            La API es pública (sin autenticación) y de solo lectura. Origen CORS abierto para integraciones externas.
        </div>
    </div>
</div>

<script>
const API_BASE  = '<?= htmlspecialchars($api_url) ?>';
const SCHEMA    = '<?= htmlspecialchars($schema) ?>';
let currentAction = 'productos';

function selectEndpoint(btn) {
    document.querySelectorAll('.ep-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentAction = btn.dataset.action;
    document.getElementById('stock-row').style.display = currentAction === 'stock' ? 'flex' : 'none';
    updateUrlBar();
}

function buildUrl() {
    let url = `${API_BASE}?action=${currentAction}&schema=${encodeURIComponent(SCHEMA)}`;
    if (currentAction === 'stock') {
        const id = document.getElementById('producto-id').value.trim();
        if (id) url += `&producto_id=${encodeURIComponent(id)}`;
    }
    return url;
}

function updateUrlBar() {
    document.getElementById('url-display').textContent = buildUrl().replace(/&/g, '&');
}

document.getElementById('producto-id')?.addEventListener('input', updateUrlBar);

function copyUrl() {
    navigator.clipboard.writeText(buildUrl()).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copiado';
        setTimeout(() => btn.innerHTML = orig, 1500);
    });
}

async function runQuery() {
    const btn = document.getElementById('btn-run');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando…';
    const viewer = document.getElementById('json-viewer');
    viewer.innerHTML = 'Consultando…';

    try {
        const t0  = Date.now();
        const res = await fetch(buildUrl());
        const ms  = Date.now() - t0;
        const raw = await res.json();

        const metaEl = document.getElementById('json-meta');
        const statusEl = document.getElementById('json-status');
        const countEl  = document.getElementById('json-count');
        metaEl.style.display = 'flex';

        const ok = raw.success !== false;
        statusEl.innerHTML = `<span class="badge ${ok ? 'badge-ok' : 'badge-err'}">${ok ? '200 OK' : 'Error'}</span> &nbsp;${ms}ms`;
        const count = Array.isArray(raw.data) ? raw.data.length : (raw.stock !== undefined ? 1 : '—');
        countEl.textContent = Array.isArray(raw.data) ? `${count} registro${count !== 1 ? 's' : ''}` : '';

        viewer.innerHTML = syntaxHighlight(JSON.stringify(raw, null, 2));
    } catch (e) {
        viewer.innerHTML = `<span style="color:#f87171">Error al conectar: ${e.message}</span>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play"></i> Ejecutar';
    }
}

function syntaxHighlight(json) {
    return json
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, m => {
            if (/^"/.test(m)) return /:$/.test(m) ? `<span class="jk">${m}</span>` : `<span class="js">${m}</span>`;
            if (/true|false/.test(m)) return `<span class="jb">${m}</span>`;
            if (/null/.test(m))       return `<span class="jnull">${m}</span>`;
            return `<span class="jn">${m}</span>`;
        });
}

updateUrlBar();
</script>

<?php require_once $base_path . 'includes/footer.php'; ?>
