# --- Stage 1: Frontend build with Node ---
FROM node:20 AS frontend
WORKDIR /app

# Install frontend deps
COPY package*.json ./
RUN npm install

# Copy project files
COPY . .

# Build frontend (Vite)
RUN npm run build

# --- Stage 2: Backend with PHP + Composer ---
FROM php:8.3-fpm

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev unzip git curl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy Laravel files
COPY . .

# Copy built frontend assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Install backend dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose app port
EXPOSE 8000 3055

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
