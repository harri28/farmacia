-- ============================================================
-- MIGRACIÓN 21: Categorías globales en public schema
-- Las categorías dejan de ser por sucursal y pasan a ser
-- compartidas por todas las sucursales del sistema.
--
-- Ejecutar:
--   $env:PGPASSWORD = "1234"
--   & "C:\Program Files\PostgreSQL\17\bin\psql.exe" -U postgres -d farmacia -f database/migration_21_categorias_global.sql
-- ============================================================

-- 1. Asegurar que public.categorias existe con constraint único por nombre
CREATE TABLE IF NOT EXISTS public.categorias (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo      BOOLEAN   DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE public.categorias
    ADD CONSTRAINT IF NOT EXISTS categorias_nombre_unique UNIQUE (nombre);

-- 2. Para cada sucursal: migrar categorías, actualizar FKs y eliminar tabla local
DO $$
DECLARE
    schema_rec  RECORD;
    old_cat     RECORD;
    new_cat_id  INTEGER;
BEGIN
    FOR schema_rec IN
        SELECT schema_name FROM public.sucursales
    LOOP
        -- Saltar si el schema no tiene tabla local de categorías
        IF NOT EXISTS (
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = schema_rec.schema_name
              AND table_name   = 'categorias'
        ) THEN
            RAISE NOTICE 'Schema % no tiene tabla local de categorías, omitiendo.', schema_rec.schema_name;
            CONTINUE;
        END IF;

        RAISE NOTICE 'Procesando schema: %', schema_rec.schema_name;

        -- Migrar cada categoría local → public.categorias
        FOR old_cat IN EXECUTE format(
            'SELECT id, nombre, descripcion, activo FROM %I.categorias', schema_rec.schema_name
        )
        LOOP
            INSERT INTO public.categorias (nombre, descripcion, activo)
            VALUES (old_cat.nombre, old_cat.descripcion, old_cat.activo)
            ON CONFLICT (nombre) DO NOTHING;

            SELECT id INTO new_cat_id
            FROM public.categorias
            WHERE LOWER(nombre) = LOWER(old_cat.nombre);

            -- Apuntar productos al nuevo ID global
            EXECUTE format(
                'UPDATE %I.productos SET categoria_id = %s WHERE categoria_id = %s',
                schema_rec.schema_name, new_cat_id, old_cat.id
            );
        END LOOP;

        -- Reemplazar FK local por FK a public.categorias
        EXECUTE format(
            'ALTER TABLE %I.productos DROP CONSTRAINT IF EXISTS productos_categoria_id_fkey',
            schema_rec.schema_name
        );
        EXECUTE format(
            'ALTER TABLE %I.productos
             ADD CONSTRAINT productos_categoria_id_fkey
             FOREIGN KEY (categoria_id) REFERENCES public.categorias(id)',
            schema_rec.schema_name
        );

        -- Eliminar tabla local (ya no se necesita)
        EXECUTE format('DROP TABLE IF EXISTS %I.categorias CASCADE', schema_rec.schema_name);

        RAISE NOTICE 'Schema % migrado correctamente.', schema_rec.schema_name;
    END LOOP;
END $$;

-- 3. Insertar categorías por defecto si public.categorias quedó vacía
INSERT INTO public.categorias (nombre, descripcion) VALUES
    ('Medicamentos',           'Fármacos y medicamentos en general'),
    ('Vitaminas y Suplementos','Vitaminas, minerales y suplementos nutricionales'),
    ('Cuidado Personal',       'Productos de higiene y cuidado personal'),
    ('Primeros Auxilios',      'Materiales de curación y primeros auxilios'),
    ('Bebés y Niños',          'Productos para bebés y niños'),
    ('Genéricos',              'Medicamentos genéricos')
ON CONFLICT (nombre) DO NOTHING;

RAISE NOTICE 'Migración 21 completada: categorías ahora son globales en public.categorias';
