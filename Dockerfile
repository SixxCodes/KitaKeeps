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

# Debug: show built assets (temporary) to help diagnose missing CSS/JS in production
RUN echo "--- frontend build output ---" && ls -la public/build || true
RUN if [ -f public/build/manifest.json ]; then echo "--- manifest.json ---" && cat public/build/manifest.json || true; else echo "no manifest.json found"; fi

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

# Copy entrypoint script and make executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Set permissions for storage & cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Increase Composer memory limit
ENV COMPOSER_MEMORY_LIMIT=-1

# Default to production unless overridden by Render env vars
ENV APP_ENV=production
ENV APP_DEBUG=false

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader -vvv

# Ensure an environment file exists for artisan commands during build, but do NOT cache config at build time
# (config will be cached at container start by the entrypoint so runtime env vars are used)
RUN if [ -f .env.example ]; then cp .env.example .env; fi \
    && chown www-data:www-data .env || true \
    && php artisan key:generate --force || true

# Expose HTTP port for Render to detect
EXPOSE 80

# Enable Apache rewrite module (needed by Laravel)
RUN a2enmod rewrite headers expires deflate

# Set Apache to serve from Laravel's public directory and set ServerName
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i "s/DirectoryIndex .*/DirectoryIndex index.php index.html/g" /etc/apache2/mods-enabled/dir.conf

# Start Apache in foreground (image default entrypoint uses apache2-foreground)
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
