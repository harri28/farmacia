-- ============================================================
-- ARCHIVO: farmacia/database/deploy_production.sql
-- DESCRIPCIÓN: Script completo de instalación en producción.
--              Ejecutar UNA SOLA VEZ en el servidor de producción.
--
-- PASOS PREVIOS:
--   1. Crear la base de datos:
--      psql -U postgres -c "CREATE DATABASE farmacia;"
--
--   2. Ejecutar este archivo:
--      psql -U postgres -d farmacia -f database/deploy_production.sql
--
--   3. Acceder al setup inicial para crear la primera empresa:
--      http://tudominio.com/farmacia/database/setup.php
--
--   4. Configurar SMTP en config/mail.php
--
-- NOTA: Todas las sentencias usan IF NOT EXISTS / IF EXISTS,
--       por lo que es seguro ejecutar este archivo más de una vez.
-- ============================================================


-- ==========================
-- SECCIÓN 1: TABLAS GLOBALES
-- ==========================

CREATE TABLE IF NOT EXISTS public.tenants (
    id         SERIAL PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    slug       VARCHAR(30)  UNIQUE NOT NULL,
    plan       VARCHAR(20)  DEFAULT 'basico',
    url        VARCHAR(200),
    ruc        VARCHAR(20),
    telefono   VARCHAR(20),
    direccion  TEXT,
    activo     BOOLEAN   DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.sucursales (
    id          SERIAL PRIMARY KEY,
    tenant_id   INTEGER      NOT NULL REFERENCES public.tenants(id) ON DELETE CASCADE,
    nombre      VARCHAR(100) NOT NULL,
    schema_name VARCHAR(60)  UNIQUE NOT NULL,
    direccion   TEXT,
    telefono    VARCHAR(20),
    activo      BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.usuarios (
    id            SERIAL PRIMARY KEY,
    tenant_id     INTEGER      REFERENCES public.tenants(id) ON DELETE CASCADE,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100),
    username      VARCHAR(50)  UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email         VARCHAR(150),
    activo        BOOLEAN   DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.usuario_sucursal (
    id          SERIAL PRIMARY KEY,
    usuario_id  INTEGER NOT NULL REFERENCES public.usuarios(id)   ON DELETE CASCADE,
    sucursal_id INTEGER NOT NULL REFERENCES public.sucursales(id) ON DELETE CASCADE,
    rol         VARCHAR(20) NOT NULL DEFAULT 'cajero',
    activo      BOOLEAN DEFAULT TRUE,
    UNIQUE (usuario_id, sucursal_id)
);

CREATE TABLE IF NOT EXISTS public.password_resets (
    id         SERIAL PRIMARY KEY,
    usuario_id INTEGER      NOT NULL REFERENCES public.usuarios(id) ON DELETE CASCADE,
    token      VARCHAR(100) NOT NULL UNIQUE,
    expires_at TIMESTAMP    NOT NULL,
    used       BOOLEAN   DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_password_resets_token      ON public.password_resets (token);
CREATE INDEX IF NOT EXISTS idx_password_resets_usuario_id ON public.password_resets (usuario_id);

-- Compatibilidad con instalaciones anteriores
ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS url       VARCHAR(200);
ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS ruc       VARCHAR(20);
ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS telefono  VARCHAR(20);
ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS direccion TEXT;
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS email     VARCHAR(150);


-- ==========================
-- SECCIÓN 2: TABLAS POR SUCURSAL (schema de ejemplo: sucursal_schema)
-- ==========================
-- NOTA: Estas tablas se crean automáticamente cuando se registra
--       una sucursal desde el panel superadmin o desde setup.php.
--       El archivo database/schema_sucursal.sql contiene la definición
--       completa y es ejecutado por el sistema en cada nueva sucursal.
--
-- Si necesitas crearlas manualmente para un schema existente:
--   SET search_path TO nombre_schema, public;
--   \i database/schema_sucursal.sql


-- ==========================
-- SECCIÓN 3: MIGRACIONES ACUMULADAS (para bases existentes)
-- ==========================
-- Las siguientes migraciones están incluidas en schema_sucursal.sql
-- para instalaciones nuevas. Para bases existentes, ejecutar por cada
-- schema de sucursal:
--
--   SET search_path TO nombre_schema, public;
--   \i database/migration_compras.sql
--   \i database/migration_gastos.sql
