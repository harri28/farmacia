-- ============================================================
-- ARCHIVO: farmacia/database/migration_facturacion_empresa.sql
-- DESCRIPCION: Campos base de empresa para facturacion electronica.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_facturacion_empresa.sql
-- ============================================================

ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS business_name VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS trade_name    VARCHAR(255);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS email         VARCHAR(150);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS country_code  VARCHAR(2) DEFAULT 'PE';
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS ubigeo        VARCHAR(6);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS departamento  VARCHAR(100);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS provincia     VARCHAR(100);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS distrito      VARCHAR(100);
ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS tax_enabled   BOOLEAN DEFAULT TRUE;

UPDATE public.tenants
SET business_name = COALESCE(NULLIF(TRIM(business_name), ''), nombre)
WHERE business_name IS NULL OR TRIM(business_name) = '';

UPDATE public.tenants
SET country_code = 'PE'
WHERE country_code IS NULL OR TRIM(country_code) = '';

UPDATE public.tenants
SET tax_enabled = TRUE
WHERE tax_enabled IS NULL;
