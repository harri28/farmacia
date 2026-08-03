-- migration_44_toma_inventario_sin_plazo.sql
-- Elimina la dependencia de "Plazo (dias)" / "Fecha limite" en Toma de
-- Inventario: la sesion ya no vence ni requiere extenderse. Se conservan
-- las columnas plazo_dias/fecha_limite en las sesiones ya existentes (no
-- se borran, solo dejan de ser obligatorias) para no perder ese dato
-- historico; las sesiones nuevas simplemente no lo usan.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_44_toma_inventario_sin_plazo.sql

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
              WHERE table_schema = schema_name AND table_name = 'toma_inventario_sesiones'
          )
        ORDER BY schema_name
    LOOP
        BEGIN
            EXECUTE format('ALTER TABLE %I.toma_inventario_sesiones ALTER COLUMN plazo_dias DROP NOT NULL', s.schema_name);
            EXECUTE format('ALTER TABLE %I.toma_inventario_sesiones ALTER COLUMN fecha_limite DROP NOT NULL', s.schema_name);
            EXECUTE format('ALTER TABLE %I.toma_inventario_sesiones DROP CONSTRAINT IF EXISTS chk_toma_inv_sesiones_plazo', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
