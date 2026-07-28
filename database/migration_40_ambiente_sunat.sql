-- migration_40_ambiente_sunat.sql
-- Agrega comprobantes_electronicos.ambiente_sunat: registra si cada
-- envio a SUNAT se hizo contra 'produccion' o 'beta' (segun el
-- sunat_server del tenant EN ESE MOMENTO). Antes no quedaba ningun
-- rastro de esto -- si alguien olvidaba cambiar la configuracion de
-- beta a produccion, no habia forma de saber despues cuales
-- comprobantes fueron reales y cuales de prueba.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_40_ambiente_sunat.sql

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
              WHERE table_schema = schema_name AND table_name = 'comprobantes_electronicos'
          )
        ORDER BY schema_name
    LOOP
        BEGIN
            EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS ambiente_sunat VARCHAR(20)', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
