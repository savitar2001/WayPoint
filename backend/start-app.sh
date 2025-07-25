#!/bin/sh
set -e

echo "Starting Laravel application..."

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "Clearing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

echo "=== DATABASE CONNECTION TEST ==="
echo "Testing TiDB connection..."
php artisan tinker --execute="
try {
    echo 'Attempting to connect to TiDB...' . PHP_EOL;
    \$pdo = \DB::connection()->getPdo();
    echo '✅ Database connection successful!' . PHP_EOL;
    echo 'Server info: ' . \$pdo->getAttribute(PDO::ATTR_SERVER_INFO) . PHP_EOL;
    echo 'Server version: ' . \$pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
} catch (\PDOException \$e) {
    echo '❌ PDO Exception: ' . \$e->getMessage() . PHP_EOL;
    echo 'Error Code: ' . \$e->getCode() . PHP_EOL;
} catch (\Exception \$e) {
    echo '❌ General Exception: ' . \$e->getMessage() . PHP_EOL;
    echo 'Error Type: ' . get_class(\$e) . PHP_EOL;
}
"

echo "Checking SSL certificate..."
if [ -f "/etc/ssl/cert.pem" ]; then
    echo "✅ CA certificate exists"
    ls -la /etc/ssl/cert.pem
else
    echo "❌ CA certificate not found"
    echo "Available certificates:"
    find /etc/ssl -name "*.pem" 2>/dev/null || echo "No .pem files found"
fi

echo "Checking environment variables..."
echo "DB_HOST: ${DB_HOST}"
echo "DB_PORT: ${DB_PORT}"
echo "DB_DATABASE: ${DB_DATABASE}"
echo "DB_USERNAME: ${DB_USERNAME}"
echo "DB_SSL_CA: ${DB_SSL_CA}"
echo "DB_SSL_VERIFY_SERVER_CERT: ${DB_SSL_VERIFY_SERVER_CERT}"

echo "Testing network connectivity..."
if command -v ping >/dev/null 2>&1; then
    ping -c 2 ${DB_HOST} || echo "Ping failed"
else
    echo "Ping command not available"
fi

echo "Caching configuration..."
php artisan config:cache

echo "Starting background services..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

echo "Starting Apache server..."
exec apache2-foreground