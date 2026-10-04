-- ============================================================
-- ARCHIVO: farmacia/database/migration_55_codigo_barras_unico_experimental.sql
-- DESCRIPCION: EXPERIMENTAL -- a pedido del usuario, para probar si
--              conviene impedir codigo_barras duplicado entre
--              productos. Hoy no hay ninguna restriccion de unicidad
--              sobre codigo_barras (a diferencia de 'codigo', que ya
--              es UNIQUE NOT NULL desde schema_sucursal.sql).
--
--              Requiere migration_54_productos_eliminados.sql
--              aplicada antes (usa la columna 'eliminado').
--
-- Por que un INDICE UNICO PARCIAL y no un UNIQUE constraint normal:
--   - Dos productos sin codigo de barras (NULL o '') deben poder
--     coexistir -- la mayoria de productos no tiene uno.
--   - Al eliminar un producto (pasa a la papelera, eliminado=TRUE) su
--     codigo_barras queda "liberado": se puede crear un producto
--     nuevo con ese mismo codigo de barras sin problema. Si luego se
--     intenta RESTAURAR el producto original, el UPDATE que vuelve a
--     poner eliminado=FALSE choca con este indice (23505) porque ya
--     hay otro producto activo con ese codigo de barras -- la propia
--     base de datos bloquea la restauracion, que es justo el caso
--     que se queria cubrir. modules/inventario/api.php's 'restaurar'
--     atrapa esa excepcion y devuelve un mensaje legible.
--
-- SI NO FUNCIONA BIEN Y SE DECIDE QUITAR (revertir, por schema):
--   DROP INDEX IF EXISTS uq_productos_codigo_barras_activo;
--   (o el loop equivalente recorriendo todos los schemas sucursal_%)
--   Es solo un indice, no una columna -- se puede quitar sin perder
--   datos ni tocar schema_sucursal.sql mas que borrando este bloque.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_55_codigo_barras_unico_experimental.sql
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
            CREATE UNIQUE INDEX IF NOT EXISTS uq_productos_codigo_barras_activo
            ON %I.productos (codigo_barras)
            WHERE codigo_barras IS NOT NULL AND codigo_barras <> '' AND eliminado = FALSE
        $q$, s);
    END LOOP;
END $$;
