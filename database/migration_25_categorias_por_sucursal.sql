-- migration_25_categorias_por_sucursal.sql
-- Revierte las categorías a ser por sucursal (tabla local en cada schema).
-- Cada sucursal tendrá su propia tabla categorias independiente.
-- Los IDs de public.categorias se copian a cada tabla local para preservar
-- referencias existentes en productos.categoria_id.

DO $$
DECLARE
    s RECORD;
BEGIN
    FOR s IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT IN ('public', 'information_schema', 'pg_catalog', 'pg_toast')
          AND schema_name NOT LIKE 'pg_%'
        ORDER BY schema_name
    LOOP
        -- 1. Crear tabla categorias local si no existe
        EXECUTE format('
            CREATE TABLE IF NOT EXISTS %I.categorias (
                id          SERIAL PRIMARY KEY,
                nombre      VARCHAR(100) NOT NULL,
                descripcion TEXT,
                activo      BOOLEAN DEFAULT TRUE,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ', s.schema_name);

        -- 2. Copiar categorias de public.categorias preservando IDs
        --    (para que productos con categoria_id existente sigan siendo válidos)
        EXECUTE format('
            INSERT INTO %I.categorias (id, nombre, activo)
            SELECT id, nombre, activo FROM public.categorias
            ON CONFLICT (id) DO NOTHING
        ', s.schema_name);

        -- 3. Sincronizar la secuencia del SERIAL con el máximo ID insertado
        EXECUTE format('
            SELECT setval(pg_get_serial_sequence(''%s.categorias'', ''id''),
                COALESCE((SELECT MAX(id) FROM %I.categorias), 0) + 1, false)
        ', s.schema_name, s.schema_name);

        RAISE NOTICE 'Schema %: categorias locales creadas y pobladas', s.schema_name;

        -- 4. Eliminar FK antiguo
        EXECUTE format('
            ALTER TABLE %I.productos
            DROP CONSTRAINT IF EXISTS productos_categoria_id_fkey
        ', s.schema_name);

        -- 5. Agregar FK apuntando a la tabla local
        EXECUTE format('
            ALTER TABLE %I.productos
            ADD CONSTRAINT productos_categoria_id_fkey
            FOREIGN KEY (categoria_id) REFERENCES %I.categorias(id)
        ', s.schema_name, s.schema_name);

        RAISE NOTICE 'Schema %: FK ahora apunta a categorias local', s.schema_name;

    END LOOP;
END $$;
