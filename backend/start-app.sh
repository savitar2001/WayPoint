#!/bin/sh
set -e

echo "Starting Laravel application..."
# 输出关键环境变量，方便调试
echo "APP_ENV=$APP_ENV"
echo "APP_DEBUG=$APP_DEBUG"
echo "APP_KEY=$APP_KEY"
echo "APP_URL=$APP_URL"
echo "LOG_CHANNEL=$LOG_CHANNEL"
echo "LOG_LEVEL=$LOG_LEVEL"

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

echo "=== LOG CONFIGURATION TEST ==="
php artisan tinker --execute="
echo 'Log channel: ' . config('logging.default') . PHP_EOL;
echo 'Log level: ' . config('logging.channels.' . config('logging.default') . '.level', 'not set') . PHP_EOL;
try {
    \Log::info('Test log entry from startup script');
    echo '✅ Logging system working!' . PHP_EOL;
} catch (\Exception \$e) {
    echo '❌ Logging failed: ' . \$e->getMessage() . PHP_EOL;
}
"

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
    \Log::error('Database connection failed: ' . \$e->getMessage());
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
        \Log::error('Redis connection failed: Value mismatch');
    }
} catch (\Exception \$e) {
    echo '❌ Redis connection failed: ' . \$e->getMessage() . PHP_EOL;
    \Log::error('Redis connection failed: ' . \$e->getMessage());
}
"

echo "=== CHECKING FOR EXISTING LOGS ==="
# 檢查日誌目錄是否存在且有內容
if [ -d "/var/www/html/storage/logs" ]; then
    echo "Log directory exists"
    ls -la /var/www/html/storage/logs/
    
    # 因為使用 daily channel，日誌文件名會包含日期
    TODAY=$(date +%Y-%m-%d)
    YESTERDAY=$(date -d "yesterday" +%Y-%m-%d)
    
    # 檢查今天和昨天的日誌
    for LOG_DATE in $TODAY $YESTERDAY; do
        LOG_FILE="/var/www/html/storage/logs/laravel-$LOG_DATE.log"
        if [ -f "$LOG_FILE" ]; then
            echo "=== LOG CONTENT ($LOG_DATE) ==="
            echo "Showing last 50 lines from: $LOG_FILE"
            tail -n 50 "$LOG_FILE"
            break
        fi
    done
    
    # 如果找不到預期的日誌文件，顯示所有日誌文件
    if ls /var/www/html/storage/logs/*.log 1> /dev/null 2>&1; then
        LATEST_LOG=$(ls -t /var/www/html/storage/logs/*.log | head -n1)
        if [ ! -f "/var/www/html/storage/logs/laravel-$TODAY.log" ] && [ ! -f "/var/www/html/storage/logs/laravel-$YESTERDAY.log" ]; then
            echo "=== LATEST AVAILABLE LOG ==="
            echo "Showing last 50 lines from: $LATEST_LOG"
            tail -n 50 "$LATEST_LOG"
        fi
    else
        echo "No log files found"
    fi
else
    echo "Log directory does not exist"
fi

echo "=== APACHE LOG DIAGNOSTICS ==="
if [ -f "/var/log/apache2/error.log" ]; then
    echo "=== APACHE ERROR LOG ==="
    if [ -s "/var/log/apache2/error.log" ]; then
        echo "Showing last 20 lines from Apache error log:"
        tail -n 20 /var/log/apache2/error.log
    else
        echo "Apache error log is empty"
    fi
else
    echo "Apache error log does not exist"
fi

echo "Starting background services..."
echo "Starting Reverb server..."
php artisan reverb:start --host="${REVERB_HOST:-0.0.0.0}" --port="${REVERB_PORT:-8080}" --debug &

echo "Starting Apache server..."
exec apache2-foreground