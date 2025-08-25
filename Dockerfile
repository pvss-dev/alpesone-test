# --- Base Image Setup ---
FROM php:8.4-fpm

# --- Set Working Directory ---
WORKDIR /var/www/html

# --- Install System Dependencies ---
RUN apt-get update \
    && apt-get install -y \
        git \
        zip \
        unzip \
        curl \
        wget \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# --- Install PHP Extensions ---
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        zip \
        intl \
        xml \

# --- Install Node.js and NPM ---
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs

# --- Install Composer ---
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# --- Configure Composer ---
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# --- Copy application files ---
COPY . /var/www/html

# --- Install dependencies ---
RUN composer install --optimize-autoloader --no-dev \
    && npm ci --only=production \
    && npm run build

# --- Set proper permissions ---
RUN chown -R www-data:www-data /var/www/html
