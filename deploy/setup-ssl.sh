#!/bin/bash
set -euo pipefail

echo "=== Setting up SSL for api-koupii.magercoding.com ==="

# Install certbot if not installed
if ! command -v certbot &> /dev/null; then
    echo "Installing certbot..."
    apt update
    apt install -y certbot python3-certbot-nginx
fi

# Setup Nginx configuration
echo "Setting up Nginx configuration..."
cp /srv/apps/koupii-api/deploy/nginx-api-koupii.conf /etc/nginx/sites-available/api-koupii.magercoding.com

# Enable site
ln -sf /etc/nginx/sites-available/api-koupii.magercoding.com /etc/nginx/sites-enabled/

# Test Nginx configuration
nginx -t

# Reload Nginx
systemctl reload nginx

# Get SSL certificate
echo "Obtaining SSL certificate..."
certbot --nginx -d api-koupii.magercoding.com --non-interactive --agree-tos --email admin@magercoding.com

# Setup auto-renewal
echo "Setting up auto-renewal..."
(crontab -l 2>/dev/null; echo "0 12 * * * /usr/bin/certbot renew --quiet") | crontab -

echo "=== SSL setup completed! ==="
echo "Certificate will auto-renew daily at 12:00 PM"
