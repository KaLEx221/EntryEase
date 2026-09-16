# =========================
# Stage 1: Composer
# =========================
FROM composer:2.7 AS composer

# =========================
# Stage 2: Laravel + Nginx
# =========================
FROM php:8.3-fpm

# =========================
# Install System Dependencies
# =========================
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

# =========================
# Install PHP Extensions
# =========================
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

# =========================
# Install Redis PHP Extension
# =========================
RUN pecl install redis \
    && docker-php-ext-enable redis

# =========================
# Install Composer
# =========================
COPY --from=composer /usr/bin/composer /usr/bin/composer

# =========================
# PHP-FPM Configuration
# =========================
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

# =========================
# Working Directory
# =========================
WORKDIR /var/www/html

# =========================
# Composer Dependencies
# =========================
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# =========================
# Node Dependencies
# =========================
COPY package.json package-lock.json ./

RUN npm ci

# =========================
# Copy Application
# =========================
COPY . .

# =========================
# Build Frontend
# =========================
RUN npm run build

# =========================
# Laravel Autoload
# =========================
RUN composer dump-autoload --optimize

# =========================
# Laravel Permissions
# =========================
RUN mkdir -p /var/www/html/storage/logs \
    && touch /var/www/html/storage/logs/laravel.log \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod 664 /var/www/html/storage/logs/laravel.log
# =========================
# Nginx Configuration
# =========================
RUN rm -f /etc/nginx/sites-enabled/default

RUN printf '%s\n' \
'server {' \
'    listen 0.0.0.0:10000;' \
'    server_name _;' \
'    root /var/www/html/public;' \
'    index index.php index.html;' \
'    client_max_body_size 100M;' \
'    proxy_connect_timeout 120s;' \
'    proxy_send_timeout 120s;' \
'    proxy_read_timeout 120s;' \
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
'        fastcgi_connect_timeout 120s;' \
'        fastcgi_send_timeout 120s;' \
'        fastcgi_read_timeout 120s;' \
'        include fastcgi_params;' \
'    }' \
'}' \
> /etc/nginx/conf.d/default.conf

# =========================
# Render Port
# =========================
ENV PORT=10000

EXPOSE 10000

# =========================
# Start Laravel + Nginx
# =========================
CMD ["sh", "-c", "\
export PORT=\"${PORT:-10000}\"; \
sed -i \"s/0.0.0.0:10000/0.0.0.0:${PORT}/\" /etc/nginx/conf.d/default.conf; \
echo '===== NGINX CONFIG ====='; \
cat /etc/nginx/conf.d/default.conf; \
echo '===== NGINX TEST ====='; \
nginx -t; \
echo '===== START PHP-FPM ====='; \
php-fpm -D; \
sleep 2; \
echo '===== CLEAR LARAVEL CONFIG ====='; \
php artisan config:clear; \
echo '===== RUN DATABASE MIGRATIONS ====='; \
php artisan migrate --force; \
echo '===== START NGINX ====='; \
nginx -g 'daemon off;' \
"]