FROM php:8.3-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    ca-certificates \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql bcmath zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && update-ca-certificates

# 啟用 Apache rewrite 模組
RUN a2enmod rewrite

# 建立 PHP 資訊頁面 (可在生產環境移除)
RUN echo "<?php phpinfo(); ?>" > /var/www/html/info.php

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 處理 Apache 與啟動腳本
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY start-app.sh /usr/local/bin/start-app.sh
RUN chmod +x /usr/local/bin/start-app.sh

# 允許 root 下的 composer 不禁用 plugins
ENV COMPOSER_ALLOW_SUPERUSER=1

# 複製 composer 檔並安裝依賴（跳過 scripts）
COPY composer.json composer.lock ./
# 使用 --no-dev 以生產模式；如需 dev 套件移除此參數
RUN composer install --no-dev --prefer-dist --optimize-autoloader --classmap-authoritative --no-interaction --no-scripts \
    && composer clear-cache

# 複製應用程式剩餘程式碼
COPY . .

# 建立必要目錄與權限
RUN mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/app/public \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && chmod -R 775 storage/framework/sessions

# 使用環境變數設定端口（本地 80，Render 10000）
ENV PORT=80
EXPOSE ${PORT}
CMD ["/usr/local/bin/start-app.sh"]