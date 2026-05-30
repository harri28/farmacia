-- ============================================================
-- MIGRACIÓN: Módulo de Compras
-- Ejecutar en cada schema de sucursal existente:
--   SET search_path TO nombre_schema, public;
--   \i database/migration_compras.sql
-- O desde setup: http://localhost/farmacia/database/migration_compras.php
-- ============================================================

CREATE TABLE IF NOT EXISTS ordenes_compra (
    id              SERIAL PRIMARY KEY,
    numero_orden    VARCHAR(20)   UNIQUE NOT NULL,
    proveedor_id    INTEGER       REFERENCES proveedores(id),
    usuario_id      INTEGER,
    estado          VARCHAR(20)   DEFAULT 'borrador',
    tipo_pago       VARCHAR(30)   DEFAULT 'efectivo',
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
    cantidad        INTEGER       NOT NULL,
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
    estado            VARCHAR(20)   DEFAULT 'pendiente',
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
