-- fix_igv_exonerado_generycpharma.sql
-- Los ~7,083 productos importados a las 2 sucursales de generycpharma
-- (ver aprendizaje_importacion_productos.md) quedaron con el valor por
-- defecto de afectacion_igv_codigo ('10' = Gravada, 18% IGV) porque el
-- sistema de origen no traia ese dato. El negocio opera bajo exoneracion
-- de IGV de la Amazonia/selva, asi que deben quedar en '20' (Exonerada)
-- con afectacion_igv_id apuntando al catalogo global correspondiente.
--
-- USO:
--   psql -U postgres -d farmacia -f database/fix_igv_exonerado_generycpharma.sql

DO $$
DECLARE
    s TEXT;
    schemas TEXT[] := ARRAY['generyc_pharma_alonso_de_alvarado', 'generyc_pharma_jr_lima_tambo'];
    afectacion_id INT;
    total_actualizado INT;
BEGIN
    SELECT id INTO afectacion_id FROM public.fe_tipos_afectacion_igv WHERE codigo = '20';
    IF afectacion_id IS NULL THEN
        RAISE EXCEPTION 'No se encontro el codigo de afectacion 20 (Exonerada) en public.fe_tipos_afectacion_igv';
    END IF;

    FOREACH s IN ARRAY schemas LOOP
        EXECUTE format(
            'UPDATE %I.productos SET afectacion_igv_id = %L, afectacion_igv_codigo = %L',
            s, afectacion_id, '20'
        );
        GET DIAGNOSTICS total_actualizado = ROW_COUNT;
        RAISE NOTICE 'Schema %: % productos actualizados a EXO (20)', s, total_actualizado;
    END LOOP;
END $$;
