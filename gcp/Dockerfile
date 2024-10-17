FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    npm \
    git \
    curl \
    libzip-dev \
    wget \
    nginx \
    git-core \
    openssl \
    libssl-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cloud Sql Proxy
RUN apt-get update \
  && apt-get install --no-install-recommends -y \
    wget \
  ;
# RUN wget https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 -O cloud_sql_proxy
# RUN chmod +x cloud_sql_proxy

# Install extensions
RUN docker-php-ext-install pdo_mysql  zip exif pcntl
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/
RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /run/nginx

COPY gcp/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /app

COPY . /app

COPY gcp/.env_prod /app/.env

RUN cd /app && composer update && composer install --no-dev && php artisan optimize:clear

RUN chown -R www-data: /app

CMD sh /app/gcp/startup.sh