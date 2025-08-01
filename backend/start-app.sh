#!/bin/sh
set -e

# 設定 Render 平台的端口
export PORT=${PORT:-10000}
echo "Using PORT: $PORT"

echo "Starting Laravel application..."
# 输出关键环境变量，方便调试
echo "APP_ENV=$APP_ENV"
echo "APP_DEBUG=$APP_DEBUG"
echo "APP_KEY=$APP_KEY"
echo "APP_URL=$APP_URL"

# 更新 Apache 設定以使用正確的端口
echo "Listen $PORT" > /etc/apache2/ports.conf
sed -i "s/\${PORT}/$PORT/g" /etc/apache2/sites-available/000-default.conf

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/storage/logs
chmod -R 777 /var/www/html/bootstrap/cache

echo "=== CLEARING ALL CACHES ==="
php artisan optimize:clear || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

rm -f /var/www/html/bootstrap/cache/config.php
rm -f /var/www/html/bootstrap/cache/services.php
rm -f /var/www/html/bootstrap/cache/packages.php

echo "=== DATABASE CONNECTION TEST ==="
php artisan tinker --execute="
try {
    \$pdo = \DB::connection()->getPdo();
    echo '✅ Database connection successful!' . PHP_EOL;
} catch (\Exception \$e) {
    echo '❌ Connection failed: ' . \$e->getMessage() . PHP_EOL;
}
"

echo "=== REDIS CONNECTION TEST ==="
php artisan tinker --execute="
try {
    \Illuminate\Support\Facades\Redis::ping();
    echo '✅ Redis connection successful!' . PHP_EOL;
} catch (\Exception \$e) {
    echo '❌ Redis connection failed: ' . \$e->getMessage() . PHP_EOL;
}
"

echo "Starting background services..."
# 如果需要 Reverb，使用不同的端口
if [ "$REVERB_HOST" ] && [ "$REVERB_PORT" ]; then
    echo "Starting Reverb server on ${REVERB_HOST}:${REVERB_PORT}..."
    php artisan reverb:start --host="${REVERB_HOST}" --port="${REVERB_PORT}" --debug &
fi

echo "Starting Apache server on port $PORT..."
exec apache2-foreground