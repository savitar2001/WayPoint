#!/bin/sh
set -e

echo "Starting Laravel application..."

echo "Setting comprehensive permissions..."
# 設置更全面的權限
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# 特別確保 logs 目錄權限
chown -R www-data:www-data /var/www/html/storage/logs
chmod -R 775 /var/www/html/storage/logs

echo "Verifying permissions..."
ls -la /var/www/html/storage/
ls -la /var/www/html/storage/logs/

echo "Clearing caches..."
php artisan optimize:clear || true
php artisan cache:clear || true

echo "Waiting for Redis connection..."
until php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping();" 2>/dev/null; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "Redis is ready!"

echo "Caching configuration..."
php artisan config:cache || echo "Config cache failed, continuing..."

echo "Starting background services..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

echo "Starting Apache server..."
exec apache2-foreground