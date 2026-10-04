-- ============================================================
-- ARCHIVO: farmacia/database/migration_56_promociones.sql
-- DESCRIPCION: Generador de promociones (Reportes, Bloque 2) -- permite
--              a admin/gerente crear descuentos temporales sobre
--              productos puntuales (tipicamente detectados por el
--              reporte de "Stock Paralizado"). El POS aplica el precio
--              promocional automaticamente mientras la promocion este
--              vigente (ver modules/ventas/api.php).
--
-- promociones          -> una promocion (nombre, tipo de descuento,
--                         valor, vigencia, activo).
-- promocion_productos  -> que productos entran en cada promocion
--                         (N a N).
--
-- Regla de seguridad (aplicada en PHP, no en la BD): si un producto
-- tuviera mas de una promocion vigente a la vez, siempre se cobra el
-- precio MAS BAJO resultante -- nunca se eligen al azar ni se suman
-- descuentos. Por eso no hay restriccion de unicidad que impida
-- promociones superpuestas: es mas simple resolverlo en el calculo
-- del precio que prohibir la superposicion en la BD.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_56_promociones.sql
-- ============================================================

DO $$
DECLARE
    s TEXT;
BEGIN
    FOR s IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT LIKE 'pg_%'
          AND schema_name NOT IN ('information_schema', 'public')
    LOOP
        EXECUTE format($q$
            CREATE TABLE IF NOT EXISTS %I.promociones (
                id              SERIAL PRIMARY KEY,
                nombre          VARCHAR(150) NOT NULL,
                descripcion     TEXT,
                tipo_descuento  VARCHAR(20)   NOT NULL DEFAULT 'porcentaje',
                valor_descuento DECIMAL(10,2) NOT NULL,
                fecha_inicio    DATE NOT NULL,
                fecha_fin       DATE NOT NULL,
                activo          BOOLEAN DEFAULT TRUE,
                creado_por      INTEGER, -- usuario_id, sin FK cross-schema (mismo criterio que ventas.usuario_id)
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT chk_promociones_tipo_descuento CHECK (tipo_descuento IN ('porcentaje', 'monto_fijo')),
                CONSTRAINT chk_promociones_fechas CHECK (fecha_fin >= fecha_inicio),
                CONSTRAINT chk_promociones_valor_positivo CHECK (valor_descuento > 0)
            )
        $q$, s);

        EXECUTE format($q$
            CREATE TABLE IF NOT EXISTS %I.promocion_productos (
                id            SERIAL PRIMARY KEY,
                promocion_id  INTEGER NOT NULL REFERENCES %I.promociones(id) ON DELETE CASCADE,
                producto_id   INTEGER NOT NULL REFERENCES %I.productos(id)   ON DELETE CASCADE,
                UNIQUE (promocion_id, producto_id)
            )
        $q$, s, s, s);

        EXECUTE format('CREATE INDEX IF NOT EXISTS idx_promocion_productos_producto ON %I.promocion_productos (producto_id)', s);
        EXECUTE format('CREATE INDEX IF NOT EXISTS idx_promociones_vigencia ON %I.promociones (activo, fecha_inicio, fecha_fin)', s);
    END LOOP;
END $$;
