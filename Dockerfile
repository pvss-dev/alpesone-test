FROM php:8.4-fpm-alpine AS builder

WORKDIR /var/www/html

RUN apk add --no-cache mysql-client curl zip unzip git

RUN docker-php-ext-install -j$(nproc) pdo_mysql pcntl

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY composer.* ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN cp .env.example .env

RUN php artisan key:generate

RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Production stage
FROM php:8.4-fpm-alpine AS production

WORKDIR /var/www/html

RUN apk add --no-cache mysql-client

RUN docker-php-ext-install -j$(nproc) pdo_mysql pcntl

COPY --from=builder /var/www/html .

RUN rm -f .env

RUN chown -R www-data:www-data /var/www/html

USER www-data
