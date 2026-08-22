#!/bin/sh
set -e

# Configurar puerto dinamico si es proveido por Render / entorno
PORT="${PORT:-80}"
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/http.d/default.conf || true

# Asegurar directorios de storage
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

# Permisos
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Enlace simbolico de storage si no existe
php artisan storage:link --force || true

# Generar clave si no esta definida
if [ -z "$APP_KEY" ] && [ -f .env ]; then
    php artisan key:generate --force || true
fi

# Limpieza de cache al iniciar
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Ejecutar comando principal (Supervisord)
exec "$@"
