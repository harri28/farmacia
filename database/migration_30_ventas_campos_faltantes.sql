-- migration_30_ventas_campos_faltantes.sql
-- migration_22 replico casi todas las columnas de "ventas" que agrego
-- migration_09, pero se le quedaron 6 afuera: anticipo, otros_cargos,
-- cuotas, payment_breakdown, cdr, estado_cpe. Esta migracion cierra ese
-- hueco para todos los schemas de sucursal existentes.
-- Usa BEGIN/EXCEPTION por schema para que un schema roto no aborte el resto
-- (mismo patron que migration_26).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_30_ventas_campos_faltantes.sql

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
          AND NOT EXISTS (
              SELECT 1 FROM information_schema.columns
              WHERE table_schema = schema_name
                AND table_name   = 'ventas'
                AND column_name  = 'anticipo'
          )
        ORDER BY schema_name
    LOOP
        BEGIN

            EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS anticipo           DECIMAL(18,2) DEFAULT 0', s.schema_name);
            EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS otros_cargos        DECIMAL(18,2) DEFAULT 0', s.schema_name);
            EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS cuotas              JSONB', s.schema_name);
            EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS payment_breakdown   JSONB', s.schema_name);
            EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS cdr                 INTEGER', s.schema_name);
            EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS estado_cpe          INTEGER', s.schema_name);

            EXECUTE format($q$
                UPDATE %I.ventas
                SET anticipo     = COALESCE(anticipo, 0),
                    otros_cargos = COALESCE(otros_cargos, 0)
                WHERE TRUE
            $q$, s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;

        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
