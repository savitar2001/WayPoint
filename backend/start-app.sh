#!/bin/sh
# filepath: /Applications/XAMPP/xamppfiles/htdocs/side-project/new-project/backend/start-app.sh

# Exit immediately if a command exits with a non-zero status.
set -e

echo "Starting Laravel application..."

# Start Laravel Reverb in the background
echo "Starting Reverb server..."
php artisan reverb:start --host="${REVERB_HOST:-0.0.0.0}" --port="${REVERB_PORT:-8080}" --debug &

# Start queue worker in the background
echo "Starting queue worker..."
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

# Start Apache in the foreground (this keeps the container running)
echo "Starting Apache server..."
exec apache2-foreground