#!/bin/sh
set -e

# Replace port in nginx config if PORT is provided by Render
TARGET_PORT="${PORT:-80}"
echo "Configuring Nginx to listen on port $TARGET_PORT"
sed -i "s/listen 80;/listen $TARGET_PORT;/g" /etc/nginx/nginx.conf 2>/dev/null || true
sed -i "s/listen \[::\]:80;/listen [::]:$TARGET_PORT;/g" /etc/nginx/nginx.conf 2>/dev/null || true

# Start in-container Redis cache server if no external Redis is specified
if [ -z "$REDIS_URL" ]; then
    echo "Starting in-container Redis cache server..."
    redis-server --daemonize yes --maxmemory 128mb --maxmemory-policy allkeys-lru --bind 127.0.0.1 --port 6379 || echo "Redis started or already running."
fi

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /run/nginx

# Create storage symlink
php artisan storage:link --force || true

# Cache Laravel configurations if APP_KEY is provided
if [ -n "$APP_KEY" ]; then
    echo "Caching Laravel configuration, routes, and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Run database migrations if AUTO_MIGRATE is true (default is true)
if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration encountered an issue or database is starting up."
fi

# Ensure all files created during bootstrap/artisan are owned and writable by www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Starting PHP-FPM..."
php-fpm -D

# Start background keep-alive pinger every 13 minutes (prevents Render 15-minute idle spin-down)
(
    while true; do
        sleep 780
        curl -s -m 20 "https://e-commerce-rased.onrender.com/api/health" >/dev/null 2>&1 || true
    done
) &

echo "Starting Nginx on port $TARGET_PORT..."
exec nginx -g "daemon off;"
