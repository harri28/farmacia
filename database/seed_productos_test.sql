-- Seed: 30 productos de farmacia para testing en Generic Pharma
-- Aplica a las 3 sucursales activas

DO $$
DECLARE
  schemas TEXT[] := ARRAY[
    'generic_pharma_jr_lima_tambo_408',
    'generic_pharma_alonso_de_alvarado_2',
    'generic_pharma_jr_amorarca_129_mora'
  ];
  s TEXT;
BEGIN
  FOREACH s IN ARRAY schemas LOOP

    -- Medicamentos (cat 1)
    EXECUTE format('INSERT INTO %I.productos (codigo,nombre,descripcion,categoria_id,precio_compra,precio_venta,stock,stock_minimo,unidad,laboratorio,presentacion,requiere_receta,activo) VALUES
      (''MED001'',''Paracetamol 500mg'',         ''Analgésico y antipirético'',             1, 0.80,  1.80, 120,20,''unidad'',''Genfarma'',  ''Tab x 100'',false,true),
      (''MED002'',''Ibuprofeno 400mg'',           ''Antiinflamatorio no esteroideo'',        1, 1.20,  2.50,  80,15,''unidad'',''Medrock'',   ''Tab x 100'',false,true),
      (''MED003'',''Amoxicilina 500mg'',          ''Antibiótico de amplio espectro'',        1, 3.50,  7.00,  60,10,''caja'',  ''Lafrancol'', ''Cap x 12'', true, true),
      (''MED004'',''Omeprazol 20mg'',             ''Inhibidor de bomba de protones'',        1, 1.50,  3.20,  90,15,''caja'',  ''Genfarma'',  ''Cap x 14'', false,true),
      (''MED005'',''Loratadina 10mg'',            ''Antihistamínico'',                       1, 0.90,  2.00,  75,10,''unidad'',''Genfar'',    ''Tab x 10'', false,true),
      (''MED006'',''Metformina 850mg'',           ''Antidiabético oral'',                    1, 1.80,  3.80,  45,10,''unidad'',''Medrock'',   ''Tab x 30'', true, true),
      (''MED007'',''Atorvastatina 20mg'',         ''Reductor de colesterol'',                1, 4.20,  9.00,  40, 8,''caja'',  ''Pfizer'',    ''Tab x 14'', true, true),
      (''MED008'',''Diclofenaco 50mg'',           ''Antiinflamatorio y analgésico'',         1, 1.10,  2.40,  65,12,''unidad'',''Genfarma'',  ''Tab x 10'', false,true),
      (''MED009'',''Azitromicina 500mg'',         ''Antibiótico macrólido'',                 1, 5.00, 10.50,  35, 8,''caja'',  ''Lafrancol'', ''Tab x 3'',  true, true),
      (''MED010'',''Ciprofloxacino 500mg'',       ''Antibiótico quinolona'',                 1, 2.80,  5.80,  50,10,''caja'',  ''Genfar'',    ''Tab x 10'', true, true),
      -- Vitaminas y Suplementos (cat 2)
      (''VIT001'',''Vitamina C 1000mg'',          ''Ácido ascórbico efervescente'',          2, 2.50,  5.50, 100,15,''unidad'',''Bayer'',      ''Tab Ef x 10'',false,true),
      (''VIT002'',''Complejo B'',                 ''Vitaminas del grupo B'',                 2, 3.20,  7.00,  80,12,''frasco'',''Genfarma'',  ''Tab x 60'', false,true),
      (''VIT003'',''Vitamina D3 2000 UI'',        ''Colecalciferol para huesos'',            2, 4.50,  9.50,  55,10,''frasco'',''Nature Made'',''Cap x 30'', false,true),
      (''VIT004'',''Omega 3 1000mg'',             ''Ácidos grasos esenciales'',              2, 6.00, 13.00,  45, 8,''frasco'',''Omegavit'',  ''Cap x 30'', false,true),
      (''VIT005'',''Calcio + Vitamina D'',        ''Suplemento óseo'',                       2, 5.50, 11.00,  60,10,''frasco'',''Centrum'',   ''Tab x 60'', false,true),
      -- Cuidado Personal (cat 3)
      (''CUI001'',''Alcohol 70° 500ml'',          ''Antiséptico para piel'',                 3, 3.00,  6.00,  70,15,''frasco'',''Clorox'',    ''500 ml'',   false,true),
      (''CUI002'',''Agua Oxigenada 10vol'',       ''Antiséptico y desinfectante'',           3, 1.50,  3.20,  85,20,''frasco'',''Farmacias'', ''120 ml'',   false,true),
      (''CUI003'',''Crema Hidratante Corporal'',  ''Hidratación profunda piel seca'',        3, 7.00, 15.00,  40, 8,''tubo'',  ''Eucerin'',   ''250 ml'',   false,true),
      (''CUI004'',''Jabón Antibacterial'',        ''Limpieza y protección bacteriana'',      3, 2.80,  5.50,  60,10,''unidad'',''Palmolive'', ''110 g'',    false,true),
      (''CUI005'',''Protector Solar SPF 50'',     ''Fotoprotección UVA/UVB'',               3,12.00, 25.00,  25, 5,''tubo'',  ''Isdin'',     ''50 ml'',    false,true),
      -- Primeros Auxilios (cat 4)
      (''PAU001'',''Gasa Estéril 10x10cm'',       ''Apósito para heridas'',                  4, 0.60,  1.50, 150,30,''unidad'',''Curaplex'',  ''x 10 unid'',false,true),
      (''PAU002'',''Esparadrapo 5cm'',            ''Fijación de apósitos'',                  4, 3.50,  7.00,  50,10,''rollo'', ''3M'',        ''5cm x 4.5m'',false,true),
      (''PAU003'',''Termómetro Digital'',         ''Medición de temperatura corporal'',      4,12.00, 25.00,  20, 5,''unidad'',''Omron'',     ''Digital'',  false,true),
      (''PAU004'',''Vendaje Elástico 10cm'',      ''Compresión y soporte articular'',        4, 2.50,  5.00,  40, 8,''rollo'', ''Curaplex'',  ''10cm x 4m'',false,true),
      -- Bebés y Niños (cat 5)
      (''BEB001'',''Paracetamol Infantil Jarabe'',''Analgésico pediátrico 120mg/5ml'',      5, 4.00,  8.50,  50,10,''frasco'',''Genfarma'',  ''120 ml'',   false,true),
      (''BEB002'',''Vitamina A + D Gotas'',       ''Suplemento vitamínico pediátrico'',      5, 5.50, 11.00,  35, 8,''frasco'',''Biomont'',   ''20 ml'',    false,true),
      (''BEB003'',''Sales de Rehidratación'',     ''Tratamiento de deshidratación'',         5, 1.20,  2.80,  80,20,''sobre'', ''ORS'',       ''x 4 sobres'',false,true),
      -- Genéricos (cat 6)
      (''GEN001'',''Ranitidina 150mg'',           ''Antiulceroso genérico'',                 6, 0.70,  1.50,  70,15,''unidad'',''Genfarma'',  ''Tab x 10'', false,true),
      (''GEN002'',''Clonazepam 0.5mg'',           ''Ansiolítico benzodiazepínico'',           6, 3.00,  6.50,  30, 5,''caja'',  ''Roche Gen.'',''Tab x 30'', true, true),
      (''GEN003'',''Captopril 25mg'',             ''Antihipertensivo genérico'',              6, 1.00,  2.20,  55,10,''unidad'',''Genfar'',    ''Tab x 20'', true, true)
      ON CONFLICT (codigo) DO NOTHING;
    ', s);

  END LOOP;
END $$;
