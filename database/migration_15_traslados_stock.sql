-- ============================================================
-- MIGRACIÓN: Órdenes de traslado entre sucursales
-- Ejecutar en la base global (schema public):
--   psql -U postgres -d farmacia -f database/migration_15_traslados_stock.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS public.ordenes_traslado (
    id                    SERIAL PRIMARY KEY,
    tenant_id             INTEGER NOT NULL REFERENCES public.tenants(id) ON DELETE CASCADE,
    numero_traslado       VARCHAR(30) NOT NULL,
    sucursal_origen_id    INTEGER NOT NULL REFERENCES public.sucursales(id) ON DELETE RESTRICT,
    sucursal_destino_id   INTEGER NOT NULL REFERENCES public.sucursales(id) ON DELETE RESTRICT,
    estado                VARCHAR(20) NOT NULL DEFAULT 'borrador', -- borrador | enviado | recibido | anulado
    observaciones         TEXT,
    usuario_solicita_id   INTEGER REFERENCES public.usuarios(id) ON DELETE SET NULL,
    usuario_envia_id      INTEGER REFERENCES public.usuarios(id) ON DELETE SET NULL,
    usuario_recibe_id     INTEGER REFERENCES public.usuarios(id) ON DELETE SET NULL,
    fecha_solicitud       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_envio           TIMESTAMP,
    fecha_recepcion       TIMESTAMP,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_ordenes_traslado_estado
        CHECK (estado IN ('borrador', 'enviado', 'recibido', 'anulado')),
    CONSTRAINT chk_ordenes_traslado_sucursales_distintas
        CHECK (sucursal_origen_id <> sucursal_destino_id)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_ordenes_traslado_tenant_numero
    ON public.ordenes_traslado (tenant_id, numero_traslado);

CREATE INDEX IF NOT EXISTS idx_ordenes_traslado_origen
    ON public.ordenes_traslado (sucursal_origen_id, estado, created_at DESC);

CREATE INDEX IF NOT EXISTS idx_ordenes_traslado_destino
    ON public.ordenes_traslado (sucursal_destino_id, estado, created_at DESC);

CREATE INDEX IF NOT EXISTS idx_ordenes_traslado_tenant
    ON public.ordenes_traslado (tenant_id, created_at DESC);

CREATE TABLE IF NOT EXISTS public.orden_traslado_detalles (
    id                      SERIAL PRIMARY KEY,
    traslado_id             INTEGER NOT NULL REFERENCES public.ordenes_traslado(id) ON DELETE CASCADE,
    producto_codigo         VARCHAR(50) NOT NULL,
    producto_nombre         VARCHAR(200) NOT NULL,
    cantidad                INTEGER NOT NULL,
    costo_unitario          DECIMAL(10,2) DEFAULT 0,
    stock_origen_snapshot   INTEGER DEFAULT 0,
    stock_destino_snapshot  INTEGER DEFAULT 0,
    observaciones           TEXT,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_orden_traslado_detalles_cantidad
        CHECK (cantidad > 0),
    CONSTRAINT chk_orden_traslado_detalles_stock_origen
        CHECK (stock_origen_snapshot >= 0),
    CONSTRAINT chk_orden_traslado_detalles_stock_destino
        CHECK (stock_destino_snapshot >= 0)
);

CREATE INDEX IF NOT EXISTS idx_orden_traslado_detalles_traslado
    ON public.orden_traslado_detalles (traslado_id);

CREATE INDEX IF NOT EXISTS idx_orden_traslado_detalles_codigo
    ON public.orden_traslado_detalles (producto_codigo);
