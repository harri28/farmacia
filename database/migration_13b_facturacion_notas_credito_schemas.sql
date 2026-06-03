-- ============================================================
-- ARCHIVO: farmacia/database/migration_13b_facturacion_notas_credito_schemas.sql
-- DESCRIPCION: Aplica el soporte de notas de credito en todos
--              los schemas de sucursal existentes.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_13b_facturacion_notas_credito_schemas.sql
-- ============================================================

DO $$
DECLARE
    s text;
BEGIN
    FOR s IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name NOT LIKE 'pg_%'
          AND schema_name NOT IN ('information_schema', 'public')
    LOOP
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS referencia_comprobante_id INTEGER', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS tipo_nota_credito_id INTEGER', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS codigo_tipo_nota_credito VARCHAR(2)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS motivo_nota_credito TEXT', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS descripcion_nota_credito TEXT', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_tipo_documento_codigo VARCHAR(2)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_serie VARCHAR(10)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_numero VARCHAR(20)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_numero_completo VARCHAR(30)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_fecha DATE', s);

        EXECUTE format('CREATE INDEX IF NOT EXISTS idx_%s_comprobantes_tipo_fecha ON %I.comprobantes_electronicos (tipo, created_at DESC)', s, s);
        EXECUTE format('CREATE INDEX IF NOT EXISTS idx_%s_comprobantes_referencia_nc ON %I.comprobantes_electronicos (referencia_comprobante_id)', s, s);

        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS tipo_documento_id INTEGER', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS codigo_tipo_documento VARCHAR(2)', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS descripcion VARCHAR(100)', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS anexo_establecimiento VARCHAR(4) DEFAULT ''0000''', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT TRUE', s);

        EXECUTE format($sql$
            INSERT INTO %I.series_comprobantes (tipo, serie, ultimo_numero, codigo_tipo_documento, descripcion, anexo_establecimiento, activo, tipo_documento_id)
            SELECT 'nota_credito', 'NC01', 0, '07', 'Nota de credito', '0000', TRUE, td.id
            FROM public.fe_tipos_documento td
            WHERE td.codigo = '07'
              AND NOT EXISTS (
                  SELECT 1
                  FROM %I.series_comprobantes
                  WHERE tipo = 'nota_credito' AND serie = 'NC01'
              )
        $sql$, s, s);
    END LOOP;
END $$;

