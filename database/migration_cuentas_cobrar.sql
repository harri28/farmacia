-- ============================================================
-- MIGRACIÓN: Cuentas por Cobrar
-- Ejecutar: http://localhost/farmacia/database/migration_cuentas_cobrar.php
-- ============================================================

CREATE TABLE IF NOT EXISTS cuentas_por_cobrar (
    id                SERIAL PRIMARY KEY,
    cliente_id        INTEGER       REFERENCES clientes(id),
    venta_id          INTEGER       REFERENCES ventas(id),
    numero_doc        VARCHAR(50),
    monto_total       DECIMAL(10,2) NOT NULL,
    monto_pagado      DECIMAL(10,2) DEFAULT 0,
    monto_pendiente   DECIMAL(10,2) NOT NULL,
    estado            VARCHAR(20)   DEFAULT 'pendiente',
    fecha_vencimiento DATE,
    notas             TEXT,
    created_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cobros_cliente (
    id          SERIAL PRIMARY KEY,
    cuenta_id   INTEGER       NOT NULL REFERENCES cuentas_por_cobrar(id),
    monto       DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(30)   DEFAULT 'efectivo',
    referencia  VARCHAR(100),
    usuario_id  INTEGER,
    notas       TEXT,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);
