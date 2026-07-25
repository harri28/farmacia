-- migration_38_audit_log_rol.sql
-- registrarAuditoria() (config/database.php) intenta insertar una
-- columna "rol" en public.audit_log que nunca existio en la tabla
-- (migration_17_audit_log.sql no la incluye). Como registrarAuditoria()
-- atrapa su propia excepcion, el error queda oculto -- pero si se
-- llama dentro de una transaccion explicita (ej. toma_crear/
-- toma_aplicar en modules/inventario/api.php), el INSERT fallido deja
-- la transaccion en estado abortado, y el COMMIT posterior termina
-- haciendo un rollback silencioso de todo (incluida la sesion de
-- Toma de Inventario recien creada), aunque la API responda 200/exito.
--
-- USO:
--   psql -U postgres -d farmacia -f database/migration_38_audit_log_rol.sql

ALTER TABLE public.audit_log ADD COLUMN IF NOT EXISTS rol VARCHAR(30);
