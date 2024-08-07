#!/bin/sh

# cd /var/www

php artisan migrate:fresh --seed
php artisan cache:clear
php artisan route:cache
cd /app

# php artisan optimize:clear

# php artisan key:generate

sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

#Start cloud_sql_proxy at Docker startup
#"sh", "-c", "./cloud_sql_proxy -instances=$CLOUD_SQL_CONNECTION_NAME=tcp:0.0.0.0:3306 & yarn start"
#./cloud_sql_proxy  -instances=my-project-1508150490957:us-central1:cih-production-db-1-mysql=tcp:0.0.0.0:3306 -credential_file=/app/docker/key_file.json

php-fpm -D

# while ! nc -w 1 -z 127.0.0.1 9000; do sleep 0.1; done;

nginx
