-- ============================================================
-- ARCHIVO: farmacia/database/migration_05_ubigeo_seed_tarapoto.sql
-- DESCRIPCION: Paso 5 - catalogo base de San Martin / Tarapoto.
-- REFERENCIA: Datos tomados de ventas-up2026.
-- USO:
--   psql -U postgres -d farmacia -f database/migration_05_ubigeo_seed_tarapoto.sql
-- ============================================================

INSERT INTO public.ubigeo_departamentos (codigo, nombre)
VALUES ('22', 'San Martin')
ON CONFLICT (codigo) DO UPDATE
SET nombre = EXCLUDED.nombre;

INSERT INTO public.ubigeo_provincias (codigo, departamento_codigo, nombre)
VALUES
    ('2201', '22', 'Moyobamba'),
    ('2202', '22', 'Bellavista'),
    ('2203', '22', 'El Dorado'),
    ('2204', '22', 'Huallaga'),
    ('2205', '22', 'Lamas'),
    ('2206', '22', 'Mariscal Caceres'),
    ('2207', '22', 'Picota'),
    ('2208', '22', 'Rioja'),
    ('2209', '22', 'San Martin'),
    ('2210', '22', 'Tocache')
ON CONFLICT (codigo) DO UPDATE
SET departamento_codigo = EXCLUDED.departamento_codigo,
    nombre = EXCLUDED.nombre;

INSERT INTO public.ubigeo_distritos (codigo, provincia_codigo, departamento_codigo, nombre)
VALUES
    ('220901', '2209', '22', 'Tarapoto'),
    ('220902', '2209', '22', 'Alberto Leveau'),
    ('220903', '2209', '22', 'Cacatachi'),
    ('220904', '2209', '22', 'Chazuta'),
    ('220905', '2209', '22', 'Chipurana'),
    ('220906', '2209', '22', 'El Porvenir'),
    ('220907', '2209', '22', 'Huimbayoc'),
    ('220908', '2209', '22', 'Juan Guerra'),
    ('220909', '2209', '22', 'La Banda de Shilcayo'),
    ('220910', '2209', '22', 'Morales'),
    ('220911', '2209', '22', 'Papaplaya'),
    ('220912', '2209', '22', 'San Antonio'),
    ('220913', '2209', '22', 'Sauce'),
    ('220914', '2209', '22', 'Shapaja')
ON CONFLICT (codigo) DO UPDATE
SET provincia_codigo = EXCLUDED.provincia_codigo,
    departamento_codigo = EXCLUDED.departamento_codigo,
    nombre = EXCLUDED.nombre;
