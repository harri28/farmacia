<?php
http_response_code(404);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresa no encontrada — FarmaSystem</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,.4);
            width: 100%; max-width: 420px;
            padding: 40px 32px;
            text-align: center;
        }
        .icon {
            width: 56px; height: 56px; margin: 0 auto 20px;
            background: #fee2e2; color: #dc2626;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            font-weight: 700;
        }
        h1 { font-size: 1.1rem; color: #1e293b; margin-bottom: 8px; }
        p { font-size: .88rem; color: #64748b; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&times;</div>
        <h1>Empresa no encontrada</h1>
        <p>Este subdominio no corresponde a ninguna empresa registrada en el sistema.</p>
    </div>
</body>
</html>
<?php exit; ?>
