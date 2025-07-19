#!/bin/sh
# filepath: /Applications/XAMPP/xamppfiles/htdocs/side-project/new-project/backend/start-app.sh

set -e

echo "Starting Laravel application..."

# Run Laravel setup commands
echo "Running Laravel setup..."

# 直接跳過 key:generate，因為在生產環境中應該使用環境變數 APP_KEY
echo "Skipping key:generate (using APP_KEY environment variable)"

php artisan config:cache
php artisan route:cache

# Wait for Redis to be ready using Laravel's Redis facade
echo "Waiting for Redis connection..."
until php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping();" 2>/dev/null; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "Redis is ready!"

# Start Laravel Reverb in the background
echo "Starting Reverb server..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &

# Start queue worker in the background
echo "Starting queue worker..."
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

# Start Apache in the foreground
echo "Starting Apache server..."
exec apache2-foreground