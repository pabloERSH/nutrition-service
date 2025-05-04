#!/bin/sh
set -e

php artisan migrate --force || echo "Миграции не удались"
crond
exec php-fpm
