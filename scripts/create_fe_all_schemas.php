<?php
require __DIR__ . '/../config/database.php';
$db = getDB();

// Obtener lista de esquemas de sucursales
$schemas = $db->query("
    SELECT DISTINCT nspname 
    FROM pg_namespace 
    WHERE nspname LIKE 'generic_pharma_%' OR nspname LIKE 'mari_boticas_%'
    ORDER BY nspname
")->fetchAll(PDO::FETCH_COLUMN);

echo "Found " . count($schemas) . " schemas\n";

foreach ($schemas as $schema) {
    try {
        // Cambiar al esquema
        $db->exec("SET search_path TO {$schema}, public");
        
        // Crear tabla si no existe
        $db->exec("
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
        ");
        echo "✓ Schema $schema: table created\n";
    } catch (Exception $e) {
        echo "✗ Schema $schema: " . $e->getMessage() . "\n";
    }
}
?>
