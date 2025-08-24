#!/bin/sh

php artisan key:gen

php artisan storage:link

if [ "$MIGRATE_FRESH" = "true" ]; then
    php artisan migrate:fresh --seed --force
else
    php artisan migrate --force
fi

php artisan serve --host=0.0.0.0 --port=8000
