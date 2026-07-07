-- migration_27_sucursal_color_icono.sql
-- Agrega columnas color e icono a public.sucursales para identificar visualmente cada sucursal.

ALTER TABLE public.sucursales
    ADD COLUMN IF NOT EXISTS color VARCHAR(20) DEFAULT '#6366f1',
    ADD COLUMN IF NOT EXISTS icono VARCHAR(50) DEFAULT 'fa-store';
