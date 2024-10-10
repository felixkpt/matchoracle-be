# Use a base image with PHP-FPM
FROM php:8.1-fpm

# Install Nginx
# Install required dependencies, including MySQLi
RUN apt-get update && \
    apt-get install -y nginx libonig-dev && \
    docker-php-ext-install mysqli && \
    rm -rf /var/lib/apt/lists/*

# Copy custom Nginx configuration
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy the entrypoint script
COPY ./docker/startup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/startup.sh

RUN mkdir -p /app

# Copy the application code from the outer folder (project root)
COPY . /app
COPY ./docker/.env_sandbox /app/.env

# RUN chown -R www-data: /app

# Use the entrypoint script to start services
ENTRYPOINT ["/usr/local/bin/startup.sh"]
