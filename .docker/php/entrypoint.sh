#!/bin/sh
set -e

cd /var/www/html

# 1. Fix permissions for Laravel (critical for bind mounts from host)
if [ -d "storage" ]; then
    chown -R www-data:www-data storage
    chmod -R 775 storage
fi

if [ -d "bootstrap/cache" ]; then
    chown -R www-data:www-data bootstrap/cache
    chmod -R 775 bootstrap/cache
fi

# 2. Generate Swagger documentation automatically on startup
# This ensures the latest OpenAPI annotations are compiled without manual intervention.
# '|| true' prevents the container from crashing if generation fails for some reason,
# allowing php-fpm to start anyway (errors will be visible in docker logs).
echo "🔄 Generating Swagger documentation..."
php artisan l5-swagger:generate --no-interaction || echo "⚠️ Warning: Swagger generation failed, but continuing startup..."

# 3. Execute the passed process (e.g., php-fpm)
exec "$@"