#!/bin/sh
set -e

echo "Starting Laravel application..."


echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Verifying directories..."
ls -la /var/www/html/storage/framework/
ls -la /var/www/html/bootstrap/

echo "Clearing caches..."
php artisan optimize:clear || true
php artisan cache:clear || true

echo "Waiting for Redis connection..."
until php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping();" 2>/dev/null; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "Redis is ready!"

# 重新缓存配置（可选）
echo "Caching configuration..."
php artisan config:cache || echo "Config cache failed, continuing..."

# 启动后台服务
echo "Starting Reverb server..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &

echo "Starting queue worker..."
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

echo "Starting Apache server..."
exec apache2-foreground