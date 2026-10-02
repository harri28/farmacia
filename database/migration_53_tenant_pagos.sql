-- Migración 53: historial de pagos del plan por tenant
-- Superadmin pulsa "Marcar como pagado" en la pantalla de la empresa: se
-- registra el pago aquí y se corre plan_vence_at a la nueva fecha. El banner
-- de aviso (migration_50/52) desaparece solo y vuelve a salir 7 días antes
-- del siguiente vencimiento.
-- Requiere migration_50_plan_vence_at.sql.
--
-- IMPORTANTE (despliegue): aplicar esta migración ANTES de desplegar el
-- código; sin ella, "Marcar como pagado" falla y el historial no carga.

CREATE TABLE IF NOT EXISTS public.tenant_pagos (
    id               SERIAL PRIMARY KEY,
    tenant_id        INTEGER NOT NULL REFERENCES public.tenants(id) ON DELETE CASCADE,
    fecha_pago       DATE NOT NULL DEFAULT CURRENT_DATE,
    monto            NUMERIC(10,2),
    vence_anterior   DATE,
    vence_nuevo      DATE NOT NULL,
    registrado_por   VARCHAR(100),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_tenant_pagos_tenant ON public.tenant_pagos (tenant_id, created_at DESC);
