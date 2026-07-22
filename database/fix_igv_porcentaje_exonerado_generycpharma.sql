-- fix_igv_porcentaje_exonerado_generycpharma.sql
-- fix_igv_exonerado_generycpharma.sql (corrida antes) solo actualizo
-- afectacion_igv_codigo/afectacion_igv_id a Exonerada, pero dejo
-- porcentaje_igv e incluye_igv con el valor original del import (18 / true).
-- Este script corrige esos dos campos para que coincidan con la afectacion.
--
-- USO:
--   psql -U postgres -d farmacia -f database/fix_igv_porcentaje_exonerado_generycpharma.sql

DO $$
DECLARE
    s TEXT;
    schemas TEXT[] := ARRAY['generyc_pharma_alonso_de_alvarado', 'generyc_pharma_jr_lima_tambo'];
    total_actualizado INT;
BEGIN
    FOREACH s IN ARRAY schemas LOOP
        EXECUTE format(
            'UPDATE %I.productos SET porcentaje_igv = 0, incluye_igv = FALSE, updated_at = NOW()
             WHERE afectacion_igv_codigo != %L AND (porcentaje_igv != 0 OR incluye_igv = TRUE)',
            s, '10'
        );
        GET DIAGNOSTICS total_actualizado = ROW_COUNT;
        RAISE NOTICE 'Schema %: % productos corregidos', s, total_actualizado;
    END LOOP;
END $$;
