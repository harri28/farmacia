-- ============================================================
-- ARCHIVO: farmacia/database/schema_sucursal.sql
-- DESCRIPCIÓN: Tablas por sucursal. Se ejecutan dentro del
--              schema de cada sucursal (search_path ya aplicado).
-- EJECUTAR para una nueva sucursal:
--   SET search_path = nombre_schema;
--   \i database/schema_sucursal.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS categorias (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS productos (
    id               SERIAL PRIMARY KEY,
    codigo           VARCHAR(50)    UNIQUE NOT NULL,
    nombre           VARCHAR(200)   NOT NULL,
    descripcion      TEXT,
    categoria_id     INTEGER        REFERENCES categorias(id),
    precio_compra    DECIMAL(10,3)  DEFAULT 0,
    precio_venta     DECIMAL(10,2)  NOT NULL,
    stock            DECIMAL(10,2)  DEFAULT 0,
    stock_minimo     DECIMAL(10,2)  DEFAULT 5,
    unidad           VARCHAR(50)    DEFAULT 'unidad',
    laboratorio      VARCHAR(100),
    presentacion     VARCHAR(100),
    requiere_receta  BOOLEAN        DEFAULT FALSE,
    activo           BOOLEAN        DEFAULT TRUE,
    favorito         BOOLEAN        DEFAULT FALSE,
    total_vendido    INTEGER        DEFAULT 0,
    created_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
);

-- Catálogo reutilizable de unidades de medida para "Precios por unidad de
-- medida" (ej. CAJA, BLISTER, PAQUETE). Extensible desde el formulario de
-- producto ("+ Nueva unidad").
CREATE TABLE IF NOT EXISTS unidades_medida_venta (
    id         SERIAL PRIMARY KEY,
    nombre     VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO unidades_medida_venta (nombre) VALUES ('CAJA'), ('BLISTER'), ('PAQUETE')
    ON CONFLICT (nombre) DO NOTHING;

-- Precios adicionales por unidad de medida, por producto (ej. un producto
-- base vendido por "unidad" puede tener ademas un precio configurado para
-- venderse por BLISTER de 10, otro por CAJA de 100, etc). El producto sigue
-- siendo la unidad base (su propio precio_venta/stock no cambian).
CREATE TABLE IF NOT EXISTS producto_precios_unidad (
    id             SERIAL PRIMARY KEY,
    producto_id    INTEGER        NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    unidad_medida  VARCHAR(50)    NOT NULL,
    abreviacion    VARCHAR(20),
    cantidad       INTEGER        NOT NULL,
    precio_venta   DECIMAL(10,2)  NOT NULL,
    created_at     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_producto_precios_unidad_producto ON producto_precios_unidad(producto_id);

CREATE TABLE IF NOT EXISTS clientes (
    id         SERIAL PRIMARY KEY,
    nombres    VARCHAR(150) NOT NULL,
    apellidos  VARCHAR(150),
    dni        VARCHAR(20)  UNIQUE,
    ruc        VARCHAR(20),
    telefono   VARCHAR(20),
    email      VARCHAR(100),
    direccion  TEXT,
    activo     BOOLEAN   DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cajas (
    id               SERIAL PRIMARY KEY,
    nombre           VARCHAR(100)  DEFAULT 'Caja Principal',
    saldo_inicial    DECIMAL(10,2) DEFAULT 0,
    saldo_actual     DECIMAL(10,2) DEFAULT 0,
    estado           VARCHAR(20)   DEFAULT 'cerrada',  -- abierta | cerrada
    apertura_at      TIMESTAMP,
    cierre_at        TIMESTAMP,
    usuario_apertura VARCHAR(100),
    usuario_id       INTEGER,   -- referencia a public.usuarios (sin FK cross-schema)
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ventas (
    id               SERIAL PRIMARY KEY,
    numero_venta     VARCHAR(20)   UNIQUE NOT NULL,
    cliente_id       INTEGER       REFERENCES clientes(id),
    caja_id          INTEGER       REFERENCES cajas(id),
    usuario_id       INTEGER,      -- referencia a public.usuarios
    subtotal         DECIMAL(10,2) NOT NULL DEFAULT 0,
    descuento        DECIMAL(10,2) DEFAULT 0,
    igv              DECIMAL(10,2) DEFAULT 0,
    total            DECIMAL(10,2) NOT NULL,
    tipo_pago        VARCHAR(30)   DEFAULT 'efectivo',
    tipo_comprobante VARCHAR(20)   DEFAULT 'boleta',
    estado           VARCHAR(20)   DEFAULT 'completada',
    observaciones    TEXT,
    vendedor         VARCHAR(100)  DEFAULT 'Administrador',
    created_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS venta_detalles (
    id                     SERIAL PRIMARY KEY,
    venta_id               INTEGER       REFERENCES ventas(id) ON DELETE CASCADE,
    producto_id            INTEGER       REFERENCES productos(id),
    cantidad               DECIMAL(10,2) NOT NULL,
    precio_unitario        DECIMAL(10,2) NOT NULL,
    descuento              DECIMAL(10,2) DEFAULT 0,
    subtotal               DECIMAL(10,2) NOT NULL,
    unidad_medida_vendida  VARCHAR(50),        -- NULL = unidad base del producto
    factor_equivalencia    DECIMAL(10,2) DEFAULT 1,  -- unidades base que representa 1 "cantidad" de esta linea
    created_at             TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS proveedores (
    id               SERIAL PRIMARY KEY,
    ruc              VARCHAR(20)  UNIQUE,
    razon_social     VARCHAR(200) NOT NULL,
    nombre_comercial VARCHAR(200),
    telefono         VARCHAR(20),
    email            VARCHAR(100),
    direccion        TEXT,
    contacto_nombre  VARCHAR(150),
    activo           BOOLEAN   DEFAULT TRUE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ingresos (
    id              SERIAL PRIMARY KEY,
    numero_ingreso  VARCHAR(20)   UNIQUE NOT NULL,
    proveedor_id    INTEGER       REFERENCES proveedores(id),
    usuario_id      INTEGER,      -- referencia a public.usuarios
    total           DECIMAL(10,2) DEFAULT 0,
    estado          VARCHAR(20)   DEFAULT 'completado',
    observaciones   TEXT,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ingreso_detalles (
    id              SERIAL PRIMARY KEY,
    ingreso_id      INTEGER       REFERENCES ingresos(id) ON DELETE CASCADE,
    producto_id     INTEGER       REFERENCES productos(id),
    cantidad        DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS salidas (
    id              SERIAL PRIMARY KEY,
    numero_salida   VARCHAR(20)   UNIQUE NOT NULL,
    motivo          VARCHAR(20)   NOT NULL DEFAULT 'otro',   -- merma | vencimiento | devolucion | otro
    usuario         VARCHAR(100),
    usuario_id      INTEGER,      -- referencia a public.usuarios
    total           DECIMAL(10,2) DEFAULT 0,
    estado          VARCHAR(20)   DEFAULT 'completado',      -- completado | anulado
    observaciones   TEXT,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS salida_detalles (
    id              SERIAL PRIMARY KEY,
    salida_id       INTEGER       REFERENCES salidas(id) ON DELETE CASCADE,
    producto_id     INTEGER       REFERENCES productos(id),
    cantidad        DECIMAL(10,2) NOT NULL,
    costo_unitario  DECIMAL(10,2) NOT NULL DEFAULT 0,
    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS caja_movimientos (
    id         SERIAL PRIMARY KEY,
    caja_id    INTEGER       REFERENCES cajas(id),
    tipo       VARCHAR(20)   NOT NULL,   -- ingreso | egreso
    concepto   VARCHAR(200),
    monto      DECIMAL(10,2) NOT NULL,
    usuario    VARCHAR(100),
    usuario_id INTEGER,      -- referencia a public.usuarios
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comprobantes_electronicos (
    id                    SERIAL PRIMARY KEY,
    venta_id              INTEGER     REFERENCES ventas(id),
    tipo                  VARCHAR(20) NOT NULL,   -- boleta | factura
    serie                 VARCHAR(10) NOT NULL,
    numero                INTEGER     NOT NULL,
    numero_completo       VARCHAR(20) NOT NULL,
    estado_sunat          VARCHAR(100),
    enlace_del_pdf        TEXT,
    enlace_del_xml        TEXT,
    enlace_del_cdr        TEXT,
    cadena_para_codigo_qr TEXT,
    nubefact_response     TEXT,
    referencia_comprobante_id INTEGER REFERENCES comprobantes_electronicos(id),
    tipo_nota_credito_id  INTEGER REFERENCES public.fe_tipos_nota_credito(id),
    codigo_tipo_nota_credito VARCHAR(2),
    motivo_nota_credito   TEXT,
    descripcion_nota_credito TEXT,
    documento_modificado_tipo_documento_codigo VARCHAR(2),
    documento_modificado_serie VARCHAR(10),
    documento_modificado_numero VARCHAR(20),
    documento_modificado_numero_completo VARCHAR(30),
    documento_modificado_fecha DATE,
    created_at            TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS series_comprobantes (
    tipo            VARCHAR(20)  PRIMARY KEY,  -- boleta | factura
    serie           VARCHAR(10)  NOT NULL,
    ultimo_numero   INTEGER      DEFAULT 0
);

CREATE TABLE IF NOT EXISTS ordenes_compra (
    id              SERIAL PRIMARY KEY,
    numero_orden    VARCHAR(20)   UNIQUE NOT NULL,
    proveedor_id    INTEGER       REFERENCES proveedores(id),
    usuario_id      INTEGER,
    estado          VARCHAR(20)   DEFAULT 'borrador',  -- borrador | pendiente | aprobada | recibida | cancelada
    tipo_pago       VARCHAR(30)   DEFAULT 'efectivo',  -- efectivo | credito | transferencia
    dias_credito    INTEGER       DEFAULT 0,
    subtotal        DECIMAL(10,2) DEFAULT 0,
    igv             DECIMAL(10,2) DEFAULT 0,
    total           DECIMAL(10,2) DEFAULT 0,
    observaciones   TEXT,
    fecha_entrega   DATE,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orden_compra_detalles (
    id              SERIAL PRIMARY KEY,
    orden_id        INTEGER       NOT NULL REFERENCES ordenes_compra(id) ON DELETE CASCADE,
    producto_id     INTEGER       REFERENCES productos(id),
    descripcion     VARCHAR(200),
    cantidad        DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS cuentas_por_pagar (
    id                SERIAL PRIMARY KEY,
    proveedor_id      INTEGER       REFERENCES proveedores(id),
    ingreso_id        INTEGER       REFERENCES ingresos(id),
    orden_compra_id   INTEGER       REFERENCES ordenes_compra(id),
    numero_doc        VARCHAR(50),
    monto_total       DECIMAL(10,2) NOT NULL,
    monto_pagado      DECIMAL(10,2) DEFAULT 0,
    monto_pendiente   DECIMAL(10,2) NOT NULL,
    estado            VARCHAR(20)   DEFAULT 'pendiente',  -- pendiente | parcial | pagado | vencido
    fecha_vencimiento DATE,
    created_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pagos_proveedor (
    id          SERIAL PRIMARY KEY,
    cuenta_id   INTEGER       NOT NULL REFERENCES cuentas_por_pagar(id),
    monto       DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(30)   DEFAULT 'efectivo',
    referencia  VARCHAR(100),
    usuario_id  INTEGER,
    notas       TEXT,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE ingresos ADD COLUMN IF NOT EXISTS tipo_pago       VARCHAR(30) DEFAULT 'efectivo';
ALTER TABLE ingresos ADD COLUMN IF NOT EXISTS orden_compra_id INTEGER REFERENCES ordenes_compra(id);

CREATE TABLE IF NOT EXISTS gastos (
    id                 SERIAL PRIMARY KEY,
    caja_id            INTEGER       REFERENCES cajas(id),
    descripcion        VARCHAR(200)  NOT NULL,
    proveedor          VARCHAR(150),
    numero_comprobante VARCHAR(50),
    monto              DECIMAL(10,2) NOT NULL,
    metodo_pago        VARCHAR(30)   DEFAULT 'efectivo',
    usuario_id         INTEGER,
    created_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Categorías iniciales en la tabla local de la sucursal
INSERT INTO categorias (nombre, descripcion) VALUES
    ('Medicamentos',          'Fármacos y medicamentos en general'),
    ('Vitaminas y Suplementos','Vitaminas, minerales y suplementos nutricionales'),
    ('Cuidado Personal',      'Productos de higiene y cuidado personal'),
    ('Primeros Auxilios',     'Materiales de curación y primeros auxilios'),
    ('Bebés y Niños',         'Productos para bebés y niños'),
    ('Genéricos',             'Medicamentos genéricos')
ON CONFLICT (nombre) DO NOTHING;

INSERT INTO clientes (nombres, apellidos, dni, telefono)
VALUES ('Cliente', 'General', '00000000', '000000000')
ON CONFLICT DO NOTHING;

INSERT INTO series_comprobantes (tipo, serie, ultimo_numero) VALUES
    ('boleta',               'B001', 0),
    ('factura',              'F001', 0),
    ('nota_credito',         'NC01', 0),
    ('nota_credito_boleta',  'BC01', 0),
    ('nota_credito_factura', 'FC01', 0)
ON CONFLICT DO NOTHING;
