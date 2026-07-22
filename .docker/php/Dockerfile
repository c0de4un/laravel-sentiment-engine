# Используем стабильный Debian-образ (Bookworm)
FROM php:8.2-fpm

# Устанавливаем системные зависимости для сборки расширений
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем стандартные расширения PHP
# Флаг -j$(nproc) ускоряет компиляцию, используя все ядра
RUN docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Устанавливаем Redis через PECL
# (В Debian-образе php-fpm уже есть все базовые инструменты для этого)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем конфигурационные файлы
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY .docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Настраиваем права (важно для bind mounts)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]