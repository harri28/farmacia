-- ============================================================
-- ARCHIVO: farmacia/database/migration_54_productos_eliminados.sql
-- DESCRIPCION: Papelera de productos ("Eliminados"). Agrega a
--              productos las columnas necesarias para un soft-delete
--              con purga automatica a los 30 dias (admin/gerente
--              pueden eliminar un producto desde Inventario; el
--              producto no se borra fisicamente de inmediato, pasa
--              a la pestaña "Eliminados" y puede restaurarse dentro
--              de ese plazo).
--
-- eliminado       -> TRUE mientras el producto esta en la papelera.
--                    Se usa ademas de 'activo' (que ya se pone en
--                    FALSE) porque 'activo=FALSE' por si solo ya se
--                    usaba para "desactivar" un producto sin
--                    eliminarlo -- eliminado distingue ambos casos.
-- eliminado_at    -> fecha/hora del soft-delete. La purga definitiva
--                    (ver scripts/purgar_productos_eliminados.php y
--                    la purga perezosa en inventario/api.php) borra
--                    el registro fisicamente 30 dias despues, salvo
--                    que tenga historial de ventas/ingresos/salidas/
--                    compras (venta_detalles.producto_id etc. no
--                    tienen ON DELETE CASCADE a proposito, para no
--                    perder el detalle de comprobantes ya emitidos).
-- eliminado_por   -> usuario_id que elimino el producto (sin FK
--                    cross-schema a public.usuarios, mismo criterio
--                    que usuario_id en ventas/ingresos/salidas de
--                    este archivo).
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_54_productos_eliminados.sql
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
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS eliminado      BOOLEAN   DEFAULT FALSE', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS eliminado_at   TIMESTAMP', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS eliminado_por  INTEGER', s);

        EXECUTE format('CREATE INDEX IF NOT EXISTS idx_productos_eliminado ON %I.productos (eliminado, eliminado_at)', s);
    END LOOP;
END $$;
