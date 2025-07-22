#!/bin/sh
set -e

echo "Starting Laravel application..."

# 确保所有必要目录存在
echo "Creating required directories..."
mkdir -p storage/framework/views \
         storage/framework/cache \
         storage/framework/sessions \
         storage/app \
         storage/logs \
         bootstrap/cache

echo "Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

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