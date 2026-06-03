-- ============================================================
-- ARCHIVO: farmacia/database/migration_08_facturacion_catalogos_seed.sql
-- DESCRIPCION: Paso 8 - datos base de catalogos SUNAT y apoyo
--              para facturacion electronica.
-- REFERENCIA: Datos tomados de ventas-up2026.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_08_facturacion_catalogos_seed.sql
-- ============================================================

INSERT INTO public.fe_tipos_documento (codigo, descripcion, estado) VALUES
    ('01', 'FACTURA ELECTRONICA', TRUE),
    ('03', 'BOLETA DE VENTA ELECTRONICA', TRUE),
    ('07', 'NOTA DE CREDITO ELECTRONICA', TRUE),
    ('08', 'NOTA DE DEBITO ELECTRONICA', TRUE),
    ('09', 'GUIA DE REMISION REMITENTE', TRUE),
    ('02', 'NOTA DE VENTA', TRUE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, estado = EXCLUDED.estado;

INSERT INTO public.fe_tipos_documento_identidad (codigo, descripcion, descripcion_documento, estado) VALUES
    ('1', 'DOCUMENTO NACIONAL DE IDENTIDAD (DNI)', 'DNI', TRUE),
    ('6', 'REGISTRO UNICO DE CONTRIBUYENTES (RUC)', 'RUC', TRUE),
    ('4', 'CARNET DE EXTRANJERIA', 'CARNET DE EXTRANJERIA', TRUE),
    ('0', 'OTRO TIPO DE DOCUMENTO', 'OTROS', TRUE),
    ('7', 'PASAPORTE', 'PASAPORTE', TRUE),
    ('A', 'CEDULA DIPLOMATICA DE IDENTIDAD', 'CEDULA DIPLOMATICA DE IDENTIDAD', TRUE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, descripcion_documento = EXCLUDED.descripcion_documento, estado = EXCLUDED.estado;

INSERT INTO public.fe_formas_pago (descripcion, estado) VALUES
    ('Efectivo', TRUE),
    ('Yape', TRUE),
    ('Plin', TRUE),
    ('Tunki', TRUE),
    ('Tarjeta de credito', TRUE),
    ('Tarjeta de debito', TRUE),
    ('Transferencia', TRUE),
    ('Cheque', TRUE)
ON CONFLICT (descripcion) DO UPDATE SET estado = EXCLUDED.estado;

INSERT INTO public.fe_monedas (codigo, descripcion, pais, simbolo, estado) VALUES
    ('PEN', 'NUEVO SOL', 'PERU', 'S/', TRUE),
    ('USD', 'US DOLLAR', 'ESTADOS UNIDOS (EEUU)', '$', TRUE),
    ('EUR', 'EURO', 'ESPANA', 'EUR', TRUE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, pais = EXCLUDED.pais, simbolo = EXCLUDED.simbolo, estado = EXCLUDED.estado;

INSERT INTO public.fe_tipos_afectacion_igv (codigo, descripcion, tipo, estado) VALUES
    ('10', 'Gravado - Operacion Onerosa', 'GRAV', TRUE),
    ('11', 'Gravado - Retiro por premio', 'GRAV', FALSE),
    ('12', 'Gravado - Retiro por donacion', 'GRAV', FALSE),
    ('13', 'Gravado - Retiro', 'GRAV', FALSE),
    ('14', 'Gravado - Retiro por publicidad', 'GRAV', FALSE),
    ('15', 'Gravado - Bonificaciones', 'GRAV', FALSE),
    ('16', 'Gravado - Retiro por entrega a trabajadores', 'GRAV', FALSE),
    ('17', 'Gravado - IVAP', 'GRAV', FALSE),
    ('20', 'Exonerado - Operacion Onerosa', 'EXO', TRUE),
    ('21', 'Exonerado - Transferencia Gratuita', 'EXO', FALSE),
    ('30', 'Inafecto - Operacion Onerosa', 'INA', TRUE),
    ('31', 'Inafecto - Retiro por Bonificacion', 'INA', FALSE),
    ('32', 'Inafecto - Retiro', 'INA', FALSE),
    ('33', 'Inafecto - Retiro por Muestras Medica', 'INA', FALSE),
    ('34', 'Inafecto - Retiro por Convenio Colectivo', 'INA', FALSE),
    ('35', 'Inafecto - Retiro por premio', 'INA', FALSE),
    ('36', 'Inafecto - Retiro por publicidad', 'INA', FALSE),
    ('40', 'Exportacion', 'EXP', FALSE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, tipo = EXCLUDED.tipo, estado = EXCLUDED.estado;

INSERT INTO public.fe_tipos_nota_credito (codigo, descripcion, estado) VALUES
    ('01', 'ANULACION DE LA OPERACION', TRUE),
    ('02', 'ANULACION POR ERROR EN EL RUC', TRUE),
    ('03', 'CORRECCION POR ERROR EN LA DESCRIPCION', TRUE),
    ('04', 'DESCUENTO GLOBAL', TRUE),
    ('05', 'DESCUENTO POR ITEM', TRUE),
    ('06', 'DEVOLUCION TOTAL', TRUE),
    ('07', 'DEVOLUCION POR ITEM', TRUE),
    ('08', 'BONIFICACION', TRUE),
    ('09', 'DISMINUCION EN EL VALOR', TRUE),
    ('10', 'OTROS CONCEPTOS', TRUE),
    ('11', 'AJUSTES DE OPERACIONES DE EXPORTACION', TRUE),
    ('12', 'AJUSTES AFECTOS AL IVAP', TRUE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, estado = EXCLUDED.estado;

INSERT INTO public.fe_tipos_nota_debito (codigo, descripcion, estado) VALUES
    ('01', 'INTERESES POR MORA', TRUE),
    ('02', 'AUMENTO EN EL VALOR', TRUE),
    ('03', 'PENALIDADES/ OTROS CONCEPTOS', TRUE),
    ('11', 'AJUSTES DE OPERACIONES DE EXPORTACION', TRUE),
    ('12', 'AJUSTES AFECTOS AL IVAP', TRUE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, estado = EXCLUDED.estado;

INSERT INTO public.fe_tipos_operacion (codigo, descripcion, estado) VALUES
    ('0101', 'Venta interna', TRUE),
    ('1001', 'Operacion sujeta a detraccion', TRUE),
    ('1002', 'Operacion sujeta a percepcion', TRUE),
    ('1003', 'Operacion sujeta a retencion', TRUE),
    ('1004', 'Venta con despacho a consumidores finales', TRUE),
    ('2001', 'Exportacion de bienes', TRUE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, estado = EXCLUDED.estado;

INSERT INTO public.fe_unidades (codigo, descripcion, estado) VALUES
    ('4A', 'BOBINAS', TRUE),
    ('BE', 'FARDO', TRUE),
    ('BG', 'BOLSA', TRUE),
    ('BJ', 'BALDE', TRUE),
    ('BLL', 'BARRILES', TRUE),
    ('BO', 'BOTELLAS', TRUE),
    ('BX', 'CAJA', TRUE),
    ('C62', 'PIEZAS', TRUE),
    ('CA', 'LATAS', TRUE),
    ('CEN', 'CIENTOS DE UNIDADES', TRUE),
    ('CJ', 'CONOS', TRUE),
    ('CMK', 'CENTIMETRO CUADRADO', TRUE),
    ('CMQ', 'CENTIMETRO CUBICO', TRUE),
    ('CMT', 'CENTIMETRO LINEAL', TRUE),
    ('CT', 'CARTONES', TRUE),
    ('CY', 'CILINDRO', TRUE),
    ('DR', 'TAMBOR', TRUE),
    ('DZN', 'DOCENA', TRUE),
    ('DZP', 'DOCENA POR 10**6', TRUE),
    ('FOT', 'PIES', TRUE),
    ('FTK', 'PIES CUADRADOS', TRUE),
    ('GLI', 'GALON INGLES (4,545956L)', TRUE),
    ('GLL', 'US GALON (3,7843 L)', TRUE),
    ('GRM', 'GRAMO', TRUE),
    ('GRO', 'GRUESA', TRUE),
    ('HLT', 'HECTOLITRO', TRUE),
    ('INH', 'PULGADAS', TRUE),
    ('KGM', 'KILOGRAMO', TRUE),
    ('KT', 'KIT', TRUE),
    ('KTM', 'KILOMETRO', TRUE),
    ('KWH', 'KILOVATIO HORA', TRUE),
    ('LBR', 'LIBRAS', TRUE),
    ('LEF', 'HOJA', TRUE),
    ('LTN', 'TONELADA LARGA', TRUE),
    ('LTR', 'LITRO', TRUE),
    ('MGM', 'MILIGRAMOS', TRUE),
    ('MLL', 'MILLARES', TRUE),
    ('MLT', 'MILILITRO', TRUE),
    ('MMK', 'MILIMETRO CUADRADO', TRUE),
    ('MMQ', 'MILIMETRO CUBICO', TRUE),
    ('MMT', 'MILIMETRO', TRUE),
    ('MTK', 'METRO CUADRADO', TRUE),
    ('MTQ', 'METRO CUBICO', TRUE),
    ('MTR', 'METRO', TRUE),
    ('MWH', 'MEGAWATT HORA', TRUE),
    ('NIU', 'UNIDAD (BIENES)', TRUE),
    ('ONZ', 'ONZAS', TRUE),
    ('PF', 'PALETAS', TRUE),
    ('PG', 'PLACAS', TRUE),
    ('PK', 'PAQUETE', TRUE),
    ('PR', 'PAR', TRUE),
    ('RM', 'RESMA', TRUE),
    ('SET', 'JUEGO', TRUE),
    ('ST', 'PLIEGO', TRUE),
    ('STN', 'TONELADA CORTA', TRUE),
    ('TNE', 'TONELADAS', TRUE),
    ('TU', 'TUBOS', TRUE),
    ('UM', 'MILLON DE UNIDADES', TRUE),
    ('YDK', 'YARDA CUADRADA', TRUE),
    ('YRD', 'YARDA', TRUE),
    ('ZZ', 'UNIDAD (SERVICIOS)', TRUE)
ON CONFLICT (codigo) DO UPDATE SET descripcion = EXCLUDED.descripcion, estado = EXCLUDED.estado;

