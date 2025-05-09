FROM php:8.2-apache
WORKDIR /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
COPY . .
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql bcmath
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist
RUN chown -R www-data:www-data storage bootstrap/cache
RUN mkdir -p /var/www/html/public && chmod 755 /var/www/html/public
# 確保 .htaccess 檔案存在且 www-data 可以讀取 (如果 .htaccess 在 COPY . . 時已存在)
# 注意：如果 .htaccess 是在 composer install 後生成的，這行可能需要調整位置或方式
RUN touch /var/www/html/public/.htaccess && chmod 644 /var/www/html/public/.htaccess
# 再次確保整個專案目錄的所有權，特別是 public 目錄
RUN chown -R www-data:www-data /var/www/html
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
EXPOSE 80
RUN a2enmod rewrite