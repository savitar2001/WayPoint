set -e

echo "Starting Laravel application..."

# Wait for Redis to be ready
echo "Waiting for Redis connection..."
until php artisan tinker --execute="Redis::ping();" 2>/dev/null; do
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

# Start Apache in the foreground (this keeps the container running)
echo "Starting Apache server..."
exec apache2-foreground