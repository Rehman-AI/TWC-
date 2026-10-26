#!/usr/bin/env bash
echo "=== Preparing Database & Permissions ==="
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

echo "=== Running Migrations & Seeder ==="
php artisan migrate --force --seed || php artisan migrate --force || true

echo "=== Optimizing Cache ==="
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "=== TWC Content Studio is Ready! ==="