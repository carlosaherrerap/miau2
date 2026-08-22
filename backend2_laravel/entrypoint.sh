#!/bin/sh
set -e

PORT="${PORT:-8000}"

mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

php artisan storage:link --force || true

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "generateValue" ]; then
    php artisan key:generate --force || true
fi

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "Iniciando Laravel en el puerto ${PORT}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
