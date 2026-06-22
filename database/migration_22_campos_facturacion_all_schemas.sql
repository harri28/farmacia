-- ============================================================
-- ARCHIVO: farmacia/database/migration_22_campos_facturacion_all_schemas.sql
-- DESCRIPCION: Aplica los campos de facturacion electronica
--              (migration_09) a todos los schemas de sucursal
--              existentes que aun no los tengan.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_22_campos_facturacion_all_schemas.sql
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

        -- ---- CLIENTES ----
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS tipo_documento_id     INTEGER', s);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS tipo_documento_codigo VARCHAR(2)', s);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS numero_documento      VARCHAR(20)', s);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS nombre_completo       VARCHAR(255)', s);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS razon_social          VARCHAR(255)', s);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS codigo_pais           VARCHAR(2) DEFAULT ''PE''', s);
        EXECUTE format('ALTER TABLE %I.clientes ADD COLUMN IF NOT EXISTS ubigeo               VARCHAR(6)', s);

        -- Rellenar numero_documento y nombre_completo en clientes existentes
        EXECUTE format($q$
            UPDATE %I.clientes
            SET tipo_documento_codigo = CASE
                    WHEN COALESCE(NULLIF(TRIM(ruc),''),'') <> '' THEN '6'
                    WHEN COALESCE(NULLIF(TRIM(dni),''),'') <> '' THEN '1'
                    ELSE COALESCE(tipo_documento_codigo, '0')
                END,
                numero_documento = COALESCE(
                    NULLIF(TRIM(ruc),''),
                    NULLIF(TRIM(dni),''),
                    numero_documento,
                    '00000000'
                ),
                nombre_completo = COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(nombres,''),' ',COALESCE(apellidos,''))), ''),
                    nombre_completo,
                    nombres
                ),
                razon_social = COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(nombres,''),' ',COALESCE(apellidos,''))), ''),
                    razon_social,
                    nombres
                ),
                codigo_pais = COALESCE(NULLIF(TRIM(codigo_pais),''), 'PE')
            WHERE TRUE
        $q$, s);

        EXECUTE format($q$
            UPDATE %I.clientes c
            SET tipo_documento_id = t.id
            FROM public.fe_tipos_documento_identidad t
            WHERE t.codigo = COALESCE(NULLIF(c.tipo_documento_codigo,''), '0')
              AND c.tipo_documento_id IS NULL
        $q$, s);

        -- ---- PRODUCTOS ----
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS codigo_interno      VARCHAR(50)', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS codigo_barras       VARCHAR(100)', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS codigo_sunat        VARCHAR(8)  DEFAULT ''00000000''', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS unidad_id           INTEGER', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS unidad_codigo       VARCHAR(3)  DEFAULT ''NIU''', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS afectacion_igv_id  INTEGER', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS afectacion_igv_codigo VARCHAR(2) DEFAULT ''10''', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS porcentaje_igv     DECIMAL(5,2) DEFAULT 18.00', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS incluye_igv        BOOLEAN DEFAULT TRUE', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS icbper_activo      BOOLEAN DEFAULT FALSE', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS factor_icbper      DECIMAL(10,4) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.productos ADD COLUMN IF NOT EXISTS product_type       VARCHAR(20) DEFAULT ''product''', s);

        EXECUTE format($q$
            UPDATE %I.productos
            SET codigo_interno = COALESCE(NULLIF(TRIM(codigo_interno),''), codigo),
                codigo_sunat   = COALESCE(NULLIF(TRIM(codigo_sunat),''), '00000000'),
                unidad_codigo  = CASE
                    WHEN LOWER(COALESCE(unidad,'')) IN ('servicio','servicios') THEN 'ZZ'
                    ELSE COALESCE(NULLIF(TRIM(unidad_codigo),''), 'NIU')
                END,
                afectacion_igv_codigo = COALESCE(NULLIF(TRIM(afectacion_igv_codigo),''), '10'),
                porcentaje_igv = COALESCE(porcentaje_igv, 18.00),
                incluye_igv    = COALESCE(incluye_igv, TRUE),
                icbper_activo  = COALESCE(icbper_activo, FALSE),
                factor_icbper  = COALESCE(factor_icbper, 0),
                product_type   = COALESCE(NULLIF(TRIM(product_type),''), 'product')
            WHERE TRUE
        $q$, s);

        -- ---- VENTAS ----
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS fecha_emision          DATE    DEFAULT CURRENT_DATE', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS fecha_vencimiento      DATE', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS hora_emision           TIME    DEFAULT CURRENT_TIME', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS moneda_id             INTEGER', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS moneda_codigo          VARCHAR(3) DEFAULT ''PEN''', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS tipo_documento_id     INTEGER', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS codigo_tipo_documento  VARCHAR(2)', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS serie                  VARCHAR(4)', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS correlativo            VARCHAR(8)', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS operacion_tipo_codigo  VARCHAR(4) DEFAULT ''0101''', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS forma_pago_id         INTEGER', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS sunat_forma_pago       VARCHAR(20) DEFAULT ''Contado''', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS exonerada              DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS inafecta               DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS gravada                DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS gratuita               DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS icbper                 DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS monto_credito          DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS vuelto                 DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS anulado                BOOLEAN DEFAULT FALSE', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS ticket_sunat           VARCHAR(100)', s);
        EXECUTE format('ALTER TABLE %I.ventas ADD COLUMN IF NOT EXISTS qr                    TEXT', s);

        EXECUTE format($q$
            UPDATE %I.ventas
            SET fecha_emision          = COALESCE(fecha_emision, DATE(created_at)),
                hora_emision           = COALESCE(hora_emision, CAST(created_at AS TIME)),
                moneda_codigo          = COALESCE(NULLIF(TRIM(moneda_codigo),''), 'PEN'),
                codigo_tipo_documento  = CASE COALESCE(tipo_comprobante,'ticket')
                    WHEN 'factura'    THEN '01'
                    WHEN 'boleta'     THEN '03'
                    WHEN 'nota_venta' THEN '02'
                    WHEN 'ticket'     THEN '02'
                    ELSE COALESCE(NULLIF(TRIM(codigo_tipo_documento),''), '02')
                END,
                operacion_tipo_codigo  = COALESCE(NULLIF(TRIM(operacion_tipo_codigo),''), '0101'),
                sunat_forma_pago       = CASE
                    WHEN LOWER(COALESCE(tipo_pago,'')) = 'credito' THEN 'Credito'
                    ELSE COALESCE(NULLIF(TRIM(sunat_forma_pago),''), 'Contado')
                END,
                gravada   = COALESCE(gravada, subtotal, 0),
                exonerada = COALESCE(exonerada, 0),
                inafecta  = COALESCE(inafecta, 0),
                gratuita  = COALESCE(gratuita, 0),
                icbper    = COALESCE(icbper, 0),
                anulado   = CASE WHEN estado = 'anulada' THEN TRUE ELSE COALESCE(anulado, FALSE) END
            WHERE TRUE
        $q$, s);

        -- ---- VENTA_DETALLES ----
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS unidad_id            INTEGER', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS unidad_codigo         VARCHAR(3) DEFAULT ''NIU''', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS afectacion_igv_id    INTEGER', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS afectacion_igv_codigo VARCHAR(2) DEFAULT ''10''', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS descripcion           VARCHAR(255)', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS codigo_interno        VARCHAR(50)', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS codigo_sunat          VARCHAR(8) DEFAULT ''00000000''', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS igv                   DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS valor_unitario        DECIMAL(18,10) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS valor_total           DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS precio_total          DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS icbper                DECIMAL(18,2) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS factor_icbper         DECIMAL(18,4) DEFAULT 0', s);
        EXECUTE format('ALTER TABLE %I.venta_detalles ADD COLUMN IF NOT EXISTS cantidad_bolsas       DECIMAL(18,2) DEFAULT 0', s);

        EXECUTE format($q$
            UPDATE %I.venta_detalles vd
            SET descripcion    = COALESCE(vd.descripcion, p.nombre),
                codigo_interno = COALESCE(NULLIF(TRIM(vd.codigo_interno),''), p.codigo_interno, p.codigo),
                codigo_sunat   = COALESCE(NULLIF(TRIM(vd.codigo_sunat),''), p.codigo_sunat, '00000000'),
                unidad_codigo  = COALESCE(NULLIF(TRIM(vd.unidad_codigo),''), p.unidad_codigo, 'NIU'),
                afectacion_igv_codigo = COALESCE(NULLIF(TRIM(vd.afectacion_igv_codigo),''), p.afectacion_igv_codigo, '10'),
                precio_total   = COALESCE(vd.precio_total, vd.subtotal, vd.cantidad * vd.precio_unitario),
                valor_unitario = COALESCE(NULLIF(vd.valor_unitario, 0), ROUND(vd.precio_unitario / 1.18, 10)),
                valor_total    = COALESCE(NULLIF(vd.valor_total, 0), vd.subtotal, vd.cantidad * ROUND(vd.precio_unitario / 1.18, 10)),
                igv            = COALESCE(NULLIF(vd.igv, 0), ROUND((vd.cantidad * vd.precio_unitario) - (vd.cantidad * ROUND(vd.precio_unitario / 1.18, 10)), 2))
            FROM %I.productos p
            WHERE p.id = vd.producto_id
        $q$, s, s);

        -- ---- COMPROBANTES_ELECTRONICOS ----
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS codigo_tipo_documento VARCHAR(2)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS fecha_emision        DATE', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS ticket_sunat         VARCHAR(100)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS codigo_respuesta     VARCHAR(20)', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS mensaje_respuesta    TEXT', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS hash_cpe             TEXT', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS soap_request         TEXT', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS soap_response        TEXT', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS payload_json         JSONB', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS sunat_response_json  JSONB', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP', s);
        EXECUTE format('ALTER TABLE %I.comprobantes_electronicos ADD COLUMN IF NOT EXISTS anulado              BOOLEAN DEFAULT FALSE', s);

        EXECUTE format($q$
            UPDATE %I.comprobantes_electronicos ce
            SET codigo_tipo_documento = CASE COALESCE(ce.tipo,'')
                    WHEN 'factura'      THEN '01'
                    WHEN 'boleta'       THEN '03'
                    WHEN 'nota_credito' THEN '07'
                    WHEN 'nota_debito'  THEN '08'
                    ELSE COALESCE(NULLIF(TRIM(codigo_tipo_documento),''), '03')
                END,
                fecha_emision = COALESCE(ce.fecha_emision, DATE(ce.created_at))
            WHERE TRUE
        $q$, s);

        -- ---- SERIES_COMPROBANTES ----
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS tipo_documento_id      INTEGER', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS codigo_tipo_documento  VARCHAR(2)', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS descripcion            VARCHAR(100)', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS anexo_establecimiento  VARCHAR(4) DEFAULT ''0000''', s);
        EXECUTE format('ALTER TABLE %I.series_comprobantes ADD COLUMN IF NOT EXISTS activo                 BOOLEAN DEFAULT TRUE', s);

        EXECUTE format($q$
            UPDATE %I.series_comprobantes
            SET codigo_tipo_documento = CASE COALESCE(tipo,'')
                    WHEN 'factura'      THEN '01'
                    WHEN 'boleta'       THEN '03'
                    WHEN 'ticket'       THEN '02'
                    ELSE COALESCE(NULLIF(TRIM(codigo_tipo_documento),''), '03')
                END,
                descripcion = CASE COALESCE(tipo,'')
                    WHEN 'factura'      THEN 'Factura'
                    WHEN 'boleta'       THEN 'Boleta'
                    WHEN 'ticket'       THEN 'Nota de venta'
                    WHEN 'nota_credito' THEN 'Nota de credito'
                    ELSE COALESCE(NULLIF(TRIM(descripcion),''), 'Comprobante')
                END,
                anexo_establecimiento = COALESCE(NULLIF(TRIM(anexo_establecimiento),''), '0000'),
                activo = COALESCE(activo, TRUE)
            WHERE TRUE
        $q$, s);

        -- Agregar serie para nota de crédito genérica si no existe
        EXECUTE format($q$
            INSERT INTO %I.series_comprobantes (tipo, serie, ultimo_numero, codigo_tipo_documento, descripcion, anexo_establecimiento, activo)
            VALUES ('nota_credito', 'NC01', 0, '07', 'Nota de credito', '0000', TRUE)
            ON CONFLICT (tipo) DO NOTHING
        $q$, s);

        -- Agregar series diferenciadas por tipo de comprobante origen (NC boleta/factura)
        EXECUTE format($q$
            INSERT INTO %I.series_comprobantes (tipo, serie, ultimo_numero, codigo_tipo_documento, descripcion, anexo_establecimiento, activo)
            VALUES ('nota_credito_boleta', 'BC01', 0, '07', 'Nota de credito boleta', '0000', TRUE)
            ON CONFLICT (tipo) DO NOTHING
        $q$, s);

        EXECUTE format($q$
            INSERT INTO %I.series_comprobantes (tipo, serie, ultimo_numero, codigo_tipo_documento, descripcion, anexo_establecimiento, activo)
            VALUES ('nota_credito_factura', 'FC01', 0, '07', 'Nota de credito factura', '0000', TRUE)
            ON CONFLICT (tipo) DO NOTHING
        $q$, s);

        RAISE NOTICE 'Schema % actualizado.', s;
    END LOOP;
END
$$;
