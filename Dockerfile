FROM composer:2.7 AS composer

FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    wget \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN printf '%s\n' \
'[www]' \
'user = www-data' \
'group = www-data' \
'listen = 127.0.0.1:9000' \
'pm = dynamic' \
'pm.max_children = 20' \
'pm.start_servers = 5' \
'pm.min_spare_servers = 3' \
'pm.max_spare_servers = 10' \
'pm.max_requests = 500' \
'pm.status_path = /fpm-status' \
'catch_workers_output = yes' \
> /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build
RUN composer run-script post-autoload-dump

RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    && chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

RUN rm -f /etc/nginx/sites-enabled/default

RUN printf '%s\n' \
'server {' \
'    listen 0.0.0.0:${PORT};' \
'    server_name _;' \
'    root /var/www/html/public;' \
'    index index.php index.html;' \
'    client_max_body_size 100M;' \
'' \
'    location / {' \
'        try_files $uri $uri/ /index.php?$query_string;' \
'    }' \
'' \
'    location ~ \.php$ {' \
'        try_files $uri =404;' \
'        fastcgi_pass 127.0.0.1:9000;' \
'        fastcgi_index index.php;' \
'        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
'        include fastcgi_params;' \
'    }' \
'}' \
> /etc/nginx/conf.d/default.conf

ENV PORT=10000
EXPOSE 10000

CMD ["sh", "-c", "export PORT=\"${PORT:-10000}\"; php-fpm -D; nginx -g 'daemon off;'"]
