-- migration_34_producto_presentaciones.sql
-- Presentaciones de venta adicionales por producto (Blister, Caja, Paquete,
-- etc.), cada una con su propio precio y cuantas unidades base (la unidad
-- normal del producto) equivale. El producto en si sigue siendo la unidad
-- base; esta tabla son presentaciones EXTRA por encima de esa unidad.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_34_producto_presentaciones.sql

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
            EXECUTE format('
                CREATE TABLE IF NOT EXISTS %I.producto_presentaciones (
                    id                     SERIAL PRIMARY KEY,
                    producto_id            INTEGER        NOT NULL REFERENCES %I.productos(id) ON DELETE CASCADE,
                    nombre                 VARCHAR(50)    NOT NULL,
                    unidades_equivalentes  INTEGER        NOT NULL,
                    precio_venta           DECIMAL(10,2)  NOT NULL,
                    created_at             TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
                )', s.schema_name, s.schema_name);

            EXECUTE format('
                CREATE INDEX IF NOT EXISTS idx_producto_presentaciones_producto
                ON %I.producto_presentaciones(producto_id)', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
