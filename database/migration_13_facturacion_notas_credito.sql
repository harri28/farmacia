-- ============================================================
-- ARCHIVO: farmacia/database/migration_13_facturacion_notas_credito.sql
-- DESCRIPCION: Paso 13 - soporte operativo para notas de credito
--              electronicas y su referencia al comprobante original.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_13_facturacion_notas_credito.sql
-- ============================================================

-- ============================================================
-- COMPROBANTES ELECTRONICOS: campos de referencia para NC
-- ============================================================
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS referencia_comprobante_id INTEGER REFERENCES comprobantes_electronicos(id);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS tipo_nota_credito_id INTEGER REFERENCES public.fe_tipos_nota_credito(id);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS codigo_tipo_nota_credito VARCHAR(2);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS motivo_nota_credito TEXT;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS descripcion_nota_credito TEXT;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_tipo_documento_codigo VARCHAR(2);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_serie VARCHAR(10);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_numero VARCHAR(20);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_numero_completo VARCHAR(30);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS documento_modificado_fecha DATE;

CREATE INDEX IF NOT EXISTS idx_comprobantes_tipo_fecha ON comprobantes_electronicos (tipo, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_comprobantes_referencia_nc ON comprobantes_electronicos (referencia_comprobante_id);

-- ============================================================
-- SERIES DE COMPROBANTES: serie base para notas de credito
-- ============================================================
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS tipo_documento_id INTEGER REFERENCES public.fe_tipos_documento(id);
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS codigo_tipo_documento VARCHAR(2);
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS descripcion VARCHAR(100);
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS anexo_establecimiento VARCHAR(4) DEFAULT '0000';
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT TRUE;

INSERT INTO series_comprobantes (tipo, serie, ultimo_numero, codigo_tipo_documento, descripcion, anexo_establecimiento, activo, tipo_documento_id)
SELECT 'nota_credito', 'NC01', 0, '07', 'Nota de credito', '0000', TRUE, td.id
FROM public.fe_tipos_documento td
WHERE td.codigo = '07'
  AND NOT EXISTS (
      SELECT 1
      FROM series_comprobantes
      WHERE tipo = 'nota_credito' AND serie = 'NC01'
  );
