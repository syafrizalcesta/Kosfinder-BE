#!/bin/bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force
php-fpm -D
nginx -g "daemon off;"
