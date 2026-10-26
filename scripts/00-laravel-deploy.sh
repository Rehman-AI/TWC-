#!/usr/bin/env bash
echo "=== Running Composer Install ==="
composer install --no-dev --working-dir=/var/www/html --optimize-autoloader

echo "=== Preparing Database & Permissions ==="
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

echo "=== Running Migrations & Seeder ==="
php artisan migrate --force --seed

echo "=== Optimizing Cache ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Laravel Ready! ==="