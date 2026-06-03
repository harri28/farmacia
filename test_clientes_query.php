<?php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['sucursal_schema'] = 'generic_pharma_jr_lima_tambo_408';

require __DIR__ . '/config/database.php';
$db = getDB();

// Test clientes query
$q = '%%';
$stmt = $db->prepare("
    SELECT
        c.id,
        c.nombres,
        c.apellidos,
        c.dni,
        c.ruc,
        c.telefono,
        c.email,
        c.direccion,
        c.activo,
        c.created_at,
        COUNT(v.id) FILTER (WHERE v.estado = 'completada') AS total_compras,
        COALESCE(SUM(v.total) FILTER (WHERE v.estado = 'completada'), 0) AS total_gastado,
        MAX(v.created_at) FILTER (WHERE v.estado = 'completada') AS ultima_compra
    FROM clientes c
    LEFT JOIN ventas v ON v.cliente_id = c.id
    WHERE (COALESCE(c.nombres,'') ILIKE :q
      OR COALESCE(c.apellidos,'') ILIKE :q
      OR COALESCE(c.dni,'') ILIKE :q
      OR COALESCE(c.ruc,'') ILIKE :q
      OR COALESCE(c.telefono,'') ILIKE :q
      OR COALESCE(c.email,'') ILIKE :q)
    GROUP BY c.id
    ORDER BY
        CASE
            WHEN COALESCE(c.dni, '') = '00000000'
            THEN 0
            ELSE 1
        END,
        COALESCE(c.nombres, '') ASC
");
$stmt->execute([':q' => $q]);
$results = $stmt->fetchAll();

echo "Clientes loaded: " . count($results) . "\n";
if (count($results) > 0) {
    echo json_encode($results[0], JSON_PRETTY_PRINT) . "\n";
}
?>
