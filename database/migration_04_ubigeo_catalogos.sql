-- ============================================================
-- ARCHIVO: farmacia/database/migration_04_ubigeo_catalogos.sql
-- DESCRIPCION: Paso 4 - tablas base para catalogos de ubigeo.
-- REFERENCIA: Estructura inspirada en ventas-up2026.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_04_ubigeo_catalogos.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS public.ubigeo_departamentos (
    codigo     VARCHAR(2) PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.ubigeo_provincias (
    codigo               VARCHAR(4) PRIMARY KEY,
    departamento_codigo  VARCHAR(2) NOT NULL REFERENCES public.ubigeo_departamentos(codigo),
    nombre               VARCHAR(100) NOT NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.ubigeo_distritos (
    codigo               VARCHAR(6) PRIMARY KEY,
    provincia_codigo     VARCHAR(4) NOT NULL REFERENCES public.ubigeo_provincias(codigo),
    departamento_codigo  VARCHAR(2) NOT NULL REFERENCES public.ubigeo_departamentos(codigo),
    nombre               VARCHAR(100) NOT NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ubigeo_provincias_departamento
    ON public.ubigeo_provincias (departamento_codigo);

CREATE INDEX IF NOT EXISTS idx_ubigeo_distritos_provincia
    ON public.ubigeo_distritos (provincia_codigo);

CREATE INDEX IF NOT EXISTS idx_ubigeo_distritos_departamento
    ON public.ubigeo_distritos (departamento_codigo);
