-- ============================================================
-- ARCHIVO: farmacia/database/schema_public.sql
-- DESCRIPCIÓN: Tablas globales (schema public) — multi-tenant.
-- USO (instalación nueva):
--   psql -U postgres -d farmacia -f database/schema_public.sql
-- USO (actualizar instalación existente):
--   psql -U postgres -d farmacia -f database/migration_password_resets.sql
-- ============================================================

-- Empresas / tenants del sistema
CREATE TABLE IF NOT EXISTS public.tenants (
    id         SERIAL PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    slug       VARCHAR(30)  UNIQUE NOT NULL,
    plan       VARCHAR(20)  DEFAULT 'basico',   -- basico | pro | enterprise
    url        VARCHAR(200),
    ruc        VARCHAR(20),
    telefono   VARCHAR(20),
    direccion  TEXT,
    activo     BOOLEAN   DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sucursales de cada tenant
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

-- Usuarios del sistema (tenant_id NULL = superadmin)
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

-- Rol de cada usuario en cada sucursal
CREATE TABLE IF NOT EXISTS public.usuario_sucursal (
    id          SERIAL PRIMARY KEY,
    usuario_id  INTEGER NOT NULL REFERENCES public.usuarios(id)   ON DELETE CASCADE,
    sucursal_id INTEGER NOT NULL REFERENCES public.sucursales(id) ON DELETE CASCADE,
    rol         VARCHAR(20) NOT NULL DEFAULT 'cajero',   -- admin | cajero
    activo      BOOLEAN DEFAULT TRUE,
    UNIQUE (usuario_id, sucursal_id)
);

-- Superadmins del sistema (completamente separados de los usuarios de empresa)
CREATE TABLE IF NOT EXISTS public.superadmins (
    id            SERIAL PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100),
    username      VARCHAR(50)  UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email         VARCHAR(150),
    activo        BOOLEAN   DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tokens de recuperación de contraseña (para usuarios de empresa Y superadmins)
CREATE TABLE IF NOT EXISTS public.password_resets (
    id            SERIAL PRIMARY KEY,
    usuario_id    INTEGER   REFERENCES public.usuarios(id)   ON DELETE CASCADE,
    superadmin_id INTEGER   REFERENCES public.superadmins(id) ON DELETE CASCADE,
    token         VARCHAR(100) NOT NULL UNIQUE,
    expires_at    TIMESTAMP    NOT NULL,
    used          BOOLEAN   DEFAULT FALSE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_password_resets_token      ON public.password_resets (token);
CREATE INDEX IF NOT EXISTS idx_password_resets_usuario_id ON public.password_resets (usuario_id);

-- ---- Compatibilidad con instalaciones anteriores ----
-- Estas sentencias son seguras de ejecutar varias veces (IF NOT EXISTS)
ALTER TABLE public.tenants    ADD COLUMN IF NOT EXISTS url       VARCHAR(200);
ALTER TABLE public.tenants    ADD COLUMN IF NOT EXISTS ruc       VARCHAR(20);
ALTER TABLE public.tenants    ADD COLUMN IF NOT EXISTS telefono  VARCHAR(20);
ALTER TABLE public.tenants    ADD COLUMN IF NOT EXISTS direccion TEXT;
ALTER TABLE public.usuarios   ADD COLUMN IF NOT EXISTS email     VARCHAR(150);
