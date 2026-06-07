#!/bin/bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache
php-fpm -D
nginx -g "daemon off;"
