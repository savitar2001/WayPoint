#!/bin/sh
set -e

# 設定端口（本地預設 80，Render 使用 10000）
export PORT=${PORT:-80}
echo "Using PORT: $PORT"

echo "Starting Laravel application..."
# 输出关键环境变量，方便调试
echo "APP_ENV=$APP_ENV"
echo "APP_DEBUG=$APP_DEBUG"
echo "APP_KEY=${APP_KEY:+(set)}"
echo "APP_URL=$APP_URL"

# 輸出 Session 和 Sanctum 相關配置
php artisan tinker --execute="
    echo '=== SESSION CONFIGURATION ===' . PHP_EOL;
    print_r(config('session'));
    echo '=== SANCTUM CONFIGURATION ===' . PHP_EOL;
    print_r(config('sanctum'));
" || echo "Failed to output session and sanctum configurations"

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

echo "=== OUTPUTTING TODAY'S LOGS ==="
TODAY=$(date +"%Y-%m-%d")
LOG_FILE="/var/www/html/storage/logs/laravel-$TODAY.log"

if [ -f "$LOG_FILE" ]; then
  echo "Logs for $TODAY:"
  grep "$TODAY" "$LOG_FILE" || echo "No logs found for today."
else
  echo "Log file not found: $LOG_FILE"
fi

# 手動執行 package:discover
php artisan package:discover --ansi || echo "package:discover failed (ignored)"

# 重新建立快取（忽略錯誤，避免中斷啟動）
php artisan config:cache || echo "config:cache failed (ignored)"
php artisan route:cache || echo "route:cache failed (ignored)"
php artisan view:cache || echo "view:cache failed (ignored)"

# （可選）資料庫遷移（如需要自動 migrate，否則註解）
# php artisan migrate --force || echo "migrate failed (ignored)"

# 背景啟動 Reverb
# 如果環境變數沒設定,使用預設值
REVERB_HOST_FINAL="${REVERB_HOST:-0.0.0.0}"
REVERB_PORT_FINAL="${REVERB_PORT:-8080}"

echo "=== STARTING REVERB WEBSOCKET SERVER ==="
echo "Reverb Host: ${REVERB_HOST_FINAL}"
echo "Reverb Port: ${REVERB_PORT_FINAL}"
echo "Reverb App ID: ${REVERB_APP_ID}"
echo "Reverb App Key: ${REVERB_APP_KEY}"

# 啟動 Reverb (後台運行)
php artisan reverb:start \
    --host="${REVERB_HOST_FINAL}" \
    --port="${REVERB_PORT_FINAL}" \
    --debug &

# 記錄 Reverb 的 PID
REVERB_PID=$!
echo "Reverb started with PID: ${REVERB_PID}"

# 等待 Reverb 啟動 (給它 2 秒時間)
sleep 2

# 檢查 Reverb 是否成功啟動
if ps -p $REVERB_PID > /dev/null; then
    echo "✅ Reverb is running"
else
    echo "❌ WARNING: Reverb failed to start"
fi

# 最終啟動 Apache
echo "=== STARTING APACHE SERVER ==="
echo "Starting Apache server on port $PORT..."
exec apache2-foreground