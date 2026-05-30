-- ============================================================
-- MIGRACIÓN: Módulo de Gastos Operativos
-- Ejecutar: http://localhost/farmacia/database/migration_gastos.php
-- ============================================================

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
