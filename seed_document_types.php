<?php
require_once 'config/database.php';

$db = getDB();

// Create table if not exists
$createTableSQL = "
CREATE TABLE IF NOT EXISTS public.fe_tipos_documento_identidad (
    id                    SERIAL PRIMARY KEY,
    codigo                VARCHAR(1) NOT NULL UNIQUE,
    descripcion           VARCHAR(255) NOT NULL,
    descripcion_documento VARCHAR(100) NOT NULL,
    estado                BOOLEAN DEFAULT TRUE,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

$data = [
    ['1', 'DOCUMENTO NACIONAL DE IDENTIDAD (DNI)', 'DNI'],
    ['6', 'REGISTRO UNICO DE CONTRIBUYENTES (RUC)', 'RUC'],
    ['4', 'CARNET DE EXTRANJERIA', 'CARNET DE EXTRANJERIA'],
    ['0', 'OTRO TIPO DE DOCUMENTO', 'OTROS'],
    ['7', 'PASAPORTE', 'PASAPORTE'],
    ['A', 'CEDULA DIPLOMATICA DE IDENTIDAD', 'CEDULA DIPLOMATICA DE IDENTIDAD'],
];

try {
    // Create table
    $db->exec($createTableSQL);
    echo "Tabla creada o ya existe.\n";
    
    // Populate data
    foreach ($data as $item) {
        $stmt = $db->prepare("
            INSERT INTO public.fe_tipos_documento_identidad (codigo, descripcion, descripcion_documento, estado)
            VALUES (:codigo, :descripcion, :descripcion_documento, TRUE)
            ON CONFLICT (codigo) DO NOTHING
        ");
        $stmt->execute([
            ':codigo' => $item[0],
            ':descripcion' => $item[1],
            ':descripcion_documento' => $item[2],
        ]);
    }
    
    // Verify
    $result = $db->query("SELECT COUNT(*) as total FROM public.fe_tipos_documento_identidad");
    $count = $result->fetch()['total'];
    echo json_encode(['success' => true, 'message' => "Tipos de documento sincronizados. Total: $count"]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
