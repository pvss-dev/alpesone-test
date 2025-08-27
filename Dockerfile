# --- Base PHP Image ---
FROM php:8.4-fpm-alpine

# --- Set Working Directory ---
WORKDIR /var/www/html

# --- Install System Dependencies ---
RUN apk add --no-cache \
        mysql-client \
        curl \
        zip \
        unzip \
        git \
        nodejs \
        npm

# --- Install PHP Extensions ---
RUN docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pcntl

# --- Install Composer ---
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# --- Configure Composer ---
ENV COMPOSER_ALLOW_SUPERUSER=1

# --- Copy application files ---
COPY . .

# --- Configure Git for safe directory ---
RUN git config --global --add safe.directory /var/www/html

# --- Install PHP dependencies ---
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# --- Generate optimized autoloader and discover packages for production ---
RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# --- Create storage directories and set permissions ---
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# --- Health check simples ---
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php artisan route:list > /dev/null || exit 1
