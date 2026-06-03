-- ============================================================
-- ARCHIVO: farmacia/database/migration_07_facturacion_catalogos_base.sql
-- DESCRIPCION: Paso 7 - tablas base de catalogos para
--              facturacion electronica compartidas por todas
--              las sucursales y empresas del sistema.
-- REFERENCIA: Estructura inspirada en ventas-up2026.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_07_facturacion_catalogos_base.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS public.fe_tipos_documento (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(2) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_tipos_documento_identidad (
    id                     SERIAL PRIMARY KEY,
    codigo                 VARCHAR(1) NOT NULL UNIQUE,
    descripcion            VARCHAR(255) NOT NULL,
    descripcion_documento  VARCHAR(100) NOT NULL,
    estado                 BOOLEAN DEFAULT TRUE,
    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_unidades (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(3) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_formas_pago (
    id          SERIAL PRIMARY KEY,
    descripcion VARCHAR(100) NOT NULL UNIQUE,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_tipos_afectacion_igv (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(2) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    tipo        VARCHAR(5) NOT NULL,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_monedas (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(3) NOT NULL UNIQUE,
    descripcion VARCHAR(100) NOT NULL,
    pais        VARCHAR(100) NOT NULL,
    simbolo     VARCHAR(5) NOT NULL,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_tipos_nota_credito (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(2) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_tipos_nota_debito (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(2) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.fe_tipos_operacion (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(4) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
