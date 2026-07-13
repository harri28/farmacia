-- migration_31_salidas_almacen.sql
-- Crea las tablas "salidas" y "salida_detalles" (registro de bajas de stock:
-- merma, vencimiento, devolución, otro -- NO transferencias entre sucursales,
-- eso ya lo cubre public.ordenes_traslado) en todos los schemas de sucursal
-- existentes. Nuevas sucursales ya la traen en schema_sucursal.sql.
-- Usa BEGIN/EXCEPTION por schema para que un schema roto no aborte el resto
-- (mismo patrón que migration_26/migration_30).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_31_salidas_almacen.sql

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
          AND NOT EXISTS (
              SELECT 1 FROM information_schema.tables
              WHERE table_schema = schema_name AND table_name = 'salidas'
          )
        ORDER BY schema_name
    LOOP
        BEGIN

            EXECUTE format($q$
                CREATE TABLE %I.salidas (
                    id              SERIAL PRIMARY KEY,
                    numero_salida   VARCHAR(20)   UNIQUE NOT NULL,
                    motivo          VARCHAR(20)   NOT NULL DEFAULT 'otro',
                    usuario         VARCHAR(100),
                    usuario_id      INTEGER,
                    total           DECIMAL(10,2) DEFAULT 0,
                    estado          VARCHAR(20)   DEFAULT 'completado',
                    observaciones   TEXT,
                    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
                )
            $q$, s.schema_name);

            EXECUTE format($q$
                CREATE TABLE %I.salida_detalles (
                    id              SERIAL PRIMARY KEY,
                    salida_id       INTEGER       REFERENCES %I.salidas(id) ON DELETE CASCADE,
                    producto_id     INTEGER       REFERENCES %I.productos(id),
                    cantidad        INTEGER       NOT NULL,
                    costo_unitario  DECIMAL(10,2) NOT NULL DEFAULT 0,
                    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
                    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
                )
            $q$, s.schema_name, s.schema_name, s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;

        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
