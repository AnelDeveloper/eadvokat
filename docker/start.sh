#!/bin/sh
set -e
cd /app

mkdir -p storage/app/private storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Hourly pruning of expired live-demo offices (php artisan demo:prune).
php artisan schedule:work >/dev/stdout 2>&1 &

exec frankenphp php-server --root public --listen ":${PORT:-8080}"
