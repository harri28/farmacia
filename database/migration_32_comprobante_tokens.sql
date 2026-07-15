-- migration_32_comprobante_tokens.sql
-- Tabla pública para "Mi Comprobante": resuelve un token aleatorio (impreso
-- en el ticket / enviado por WhatsApp) al schema de sucursal + venta_id
-- correspondiente, sin necesitar login ni saber de antemano el tenant.
-- Solo se ejecuta una vez contra el schema public (no hay uno por sucursal).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_32_comprobante_tokens.sql

CREATE TABLE IF NOT EXISTS public.comprobante_tokens (
    id          SERIAL PRIMARY KEY,
    token       VARCHAR(40) UNIQUE NOT NULL,
    schema_name VARCHAR(63) NOT NULL,
    venta_id    INTEGER     NOT NULL,
    created_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_comprobante_tokens_token
    ON public.comprobante_tokens (token);
