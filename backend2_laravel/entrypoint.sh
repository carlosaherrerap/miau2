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

# Auto-inicializar esquema y datos si la base de datos esta vacia
if [ -f /var/www/html/database/schema_completo.sql ]; then
    php -r "
    require __DIR__.'/vendor/autoload.php';
    \$app = require_once __DIR__.'/bootstrap/app.php';
    \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
    \$kernel->bootstrap();
    try {
        if (!Illuminate\Support\Facades\Schema::hasTable('usuario')) {
            echo 'Base de datos vacia. Inicializando tablas y datos iniciales...'.PHP_EOL;
            \$sql = file_get_contents('/var/www/html/database/schema_completo.sql');
            Illuminate\Support\Facades\DB::unprepared(\$sql);
            echo 'Tablas y datos cargados exitosamente.'.PHP_EOL;
        }
    } catch (\Exception \$e) {
        echo 'Nota de BD: ' . \$e->getMessage() . PHP_EOL;
    }
    " || true
fi

echo "Iniciando Laravel en el puerto ${PORT}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
