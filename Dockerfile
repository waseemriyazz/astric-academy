# ============================================================
# Stage 1: Build
# ============================================================
FROM php:8.4-cli AS build

# Install system packages
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
    zip \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        mbstring \
        bcmath \
        gd \
        xml \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy dependency manifests
COPY composer.json composer.lock ./
COPY package.json package-lock.json* ./

# Install dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

RUN npm install

# Copy application
COPY . .

# ---------- DEBUG ----------
RUN pwd
RUN ls -la
RUN ls -la resources || true
RUN ls -la resources/js || true
RUN ls -la resources/css || true
RUN ls -la public || true
RUN cat package.json
RUN cat vite.config.js
# ---------------------------

# Build frontend
RUN npm run build

# ============================================================
# Stage 2: Runtime
# ============================================================
FROM php:8.4-fpm

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
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY startup.sh /startup.sh

RUN chmod +x /startup.sh

RUN mkdir -p \
    /var/log/supervisor \
    /var/run/php \
    /data

RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /data

EXPOSE 10000

CMD ["/startup.sh"]