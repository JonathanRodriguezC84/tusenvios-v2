# Checklist de despliegue en cPanel - Tus Envios

## 1. Preparar el proyecto local

Ejecutar:

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Verificar que exista:

```text
public/build
vendor
.env
public/pwa-icon.svg
```

## 2. Crear base de datos en cPanel

En cPanel:

1. Ir a MySQL Databases.
2. Crear base de datos, ejemplo: `tus_envios`.
3. Crear usuario MySQL.
4. Asignar usuario a base de datos con todos los permisos.
5. Guardar:
   - nombre de base de datos,
   - usuario,
   - contraseÃ±a.

## 3. Configurar subdominio

Crear:

```text
tusenvios.com.co
```

Idealmente apuntar el document root a:

```text
/home/rci/rci-envios/public
```

Si cPanel no permite apuntar a `public`, usar estructura alternativa:

```text
/home/rci/rci-envios
/home/rci/public_html/app
```

Y ajustar rutas en `public/index.php`.

## 4. Configurar .env de produccion

Ejemplo:

```env
APP_NAME="Tus Envios"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tusenvios.com.co

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=CPANEL_DB_NAME
DB_USERNAME=CPANEL_DB_USER
DB_PASSWORD=CPANEL_DB_PASSWORD
```

Generar o conservar:

```env
APP_KEY=base64:...
```

## 5. Subir archivos

Subir el proyecto completo comprimido, incluyendo:

- `app`
- `bootstrap`
- `config`
- `database`
- `public`
- `resources`
- `routes`
- `storage`
- `vendor`
- `.env`
- `artisan`
- `composer.json`
- `composer.lock`

No subir:

- `.git`
- `node_modules`
- archivos temporales.

## 6. Migraciones

Si hay SSH:

```bash
php artisan migrate --force
```

Si no hay SSH:

1. Exportar SQL desde local.
2. Importar SQL en phpMyAdmin.
3. Verificar tablas:
   - users
   - tenants
   - affiliated_companies
   - shipments
   - shipment_events
   - affiliate_settlements
   - affiliate_settlement_items
   - audit_logs

## 7. Permisos de carpetas

Verificar escritura en:

```text
storage
bootstrap/cache
```

## 8. SSL

Activar SSL para:

```text
tusenvios.com.co
tusenvios.com.co
```

## 9. Pruebas despues de subir

Probar:

- Login.
- Dashboard.
- Crear guia.
- Imprimir guia.
- Escanear guia.
- Tracking publico.
- Crear cliente.
- Crear afiliada.
- Crear usuario.
- Liquidacion por afiliada.
- Cerrar liquidacion.
- Marcar liquidacion pagada.
- Descargar CSV de liquidacion.
- Imprimir comprobante de liquidacion.
- Auditoria.

## 10. Pendientes antes de produccion real

- Backups automaticos de base de datos.
- Correo real para recuperacion de contraseÃ±a.
- Politica de contraseÃ±as.
- Revision de permisos por rol.
- Configuracion de subdominios de clientes.
- Monitoreo de errores.

