-- migration_20: Agrega unidad_medida a orden_compra_detalles en todos los esquemas

DO $$
DECLARE r RECORD;
BEGIN
    FOR r IN
        SELECT DISTINCT schemaname FROM pg_tables
        WHERE tablename = 'orden_compra_detalles'
        ORDER BY schemaname
    LOOP
        EXECUTE format(
            'ALTER TABLE %I.orden_compra_detalles ADD COLUMN IF NOT EXISTS unidad_medida VARCHAR(50)',
            r.schemaname
        );
        RAISE NOTICE 'Migrado esquema: %', r.schemaname;
    END LOOP;
END;
$$;
