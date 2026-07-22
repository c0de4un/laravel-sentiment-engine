# Используем Alpine для минимального размера и безопасности
FROM php:8.2-fpm-alpine

# Устанавливаем системные зависимости и расширения PHP
# Обратите внимание: добавлены postgresql-dev, libpq, и redis через pecl
RUN apk add --no-cache \
    git curl zip unzip libzip-dev \
    libpng-dev libonig-dev libxml2-dev \
    postgresql-dev libpq \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .phpize-deps # Очищаем зависимости сборки для уменьшения размера

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем конфиги
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY .docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Права на директорию (важно для bind mounts в Windows/Mac)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]