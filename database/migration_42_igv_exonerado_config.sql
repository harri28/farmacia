-- migration_42_igv_exonerado_config.sql
-- Agrega public.tenants.igv_exonerado: interruptor por empresa para marcar
-- exoneracion de IGV (Ley de Amazonia / Region Selva, Ley 27037), en vez de
-- tener que correr un UPDATE manual como migration_29 cada vez que se cree
-- un tenant nuevo en esa region.
--
-- Se retropobla a TRUE para todos los tenants existentes, porque
-- migration_29_exoneracion_igv_selva.sql ya habia exonerado TODOS los
-- productos de TODOS los schemas existentes en su momento (hasta ahora,
-- cada tenant de este sistema ha sido de la region Selva). Los tenants
-- nuevos que se creen despues de esta migracion nacen con el valor por
-- defecto (FALSE = factura con IGV normal); el admin debe activar el
-- interruptor manualmente desde Admin -> Configuracion si corresponde.
--
-- Ademas re-aplica la logica de migration_29 a cualquier schema que se
-- haya quedado fuera (sucursales creadas despues de esa migracion, como
-- Grupo Tapullima & Manayalle SAC), para que el backfill quede consistente
-- con los productos reales.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_42_igv_exonerado_config.sql

ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS igv_exonerado BOOLEAN DEFAULT FALSE;

UPDATE public.tenants SET igv_exonerado = TRUE WHERE igv_exonerado IS NOT TRUE;

DO $$
DECLARE
    s      RECORD;
    exo_id INT;
BEGIN
    SELECT id INTO exo_id FROM public.fe_tipos_afectacion_igv WHERE codigo = '20' LIMIT 1;
    IF exo_id IS NULL THEN
        RAISE EXCEPTION 'No se encontro el tipo de afectacion IGV con codigo 20 (Exonerado)';
    END IF;

    FOR s IN
        SELECT table_schema AS schema_name
        FROM information_schema.tables
        WHERE table_name = 'productos'
          AND table_schema NOT IN ('public', 'pg_catalog', 'information_schema')
        ORDER BY table_schema
    LOOP
        BEGIN
            EXECUTE format('
                UPDATE %I.productos
                SET afectacion_igv_id     = %s,
                    afectacion_igv_codigo = ''20'',
                    updated_at            = NOW()
                WHERE afectacion_igv_codigo IS DISTINCT FROM ''20''
            ', s.schema_name, exo_id);

            RAISE NOTICE 'Schema % OK', s.schema_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Schema % SALTADO: %', s.schema_name, SQLERRM;
        END;
    END LOOP;
END $$;
