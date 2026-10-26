#!/usr/bin/env bash

echo "=== Preparing Database & Permissions ==="
mkdir -p /var/www/html/database \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache

if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

echo "=== Running Package Discovery & Filament Setup ==="
php artisan package:discover --ansi || true
php artisan filament:upgrade || true

echo "=== Running Migrations & Seeder ==="
php artisan migrate --force --seed || php artisan migrate --force || true

echo "=== Optimizing Cache ==="
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "=== TWC Content Studio is LIVE! ==="