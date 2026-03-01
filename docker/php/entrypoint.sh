#!/bin/sh
set -e
cd /var/www/html

# If vendor missing (e.g. bind mount), install deps
if [ ! -d vendor/ ] || [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

# Build frontend if not present
if [ ! -f public/build/manifest.json ] && [ -f package.json ]; then
    npm ci && npm run build || true
fi

# Laravel setup
php artisan config:clear || true
php artisan storage:link 2>/dev/null || true

exec php-fpm
