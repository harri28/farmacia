-- ============================================================
-- MIGRACIÓN 17: Tabla de auditoría del sistema
-- Ejecutar: psql -U postgres -d farmacia -f migration_17_audit_log.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS public.audit_log (
    id             BIGSERIAL    PRIMARY KEY,
    tenant_id      INTEGER,
    sucursal_id    INTEGER,
    usuario_id     INTEGER,
    username       VARCHAR(100),
    nombre_usuario VARCHAR(200),
    accion         VARCHAR(100) NOT NULL,
    modulo         VARCHAR(100) DEFAULT '',
    detalle        TEXT         DEFAULT '',
    ip_address     VARCHAR(45),
    created_at     TIMESTAMPTZ  DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_audit_log_tenant_created
    ON public.audit_log(tenant_id, created_at DESC);

CREATE INDEX IF NOT EXISTS idx_audit_log_created
    ON public.audit_log(created_at DESC);
