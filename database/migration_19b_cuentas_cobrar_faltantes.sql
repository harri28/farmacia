-- migration_19b: Crea cuentas_por_cobrar y cobros_cliente en esquemas faltantes

DO $$
DECLARE r RECORD;
BEGIN
    FOR r IN
        SELECT s.schemaname
        FROM (SELECT DISTINCT schemaname FROM pg_tables WHERE tablename = 'clientes' AND schemaname != 'public') s
        LEFT JOIN (SELECT DISTINCT schemaname FROM pg_tables WHERE tablename = 'cuentas_por_cobrar') c
               ON c.schemaname = s.schemaname
        WHERE c.schemaname IS NULL
        ORDER BY s.schemaname
    LOOP
        EXECUTE format($sql$
            CREATE TABLE IF NOT EXISTS %I.cuentas_por_cobrar (
                id                SERIAL PRIMARY KEY,
                cliente_id        INTEGER       REFERENCES %I.clientes(id),
                venta_id          INTEGER       REFERENCES %I.ventas(id),
                numero_doc        VARCHAR(50),
                monto_total       DECIMAL(10,2) NOT NULL,
                monto_pagado      DECIMAL(10,2) DEFAULT 0,
                monto_pendiente   DECIMAL(10,2) NOT NULL,
                estado            VARCHAR(20)   DEFAULT 'pendiente',
                fecha_vencimiento DATE,
                notas             TEXT,
                created_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
            )
        $sql$, r.schemaname, r.schemaname, r.schemaname);

        EXECUTE format($sql$
            CREATE TABLE IF NOT EXISTS %I.cobros_cliente (
                id          SERIAL PRIMARY KEY,
                cuenta_id   INTEGER       NOT NULL REFERENCES %I.cuentas_por_cobrar(id),
                monto       DECIMAL(10,2) NOT NULL,
                metodo_pago VARCHAR(30)   DEFAULT 'efectivo',
                referencia  VARCHAR(100),
                usuario_id  INTEGER,
                notas       TEXT,
                created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
            )
        $sql$, r.schemaname, r.schemaname);

        RAISE NOTICE 'Creado en esquema: %', r.schemaname;
    END LOOP;
END;
$$;
