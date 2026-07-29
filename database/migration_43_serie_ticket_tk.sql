-- Migración 43: unifica el código de serie de Ticket/Nota de venta a 'TK'
-- (antes 'NV01', sembrado por migration_09; algunas sucursales ni siquiera
-- tenían la fila tipo='ticket' porque el INSERT de migration_09 dependía
-- de un JOIN contra public.fe_tipos_documento que pudo no matchear en
-- el momento en que corrió). Conserva ultimo_numero donde ya existía la
-- fila, para no resetear el correlativo real ya emitido.
--
-- El número completo de ticket se arma en modules/ventas/api.php como
-- serie + correlativo de 4 dígitos, SIN guión (ej. TK0001) — a diferencia
-- de boleta/factura que sí usan guión + 8 dígitos (ej. B001-00000001),
-- formato exigido por SUNAT y que no aplica a tickets (no son documentos
-- electrónicos).

DO $$
DECLARE
    s RECORD;
BEGIN
    FOR s IN
        SELECT schema_name FROM information_schema.schemata
        WHERE schema_name NOT IN ('public', 'information_schema')
          AND schema_name NOT LIKE 'pg_%'
    LOOP
        IF EXISTS (
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = s.schema_name AND table_name = 'series_comprobantes'
        ) THEN
            EXECUTE format('UPDATE %I.series_comprobantes SET serie = ''TK'' WHERE tipo = ''ticket''', s.schema_name);

            EXECUTE format(
                'INSERT INTO %I.series_comprobantes (tipo, serie, ultimo_numero, codigo_tipo_documento, descripcion, anexo_establecimiento, activo)
                 SELECT ''ticket'', ''TK'', 0, ''02'', ''Nota de venta'', ''0000'', TRUE
                 WHERE NOT EXISTS (SELECT 1 FROM %I.series_comprobantes WHERE tipo = ''ticket'')',
                s.schema_name, s.schema_name
            );
        END IF;
    END LOOP;
END $$;
