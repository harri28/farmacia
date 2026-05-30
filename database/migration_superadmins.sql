-- ============================================================
-- MIGRACIÓN: Tabla separada para superadmins del sistema
-- Ejecutar: psql -U postgres -d farmacia -f database/migration_superadmins.sql
-- ============================================================

-- 1. Crear tabla exclusiva para superadmins del sistema
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

-- 2. Migrar superadmins existentes desde public.usuarios (tenant_id IS NULL)
INSERT INTO public.superadmins (nombre, apellido, username, password_hash, email, activo, created_at)
SELECT nombre, apellido, username, password_hash, email, activo, created_at
FROM public.usuarios
WHERE tenant_id IS NULL
ON CONFLICT (username) DO NOTHING;

-- 3. Adaptar password_resets para soportar ambas tablas
ALTER TABLE public.password_resets
    ADD COLUMN IF NOT EXISTS superadmin_id INTEGER REFERENCES public.superadmins(id) ON DELETE CASCADE;

ALTER TABLE public.password_resets
    ALTER COLUMN usuario_id DROP NOT NULL;

-- 4. Eliminar superadmins de la tabla de usuarios de empresa
--    (seguro porque ya fueron copiados al paso 2)
DELETE FROM public.usuarios WHERE tenant_id IS NULL;
