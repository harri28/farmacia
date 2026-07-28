-- migration_41_motivo_anulacion_venta.sql
-- Agrega ventas.motivo_anulacion: a partir de ahora anular una venta
-- exige una justificacion obligatoria (no se puede anular sin motivo).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_41_motivo_anulacion_venta.sql

DO $$
DECLARE s RECORD;
BEGIN
    FOR s IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT LIKE 'pg_%'
          AND schema_name NOT IN ('information_schema', 'public')
          AND EXISTS (
              SELECT 1 FROM information_schema.tables
              WHERE table_schema = schema_name AND table_name = 'ventas'
          )
        ORDER BY schema_name
    LOOP
        BEGIN
            EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS motivo_anulacion TEXT', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
