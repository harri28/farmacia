-- ============================================================
-- ARCHIVO: farmacia/database/migration_02_facturacion_empresa_sunat.sql
-- DESCRIPCION: Paso 2 - datos SUNAT y servicios auxiliares.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_02_facturacion_empresa_sunat.sql
-- ============================================================

ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS api_url                VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS whatsapp_instance      VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS sunat_username         VARCHAR(100);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS sunat_password         VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS gre_client_id          VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS gre_client_secret      TEXT;
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS certificate_path       VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS certificate_password   VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS certificate_expires_at DATE;
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS sunat_server           VARCHAR(10);
