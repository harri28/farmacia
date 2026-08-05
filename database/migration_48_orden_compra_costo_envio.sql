-- migration_48_orden_compra_costo_envio.sql
-- Agrega ordenes_compra.costo_envio: nuevo campo "Costo de Envío" en
-- Nueva Orden de Compra. Solo se suma al total de la orden (y por lo
-- tanto al monto de la cuenta por pagar / ingreso generado) -- NO se
-- reparte entre los productos ni afecta su precio_compra, asi que no
-- afecta el "Valor en inventario" (SUM(stock * precio_compra)).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_48_orden_compra_costo_envio.sql

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
            EXECUTE format('ALTER TABLE %I.ordenes_compra ADD COLUMN IF NOT EXISTS costo_envio DECIMAL(10,2) DEFAULT 0', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
