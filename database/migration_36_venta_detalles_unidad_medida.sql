-- migration_36_venta_detalles_unidad_medida.sql
-- Permite vender un producto por una presentacion configurada en
-- "Precios por unidad de medida" (ej. BLISTER) en vez de solo su unidad
-- base. Se necesitan estas columnas para poder anular la venta despues
-- y restaurar el stock correctamente (cantidad x factor_equivalencia).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_36_venta_detalles_unidad_medida.sql

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
              WHERE table_schema = schema_name AND table_name = 'venta_detalles'
          )
        ORDER BY schema_name
    LOOP
        BEGIN
            EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS unidad_medida_vendida VARCHAR(50)', s.schema_name);
            EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS factor_equivalencia DECIMAL(10,2) DEFAULT 1', s.schema_name);
            EXECUTE format('UPDATE %I.venta_detalles SET factor_equivalencia = 1 WHERE factor_equivalencia IS NULL', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
