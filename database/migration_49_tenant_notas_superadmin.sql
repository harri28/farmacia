-- migration_49_tenant_notas_superadmin.sql
-- Agrega public.tenants.notas_superadmin: notas internas de uso exclusivo
-- del panel de superadmin (modules/superadmin/empresa.php). No se expone
-- ni se edita desde el panel Admin de la propia empresa (config_guardar en
-- modules/admin/api.php no la toca).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_49_tenant_notas_superadmin.sql

ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS notas_superadmin TEXT;