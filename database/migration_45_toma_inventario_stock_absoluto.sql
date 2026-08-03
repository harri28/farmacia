-- migration_45_toma_inventario_stock_absoluto.sql
-- Cambia "Aplicar al stock" en Toma de Inventario de un ajuste POR
-- DIFERENCIA (stock = stock + diferencia, calculada contra la foto del
-- stock al crear la sesion) a un REEMPLAZO DIRECTO (stock = cantidad
-- contada), porque el cliente reporto que si el stock real cambiaba
-- entre crear la sesion y aplicar el conteo (ej. por una venta), el
-- resultado final no coincidia con el numero que habia contado
-- fisicamente -- confirmado con datos reales de produccion.
--
-- Agrega stock_antes_aplicar: guarda el stock justo antes de cada
-- aplicacion, para que "Editar" (toma_reabrir_producto) pueda revertir
-- al valor exacto anterior en vez de recalcular con la diferencia vieja.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_45_toma_inventario_stock_absoluto.sql

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
            EXECUTE format('ALTER TABLE %I.toma_inventario_detalles ADD COLUMN IF NOT EXISTS stock_antes_aplicar DECIMAL(10,2)', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
