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

# Skip config cache at build time - will be done at runtime with proper .env

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
COPY <<'EOF' /app/.frankenphp.php
<?php

return [
    'worker' => [
        'file' => '/app/public/index.php',
        'num' => (int)($_ENV['FRANKENPHP_WORKERS'] ?? 4),
    ],
];
EOF

# Create entrypoint script
COPY <<'EOF' /app/entrypoint.sh
#!/bin/bash
set -e

# Generate APP_KEY if not set and .env is writable
if [ -z "$APP_KEY" ] && [ -w "/app/.env" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --no-interaction
elif [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY not set and .env is not writable"
fi

# Wait for database connection if needed (with timeout)
if [ "$DB_CONNECTION" != "sqlite" ] && [ "$DB_CONNECTION" != "array" ]; then
    echo "Waiting for database connection..."
    timeout=60
    count=0
    until php artisan migrate:status > /dev/null 2>&1; do
        if [ $count -ge $timeout ]; then
            echo "Database connection timeout after ${timeout}s, continuing anyway..."
            break
        fi
        echo "Database not ready, waiting 2 seconds... ($count/$timeout)"
        sleep 2
        count=$((count + 1))
    done
fi

# Run migrations if needed (with error handling)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction || echo "Migration failed, continuing..."
fi

# Clear and rebuild caches if needed
if [ "$CLEAR_CACHE" = "true" ]; then
    echo "Clearing application caches..."
    php artisan cache:clear || true
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Fix permissions (if directories exist and are writable)
if [ -d "/app/storage" ] && [ -w "/app/storage" ]; then
    chown -R www:www /app/storage 2>/dev/null || true
fi
if [ -d "/app/bootstrap/cache" ] && [ -w "/app/bootstrap/cache" ]; then
    chown -R www:www /app/bootstrap/cache 2>/dev/null || true
fi

# Debug: Show environment and config
echo "=== Container Debug Info ==="
echo "APP_ENV: $APP_ENV"
echo "APP_KEY: ${APP_KEY:0:10}..." # Show only first 10 chars for security
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_HOST: $DB_HOST"
echo "Starting FrankenPHP on port 8080..."
echo "FrankenPHP config:"
cat /app/.frankenphp.php || echo "No FrankenPHP config found"

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

# Start FrankenPHP with worker mode on explicit port
CMD ["frankenphp", "run", "--listen", ":8080", "--config", "/app/.frankenphp.php"]
