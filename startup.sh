#!/bin/bash

# ==========================================
# Startup script for Render deployment
# ==========================================

# Exit on any error
set -e

echo "=== Starting astric-academy ==="

# Ensure the SQLite database exists
mkdir -p /var/www/html/database

if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "Creating SQLite database..."
    touch /var/www/html/database/database.sqlite
fi

chown www-data:www-data /var/www/html/database/database.sqlite
chmod 664 /var/www/html/database/database.sqlite

# Set proper permissions on Laravel storage and cache
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Create storage symlink if it doesn't exist
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage symlink..."
    php /var/www/html/artisan storage:link --force 2>/dev/null || true
fi

# Run database migrations (ignore if already up-to-date)
echo "Running database migrations..."
php /var/www/html/artisan migrate --force --isolated 2>/dev/null || \
php /var/www/html/artisan migrate --force 2>/dev/null || true

# Clear and cache config for performance
echo "Optimizing Laravel..."
php /var/www/html/artisan config:cache 2>/dev/null || true
php /var/www/html/artisan route:cache 2>/dev/null || true
php /var/www/html/artisan view:cache 2>/dev/null || true

echo "=== Starting supervisor ==="

# Start supervisor (manages nginx, php-fpm, and queue worker)
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf