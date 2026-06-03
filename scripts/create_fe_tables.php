<?php
require __DIR__ . '/../config/database.php';
$db = getDB();

// Crear tabla fe_tipos_afectacion_igv en el esquema actual
$sql = "
CREATE TABLE IF NOT EXISTS fe_tipos_afectacion_igv (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(10) NOT NULL,
    codigo VARCHAR(5) NOT NULL,
    descripcion VARCHAR(200),
    activo BOOLEAN DEFAULT TRUE
);

INSERT INTO fe_tipos_afectacion_igv (tipo, codigo, descripcion, activo) 
SELECT 'GRAV', '10', 'Gravada - Operación Onerosa', true
WHERE NOT EXISTS (SELECT 1 FROM fe_tipos_afectacion_igv WHERE codigo = '10');

INSERT INTO fe_tipos_afectacion_igv (tipo, codigo, descripcion, activo)
SELECT 'EXO', '20', 'Exonerada - Exonerada de IGV', true
WHERE NOT EXISTS (SELECT 1 FROM fe_tipos_afectacion_igv WHERE codigo = '20');

INSERT INTO fe_tipos_afectacion_igv (tipo, codigo, descripcion, activo)
SELECT 'INA', '30', 'Inafecta - Operación Inafecta', true
WHERE NOT EXISTS (SELECT 1 FROM fe_tipos_afectacion_igv WHERE codigo = '30');
";

try {
    $db->exec($sql);
    echo "Table fe_tipos_afectacion_igv created/initialized in schema: " . $db->query("SELECT current_schema()")->fetchColumn() . "\n";
    echo "Rows inserted.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
