#!/bin/bash

set -e

echo "=== Starting Astric Academy ==="

cd /var/www/html

# ------------------------------------------------------------
# Create SQLite database
# ------------------------------------------------------------
mkdir -p database

if [ ! -f database/database.sqlite ]; then
    echo "Creating SQLite database..."
    touch database/database.sqlite
fi

# ------------------------------------------------------------
# Create Laravel runtime directories
# ------------------------------------------------------------
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# ------------------------------------------------------------
# Permissions
# ------------------------------------------------------------
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

# ------------------------------------------------------------
# Storage symlink
# ------------------------------------------------------------
php artisan storage:link --force || true

# ------------------------------------------------------------
# Clear old caches
# ------------------------------------------------------------
php artisan optimize:clear || true

# ------------------------------------------------------------
# Run migrations
# ------------------------------------------------------------
php artisan migrate --force || true

# ------------------------------------------------------------
# Run seeders (idempotent - uses firstOrCreate)
# ------------------------------------------------------------
php artisan db:seed --force || true

# ------------------------------------------------------------
# Rebuild caches
# ------------------------------------------------------------
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "=== Starting Supervisor ==="

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf