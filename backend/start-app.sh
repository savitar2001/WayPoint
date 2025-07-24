#!/bin/sh
set -e

echo "Starting Laravel application..."

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "Debugging configuration..."
echo "Current working directory: $(pwd)"
echo "PHP version: $(php --version)"
echo "Laravel version: $(php artisan --version 2>/dev/null || echo 'Laravel command failed')"

# 檢查配置文件
echo "Checking config files..."
ls -la config/ || echo "Config directory not found"
ls -la config/view.php || echo "View config not found"

# 檢查當前的 view 配置
echo "Current view configuration:"
php artisan tinker --execute="echo config('view.compiled');" 2>/dev/null || echo "Config check failed"

echo "Clearing caches..."
php artisan optimize:clear || true

echo "Waiting for Redis..."
until php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping();" 2>/dev/null; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "Redis is ready!"

echo "Testing basic Laravel functionality..."
php artisan route:list --path=api 2>/dev/null || echo "Route list failed"

echo "Starting background services..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

# 啟動 Apache 在背景
echo "Starting Apache server..."
apache2-foreground &

# 等待 Apache 啟動
echo "Waiting for Apache to start..."
sleep 5

# 進行 web 測試
echo "Testing web functionality..."
# 測試內部 HTTP 請求
curl -f -s http://localhost/ > /dev/null && echo "✅ Root web test passed" || echo "❌ Root web test failed"

# 檢查 Apache 錯誤日誌
echo "Checking Apache error logs..."
tail -5 /var/log/apache2/error.log 2>/dev/null || echo "No Apache error log found"

# 測試簡單的 PHP 文件
echo "Testing basic PHP..."
echo "<?php echo 'PHP is working'; ?>" > /var/www/html/test.php
curl -f -s http://localhost/test.php && echo "✅ PHP test passed" || echo "❌ PHP test failed"

# 讓 Apache 回到前台運行
echo "Web tests completed. Apache running in foreground..."
wait