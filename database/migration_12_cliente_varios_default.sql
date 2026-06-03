-- ============================================================
-- ARCHIVO: farmacia/database/migration_12_cliente_varios_default.sql
-- DESCRIPCION: Paso 12 - asegura el cliente base CLIENTES VARIOS
--              para ventas rapidas y boletas de mostrador.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_12_cliente_varios_default.sql
-- ============================================================

DO $$
DECLARE
    suc RECORD;
BEGIN
    FOR suc IN
        SELECT schema_name
        FROM public.sucursales
        WHERE schema_name IS NOT NULL
          AND schema_name <> ''
    LOOP
        EXECUTE format($sql$
            UPDATE %1$I.clientes
            SET nombres = 'CLIENTES',
                apellidos = 'VARIOS',
                dni = '00000000',
                ruc = NULL,
                telefono = NULL,
                email = NULL,
                direccion = COALESCE(direccion, 'MOSTRADOR'),
                activo = TRUE,
                tipo_documento_codigo = '1',
                numero_documento = '00000000',
                nombre_completo = 'CLIENTES VARIOS',
                razon_social = 'CLIENTES VARIOS',
                codigo_pais = COALESCE(NULLIF(TRIM(codigo_pais), ''), 'PE')
            WHERE id = 1
        $sql$, suc.schema_name);

        EXECUTE format($sql$
            UPDATE %1$I.clientes c
            SET tipo_documento_id = td.id
            FROM public.fe_tipos_documento_identidad td
            WHERE c.id = 1
              AND td.codigo = '1'
        $sql$, suc.schema_name);
    END LOOP;
END $$;
