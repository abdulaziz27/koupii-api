# Deployment Guide - Koupii LMS API

## Environment Configuration

### Required Environment Variables

```bash
APP_NAME="Koupii LMS API"
APP_ENV=production
APP_KEY=<generate-with-php-artisan-key:generate>
APP_DEBUG=false
APP_URL=https://api-koupii.magercoding.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koupii_api
DB_USERNAME=koupii_user
DB_PASSWORD=<your-db-password>

# L5 Swagger Configuration
L5_SWAGGER_USE_ABSOLUTE_PATH=false
L5_FORMAT_TO_USE_FOR_DOCS=json
L5_SWAGGER_BASE_PATH=
```

## Deployment Steps

1. **Pull latest code**
   ```bash
   cd /srv/apps/koupii-api/releases/current
   git pull origin main
   ```

2. **Install dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Set permissions**
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

4. **Generate L5 Swagger documentation**
   ```bash
   php artisan l5-swagger:generate
   ```

5. **Clear caches**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

6. **Reload services**
   ```bash
   systemctl reload nginx
   systemctl reload php8.3-fpm
   ```

## Important Files

- `app/Swagger/OpenApiSpec.php` - Contains OpenAPI annotations (REQUIRED)
- `config/l5-swagger.php` - L5 Swagger configuration
- `resources/views/vendor/l5-swagger/index.blade.php` - Swagger UI template

## Troubleshooting

### L5 Swagger Issues
- Ensure `app/Swagger/OpenApiSpec.php` exists and contains `@OA\Info` annotations
- Check that `use_absolute_path` is set to `false` in config
- Verify route `/docs` returns JSON correctly

### Nginx Issues
- Ensure no conflicting directories exist in `public/`
- Check that all requests are properly routed to `index.php`
- Verify SSL certificates are valid

## API Endpoints

- `GET /` - API root (JSON response)
- `GET /health` - Health check
- `GET /api/documentation` - Swagger UI
- `GET /docs` - OpenAPI JSON specification
