# Tus Envios en cPanel

Dominio: `tusenvios.com.co`
PHP recomendado: `8.3 native`
Base de datos: MySQL

## 1. Subir archivos

Sube el contenido del proyecto `tus-envios` a una carpeta fuera de `public_html`, por ejemplo:

```text
/home/USUARIO/tus-envios
```

El dominio debe apuntar a:

```text
/home/USUARIO/tus-envios/public
```

Si cPanel no permite cambiar el document root, crea la app en una subcarpeta y ajusta el dominio/subdominio desde Domains.

## 2. Crear base de datos

En cPanel crea una base MySQL, usuario y clave. Luego copia `.env.example` como `.env` y ajusta:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tusenvios.com.co
DB_DATABASE=USUARIO_tus_envios
DB_USERNAME=USUARIO_tus_envios
DB_PASSWORD=CLAVE_SEGURA
```

## 3. Comandos por SSH

Desde la carpeta del proyecto:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Los assets ya pueden ir compilados desde local con:

```bash
npm install
npm run build
```

## 4. Softaculous

No instales Laravel encima con Softaculous. Softaculous sirve para crear apps nuevas, pero este proyecto ya tiene codigo propio.

Usa Softaculous/cPanel solo como apoyo para:

- Confirmar PHP 8.3
- Crear base de datos
- Ver archivos
- Ver backups

## 5. Checklist final

- El dominio abre la portada.
- `/login` abre el acceso.
- `/register` crea un negocio.
- `/dashboard` carga despues de iniciar sesion.
- `/my-brand` permite personalizar etiqueta.
- `/shipments/create` crea guia.
- `/track` consulta una guia.
- `storage` esta enlazado correctamente.
