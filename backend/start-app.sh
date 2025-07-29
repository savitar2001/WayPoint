#!/bin/sh
set -e

echo "Starting Laravel application..."
# 输出关键环境变量，方便调试
echo "APP_ENV=$APP_ENV"
echo "APP_DEBUG=$APP_DEBUG"
echo "APP_KEY=$APP_KEY"
echo "APP_URL=$APP_URL"
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

echo "=== CLEARING ALL CACHES ==="
# 強制清除所有緩存，包括配置緩存
php artisan optimize:clear || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# 刪除 bootstrap 緩存文件
rm -f /var/www/html/bootstrap/cache/config.php
rm -f /var/www/html/bootstrap/cache/services.php
rm -f /var/www/html/bootstrap/cache/packages.php

echo "=== VERIFYING ENVIRONMENT VARIABLES ==="
echo "DB_HOST: ${DB_HOST}"
echo "DB_PORT: ${DB_PORT}"
echo "DB_DATABASE: ${DB_DATABASE}"
echo "DB_USERNAME: ${DB_USERNAME}"
echo "DB_SSL_CA: ${DB_SSL_CA}"
echo "DB_SSL_VERIFY_SERVER_CERT: ${DB_SSL_VERIFY_SERVER_CERT}"

echo "=== DATABASE CONNECTION TEST ==="
echo "Testing TiDB connection..."
php artisan tinker --execute="
try {
    echo 'Config DB_HOST: ' . config('database.connections.mysql.host') . PHP_EOL;
    echo 'Config DB_PORT: ' . config('database.connections.mysql.port') . PHP_EOL;
    echo 'Attempting to connect to TiDB...' . PHP_EOL;
    \$pdo = \DB::connection()->getPdo();
    echo '✅ Database connection successful!' . PHP_EOL;
    echo 'Server version: ' . \$pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
} catch (\Exception \$e) {
    echo '❌ Connection failed: ' . \$e->getMessage() . PHP_EOL;
}
"

echo "Starting background services..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

echo "Starting Apache server..."
# 持续输出 Laravel 日志到控制台
tail -n 100 -f /var/www/html/storage/logs/laravel.log &
exec apache2-foreground