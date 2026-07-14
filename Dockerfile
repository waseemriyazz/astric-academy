# ============================================================
# Stage 1 - PHP Dependencies
# ============================================================
FROM php:8.4-fpm AS php-builder

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    sqlite3 \
    pkg-config \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    libonig-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        mbstring \
        bcmath \
        gd \
        xml \
        zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

# ============================================================
# Stage 2 - Frontend
# ============================================================
FROM node:22 AS frontend-builder

WORKDIR /var/www/html

COPY package*.json ./

RUN npm ci

COPY . .

RUN node -v
RUN npm -v

RUN test -f vite.config.js
RUN test -f package.json
RUN test -d resources
RUN test -d resources/js
RUN test -d resources/css

RUN npm run build

# ============================================================
# Stage 3 - Runtime
# ============================================================
FROM php:8.4-fpm

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    libonig-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        mbstring \
        bcmath \
        gd \
        xml \
        zip \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=php-builder /var/www/html /var/www/html

COPY --from=frontend-builder /var/www/html/public/build ./public/build

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY startup.sh /startup.sh

RUN chmod +x /startup.sh

RUN mkdir -p \
    /var/log/supervisor \
    /var/run/php \
    /data

RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    /data

EXPOSE 10000

CMD ["/startup.sh"]