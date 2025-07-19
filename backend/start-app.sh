#!/bin/sh
# filepath: /Applications/XAMPP/xamppfiles/htdocs/side-project/new-project/backend/start-app.sh

set -e

echo "Starting Laravel application..."

# 確保快取目錄存在且有正確權限
echo "Setting up cache directories..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# 設定權限
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 清除所有快取（避免路徑問題）
echo "Clearing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# 重新快取配置（但先確保目錄存在）
echo "Rebuilding caches..."
php artisan config:cache
php artisan route:cache

# Wait for Redis to be ready
echo "Waiting for Redis connection..."
until php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping();" 2>/dev/null; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "Redis is ready!"

# Start services
echo "Starting Reverb server..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &

echo "Starting queue worker..."
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

echo "Starting Apache server..."
exec apache2-foreground