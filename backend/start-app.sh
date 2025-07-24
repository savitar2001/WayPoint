#!/bin/sh
set -e

echo "Starting Laravel application..."

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "=== ENVIRONMENT DIAGNOSTICS ==="
echo "APP_KEY: ${APP_KEY}"
echo "APP_ENV: ${APP_ENV}"
echo "DB_HOST: ${DB_HOST}"
echo "REDIS_URL: ${REDIS_URL}"

echo "Clearing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# 測試數據庫連接
echo "Testing database connection..."
php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'Database connection: OK' . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . PHP_EOL;
}
" || echo "Database test failed"

# 測試 Redis 連接
echo "Testing Redis connection..."
php artisan tinker --execute="
try {
    \Illuminate\Support\Facades\Redis::ping();
    echo 'Redis connection: OK' . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Redis connection failed: ' . \$e->getMessage() . PHP_EOL;
}
" || echo "Redis test failed"

# 測試基本配置
echo "Testing Laravel configuration..."
php artisan tinker --execute="
echo 'APP_KEY exists: ' . (config('app.key') ? 'YES' : 'NO') . PHP_EOL;
echo 'Database configured: ' . config('database.default') . PHP_EOL;
echo 'Cache driver: ' . config('cache.default') . PHP_EOL;
" || echo "Config test failed"

# 如果 Redis 連接失敗，暫時改用 file cache
if ! php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping();" 2>/dev/null; then
    echo "Redis failed, switching to file cache temporarily..."
    export CACHE_STORE=file
    export SESSION_DRIVER=file
    export QUEUE_CONNECTION=sync
fi

echo "Caching configuration..."
php artisan config:cache || echo "Config cache failed"

echo "Starting background services..."
# 只有在 Redis 可用時才啟動這些服務
if [ "$QUEUE_CONNECTION" != "sync" ]; then
    php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &
    php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &
fi

echo "Starting Apache server..."
exec apache2-foreground