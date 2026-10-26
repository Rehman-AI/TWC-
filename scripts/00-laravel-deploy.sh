#!/bin/sh

echo "=== TWC Content Studio Startup ==="
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

echo "=== Running Migrations & Seeder ==="
php artisan migrate --force --seed || php artisan migrate --force || true
php artisan filament:upgrade || true

echo "=== TWC Content Studio is LIVE! ==="