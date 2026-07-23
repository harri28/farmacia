-- migration_35_cantidades_decimales.sql
-- Permite cantidades con decimales (ej. 2.5 kg, 1.5 litros) en todo el
-- sistema: stock del producto, ventas, ingresos, salidas de almacen y
-- ordenes de compra. Antes estas columnas eran INTEGER, forzando a
-- redondear cualquier cantidad medida por peso/volumen.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_35_cantidades_decimales.sql

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
            EXECUTE format('ALTER TABLE %I.productos ALTER COLUMN stock TYPE DECIMAL(10,2)', s.schema_name);
            EXECUTE format('ALTER TABLE %I.productos ALTER COLUMN stock_minimo TYPE DECIMAL(10,2)', s.schema_name);
            EXECUTE format('ALTER TABLE %I.venta_detalles ALTER COLUMN cantidad TYPE DECIMAL(10,2)', s.schema_name);
            EXECUTE format('ALTER TABLE %I.ingreso_detalles ALTER COLUMN cantidad TYPE DECIMAL(10,2)', s.schema_name);

            IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = s.schema_name AND table_name = 'salida_detalles') THEN
                EXECUTE format('ALTER TABLE %I.salida_detalles ALTER COLUMN cantidad TYPE DECIMAL(10,2)', s.schema_name);
            END IF;
            IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = s.schema_name AND table_name = 'orden_compra_detalles') THEN
                EXECUTE format('ALTER TABLE %I.orden_compra_detalles ALTER COLUMN cantidad TYPE DECIMAL(10,2)', s.schema_name);
            END IF;

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;

-- Traslados entre sucursales (public, no es por-sucursal)
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'orden_traslado_detalles') THEN
        ALTER TABLE public.orden_traslado_detalles ALTER COLUMN cantidad TYPE DECIMAL(10,2);
        ALTER TABLE public.orden_traslado_detalles ALTER COLUMN stock_origen_snapshot TYPE DECIMAL(10,2);
        ALTER TABLE public.orden_traslado_detalles ALTER COLUMN stock_destino_snapshot TYPE DECIMAL(10,2);
        RAISE NOTICE 'public.orden_traslado_detalles OK';
    END IF;
END $$;
