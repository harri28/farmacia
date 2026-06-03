-- ============================================================
-- ARCHIVO: farmacia/database/migration_14_facturacion_series_notas_credito.sql
-- DESCRIPCION: Agrega series de nota de credito por tipo de origen
--              (boleta / factura) en todos los schemas de sucursal.
-- ============================================================

DO $$
DECLARE
    schema_rec RECORD;
    v_tipo_documento_id INTEGER;
BEGIN
    SELECT id
    INTO v_tipo_documento_id
    FROM public.fe_tipos_documento
    WHERE codigo = '07'
    LIMIT 1;

    IF v_tipo_documento_id IS NULL THEN
        RAISE EXCEPTION 'No se encontro el tipo de documento 07 en fe_tipos_documento.';
    END IF;

    FOR schema_rec IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT IN ('information_schema', 'pg_catalog')
          AND schema_name NOT LIKE 'pg_toast%'
          AND schema_name NOT LIKE 'pg_temp_%'
    LOOP
        EXECUTE format(
            'INSERT INTO %I.series_comprobantes (
                tipo, serie, ultimo_numero, tipo_documento_id,
                codigo_tipo_documento, descripcion, anexo_establecimiento, activo
            ) VALUES (
                %L, %L, 0, %s, %L, %L, %L, TRUE
            )
            ON CONFLICT (tipo) DO UPDATE SET
                serie = EXCLUDED.serie,
                tipo_documento_id = EXCLUDED.tipo_documento_id,
                codigo_tipo_documento = EXCLUDED.codigo_tipo_documento,
                descripcion = EXCLUDED.descripcion,
                anexo_establecimiento = EXCLUDED.anexo_establecimiento,
                activo = TRUE',
            schema_rec.schema_name,
            'nota_credito_boleta', 'BC01', v_tipo_documento_id, '07', 'Nota de crédito boleta', '0000'
        );

        EXECUTE format(
            'INSERT INTO %I.series_comprobantes (
                tipo, serie, ultimo_numero, tipo_documento_id,
                codigo_tipo_documento, descripcion, anexo_establecimiento, activo
            ) VALUES (
                %L, %L, 0, %s, %L, %L, %L, TRUE
            )
            ON CONFLICT (tipo) DO UPDATE SET
                serie = EXCLUDED.serie,
                tipo_documento_id = EXCLUDED.tipo_documento_id,
                codigo_tipo_documento = EXCLUDED.codigo_tipo_documento,
                descripcion = EXCLUDED.descripcion,
                anexo_establecimiento = EXCLUDED.anexo_establecimiento,
                activo = TRUE',
            schema_rec.schema_name,
            'nota_credito_factura', 'FC01', v_tipo_documento_id, '07', 'Nota de crédito factura', '0000'
        );
    END LOOP;
END
$$;
