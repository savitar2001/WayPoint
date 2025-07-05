FROM php:8.2-apache AS base
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

# 3. 複製 composer 相關檔案並安裝依賴 (利用快取)
# 這樣只有在 composer.json/lock 變動時，才會重新執行 composer install
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# 4. 複製整個應用程式的程式碼
# 這會包含 start-app.sh, apache-vhost.conf, 和所有 Laravel 程式碼
COPY . .

# 5. 設定 Apache 和權限
COPY --from=base /var/www/html/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
RUN chown -R www-data:www-data storage bootstrap/cache

# 6. 設定啟動腳本
RUN mv /var/www/html/start-app.sh /usr/local/bin/start-app.sh && \
    chmod +x /usr/local/bin/start-app.sh

EXPOSE 80
CMD ["/usr/local/bin/start-app.sh"]
