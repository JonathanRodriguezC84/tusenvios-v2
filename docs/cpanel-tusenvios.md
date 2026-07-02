# Guia de despliegue en cPanel para tusenvios.com.co

Esta aplicacion es Laravel y puede instalarse en cPanel siempre que el hosting tenga PHP 8.3 o superior, Composer y acceso para configurar la carpeta publica del dominio.

## Nota si el cPanel usa Softaculous

En la pantalla de Softaculous puede aparecer Laravel con versiones como 11.51.0 o 10.50.2. Esa opcion instala una aplicacion Laravel nueva, no sube automaticamente este proyecto.

La aplicacion local actual usa Laravel 13, por lo que hay dos caminos:

1. Mantener Laravel 13 y subir el proyecto manualmente al hosting, siempre que cPanel permita PHP 8.3 o superior y Composer.
2. Adaptar el proyecto a Laravel 11 para que coincida con la version disponible en Softaculous.

No se recomienda presionar "Instalar" y luego copiar archivos encima sin revisar compatibilidad, porque se mezclarian dos aplicaciones Laravel distintas.

Softaculous puede servir para crear la carpeta/base inicial o para administrar instalaciones, pero el despliegue real de Tus Envios debe hacerse subiendo este proyecto y configurando su `.env`, base de datos, permisos y carpeta `public`.

## Preparacion local

1. Generar los archivos del frontend:

```bash
npm run build
```

2. Subir al hosting el proyecto completo, incluyendo:

- `app`
- `bootstrap`
- `config`
- `database`
- `public`
- `resources`
- `routes`
- `storage`
- `vendor`
- `artisan`
- `composer.json`
- `composer.lock`
- `.env`

No es necesario subir `node_modules`.

## Configuracion del dominio

En cPanel, el document root de `tusenvios.com.co` debe apuntar a la carpeta:

```text
public
```

Si cPanel no permite escoger `public` directamente, se puede dejar el proyecto fuera de `public_html` y copiar el contenido de `public` dentro de `public_html`, ajustando las rutas de `index.php` hacia la carpeta real del proyecto.

## Variables principales

En el archivo `.env` del hosting:

```env
APP_NAME="Tus Envios"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tusenvios.com.co
```

Configurar tambien la base de datos creada en cPanel:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usuario_basededatos
DB_USERNAME=usuario_usuario
DB_PASSWORD=clave_segura
```

## Comandos despues de subir

Desde Terminal de cPanel o SSH:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si ya existe una `APP_KEY` valida en `.env`, no ejecutar `key:generate` para evitar invalidar sesiones o datos cifrados.

## Permisos

Estas carpetas deben poder escribirse desde PHP:

```text
storage
bootstrap/cache
```

## Primeras pruebas

1. Abrir `https://tusenvios.com.co`.
2. Entrar al panel con un usuario administrador.
3. Crear una guia de prueba.
4. Imprimir una etiqueta.
5. Consultar la guia desde `/track`.

## Recomendaciones

- Activar SSL en cPanel antes de usar la plataforma con clientes.
- Mantener `APP_DEBUG=false` en produccion.
- Hacer backup de base de datos antes de correr migraciones futuras.
- Confirmar que el hosting permite PHP 8.3, porque el proyecto actual usa Laravel 13.
