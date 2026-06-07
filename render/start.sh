#!/bin/bash
mkdir -p /tmp/views
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force
php artisan view:clear
php artisan cache:clear
php artisan config:clear
chmod -R 777 /var/www/storage
chmod -R 777 /var/www/bootstrap/cache
php-fpm -D
nginx -g "daemon off;"