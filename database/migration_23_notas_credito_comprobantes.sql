-- ============================================================
-- ARCHIVO: farmacia/database/migration_23_notas_credito_comprobantes.sql
-- DESCRIPCION: Agrega las columnas de nota de crédito a la tabla
--              comprobantes_electronicos en todos los schemas de sucursal.
--              Estas columnas están en schema_sucursal.sql pero no fueron
--              incluidas en la migration_22.
-- USO:
--   $env:PGPASSWORD = "1234"
--   & "C:\Program Files\PostgreSQL\17\bin\psql.exe" -U postgres -d farmacia -f database/migration_23_notas_credito_comprobantes.sql
-- ============================================================

DO $$
DECLARE
    s TEXT;
BEGIN
    FOR s IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT LIKE 'pg_%'
          AND schema_name NOT IN ('information_schema', 'public')
    LOOP
        -- Referencia al comprobante que originó la nota de crédito
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS referencia_comprobante_id INTEGER REFERENCES %I.comprobantes_electronicos(id)',
            s, s
        );

        -- Tipo de nota de crédito (catálogo SUNAT)
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS tipo_nota_credito_id INTEGER REFERENCES public.fe_tipos_nota_credito(id)',
            s
        );
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS codigo_tipo_nota_credito VARCHAR(2)',
            s
        );
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS motivo_nota_credito TEXT',
            s
        );
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS descripcion_nota_credito TEXT',
            s
        );

        -- Datos del documento modificado (comprobante original referenciado en UBL)
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_tipo_documento_codigo VARCHAR(2)',
            s
        );
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_serie VARCHAR(10)',
            s
        );
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_numero VARCHAR(20)',
            s
        );
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_numero_completo VARCHAR(30)',
            s
        );
        EXECUTE format(
            'ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_fecha DATE',
            s
        );

        RAISE NOTICE 'Schema % actualizado.', s;
    END LOOP;
END
$$;
