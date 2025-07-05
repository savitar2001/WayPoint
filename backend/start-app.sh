#!/bin/sh

# Exit immediately if a command exits with a non-zero status.
set -e

# Start Laravel Reverb in the background
# Ensure environment variables for REVERB_HOST and REVERB_PORT are available
echo "Starting Reverb server..."
php artisan reverb:start --host="${REVERB_HOST:-0.0.0.0}" --port="${REVERB_PORT:-8080}" --debug &

# Start Apache in the foreground
echo "Starting Apache server..."
exec apache2-foreground