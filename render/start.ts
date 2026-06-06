#!/bin/bash
php artisan migrate --force
php-fpm -D
nginx -g "daemon off;"