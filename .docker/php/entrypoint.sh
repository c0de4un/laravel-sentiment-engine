#!/bin/sh
set -e

cd /var/www/html

# 1. Фиксим права для Laravel (критично при bind mounts с хост-машины)
if [ -d "storage" ]; then
    chown -R www-data:www-data storage
    chmod -R 775 storage
fi

if [ -d "bootstrap/cache" ]; then
    chown -R www-data:www-data bootstrap/cache
    chmod -R 775 bootstrap/cache
fi

# Запускаем переданный процесс (php-fpm)
exec "$@"