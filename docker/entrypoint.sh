#!/bin/sh
set -e

echo "=== Runtime initialization ==="

# Cache configuration at runtime (when APP_KEY is available via env)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Auto-seed if database is empty (fresh deploy)
if [ "$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null)" = "0" ]; then
    echo "=== Empty database detected, running seeders ==="
    php artisan db:seed --force
fi

echo "=== Starting PHP-FPM ==="
exec php-fpm
