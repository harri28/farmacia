-- ============================================================
-- MIGRACIÓN: Fecha de Vencimiento en Productos
-- Ejecutar: http://localhost/farmacia/database/migration_fecha_vencimiento.php
-- ============================================================

ALTER TABLE productos ADD COLUMN IF NOT EXISTS fecha_vencimiento DATE;
