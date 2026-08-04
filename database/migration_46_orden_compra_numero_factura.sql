-- migration_46_orden_compra_numero_factura.sql
-- Agrega ordenes_compra.numero_factura (ej. "F001-00040598"): reemplaza
-- el checkbox "Incluir IGV" de Nueva Orden de Compra, que se quito -- las
-- ordenes de compra ya no calculan IGV (quedan simples, subtotal = total).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_46_orden_compra_numero_factura.sql

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
              WHERE table_schema = schema_name AND table_name = 'ordenes_compra'
          )
        ORDER BY schema_name
    LOOP
        BEGIN
            EXECUTE format('ALTER TABLE %I.ordenes_compra ADD COLUMN IF NOT EXISTS numero_factura VARCHAR(30)', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
