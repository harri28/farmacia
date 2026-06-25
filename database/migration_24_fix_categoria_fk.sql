-- migration_24_fix_categoria_fk.sql
-- Corrige el FK de productos.categoria_id para que apunte a public.categorias
-- en lugar de la tabla categorias local de cada schema.
-- schema_sucursal.sql ya tiene el FK correcto; esta migración aplica el fix
-- a los 8 schemas existentes.

DO $$
DECLARE
    s RECORD;
    has_local_cat BOOLEAN;
BEGIN
    FOR s IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT IN ('public', 'information_schema', 'pg_catalog', 'pg_toast')
          AND schema_name NOT LIKE 'pg_%'
        ORDER BY schema_name
    LOOP
        -- 1. Verificar si existe tabla categorias local en este schema
        SELECT EXISTS (
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = s.schema_name AND table_name = 'categorias'
        ) INTO has_local_cat;

        IF has_local_cat THEN
            -- 2. Poner NULL en productos cuyo categoria_id no exista en public.categorias
            EXECUTE format('
                UPDATE %I.productos
                SET categoria_id = NULL
                WHERE categoria_id IS NOT NULL
                  AND categoria_id NOT IN (SELECT id FROM public.categorias)
            ', s.schema_name);

            -- 3. Eliminar FK antiguo (apuntaba a la tabla local)
            EXECUTE format('
                ALTER TABLE %I.productos
                DROP CONSTRAINT IF EXISTS productos_categoria_id_fkey
            ', s.schema_name);

            -- 4. Eliminar tabla categorias local (ya no se necesita)
            EXECUTE format('DROP TABLE IF EXISTS %I.categorias CASCADE', s.schema_name);

            RAISE NOTICE 'Schema %: tabla categorias local eliminada', s.schema_name;
        END IF;

        -- 5. Agregar FK apuntando a public.categorias (si no existe ya)
        IF NOT EXISTS (
            SELECT 1
            FROM pg_constraint c
            JOIN pg_class t ON t.oid = c.conrelid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            JOIN pg_class ft ON ft.oid = c.confrelid
            JOIN pg_namespace fn ON fn.oid = ft.relnamespace
            WHERE n.nspname = s.schema_name
              AND t.relname = 'productos'
              AND c.contype = 'f'
              AND fn.nspname = 'public'
              AND ft.relname = 'categorias'
        ) THEN
            EXECUTE format('
                ALTER TABLE %I.productos
                ADD CONSTRAINT productos_categoria_id_fkey
                FOREIGN KEY (categoria_id) REFERENCES public.categorias(id)
            ', s.schema_name);

            RAISE NOTICE 'Schema %: FK apuntando a public.categorias agregado', s.schema_name;
        ELSE
            RAISE NOTICE 'Schema %: FK ya apuntaba a public.categorias, sin cambios', s.schema_name;
        END IF;

    END LOOP;
END $$;
