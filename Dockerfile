FROM richarvey/nginx-php-fpm:3.1.6

# Set working directory to webroot
WORKDIR /var/www/html

# Copy all project files directly into /var/www/html
COPY . /var/www/html

# Set permissions for scripts
RUN chmod -R 755 /var/www/html/scripts

# Image configuration
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel configuration
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Install all composer dependencies during build so vendor/autoload.php is always present
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set write permissions for storage, cache, and database
RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

CMD ["/start.sh"]