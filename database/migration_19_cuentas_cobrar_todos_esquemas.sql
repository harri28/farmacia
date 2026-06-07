-- migration_19: Crea cuentas_por_cobrar y cobros_cliente en todos los esquemas con ordenes_compra

DO $$
DECLARE r RECORD;
BEGIN
    FOR r IN
        SELECT DISTINCT schemaname FROM pg_tables
        WHERE tablename = 'ordenes_compra'
        ORDER BY schemaname
    LOOP
        EXECUTE format('SET search_path TO %I, public', r.schemaname);

        EXECUTE $sql$
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
            )
        $sql$;

        EXECUTE $sql$
            CREATE TABLE IF NOT EXISTS cobros_cliente (
                id          SERIAL PRIMARY KEY,
                cuenta_id   INTEGER       NOT NULL REFERENCES cuentas_por_cobrar(id),
                monto       DECIMAL(10,2) NOT NULL,
                metodo_pago VARCHAR(30)   DEFAULT 'efectivo',
                referencia  VARCHAR(100),
                usuario_id  INTEGER,
                notas       TEXT,
                created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
            )
        $sql$;

        RAISE NOTICE 'Creado en esquema: %', r.schemaname;
    END LOOP;
END;
$$;
