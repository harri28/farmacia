-- ============================================================
-- ARCHIVO: farmacia/database/migration_06_facturacion_datos_botica.sql
-- DESCRIPCION: Paso 6 - datos base del negocio para arrancar la
--              configuracion de la botica sin tocar el logo actual.
-- NOTA:
--   Mantiene credenciales y certificado ya cargados. Solo acomoda
--   nombre visible del negocio, direccion y ubigeo base.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_06_facturacion_datos_botica.sql
-- ============================================================

UPDATE public.tenants
SET nombre                 = 'Generic Pharma',
    business_name          = 'Generic Pharma',
    trade_name             = 'Generic Pharma',
    direccion              = 'Alonso de Alvarado 209, Tarapoto',
    country_code           = 'PE',
    ubigeo                 = '220901',
    departamento           = 'San Martin',
    provincia              = 'San Martin',
    distrito               = 'Tarapoto',
    tax_enabled            = TRUE,
    ruc                    = COALESCE(NULLIF(ruc, ''), '20610316884'),
    email                  = COALESCE(NULLIF(email, ''), 'admin@mytems.cloud'),
    telefono               = COALESCE(NULLIF(telefono, ''), '950772205'),
    sunat_username         = COALESCE(NULLIF(sunat_username, ''), 'MYTEMS23'),
    sunat_password         = COALESCE(NULLIF(sunat_password, ''), 'Mytems23'),
    gre_client_id          = COALESCE(NULLIF(gre_client_id, ''), '0be1bb0d-3a73-4fb9-a777-32732749f14f'),
    gre_client_secret      = COALESCE(NULLIF(gre_client_secret, ''), 'A/0j3KRVQhnsugi70znTyA=='),
    certificate_path       = COALESCE(NULLIF(certificate_path, ''), 'facturacion/certs/20610316884.pfx'),
    certificate_password   = COALESCE(NULLIF(certificate_password, ''), 'mytems2022'),
    certificate_expires_at = COALESCE(certificate_expires_at, DATE '2026-12-06'),
    sunat_server           = COALESCE(NULLIF(sunat_server, ''), '3')
WHERE slug = 'generic_pharma';
