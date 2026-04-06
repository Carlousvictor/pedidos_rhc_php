#!/bin/bash
set -e

# Copy .env from example if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate app key FIRST (before any caching)
php artisan key:generate --force

# Wait for MySQL to be ready
echo "Waiting for database..."
for i in $(seq 1 30); do
    php artisan db:monitor --databases=mysql 2>/dev/null && echo "Database is ready!" && break
    echo "Attempt $i/30 - waiting..."
    sleep 2
done

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Seed database
php artisan db:seed --class=NovosItensSeeder --force 2>/dev/null || true

# Clear and rebuild caches AFTER key is set
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan config:cache
php artisan route:cache

exec "$@"
