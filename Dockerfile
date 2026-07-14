# ============================================================
# Stage 1: Build — Install PHP deps & compile frontend assets
# ============================================================
FROM php:8.3-cli AS build

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        mbstring \
        zip \
        gd \
        xml \
        bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for dependency caching)
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev, no scripts, no autoloader yet)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy the rest of the application
COPY . .

# Generate autoloader and run post-autoload-dump scripts
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist

# Install Node.js and build frontend
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install --ignore-scripts \
    && npm run build \
    && apt-get remove -y nodejs \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ============================================================
# Stage 2: Runtime — PHP-FPM + Nginx
# ============================================================
FROM php:8.3-fpm AS runtime

# Install Nginx and required PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        mbstring \
        zip \
        gd \
        xml \
        bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Create necessary directories
RUN mkdir -p /var/www/html \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/run/php \
    && mkdir -p /data

# Copy application from build stage
COPY --from=build /var/www/html /var/www/html

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy Supervisor configuration
COPY supervisord.conf /etc/supervisor/supervisord.conf

# Copy startup script
COPY startup.sh /startup.sh

# Make startup script executable
RUN chmod +x /startup.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /data

# Expose port 10000 (Render expects this port)
EXPOSE 10000

# Remove default nginx site
RUN rm -f /etc/nginx/sites-enabled/default

# Start supervisor (which manages nginx, php-fpm, and queue worker)
CMD ["/startup.sh"]