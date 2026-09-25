# Stage 1: Build frontend / Vite assets
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Production PHP-FPM + Nginx Container
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    bash \
    dos2unix

# Configure and install PHP extensions required by Laravel & Supabase PostgreSQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        pdo_mysql \
        gd \
        zip \
        bcmath \
        opcache \
        mbstring

# Copy OPcache production configuration
COPY docker/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# Copy Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy Vite build artifacts from stage 1
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-req=php

# Copy Nginx configuration directly to master config
RUN rm -rf /etc/nginx/http.d/* /etc/nginx/conf.d/*
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy and prepare entrypoint script (ensure Linux LF line endings)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN dos2unix /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Set directory permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
