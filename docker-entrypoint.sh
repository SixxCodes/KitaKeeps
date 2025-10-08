#!/bin/sh
set -e

# If .env doesn't exist, copy example
if [ ! -f /var/www/html/.env ]; then
  if [ -f /var/www/html/.env.example ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    echo "Copied .env.example to .env"
  fi
fi

cd /var/www/html

# Ensure permissions
chown -R www-data:www-data storage bootstrap/cache || true

# Debug: show public/build at runtime
echo "--- runtime public/build ---"
ls -la public/build || echo "public/build not found"
if [ -f public/build/manifest.json ]; then
  echo "--- runtime manifest.json ---"
  cat public/build/manifest.json || true
else
  echo "runtime manifest.json not found"
fi

# Generate APP_KEY if missing
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "" ]; then
  php artisan key:generate --force || true
fi

# Clear and re-cache config/routes/views using runtime env vars
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

if [ "$APP_ENV" != "production" ]; then
  echo "Skipping config:cache in non-production environment"
else
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

# Execute the CMD
exec "$@"
