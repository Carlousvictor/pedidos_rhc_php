#!/bin/bash
set -e

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Seed database (only if tables are empty)
php artisan db:seed --class=ItensSeeder --force 2>/dev/null || true
php artisan db:seed --class=NovosItensSeeder --force 2>/dev/null || true

# Cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
