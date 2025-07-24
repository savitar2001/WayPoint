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

echo "Starting Apache server..."
exec apache2-foreground