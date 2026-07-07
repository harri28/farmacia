-- migration_29_banco_imagen.sql
-- Agrega columna imagen_path a cuentas_banco para logo/imagen del banco.

ALTER TABLE public.cuentas_banco
    ADD COLUMN IF NOT EXISTS imagen_path VARCHAR(255);
