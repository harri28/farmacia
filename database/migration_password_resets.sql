-- ============================================================
-- MIGRACIÓN: Recuperación de contraseña + columna email
-- Ejecutar sobre bases de datos existentes (anteriores a esta versión).
-- psql -U postgres -d farmacia -f database/migration_password_resets.sql
-- ============================================================

ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS url       VARCHAR(200);
ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS ruc       VARCHAR(20);
ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS telefono  VARCHAR(20);
ALTER TABLE public.tenants  ADD COLUMN IF NOT EXISTS direccion TEXT;
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS email     VARCHAR(150);

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
