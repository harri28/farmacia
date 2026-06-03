-- ============================================================
-- ARCHIVO: farmacia/database/migration_11_facturacion_productos_defaults.sql
-- DESCRIPCION: Paso 11 - normaliza productos existentes para
--              dejarlos listos para facturacion electronica.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_11_facturacion_productos_defaults.sql
-- ============================================================

DO $$
DECLARE
    suc RECORD;
BEGIN
    FOR suc IN
        SELECT schema_name
        FROM public.sucursales
        WHERE schema_name IS NOT NULL
          AND schema_name <> ''
    LOOP
        EXECUTE format($sql$
            UPDATE %1$I.productos
            SET codigo_interno = COALESCE(NULLIF(TRIM(codigo_interno), ''), codigo),
                codigo_barras = NULLIF(TRIM(codigo_barras), ''),
                codigo_sunat = COALESCE(NULLIF(TRIM(codigo_sunat), ''), '00000000'),
                unidad_codigo = CASE
                    WHEN LOWER(COALESCE(unidad, '')) IN ('servicio', 'servicios') THEN 'ZZ'
                    ELSE COALESCE(NULLIF(TRIM(unidad_codigo), ''), 'NIU')
                END,
                afectacion_igv_codigo = COALESCE(NULLIF(TRIM(afectacion_igv_codigo), ''), '10'),
                porcentaje_igv = COALESCE(porcentaje_igv, 18.00),
                incluye_igv = COALESCE(incluye_igv, TRUE),
                icbper_activo = COALESCE(icbper_activo, FALSE),
                factor_icbper = COALESCE(factor_icbper, 0),
                product_type = COALESCE(NULLIF(TRIM(product_type), ''), 'product')
            WHERE TRUE
        $sql$, suc.schema_name);

        EXECUTE format($sql$
            UPDATE %1$I.productos p
            SET unidad_id = u.id
            FROM public.fe_unidades u
            WHERE u.codigo = COALESCE(NULLIF(p.unidad_codigo, ''), 'NIU')
              AND (p.unidad_id IS NULL OR p.unidad_id <> u.id)
        $sql$, suc.schema_name);

        EXECUTE format($sql$
            UPDATE %1$I.productos p
            SET afectacion_igv_id = a.id
            FROM public.fe_tipos_afectacion_igv a
            WHERE a.codigo = COALESCE(NULLIF(p.afectacion_igv_codigo, ''), '10')
              AND (p.afectacion_igv_id IS NULL OR p.afectacion_igv_id <> a.id)
        $sql$, suc.schema_name);
    END LOOP;
END $$;
