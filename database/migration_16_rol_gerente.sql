-- ============================================================
-- MIGRACIÓN 16: Agregar rol 'gerente' (Superadmin de farmacia)
-- Rol intermedio entre admin y superadmin de sistema.
-- Tiene acceso completo, incluyendo Administración y E-commerce.
-- ============================================================

-- Eliminar constraint anterior y recrear con el nuevo valor
ALTER TABLE public.usuario_sucursal
    DROP CONSTRAINT IF EXISTS usuario_sucursal_rol_check;

ALTER TABLE public.usuario_sucursal
    ADD CONSTRAINT usuario_sucursal_rol_check
    CHECK (rol IN ('cajero', 'admin', 'gerente'));
