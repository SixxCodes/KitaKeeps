# =========================
# Stage 1 - Build Frontend (Vite)
# =========================
FROM node:18 AS frontend
WORKDIR /app

# Copy only necessary files for npm install
COPY package*.json ./ 
RUN npm install

# Copy frontend files and build
COPY resources/js ./resources/js
COPY resources/css ./resources/css
COPY vite.config.js ./
RUN npm run build

# =========================
# Stage 2 - Backend (Laravel + PHP + Composer)
# =========================
FROM php:8.2-fpm

# Install system dependencies and PHP extensions (before composer)
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev zip \
    libxml2-dev zlib1g-dev libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath intl xml

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy Laravel app
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Set permissions for storage & cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Install PHP dependencies (with memory limit)
RUN php -d memory_limit=-1 composer install --no-dev --optimize-autoloader -vvv

# Optimize Laravel caches
RUN php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
