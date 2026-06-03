# Orden de Facturacion Electronica

Ejecutar estos scripts en este orden sobre la base `farmacia`:

1. [migration_01_facturacion_empresa_base.sql](C:/laragon/www/farmacia/database/migration_01_facturacion_empresa_base.sql)
2. [migration_02_facturacion_empresa_sunat.sql](C:/laragon/www/farmacia/database/migration_02_facturacion_empresa_sunat.sql)
3. [migration_03_facturacion_seed_mytems.sql](C:/laragon/www/farmacia/database/migration_03_facturacion_seed_mytems.sql)
4. [migration_04_ubigeo_catalogos.sql](C:/laragon/www/farmacia/database/migration_04_ubigeo_catalogos.sql)
5. [migration_05_ubigeo_seed_peru.sql](C:/laragon/www/farmacia/database/migration_05_ubigeo_seed_peru.sql)
6. [migration_06_facturacion_datos_botica.sql](C:/laragon/www/farmacia/database/migration_06_facturacion_datos_botica.sql)
7. [migration_07_facturacion_catalogos_base.sql](C:/laragon/www/farmacia/database/migration_07_facturacion_catalogos_base.sql)
8. [migration_08_facturacion_catalogos_seed.sql](C:/laragon/www/farmacia/database/migration_08_facturacion_catalogos_seed.sql)
9. [migration_09_facturacion_campos_operativos.sql](C:/laragon/www/farmacia/database/migration_09_facturacion_campos_operativos.sql)
10. [migration_10_facturacion_certificado_mytems.sql](C:/laragon/www/farmacia/database/migration_10_facturacion_certificado_mytems.sql)
11. [migration_11_facturacion_productos_defaults.sql](C:/laragon/www/farmacia/database/migration_11_facturacion_productos_defaults.sql)
12. [migration_12_cliente_varios_default.sql](C:/laragon/www/farmacia/database/migration_12_cliente_varios_default.sql)
13. [migration_13_facturacion_notas_credito.sql](C:/laragon/www/farmacia/database/migration_13_facturacion_notas_credito.sql)
14. [migration_14_facturacion_series_notas_credito.sql](C:/laragon/www/farmacia/database/migration_14_facturacion_series_notas_credito.sql)

Comando sugerido:

```bat
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_01_facturacion_empresa_base.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_02_facturacion_empresa_sunat.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_03_facturacion_seed_mytems.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_04_ubigeo_catalogos.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_05_ubigeo_seed_peru.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_06_facturacion_datos_botica.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_07_facturacion_catalogos_base.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_08_facturacion_catalogos_seed.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_10_facturacion_certificado_mytems.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_11_facturacion_productos_defaults.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_12_cliente_varios_default.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_13_facturacion_notas_credito.sql"
"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres -d farmacia -f "C:\laragon\www\farmacia\database\migration_14_facturacion_series_notas_credito.sql"
```

Y luego, por cada schema de sucursal:

```sql
SET search_path TO nombre_schema, public;
\i C:/laragon/www/farmacia/database/migration_09_facturacion_campos_operativos.sql
```

Notas:

- `migration_01` agrega datos base de empresa.
- `migration_02` agrega datos SUNAT y servicios auxiliares.
- `migration_03` carga los datos Mytems en el tenant `generic_pharma`.
- `migration_04` crea las tablas de ubigeo.
- `migration_05` carga el ubigeo completo del Peru, tomando como referencia ventas-up2026.
- `migration_06` acomoda razon social, nombre del negocio y direccion base sin tocar el logo.
- `migration_07` crea los catalogos compartidos de facturacion electronica.
- `migration_08` carga catalogos SUNAT, medios de pago, monedas, unidades y tipos base.
- `migration_09` agrega a cada sucursal los campos nuevos para clientes, productos, ventas, detalles y comprobantes.
- `migration_10` deja apuntado el certificado `.pfx` base dentro del proyecto.
- `migration_11` normaliza los productos ya registrados con defaults tributarios y enlaces a catalogos.
- `migration_12` deja el cliente base `CLIENTES VARIOS` listo para boletas de mostrador.
- `migration_13` prepara las notas de crédito con sus campos de referencia y la serie `NC01`.
- `migration_14` crea las series `BC01` y `FC01` para notas de crÃ©dito segun el comprobante origen.
- El logo no se modifica en estos scripts.
