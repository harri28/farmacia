-- ============================================================
-- ARCHIVO: farmacia/database/migration_10_facturacion_certificado_mytems.sql
-- DESCRIPCION: Paso 10 - deja configurado el certificado base
--              dentro del proyecto para la empresa actual.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_10_facturacion_certificado_mytems.sql
-- ============================================================

UPDATE public.tenants
SET certificate_path = 'facturacion/certs/20610316884.pfx',
    certificate_password = COALESCE(NULLIF(certificate_password, ''), 'mytems2022'),
    certificate_expires_at = COALESCE(certificate_expires_at, DATE '2026-12-06')
WHERE slug = 'generic_pharma';
