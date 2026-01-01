# Build stage: Install Composer dependencies and publish assets
FROM php:8.2-cli-alpine AS composer-stage

# Install composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Define build argument for environment (default: production)
ARG APP_ENV=production

WORKDIR /app
# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./
# Install dependencies (conditionally exclude dev dependencies for production)
RUN if [ "$APP_ENV" = "production" ]; then \
        composer install --no-dev --no-scripts --no-autoloader --prefer-dist; \
    else \
        composer install --no-scripts --no-autoloader --prefer-dist; \
    fi
# Copy application files needed for autoload generation
COPY . .
# Generate optimized autoload files
RUN if [ "$APP_ENV" = "production" ]; then \
        composer dump-autoload --optimize --no-dev; \
    else \
        composer dump-autoload --optimize; \
    fi

# Build stage: Build frontend assets
FROM node:20-alpine AS node-stage
WORKDIR /app
# Install build dependencies for native npm packages (esbuild, lightningcss, etc.)
RUN apk add --no-cache python3 make g++
# Copy package files first to leverage Docker cache
COPY package*.json package-lock.json ./
# Install node dependencies
RUN npm ci
# Copy source code for building assets
COPY . .
COPY --from=composer-stage /app/vendor ./vendor
# Build production assets
RUN npm run build

# Final stage: Create runtime image
FROM php:8.2-fpm-alpine
# Add metadata labels
LABEL maintainer="Ahmad Alangkibar <alangkibar@localplace.id>"
LABEL version="1.0"
LABEL description="CMS Backend API Local Place"

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    tzdata \
    curl \
    ca-certificates \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    mbstring \
    opcache \
    pcntl \
    && rm -rf /var/cache/apk/*

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Set timezone to Asia/Jakarta
ENV TZ=Asia/Jakarta
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Create non-root user and group
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=appuser:appgroup . .
COPY --from=composer-stage --chown=appuser:appgroup /app/vendor ./vendor
COPY --from=node-stage --chown=appuser:appgroup /app/public/build ./public/build

# Configure PHP-FPM
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Configure OPcache for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Configure PHP-FPM to run as appuser
RUN sed -i 's/user = www-data/user = appuser/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = appgroup/g' /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
COPY ./.docker/nginx.conf /etc/nginx/nginx.conf
COPY ./.docker/default.conf /etc/nginx/http.d/default.conf

# Configure Supervisor
COPY ./.docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set proper permissions
RUN chown -R appuser:appgroup /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && chown -R appuser:appgroup /var/log/supervisor \
    && mkdir -p /run/nginx \
    && chown -R appuser:appgroup /run/nginx \
    && chown -R appuser:appgroup /var/lib/nginx \
    && chown -R appuser:appgroup /var/log/nginx

# Publish Livewire assets to public directory
RUN php artisan vendor:publish --tag=livewire:assets --ansi --force || true

# Create symlink for Flux assets
RUN ln -sf ../vendor/livewire/flux/dist /var/www/html/public/flux

# Expose port 8080 (nginx)
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8080/health || exit 1

# Run supervisor to manage nginx and php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
