#!/bin/bash
set -e

# Copy .env from example if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --force
fi

# Wait for MySQL to be ready (Railway may take a few seconds)
echo "Waiting for database..."
for i in $(seq 1 30); do
    if php artisan db:monitor --databases=mysql 2>/dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Attempt $i/30 - waiting..."
    sleep 2
done

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Seed database (only inserts new, skips existing)
php artisan db:seed --class=ItensSeeder --force 2>/dev/null || true
php artisan db:seed --class=NovosItensSeeder --force 2>/dev/null || true

# Cache for production
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
