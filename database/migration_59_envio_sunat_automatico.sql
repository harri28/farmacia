-- migration_59_envio_sunat_automatico.sql
-- Agrega el interruptor por sucursal "envio automatico a SUNAT".
-- TRUE  = el worker (cron 1-2am) envia las B/F de esa sucursal.
-- FALSE = modo manual: el worker NO envia; el usuario reenvia a mano.
-- Tabla GLOBAL (public.sucursales), una sola ALTER (no por esquema).

ALTER TABLE public.sucursales
    ADD COLUMN IF NOT EXISTS envio_sunat_automatico BOOLEAN DEFAULT TRUE;
