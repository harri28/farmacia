-- ============================================================
-- ARCHIVO: farmacia/database/migration_58_reenvio_sunat_automatico.sql
-- DESCRIPCION: Agrega columnas a comprobantes_electronicos para
--              soportar reenvio automatico a SUNAT via worker (cron):
--              reenvio_intentos      - cuantos reintentos lleva (0..20)
--              reenvio_proximo_at    - cuando el worker debe volver a probar
--              reenvio_ultimo_at     - timestamp del ultimo intento
--              reenvio_ultimo_error  - mensaje del ultimo error (debug)
--
--              El worker busca comprobantes con enlace_del_cdr vacio y
--              reenvio_proximo_at <= NOW() y los reintenta.
--              Al llegar a 20 intentos se setea reenvio_proximo_at = NULL
--              para que no se vuelva a tocar (requiere intervencion manual).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_58_reenvio_sunat_automatico.sql
-- ============================================================

DO $$
DECLARE
    s TEXT;
BEGIN
    FOR s IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT LIKE 'pg_%'
          AND schema_name NOT IN ('information_schema', 'public')
    LOOP
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS reenvio_intentos     INTEGER   DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS reenvio_proximo_at   TIMESTAMP', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS reenvio_ultimo_at    TIMESTAMP', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS reenvio_ultimo_error TEXT', s);
        -- Indice parcial: solo los que estan programados para reintento
        EXECUTE format('CREATE INDEX IF NOT EXISTS idx_%s_ce_reenvio_pendiente ON %I.comprobantes_electronicos (reenvio_proximo_at) WHERE reenvio_proximo_at IS NOT NULL AND COALESCE(enlace_del_cdr, '''') = ''''', s, s);
    END LOOP;
END $$;
