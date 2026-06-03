-- ============================================================
-- ARCHIVO: farmacia/database/migration_09_facturacion_campos_operativos.sql
-- DESCRIPCION: Paso 9 - adecua tablas operativas de sucursal
--              para preparar ventas, clientes, productos y
--              comprobantes hacia facturacion electronica.
-- USO:
--   SET search_path TO nombre_schema, public;
--   \i database/migration_09_facturacion_campos_operativos.sql
-- ============================================================

-- ============================================================
-- CLIENTES
-- ============================================================
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS tipo_documento_id INTEGER REFERENCES public.fe_tipos_documento_identidad(id);
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS tipo_documento_codigo VARCHAR(2);
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS numero_documento VARCHAR(20);
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS nombre_completo VARCHAR(255);
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS razon_social VARCHAR(255);
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS codigo_pais VARCHAR(2) DEFAULT 'PE';
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS ubigeo VARCHAR(6);

UPDATE clientes
SET tipo_documento_codigo = CASE
        WHEN COALESCE(NULLIF(TRIM(ruc), ''), '') <> '' THEN '6'
        WHEN COALESCE(NULLIF(TRIM(dni), ''), '') <> '' THEN '1'
        ELSE COALESCE(tipo_documento_codigo, '0')
    END,
    numero_documento = COALESCE(NULLIF(TRIM(ruc), ''), NULLIF(TRIM(dni), ''), numero_documento, '00000000'),
    nombre_completo = COALESCE(NULLIF(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, ''))), ''), nombre_completo, nombres),
    razon_social = COALESCE(NULLIF(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, ''))), ''), razon_social, nombres),
    codigo_pais = COALESCE(NULLIF(TRIM(codigo_pais), ''), 'PE')
WHERE TRUE;

UPDATE clientes c
SET tipo_documento_id = t.id
FROM public.fe_tipos_documento_identidad t
WHERE t.codigo = COALESCE(NULLIF(c.tipo_documento_codigo, ''), '0')
  AND c.tipo_documento_id IS NULL;

CREATE INDEX IF NOT EXISTS idx_clientes_numero_documento ON clientes (numero_documento);

-- ============================================================
-- PRODUCTOS
-- ============================================================
ALTER TABLE productos ADD COLUMN IF NOT EXISTS codigo_interno VARCHAR(50);
ALTER TABLE productos ADD COLUMN IF NOT EXISTS codigo_barras VARCHAR(100);
ALTER TABLE productos ADD COLUMN IF NOT EXISTS codigo_sunat VARCHAR(8) DEFAULT '00000000';
ALTER TABLE productos ADD COLUMN IF NOT EXISTS unidad_id INTEGER REFERENCES public.fe_unidades(id);
ALTER TABLE productos ADD COLUMN IF NOT EXISTS unidad_codigo VARCHAR(3) DEFAULT 'NIU';
ALTER TABLE productos ADD COLUMN IF NOT EXISTS afectacion_igv_id INTEGER REFERENCES public.fe_tipos_afectacion_igv(id);
ALTER TABLE productos ADD COLUMN IF NOT EXISTS afectacion_igv_codigo VARCHAR(2) DEFAULT '10';
ALTER TABLE productos ADD COLUMN IF NOT EXISTS porcentaje_igv DECIMAL(5,2) DEFAULT 18.00;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS incluye_igv BOOLEAN DEFAULT TRUE;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS icbper_activo BOOLEAN DEFAULT FALSE;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS factor_icbper DECIMAL(10,4) DEFAULT 0;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS product_type VARCHAR(20) DEFAULT 'product';

UPDATE productos
SET codigo_interno = COALESCE(NULLIF(TRIM(codigo_interno), ''), codigo),
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
WHERE TRUE;

UPDATE productos p
SET unidad_id = u.id
FROM public.fe_unidades u
WHERE u.codigo = COALESCE(NULLIF(p.unidad_codigo, ''), 'NIU')
  AND p.unidad_id IS NULL;

UPDATE productos p
SET afectacion_igv_id = a.id
FROM public.fe_tipos_afectacion_igv a
WHERE a.codigo = COALESCE(NULLIF(p.afectacion_igv_codigo, ''), '10')
  AND p.afectacion_igv_id IS NULL;

CREATE INDEX IF NOT EXISTS idx_productos_codigo_sunat ON productos (codigo_sunat);

-- ============================================================
-- VENTAS
-- ============================================================
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS fecha_emision DATE DEFAULT CURRENT_DATE;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS fecha_vencimiento DATE;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS hora_emision TIME DEFAULT CURRENT_TIME;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS moneda_id INTEGER REFERENCES public.fe_monedas(id);
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS moneda_codigo VARCHAR(3) DEFAULT 'PEN';
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS tipo_documento_id INTEGER REFERENCES public.fe_tipos_documento(id);
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS codigo_tipo_documento VARCHAR(2);
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS serie VARCHAR(4);
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS correlativo VARCHAR(8);
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS operacion_tipo_codigo VARCHAR(4) DEFAULT '0101';
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS forma_pago_id INTEGER REFERENCES public.fe_formas_pago(id);
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS sunat_forma_pago VARCHAR(20) DEFAULT 'Contado';
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS exonerada DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS inafecta DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS gravada DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS gratuita DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS anticipo DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS otros_cargos DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS icbper DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS monto_credito DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS vuelto DECIMAL(18,2) DEFAULT 0;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS cuotas JSONB;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS payment_breakdown JSONB;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS anulado BOOLEAN DEFAULT FALSE;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS cdr INTEGER;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS estado_cpe INTEGER;
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS ticket_sunat VARCHAR(100);
ALTER TABLE ventas ADD COLUMN IF NOT EXISTS qr TEXT;

UPDATE ventas
SET fecha_emision = COALESCE(fecha_emision, DATE(created_at)),
    fecha_vencimiento = COALESCE(fecha_vencimiento, DATE(created_at)),
    hora_emision = COALESCE(hora_emision, CAST(created_at AS TIME)),
    moneda_codigo = COALESCE(NULLIF(TRIM(moneda_codigo), ''), 'PEN'),
    codigo_tipo_documento = CASE COALESCE(tipo_comprobante, 'ticket')
        WHEN 'factura' THEN '01'
        WHEN 'boleta' THEN '03'
        WHEN 'nota_venta' THEN '02'
        WHEN 'ticket' THEN '02'
        ELSE COALESCE(NULLIF(TRIM(codigo_tipo_documento), ''), '02')
    END,
    operacion_tipo_codigo = COALESCE(NULLIF(TRIM(operacion_tipo_codigo), ''), '0101'),
    sunat_forma_pago = CASE
        WHEN LOWER(COALESCE(tipo_pago, '')) = 'credito' THEN 'Credito'
        ELSE COALESCE(NULLIF(TRIM(sunat_forma_pago), ''), 'Contado')
    END,
    gravada = COALESCE(gravada, subtotal, 0),
    exonerada = COALESCE(exonerada, 0),
    inafecta = COALESCE(inafecta, 0),
    gratuita = COALESCE(gratuita, 0),
    anticipo = COALESCE(anticipo, 0),
    otros_cargos = COALESCE(otros_cargos, 0),
    icbper = COALESCE(icbper, 0),
    monto_credito = COALESCE(monto_credito, 0),
    vuelto = COALESCE(vuelto, 0),
    anulado = CASE WHEN estado = 'anulada' THEN TRUE ELSE COALESCE(anulado, FALSE) END
WHERE TRUE;

UPDATE ventas v
SET moneda_id = m.id
FROM public.fe_monedas m
WHERE m.codigo = COALESCE(NULLIF(v.moneda_codigo, ''), 'PEN')
  AND v.moneda_id IS NULL;

UPDATE ventas v
SET tipo_documento_id = d.id
FROM public.fe_tipos_documento d
WHERE d.codigo = COALESCE(NULLIF(v.codigo_tipo_documento, ''), '02')
  AND v.tipo_documento_id IS NULL;

UPDATE ventas v
SET forma_pago_id = f.id
FROM public.fe_formas_pago f
WHERE LOWER(f.descripcion) = LOWER(CASE
        WHEN COALESCE(NULLIF(TRIM(v.tipo_pago), ''), '') = 'tarjeta' THEN 'Tarjeta de credito'
        WHEN COALESCE(NULLIF(TRIM(v.tipo_pago), ''), '') = 'transferencia' THEN 'Transferencia'
        WHEN COALESCE(NULLIF(TRIM(v.tipo_pago), ''), '') = 'yape' THEN 'Yape'
        WHEN COALESCE(NULLIF(TRIM(v.tipo_pago), ''), '') = 'plin' THEN 'Plin'
        ELSE 'Efectivo'
    END)
  AND v.forma_pago_id IS NULL;

CREATE INDEX IF NOT EXISTS idx_ventas_fecha_emision ON ventas (fecha_emision);
CREATE INDEX IF NOT EXISTS idx_ventas_codigo_tipo_documento ON ventas (codigo_tipo_documento);

-- ============================================================
-- DETALLE DE VENTAS
-- ============================================================
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS unidad_id INTEGER REFERENCES public.fe_unidades(id);
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS unidad_codigo VARCHAR(3) DEFAULT 'NIU';
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS afectacion_igv_id INTEGER REFERENCES public.fe_tipos_afectacion_igv(id);
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS afectacion_igv_codigo VARCHAR(2) DEFAULT '10';
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS descripcion VARCHAR(255);
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS codigo_interno VARCHAR(50);
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS codigo_sunat VARCHAR(8) DEFAULT '00000000';
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS igv DECIMAL(18,2) DEFAULT 0;
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS valor_unitario DECIMAL(18,10) DEFAULT 0;
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS valor_total DECIMAL(18,2) DEFAULT 0;
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS precio_total DECIMAL(18,2) DEFAULT 0;
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS icbper DECIMAL(18,2) DEFAULT 0;
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS factor_icbper DECIMAL(18,4) DEFAULT 0;
ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS cantidad_bolsas DECIMAL(18,2) DEFAULT 0;

UPDATE venta_detalles vd
SET descripcion = COALESCE(vd.descripcion, p.nombre),
    codigo_interno = COALESCE(NULLIF(TRIM(vd.codigo_interno), ''), p.codigo_interno, p.codigo),
    codigo_sunat = COALESCE(NULLIF(TRIM(vd.codigo_sunat), ''), p.codigo_sunat, '00000000'),
    unidad_codigo = COALESCE(NULLIF(TRIM(vd.unidad_codigo), ''), p.unidad_codigo, 'NIU'),
    afectacion_igv_codigo = COALESCE(NULLIF(TRIM(vd.afectacion_igv_codigo), ''), p.afectacion_igv_codigo, '10'),
    precio_total = COALESCE(vd.precio_total, vd.subtotal, vd.cantidad * vd.precio_unitario),
    valor_unitario = COALESCE(vd.valor_unitario, ROUND(vd.precio_unitario / 1.18, 10)),
    valor_total = COALESCE(vd.valor_total, vd.subtotal, vd.cantidad * ROUND(vd.precio_unitario / 1.18, 10)),
    igv = COALESCE(vd.igv, ROUND((vd.cantidad * vd.precio_unitario) - (vd.cantidad * ROUND(vd.precio_unitario / 1.18, 10)), 2)),
    icbper = COALESCE(vd.icbper, 0),
    factor_icbper = COALESCE(vd.factor_icbper, 0),
    cantidad_bolsas = COALESCE(vd.cantidad_bolsas, 0)
FROM productos p
WHERE p.id = vd.producto_id;

UPDATE venta_detalles vd
SET unidad_id = u.id
FROM public.fe_unidades u
WHERE u.codigo = COALESCE(NULLIF(vd.unidad_codigo, ''), 'NIU')
  AND vd.unidad_id IS NULL;

UPDATE venta_detalles vd
SET afectacion_igv_id = a.id
FROM public.fe_tipos_afectacion_igv a
WHERE a.codigo = COALESCE(NULLIF(vd.afectacion_igv_codigo, ''), '10')
  AND vd.afectacion_igv_id IS NULL;

-- ============================================================
-- COMPROBANTES ELECTRONICOS
-- ============================================================
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS codigo_tipo_documento VARCHAR(2);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS fecha_emision DATE;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS ticket_sunat VARCHAR(100);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS codigo_respuesta VARCHAR(20);
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS mensaje_respuesta TEXT;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS hash_cpe TEXT;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS soap_request TEXT;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS soap_response TEXT;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS payload_json JSONB;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS sunat_response_json JSONB;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE comprobantes_electronicos ADD COLUMN IF NOT EXISTS anulado BOOLEAN DEFAULT FALSE;

UPDATE comprobantes_electronicos ce
SET codigo_tipo_documento = CASE COALESCE(ce.tipo, '')
        WHEN 'factura' THEN '01'
        WHEN 'boleta' THEN '03'
        WHEN 'nota_credito' THEN '07'
        WHEN 'nota_debito' THEN '08'
        ELSE COALESCE(NULLIF(TRIM(codigo_tipo_documento), ''), '03')
    END,
    fecha_emision = COALESCE(ce.fecha_emision, DATE(ce.created_at))
WHERE TRUE;

-- ============================================================
-- SERIES DE COMPROBANTES
-- ============================================================
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS tipo_documento_id INTEGER REFERENCES public.fe_tipos_documento(id);
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS codigo_tipo_documento VARCHAR(2);
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS descripcion VARCHAR(100);
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS anexo_establecimiento VARCHAR(4) DEFAULT '0000';
ALTER TABLE series_comprobantes ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT TRUE;

UPDATE series_comprobantes
SET codigo_tipo_documento = CASE COALESCE(tipo, '')
        WHEN 'factura' THEN '01'
        WHEN 'boleta' THEN '03'
        WHEN 'ticket' THEN '02'
        ELSE COALESCE(NULLIF(TRIM(codigo_tipo_documento), ''), '03')
    END,
    descripcion = CASE COALESCE(tipo, '')
        WHEN 'factura' THEN 'Factura'
        WHEN 'boleta' THEN 'Boleta'
        WHEN 'ticket' THEN 'Nota de venta'
        ELSE COALESCE(NULLIF(TRIM(descripcion), ''), 'Comprobante')
    END,
    anexo_establecimiento = COALESCE(NULLIF(TRIM(anexo_establecimiento), ''), '0000'),
    activo = COALESCE(activo, TRUE)
WHERE TRUE;

UPDATE series_comprobantes sc
SET tipo_documento_id = td.id
FROM public.fe_tipos_documento td
WHERE td.codigo = COALESCE(NULLIF(sc.codigo_tipo_documento, ''), '03')
  AND sc.tipo_documento_id IS NULL;

INSERT INTO series_comprobantes (tipo, serie, ultimo_numero, codigo_tipo_documento, descripcion, anexo_establecimiento, activo, tipo_documento_id)
SELECT 'ticket', 'NV01', 0, '02', 'Nota de venta', '0000', TRUE, td.id
FROM public.fe_tipos_documento td
WHERE td.codigo = '02'
  AND NOT EXISTS (
      SELECT 1 FROM series_comprobantes WHERE tipo = 'ticket'
  );
