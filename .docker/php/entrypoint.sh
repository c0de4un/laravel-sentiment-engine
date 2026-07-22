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

# 2. (Опционально) Магия SQLite, если вдруг переключишь .env на sqlite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_FILE="database/database.sqlite"
    if [ ! -f "$DB_FILE" ]; then
        echo "👉 Creating SQLite database file at $DB_FILE..."
        touch "$DB_FILE"
        chown www-data:www-data "$DB_FILE"
        chmod 775 "$DB_FILE"
    fi
fi

# Запускаем переданный процесс (php-fpm)
exec "$@"