-- =====================================================================
-- migration_59: numeración correlativa de órdenes de compra
--
-- Antes: OC20261119-0001 (fecha + correlativo diario, difícil de leer).
-- Ahora: OC-0001, OC-0002, ... correlativo por sucursal, sin fecha.
--
-- 1) Renumera TODAS las órdenes existentes en cada esquema sucursal_*,
--    en el orden en que se crearon (created_at, id).
-- 2) El código (modules/compras/api.php, generarNumeroOrden) ya genera
--    el siguiente OC-XXXX a partir del MAX existente con prefijo 'OC-'.
--
-- Idempotente: se puede ejecutar más de una vez. Usa dos pasos (temporal
-- y final) para no chocar con la restricción UNIQUE de numero_orden.
--
-- Ejecutar (VPS):
--   psql -U postgres -d farmacia -f database/migration_59_oc_numeracion_correlativa.sql
-- Hacer backup antes (pg_dump) si la base está en producción.
-- =====================================================================

DO $$
DECLARE
    s TEXT;
BEGIN
    FOR s IN
        SELECT schema_name FROM information_schema.schemata
        WHERE schema_name LIKE 'sucursal\_%' ESCAPE '\'
    LOOP
        IF to_regclass(format('%I.ordenes_compra', s)) IS NULL THEN
            CONTINUE;
        END IF;

        -- Paso 1: valor temporal único (evita colisiones de UNIQUE)
        EXECUTE format(
            'UPDATE %I.ordenes_compra SET numero_orden = ''TMP-'' || id::text',
            s
        );

        -- Paso 2: correlativo final por orden de creación
        EXECUTE format($f$
            UPDATE %1$I.ordenes_compra o
            SET numero_orden = r.nuevo
            FROM (
                SELECT id,
                       'OC-' || LPAD(ROW_NUMBER() OVER (ORDER BY created_at, id)::text, 4, '0') AS nuevo
                FROM %1$I.ordenes_compra
            ) r
            WHERE o.id = r.id
        $f$, s);

        RAISE NOTICE 'Esquema %: órdenes renumeradas', s;
    END LOOP;
END $$;
