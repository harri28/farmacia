-- ============================================================
-- MIGRACION 50: Modo oscuro a nivel de empresa (tenant)
-- ============================================================
-- Agrega la columna tema_oscuro a public.tenant_config para que
-- el admin/gerente pueda activar el modo oscuro para todos los
-- usuarios de su empresa desde Admin > Configuracion.

ALTER TABLE public.tenant_config
    ADD COLUMN IF NOT EXISTS tema_oscuro BOOLEAN NOT NULL DEFAULT FALSE;
