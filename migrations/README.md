# Migraciones

## Forma de pago de ventas (2026-09-16)

La aplicación debe actualizarse en este orden:

1. Detener temporalmente la carga de ventas.
2. Respaldar al menos las tablas `ventas` y `venta_items`:
   `mysqldump -u root -p catastro_minutas ventas venta_items > respaldo_ventas_YYYYMMDD.sql`
3. Aplicar `20260916_add_forma_pago_ventas.sql` sobre la base correspondiente.
4. Revisar las dos consultas de verificación incluidas al final del script. La suma de las tres cantidades debe coincidir con `ventas_totales`.
5. Publicar el código de la aplicación y registrar una venta de prueba controlada en cada forma de pago.

El script puede reejecutarse: consulta `information_schema` antes de crear la columna, la restricción y el índice. No modifica ventas que ya tengan una clasificación. Las ventas anteriores a la migración quedan con `forma_pago = NULL` y la interfaz las presenta como “Sin especificar”; no se presupone que hayan sido en efectivo.

No se debe reclasificar el histórico sin documentación respaldatoria. Si posteriormente se obtiene esa evidencia, la actualización debe hacerse mediante un procedimiento de datos separado y autorizado.

### Reversión

1. Detener temporalmente la carga y guardar un respaldo nuevo.
2. Ejecutar `20260916_remove_forma_pago_ventas.sql`.
3. Volver a la versión anterior del código.

La reversión elimina la columna y, por lo tanto, pierde la clasificación de pago capturada desde la migración. Para recuperarla es necesario restaurar el respaldo. El script de reversión también es seguro ante reejecuciones.
