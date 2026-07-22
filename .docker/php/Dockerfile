FROM php:8.2-fpm-alpine

# Устанавливаем системные зависимости
# ВАЖНО: В Alpine пакет называется oniguruma-dev, а не libonig-dev (как в Debian/Ubuntu)
RUN apk add --no-cache \
    git curl zip unzip libzip-dev \
    libpng-dev oniguruma-dev libxml2-dev \
    postgresql-dev libpq \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .phpize-deps # Очищаем временные зависимости сборки для уменьшения размера образа

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем конфиги
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY .docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Права на директорию (важно для bind mounts с хост-машины)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]