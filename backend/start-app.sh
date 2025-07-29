#!/bin/sh
set -e

echo "Starting Laravel application..."
# 输出关键环境变量，方便调试
echo "APP_ENV=$APP_ENV"
echo "APP_DEBUG=$APP_DEBUG"
echo "APP_KEY=$APP_KEY"
echo "APP_URL=$APP_URL"
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

echo "=== CLEARING ALL CACHES ==="
# 強制清除所有緩存，包括配置緩存
php artisan optimize:clear || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# 刪除 bootstrap 緩存文件
rm -f /var/www/html/bootstrap/cache/config.php
rm -f /var/www/html/bootstrap/cache/services.php
rm -f /var/www/html/bootstrap/cache/packages.php


echo "檢查 MySQL 資料庫連線..."
if ! mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; then
  echo "[ERROR] 無法連線到 MySQL 資料庫，請檢查 DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD 設定"
  exit 1
fi

echo "檢查 Redis 連線..."
if ! redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" ping | grep -q PONG; then
  echo "[ERROR] 無法連線到 Redis，請檢查 REDIS_HOST/REDIS_PORT 設定"
  exit 1
fi
echo "Starting background services..."
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 &

echo "Starting Apache server..."
exec apache2-foreground