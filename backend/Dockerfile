FROM php:8.2-apache
WORKDIR /var/www/html

# 1. 安裝必要的系統套件和 PHP 擴充功能
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql bcmath zip pcntl

# 2. 複製 Composer 執行檔
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. 提前處理所有需要特殊路徑的設定檔
# 先從建置上下文 (backend/ 目錄) 複製設定檔到它們在映像檔中的最終位置
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY start-app.sh /usr/local/bin/start-app.sh

# 賦予腳本執行權限
RUN chmod +x /usr/local/bin/start-app.sh
# 啟用 Apache rewrite 模組
RUN a2enmod rewrite

# 4. 複製 composer.json/lock 並安裝依賴
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# 5. 最後，複製應用程式剩餘的所有程式碼
COPY . .

# 6. 設定儲存目錄的權限
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["/usr/local/bin/start-app.sh"]