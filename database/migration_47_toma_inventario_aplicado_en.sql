-- migration_47_toma_inventario_aplicado_en.sql
-- Agrega toma_inventario_detalles.aplicado_en: registra el momento exacto
-- en que se le dio clic a "Aplicar" para esa fila (distinto de
-- contado_en, que es cuando se guardo el conteo fisico). Soporta la
-- columna "Actualizado" en el detalle de la sesion, estilo WhatsApp
-- (relativo hasta 3 dias, fecha exacta desde el 4to).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_47_toma_inventario_aplicado_en.sql

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
            EXECUTE format('ALTER TABLE %I.toma_inventario_detalles ADD COLUMN IF NOT EXISTS aplicado_en TIMESTAMP', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
