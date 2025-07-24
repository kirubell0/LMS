# Multi-stage build for Laravel with React/Inertia.js
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY tsconfig.json ./
COPY tailwind.config.ts ./
COPY vite.config.ts ./
COPY eslint.config.js ./
COPY components.json ./

# Install Node dependencies
RUN npm ci --only=production

# Copy source files
COPY resources/ ./resources/
COPY public/ ./public/

# Build assets
RUN npm run build

# PHP stage
FROM php:8.2-fpm-alpine AS php-base

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    curl-dev \
    libxml2-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_sqlite \
        pdo_mysql \
        gd \
        zip \
        mbstring \
        curl \
        xml \
        intl \
        bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy application files
COPY . .
COPY --from=node-builder /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Create required directories
RUN mkdir -p /app/storage/fonts \
    && mkdir -p /app/storage/logs \
    && mkdir -p /app/storage/framework/cache \
    && mkdir -p /app/storage/framework/sessions \
    && mkdir -p /app/storage/framework/views \
    && mkdir -p /run/nginx \
    && mkdir -p /var/log/supervisor
# Install dependencies
# RUN apt-get update && apt-get install -y \
#     imagemagick \
#     libmagickwand-dev \
#     && pecl install imagick \
#     && docker-php-ext-enable imagick
# Configure PHP-FPM
RUN echo '[www]' > /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen = 127.0.0.1:9000' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'user = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'group = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen.owner = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen.group = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_children = 50' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.min_spare_servers = 4' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_spare_servers = 32' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.start_servers = 18' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'clear_env = no' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'php_admin_value[post_max_size] = 35M' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'php_admin_value[upload_max_filesize] = 30M' >> /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8080/health || exit 1

# Start supervisor
CMD ["/start.sh"]