# Tus Envios - Instalacion en cPanel con Laravel 11

## 1. Antes de subir

Este paquete esta preparado para instalarse sobre Laravel 11 en cPanel/Softaculous.

No subas estas carpetas desde tu computador:

- `node_modules`
- `vendor` de Laravel 13
- `.env` local
- logs/cache/sesiones locales

## 2. En cPanel

1. Entra a Softaculous.
2. Instala Laravel 11.
3. Usa el dominio `tusenvios.com.co`.
4. Si Softaculous permite elegir carpeta publica, apunta el dominio a la carpeta `public`.
5. Si no permite apuntar a `public`, instala Laravel normalmente y luego revisamos el Document Root desde cPanel.

## 3. Base de datos

1. En cPanel abre MySQL Databases.
2. Crea una base de datos, por ejemplo:
   `usuario_tusenvios`
3. Crea un usuario MySQL.
4. Asigna el usuario a la base de datos con todos los permisos.
5. Guarda:
   - nombre de base de datos
   - usuario
   - clave

## 4. Subir archivos

Opcion recomendada:

1. Instala Laravel 11 con Softaculous.
2. Sube el contenido de este paquete encima de la instalacion.
3. No reemplaces `vendor` si Softaculous ya lo creo.
4. Copia `.env.cpanel.example` como `.env`.
5. Cambia los datos de base de datos, URL y correo.

## 5. Comandos necesarios

Desde Terminal de cPanel o SSH, dentro de la carpeta del proyecto:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

Si cPanel tiene Composer disponible:

```bash
composer install --no-dev --optimize-autoloader
```

## 6. Despues de instalar

Revisa:

- `https://tusenvios.com.co`
- `https://tusenvios.com.co/register`
- `https://tusenvios.com.co/login`
- Crear cuenta con 10 guias gratis.
- Crear guia.
- Imprimir etiqueta.
- Ver admin.
- Probar pagos Bold en modo real o pruebas segun la cuenta.

## 7. Importante sobre Laravel 13 local

El proyecto local fue creado con Laravel 13, pero cPanel solo tiene Laravel 10/11.
Por eso este paquete cambia `composer.json` a Laravel 11 y deja fuera el `composer.lock` local.

Si alguna pantalla falla despues de subir, primero limpiar caches:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```
