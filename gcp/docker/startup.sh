cd /app

sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

nginx