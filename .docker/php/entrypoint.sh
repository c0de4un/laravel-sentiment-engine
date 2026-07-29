#!/bin/sh
set -e

cd /var/www/html

# Создаём директорию для логов воркеров (если ещё нет)
mkdir -p /var/log
chown www-data:www-data /var/log
chmod 755 /var/log

# Права на storage и cache
if [ -d "storage" ]; then
    chown -R www-data:www-data storage
    chmod -R 775 storage
fi

if [ -d "bootstrap/cache" ]; then
    chown -R www-data:www-data bootstrap/cache
    chmod -R 775 bootstrap/cache
fi

# Миграции (убедитесь, что сидер не дублирует записи)
php artisan migrate --seed

# Обновляем автозагрузку Composer (важно для новых классов)
composer dump-autoload --optimize

# Генерация Swagger (если упадёт – игнорируем)
echo "🔄 Generating Swagger documentation..."
php artisan l5-swagger:generate --no-interaction || echo "⚠️ Warning: Swagger generation failed"

exec "$@"