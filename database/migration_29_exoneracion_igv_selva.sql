-- Migration 29: Exoneración IGV Selva
-- Actualiza todos los productos existentes a Exonerado (código 20)
-- para cumplir con la Ley de Amazonía (Ley 27037) - Región Selva.
-- Aplica a todos los schemas que contienen la tabla 'productos'.

DO $$
DECLARE
    s   RECORD;
    exo_id INT;
BEGIN
    SELECT id INTO exo_id
    FROM public.fe_tipos_afectacion_igv
    WHERE codigo = '20'
    LIMIT 1;

    IF exo_id IS NULL THEN
        RAISE EXCEPTION 'No se encontró el tipo de afectación IGV con código 20 (Exonerado)';
    END IF;

    FOR s IN
        SELECT table_schema AS schema_name
        FROM information_schema.tables
        WHERE table_name = 'productos'
          AND table_schema NOT IN ('public', 'pg_catalog', 'information_schema')
        ORDER BY table_schema
    LOOP
        RAISE NOTICE 'Actualizando schema: %', s.schema_name;

        EXECUTE format('
            UPDATE %I.productos
            SET afectacion_igv_id     = %s,
                afectacion_igv_codigo = ''20'',
                updated_at            = NOW()
            WHERE afectacion_igv_codigo != ''20''
        ', s.schema_name, exo_id);

        RAISE NOTICE '  → % productos actualizados', found;
    END LOOP;
END $$;
