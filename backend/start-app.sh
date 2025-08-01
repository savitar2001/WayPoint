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
chmod -R 777 /var/www/html/storage/logs
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

echo "=== REDIS CONNECTION TEST ==="
php artisan tinker --execute="
try {
    \Illuminate\Support\Facades\Redis::set('copilot_test', 'ok');
    \$value = \Illuminate\Support\Facades\Redis::get('copilot_test');
    if (\$value === 'ok') {
        echo '✅ Redis connection successful!' . PHP_EOL;
    } else {
        echo '❌ Redis connection failed: Value mismatch.' . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo '❌ Redis connection failed: ' . \$e->getMessage() . PHP_EOL;
}
"

echo "=== CHECKING FOR EXISTING LOGS ==="
# 檢查日誌目錄是否存在且有內容
if [ -d "/var/www/html/storage/logs" ]; then
    echo "Log directory exists"
    ls -la /var/www/html/storage/logs/
    
    # 顯示最新的日誌文件內容
    LATEST_LOG=$(find /var/www/html/storage/logs -name "*.log" -type f -printf '%T@ %p\n' | sort -n | tail -1 | cut -d' ' -f2)
    if [ ! -z "$LATEST_LOG" ]; then
        echo "=== LATEST LOG CONTENT ==="
        echo "Showing last 50 lines from: $LATEST_LOG"
        tail -n 50 "$LATEST_LOG"
    else
        echo "No log files found"
    fi
else
    echo "Log directory does not exist"
fi

echo "=== APACHE ERROR LOG ==="
# 也檢查 Apache 錯誤日誌
if [ -f "/var/log/apache2/error.log" ]; then
    echo "Showing last 20 lines from Apache error log:"
    tail -n 20 /var/log/apache2/error.log
fi

echo "Starting background services..."
echo "Starting Reverb server..."
php artisan reverb:start --host="${REVERB_HOST:-0.0.0.0}" --port="${REVERB_PORT:-8080}" --debug &

echo "Starting Apache server..."
exec apache2-foreground