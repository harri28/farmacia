<!DOCTYPE html>
<html>
<head>
    <title>Debug Clientes</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .debug { background: #f9f9f9; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
    </style>Ay
</head>
<body>

<h1>Debug Clientes</h1>

<div class="debug" id="api-response"></div>
<div class="debug" id="render-result"></div>

<h2>Tabla Renderizada</h2>
<table id="result-table" style="display:none;">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Teléfono</th>
            <th>Compras</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody id="tbody-debug"></tbody>
</table>

<script>
async function test() {
    const apiUrl = '/farmacia/modules/clientes/api.php';
    
    // First check session/debug
    try {
        const debugR = await fetch(`${apiUrl}?action=debug`);
        const debugData = await debugR.json();
        document.getElementById('render-result').innerHTML = '<strong>Session Info:</strong><pre>' + JSON.stringify(debugData, null, 2) + '</pre>';
    } catch (e) {
        document.getElementById('render-result').innerHTML = `<strong style="color:red">Debug Error: ${e.message}</strong>`;
    }
    
    // Then try to load clientes
    try {
        const r = await fetch(`${apiUrl}?action=listar&q=%25&estado=`);
        const text = await r.text();
        
        // Try parsing as JSON
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            document.getElementById('api-response').innerHTML = '<strong style="color:red">JSON Parse Error:</strong><pre>' + text.substring(0, 500) + '</pre>';
            return;
        }
        
        document.getElementById('api-response').innerHTML = '<strong>API Response:</strong><pre>' + JSON.stringify(data, null, 2) + '</pre>';
        
        if (!Array.isArray(data)) {
            document.getElementById('render-result').innerHTML += '<p style="color:red"><strong>ERROR: API did not return array</strong></p>';
            return;
        }
        
        // Simulate rendering
        const tbody = document.getElementById('tbody-debug');
        tbody.innerHTML = data.map(c => {
            const nombre = [c.nombres, c.apellidos].filter(Boolean).join(' ').trim() || 'Cliente';
            const documento = c.dni || c.ruc || '-';
            return `
                <tr>
                    <td>${nombre}</td>
                    <td>${documento}</td>
                    <td>${c.telefono || '-'}</td>
                    <td>${c.total_compras ?? 0}</td>
                    <td>S/ ${(parseFloat(c.total_gastado) || 0).toFixed(2)}</td>
                </tr>
            `;
        }).join('');
        
        document.getElementById('result-table').style.display = 'table';
        document.getElementById('render-result').innerHTML += `<p><strong>SUCCESS: ${data.length} cliente${data.length === 1 ? '' : 's'} rendered</strong></p>`;
        
    } catch (e) {
        document.getElementById('api-response').innerHTML = `<strong style="color:red">Fetch ERROR: ${e.message}</strong>`;
    }
}

window.onload = test;
</script>

</body>
</html>
