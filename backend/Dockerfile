FROM php:8.2-apache
WORKDIR /var/www/html

# 1. 安裝必要的系統套件和 PHP 擴充功能
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    ca-certificates \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql bcmath zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

RUN a2enmod rewrite \
    && echo "LoadModule php_module /usr/local/lib/php/extensions/no-debug-non-zts-20220829/php.so" >> /etc/apache2/apache2.conf \
    && echo "AddType application/x-httpd-php .php" >> /etc/apache2/apache2.conf

# 或者更簡單的方法，確保 PHP 被正確處理
RUN echo "<?php phpinfo(); ?>" > /var/www/html/info.php


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. 提前處理所有需要特殊路徑的設定檔
COPY backend/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY backend/start-app.sh /usr/local/bin/start-app.sh

# 賦予腳本執行權限
RUN chmod +x /usr/local/bin/start-app.sh
# 啟用 Apache rewrite 模組
RUN a2enmod rewrite

# 4. 複製 composer.json/lock 並安裝依賴
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# 5. 最後，複製應用程式剩餘的所有程式碼
COPY backend/. .

# 6. 確保快取目錄存在並設定權限
# 6. 確保快取目錄存在並設定權限（修正路徑結構）
RUN mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/app/public \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["/usr/local/bin/start-app.sh"]