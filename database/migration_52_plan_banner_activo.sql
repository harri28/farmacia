-- Migración 52: interruptor del aviso de vencimiento del plan, por tenant
-- Permite a Superadmin decidir, empresa por empresa, si el cliente ve el
-- banner de "tu plan vence..." (algunos clientes pagan el plan por un año
-- y no deben verlo). Queda APAGADO por defecto: ninguna empresa lo ve hasta
-- que Superadmin lo active. La fecha plan_vence_at se conserva igual.
-- Requiere migration_50_plan_vence_at.sql.
--
-- IMPORTANTE (despliegue): aplicar esta migración ANTES de desplegar el
-- código; si no, la consulta del header falla y el banner no se muestra.

ALTER TABLE public.tenants ADD COLUMN IF NOT EXISTS plan_banner_activo BOOLEAN NOT NULL DEFAULT FALSE;
