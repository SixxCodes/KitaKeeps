# =========================
# Stage 1 - Frontend Build (Vite + Vue)
# =========================
FROM node:18 AS frontend
WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy frontend source and Vite config
COPY resources/js ./resources/js
COPY resources/css ./resources/css
COPY vite.config.js ./

# Build production assets
RUN npm run build

# =========================
# Stage 2 - Backend (Laravel + PHP + Composer)
# =========================
FROM php:8.2-apache

# Install system dependencies including libcurl dev headers
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip libpq-dev libonig-dev libzip-dev zip \
    libxml2-dev zlib1g-dev libicu-dev g++ libcurl4-openssl-dev \
    # Libraries required to build and enable the GD extension
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions needed by Laravel
# Configure and install GD first (requires the dev libraries above), then other extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install \
    pdo_mysql mbstring zip bcmath intl xml opcache fileinfo curl

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory (Apache default document root)
WORKDIR /var/www/html

# Copy Laravel app files
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Set permissions for storage & cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Increase Composer memory limit
ENV COMPOSER_MEMORY_LIMIT=-1

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader -vvv

# Ensure an environment file exists for artisan commands during build, then optimize caches
RUN if [ -f .env.example ]; then cp .env.example .env; fi \
    && chown www-data:www-data .env || true \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose HTTP port for Render to detect
EXPOSE 80

# Enable Apache rewrite module (needed by Laravel)
RUN a2enmod rewrite headers expires deflate

# Start Apache in foreground (image default entrypoint uses apache2-foreground)
CMD ["apache2-foreground"]
