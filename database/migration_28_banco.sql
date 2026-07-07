-- migration_28_banco.sql
-- Tablas para el módulo de cuentas bancarias (nivel tenant, en public schema).

CREATE TABLE IF NOT EXISTS public.cuentas_banco (
    id             SERIAL PRIMARY KEY,
    tenant_id      INTEGER NOT NULL REFERENCES public.tenants(id) ON DELETE CASCADE,
    nombre_cuenta  VARCHAR(150) NOT NULL,
    banco          VARCHAR(100),
    tipo_cuenta    VARCHAR(50)  NOT NULL DEFAULT 'ahorros',
    numero_cuenta  VARCHAR(50),
    saldo          DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    activo         BOOLEAN DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.banco_movimientos (
    id                   SERIAL PRIMARY KEY,
    cuenta_banco_id      INTEGER NOT NULL REFERENCES public.cuentas_banco(id) ON DELETE CASCADE,
    sucursal_id          INTEGER REFERENCES public.sucursales(id),
    fecha                DATE    NOT NULL DEFAULT CURRENT_DATE,
    beneficiario         VARCHAR(255),
    numero_comprobante   VARCHAR(50),
    tipo                 VARCHAR(20) NOT NULL DEFAULT 'ingreso', -- ingreso | egreso
    concepto             TEXT,
    monto                DECIMAL(18,2) NOT NULL DEFAULT 0,
    referencia_venta_id  INTEGER,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_banco_mov_cuenta ON public.banco_movimientos (cuenta_banco_id);
CREATE INDEX IF NOT EXISTS idx_banco_mov_fecha  ON public.banco_movimientos (fecha);
