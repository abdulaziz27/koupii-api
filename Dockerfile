# Multi-stage Dockerfile for Laravel with FrankenPHP
# Stage 1: Build dependencies and cache Laravel configurations
FROM composer:2 AS composer-stage

WORKDIR /app

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (production optimized)
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

# Copy application source
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Create Laravel caches with minimal environment
# We'll set APP_KEY during runtime if not provided
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/tmp/dummy.sqlite \
    CACHE_DRIVER=array \
    SESSION_DRIVER=array \
    QUEUE_CONNECTION=sync

# Create dummy database for caching commands
RUN touch /tmp/dummy.sqlite

# Cache Laravel configurations, routes, and views
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Stage 2: Final production image with FrankenPHP
FROM dunglas/frankenphp:latest-php8.3

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    zip \
    unzip \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Set working directory
WORKDIR /app

# Copy application from composer stage
COPY --from=composer-stage /app .

# Create non-root user for security
RUN groupadd -g 1000 www && \
    useradd -u 1000 -ms /bin/bash -g www www

# Set proper permissions for Laravel directories
RUN chown -R www:www /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Create FrankenPHP configuration for worker mode
COPY <<EOF /app/.frankenphp.php
<?php

return [
    'worker' => [
        'file' => '/app/public/index.php',
        'num' => $_ENV['FRANKENPHP_WORKERS'] ?? 4,
    ],
];
EOF

# Create entrypoint script
COPY <<'EOF' /app/entrypoint.sh
#!/bin/bash
set -e

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Wait for database connection if needed
if [ "$DB_CONNECTION" != "sqlite" ]; then
    echo "Waiting for database connection..."
    until php artisan migrate:status > /dev/null 2>&1; do
        echo "Database not ready, waiting 5 seconds..."
        sleep 5
    done
fi

# Run migrations if needed
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction
fi

# Clear and rebuild caches if needed
if [ "$CLEAR_CACHE" = "true" ]; then
    echo "Clearing application caches..."
    php artisan cache:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Fix permissions
chown -R www:www /app/storage /app/bootstrap/cache

# Execute the main command
exec "$@"
EOF

RUN chmod +x /app/entrypoint.sh

# Switch to non-root user
USER www

# Expose port 8080 (FrankenPHP default)
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:8080/api/health || exit 1

# Set entrypoint and default command
ENTRYPOINT ["/app/entrypoint.sh"]

# Start FrankenPHP with worker mode
CMD ["frankenphp", "run", "--config", "/app/.frankenphp.php"]
