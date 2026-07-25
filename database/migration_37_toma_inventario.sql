-- migration_37_toma_inventario.sql
-- Crea las tablas "toma_inventario_sesiones" y "toma_inventario_detalles"
-- (conteo fisico de inventario por sesion, con plazo en dias) en todos
-- los schemas de sucursal existentes. Nuevas sucursales ya la traen en
-- schema_sucursal.sql.
-- Usa BEGIN/EXCEPTION por schema para que un schema roto no aborte el
-- resto (mismo patrón que migration_26/migration_30/migration_31).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_37_toma_inventario.sql

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
              WHERE table_schema = schema_name AND table_name = 'toma_inventario_sesiones'
          )
        ORDER BY schema_name
    LOOP
        BEGIN

            EXECUTE format($q$
                CREATE TABLE %I.toma_inventario_sesiones (
                    id                 SERIAL PRIMARY KEY,
                    codigo             VARCHAR(30)   UNIQUE NOT NULL,
                    nombre             VARCHAR(150),
                    categorias_ids     INTEGER[]     NOT NULL,
                    plazo_dias         INTEGER       NOT NULL DEFAULT 1,
                    fecha_inicio       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    fecha_limite       TIMESTAMP     NOT NULL,
                    estado             VARCHAR(20)   NOT NULL DEFAULT 'activa',
                    total_productos    INTEGER       NOT NULL DEFAULT 0,
                    total_contados     INTEGER       NOT NULL DEFAULT 0,
                    usuario_creador_id INTEGER,
                    usuario_cierre_id  INTEGER,
                    observaciones      TEXT,
                    fecha_cierre       TIMESTAMP,
                    created_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                    updated_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT chk_toma_inv_sesiones_estado CHECK (estado IN ('activa', 'completada', 'cancelada')),
                    CONSTRAINT chk_toma_inv_sesiones_plazo  CHECK (plazo_dias >= 1)
                )
            $q$, s.schema_name);

            EXECUTE format($q$
                CREATE INDEX idx_toma_inv_sesiones_estado
                    ON %I.toma_inventario_sesiones (estado, created_at DESC)
            $q$, s.schema_name);

            EXECUTE format($q$
                CREATE TABLE %I.toma_inventario_detalles (
                    id                SERIAL PRIMARY KEY,
                    sesion_id         INTEGER       NOT NULL REFERENCES %I.toma_inventario_sesiones(id) ON DELETE CASCADE,
                    producto_id       INTEGER       REFERENCES %I.productos(id) ON DELETE SET NULL,
                    producto_codigo   VARCHAR(50)   NOT NULL,
                    producto_nombre   VARCHAR(200)  NOT NULL,
                    categoria_id      INTEGER,
                    categoria_nombre  VARCHAR(100),
                    unidad            VARCHAR(50),
                    stock_sistema     DECIMAL(10,2) NOT NULL,
                    cantidad_contada  DECIMAL(10,2),
                    diferencia        DECIMAL(10,2),
                    usuario_conteo_id INTEGER,
                    contado_en        TIMESTAMP,
                    created_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT chk_toma_inv_detalles_cantidad CHECK (cantidad_contada IS NULL OR cantidad_contada >= 0)
                )
            $q$, s.schema_name, s.schema_name, s.schema_name);

            EXECUTE format($q$
                CREATE UNIQUE INDEX uq_toma_inv_detalles_sesion_producto
                    ON %I.toma_inventario_detalles (sesion_id, producto_id) WHERE producto_id IS NOT NULL
            $q$, s.schema_name);

            EXECUTE format($q$
                CREATE INDEX idx_toma_inv_detalles_sesion
                    ON %I.toma_inventario_detalles (sesion_id)
            $q$, s.schema_name);

            RAISE NOTICE 'Schema % OK', s.schema_name;

        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
