FROM serversideup/php:8.4-fpm-nginx

USER root

WORKDIR /var/www/html

# Copy project files
COPY --chown=www-data:www-data . /var/www/html

# Touch empty .env
RUN touch /var/www/html/.env

# Install Composer dependencies using native PHP 8.4
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Ensure writable directories
RUN mkdir -p /var/www/html/database \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Add startup script to entrypoint.d
RUN mkdir -p /etc/entrypoint.d
COPY --chmod=755 scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-deploy.sh

USER www-data

EXPOSE 8080