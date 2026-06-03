-- ============================================================
-- ARCHIVO: farmacia/database/migration_03_facturacion_seed_mytems.sql
-- DESCRIPCION: Paso 3 - datos base Mytems para configuracion demo.
-- NOTA:
--   Mantiene el logo actual del tenant. Solo actualiza datos de empresa
--   y credenciales base para pruebas/configuracion inicial.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_03_facturacion_seed_mytems.sql
-- ============================================================

UPDATE public.tenants
SET nombre                 = 'MYTEMS E.I.R.L.',
    ruc                    = '20610316884',
    business_name          = 'MYTEMS E.I.R.L.',
    trade_name             = 'MYTEMS REPARA',
    email                  = 'admin@mytems.cloud',
    telefono               = '950772205',
    direccion              = 'Jr. Manco Capac 456',
    country_code           = 'PE',
    ubigeo                 = '220901',
    departamento           = 'San Martin',
    provincia              = 'San Martin',
    distrito               = 'Tarapoto',
    api_url                = 'https://api.mytems.cloud',
    whatsapp_instance      = 'NTE5NTA3NzIyMDU=',
    tax_enabled            = TRUE,
    sunat_username         = 'MYTEMS23',
    sunat_password         = 'Mytems23',
    gre_client_id          = '0be1bb0d-3a73-4fb9-a777-32732749f14f',
    gre_client_secret      = 'A/0j3KRVQhnsugi70znTyA==',
    certificate_path       = 'facturacion/certs/certificado_prueba.pfx',
    certificate_password   = 'mytems2022',
    certificate_expires_at = DATE '2026-12-06',
    sunat_server           = '3'
WHERE slug = 'generic_pharma';
