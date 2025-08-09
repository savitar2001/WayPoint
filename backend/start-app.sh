#!/bin/sh
set -e

# 設定 Render 平台的端口
export PORT=${PORT:-10000}
echo "Using PORT: $PORT"

echo "Starting Laravel application..."
# 输出关键环境变量，方便调试
echo "APP_ENV=$APP_ENV"
echo "APP_DEBUG=$APP_DEBUG"
echo "APP_KEY=${APP_KEY:+(set)}"
echo "APP_URL=$APP_URL"

# 更新 Apache 設定以使用正確的端口
# 動態注入 ports.conf
echo "Listen $PORT" > /etc/apache2/ports.conf
# 取代 vhost 中的 ${PORT}
sed -i "s/\${PORT}/$PORT/g" /etc/apache2/sites-available/000-default.conf || true

# 權限設定
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache || true

# 確認 .htaccess 是否存在 (若無就建立基本 rewrite)
if [ ! -f /var/www/html/public/.htaccess ]; then
  cat > /var/www/html/public/.htaccess <<'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF
fi

echo "=== CLEARING & OPTIMIZING CACHES ==="
php artisan optimize:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# 重新建立快取（忽略錯誤，避免中斷啟動）
php artisan config:cache || echo "config:cache failed (ignored)"
php artisan route:cache || echo "route:cache failed (ignored)"
php artisan view:cache || echo "view:cache failed (ignored)"

# （可選）資料庫遷移（如需要自動 migrate，否則註解）
# php artisan migrate --force || echo "migrate failed (ignored)"

# 背景啟動 Reverb（如設置）
if [ "$REVERB_HOST" ] && [ "$REVERB_PORT" ]; then
    echo "Starting Reverb server on ${REVERB_HOST}:${REVERB_PORT}..."
    php artisan reverb:start --host="${REVERB_HOST}" --port="${REVERB_PORT}" --debug &
fi

# 最終啟動 Apache
echo "Starting Apache server on port $PORT..."
exec apache2-foreground