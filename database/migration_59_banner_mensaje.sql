-- ============================================================
-- migration_59_banner_mensaje.sql
-- Mensaje personalizado del banner de vencimiento de plan, por tenant.
-- public.tenants es una tabla GLOBAL (no per-sucursal), asi que es un
-- unico ALTER, sin el loop por esquemas.
-- Si queda vacio/NULL, el banner usa el texto dinamico por defecto
-- (conteo automatico de dias) definido en includes/header.php.
-- ============================================================

ALTER TABLE public.tenants
    ADD COLUMN IF NOT EXISTS plan_banner_mensaje TEXT;
