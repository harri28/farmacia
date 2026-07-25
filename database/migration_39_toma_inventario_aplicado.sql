-- migration_39_toma_inventario_aplicado.sql
-- Agrega la columna "aplicado" a toma_inventario_detalles: permite
-- aplicar el ajuste de stock de un producto individualmente (boton
-- de aspa por fila), en vez de esperar a cerrar toda la sesion de
-- Toma de Inventario de una sola vez.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_39_toma_inventario_aplicado.sql

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
              WHERE table_schema = schema_name AND table_name = 'toma_inventario_detalles'
          )
        ORDER BY schema_name
    LOOP
        BEGIN
            EXECUTE format('ALTER TABLE %I.toma_inventario_detalles ADD COLUMN IF NOT EXISTS aplicado BOOLEAN NOT NULL DEFAULT FALSE', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
