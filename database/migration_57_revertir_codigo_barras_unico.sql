-- ============================================================
-- ARCHIVO: farmacia/database/migration_57_revertir_codigo_barras_unico.sql
-- DESCRIPCION: Revierte migration_55_codigo_barras_unico_experimental.sql.
--              El experimento asumia que un codigo de barras identificaba
--              UNA presentacion unica -- en el catalogo real del usuario
--              (confirmado 2026-10-05 contra la BD de produccion), varias
--              presentaciones del mismo producto (ej. paquete x10 vs
--              unidad suelta) comparten el mismo codigo de barras a
--              proposito. La restriccion de unicidad bloqueaba cargar
--              esas presentaciones y no se puede sostener.
--
--              Solo se elimina el INDICE. Las columnas eliminado/
--              eliminado_at/eliminado_por (migration_54) y toda la
--              papelera de productos siguen funcionando igual, son
--              independientes.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_57_revertir_codigo_barras_unico.sql
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
        EXECUTE format('DROP INDEX IF EXISTS %I.uq_productos_codigo_barras_activo', s);
    END LOOP;
END $$;
