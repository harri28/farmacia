-- migration_34_precios_unidad_medida.sql
-- "Precios por unidad de medida": un producto (unidad base) puede tener
-- ademas precios configurados para venderse por CAJA, BLISTER, PAQUETE, etc.
-- unidades_medida_venta es el catalogo reutilizable del desplegable
-- (extensible desde el formulario); producto_precios_unidad son las filas
-- configuradas por producto (unidad, abreviacion, cantidad, precio).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_34_precios_unidad_medida.sql

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
                CREATE TABLE IF NOT EXISTS %I.unidades_medida_venta (
                    id         SERIAL PRIMARY KEY,
                    nombre     VARCHAR(50) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )', s.schema_name);

            EXECUTE format('
                INSERT INTO %I.unidades_medida_venta (nombre) VALUES (''CAJA''), (''BLISTER''), (''PAQUETE'')
                ON CONFLICT (nombre) DO NOTHING', s.schema_name);

            EXECUTE format('
                CREATE TABLE IF NOT EXISTS %I.producto_precios_unidad (
                    id             SERIAL PRIMARY KEY,
                    producto_id    INTEGER        NOT NULL REFERENCES %I.productos(id) ON DELETE CASCADE,
                    unidad_medida  VARCHAR(50)    NOT NULL,
                    abreviacion    VARCHAR(20),
                    cantidad       INTEGER        NOT NULL,
                    precio_venta   DECIMAL(10,2)  NOT NULL,
                    created_at     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
                )', s.schema_name, s.schema_name);

            EXECUTE format('
                CREATE INDEX IF NOT EXISTS idx_producto_precios_unidad_producto
                ON %I.producto_precios_unidad(producto_id)', s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
