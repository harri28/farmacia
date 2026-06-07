-- migration_18: Agrega columnas de tipo de documento a todas las tablas clientes
-- Aplica a public + todos los esquemas de sucursal

DO $$
DECLARE
    r RECORD;
BEGIN
    FOR r IN
        SELECT schemaname
        FROM pg_tables
        WHERE tablename = 'clientes'
        ORDER BY schemaname
    LOOP
        -- Agregar columnas nuevas (IF NOT EXISTS evita error si ya existen)
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS tipo_documento_id      INT REFERENCES public.fe_tipos_documento_identidad(id)', r.schemaname);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS tipo_documento_codigo  VARCHAR(5)', r.schemaname);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS numero_documento       VARCHAR(20)', r.schemaname);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS nombre_completo        VARCHAR(300)', r.schemaname);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS razon_social           VARCHAR(300)', r.schemaname);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS codigo_pais            VARCHAR(5) DEFAULT ''PE''', r.schemaname);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS ubigeo                 VARCHAR(6)', r.schemaname);

        -- Poblar datos a partir de las columnas dni/ruc existentes
        EXECUTE format($sql$
            UPDATE %I.clientes SET
                tipo_documento_codigo = CASE
                    WHEN ruc  IS NOT NULL AND ruc  <> '' THEN '6'
                    WHEN dni  IS NOT NULL AND dni  <> '' THEN '1'
                    ELSE '0'
                END,
                numero_documento = COALESCE(NULLIF(ruc, ''), NULLIF(dni, ''), ''),
                nombre_completo  = TRIM(COALESCE(nombres, '') || ' ' || COALESCE(apellidos, '')),
                razon_social     = CASE
                    WHEN ruc IS NOT NULL AND ruc <> '' THEN TRIM(COALESCE(nombres, ''))
                    ELSE TRIM(COALESCE(nombres, '') || ' ' || COALESCE(apellidos, ''))
                END,
                codigo_pais = 'PE'
            WHERE tipo_documento_codigo IS NULL
        $sql$, r.schemaname);

        -- Asignar tipo_documento_id en base al codigo determinado
        EXECUTE format($sql$
            UPDATE %I.clientes c
            SET tipo_documento_id = td.id
            FROM public.fe_tipos_documento_identidad td
            WHERE td.codigo = c.tipo_documento_codigo
              AND c.tipo_documento_id IS NULL
        $sql$, r.schemaname);

        RAISE NOTICE 'Migrado esquema: %', r.schemaname;
    END LOOP;
END;
$$;
