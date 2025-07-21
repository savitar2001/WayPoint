#!/bin/sh
set -e

echo "Starting Laravel application..."

# 確保所有必要目錄存在
echo "Creating required directories..."
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/app
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 不要立即快取，先讓應用正常啟動
echo "Clearing any existing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Wait for Redis
echo "Waiting for Redis connection..."
until php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping();" 2>/dev/null; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "Redis is ready!" 

echo "Caching configuration..."
php artisan config:cache || echo "Config cache failed, continuing..."

# Start services
echo "Starting Reverb server..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &

echo "Starting queue worker..."
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

echo "Starting Apache server..."
exec apache2-foreground