<?php
require __DIR__ . '/../config/database.php';
$db = getDB();
try {
    $tables = ['fe_tipos_afectacion_igv', 'fe_unidades', 'fe_tipos_documento_identidad'];
    foreach ($tables as $tbl) {
        try {
            $cnt_public = $db->query("SELECT COUNT(*) FROM public.$tbl")->fetchColumn();
            echo "public.$tbl: $cnt_public rows\n";
        } catch (Exception $e) {
            echo "public.$tbl: NOT FOUND\n";
        }
        try {
            $cnt_curr = $db->query("SELECT COUNT(*) FROM $tbl")->fetchColumn();
            echo "current_schema.$tbl: $cnt_curr rows\n";
        } catch (Exception $e) {
            echo "current_schema.$tbl: NOT FOUND\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
