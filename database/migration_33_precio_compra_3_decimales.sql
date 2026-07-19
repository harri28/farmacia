-- migration_33_precio_compra_3_decimales.sql
-- precio_compra pasa de DECIMAL(10,2) a DECIMAL(10,3): algunos proveedores
-- entregan precio de compra con 3 decimales (ej. "S/ 0.234") y se estaba
-- perdiendo precision al redondear a 2. Ensancha la columna en todos los
-- schemas de sucursal existentes (schema_sucursal.sql ya quedo actualizado
-- para sucursales nuevas).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_33_precio_compra_3_decimales.sql

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
              WHERE table_schema = schema_name AND table_name = 'productos'
          )
        ORDER BY schema_name
    LOOP
        BEGIN
            EXECUTE format('ALTER TABLE %I.productos ALTER COLUMN precio_compra TYPE DECIMAL(10,3)', s.schema_name);
            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
