# Koupii API Production Deployment Guide

This guide provides complete instructions for deploying the Koupii API to a production VPS using Docker Compose, FrankenPHP, and GitHub Actions CI/CD.

## 🏗️ Architecture Overview

```
Internet → Cloudflare → Nginx (VPS) → FrankenPHP Container → Laravel App
                                   ↳ Queue Worker Container
                                   ↳ Scheduler Container
```

## 📋 Prerequisites

### VPS Requirements

-   Ubuntu 24.04 LTS
-   Minimum 2GB RAM, 2 CPU cores
-   20GB+ storage
-   Root or sudo access

### Domain Setup

-   Domain: `api-koupii.magercoding.com`
-   DNS A record pointing to VPS IP
-   (Optional) Cloudflare for SSL/CDN

### GitHub Repository Setup

-   Repository with push access
-   GitHub Actions enabled
-   Container registry access

## 🚀 Initial VPS Setup

### 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip jq
```

### 2. Install Docker & Docker Compose

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo apt install -y docker-compose-plugin

# Verify installation
docker --version
docker compose version
```

### 3. Install Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 4. Create Application Directory Structure

```bash
sudo mkdir -p /srv/apps/koupii-api/{deploy,storage,bootstrap-cache}
sudo chown -R $USER:$USER /srv/apps/koupii-api
cd /srv/apps/koupii-api
```

### 5. Setup Nginx Configuration

```bash
# Copy the nginx configuration
sudo cp /path/to/deploy/nginx-koupii-api.conf /etc/nginx/sites-available/koupii-api

# Enable the site
sudo ln -s /etc/nginx/sites-available/koupii-api /etc/nginx/sites-enabled/

# Remove default site
sudo rm -f /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### 6. Setup Environment File

```bash
cd /srv/apps/koupii-api

# Copy environment template
cp /path/to/deploy/env.sample .env

# Edit with production values
nano .env
```

**Important Environment Variables:**

```bash
# Generate a secure APP_KEY
php artisan key:generate --show

# Database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=db_koupii
DB_USERNAME=koupii_user
DB_PASSWORD=

# Production URL
APP_URL=https://api-koupii.magercoding.com

# CORS for frontend
CORS_ALLOWED_ORIGINS=https://koupii.magercoding.com

# Stripe production keys (later)

```

### 7. Setup Database (MySQL)

```bash
# Install MySQL
sudo apt install -y mysql-server

# Secure installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p << EOF
CREATE DATABASE koupii_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'koupii_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON koupii_production.* TO 'koupii_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF
```

### 8. Setup Redis (Optional but Recommended)

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
# Set: supervised systemd
# Set: maxmemory 256mb
# Set: maxmemory-policy allkeys-lru

# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

## 🔧 GitHub Actions Setup

### 1. Required Secrets

Add these secrets to your GitHub repository (`Settings > Secrets and variables > Actions`):

```bash
VPS_HOST=your.vps.ip.address
VPS_USER=your_username  # usually 'root' or your sudo user
VPS_SSH_KEY=-----BEGIN OPENSSH PRIVATE KEY-----
your_private_key_content
-----END OPENSSH PRIVATE KEY-----
VPS_PORT=22  # Optional, defaults to 22
```

### 2. Generate SSH Key Pair

```bash
# On your local machine
ssh-keygen -t ed25519 -C "koupii-deploy@github-actions" -f ~/.ssh/koupii-deploy

# Copy public key to VPS
ssh-copy-id -i ~/.ssh/koupii-deploy.pub user@your.vps.ip

# Add private key content to GitHub Secrets as VPS_SSH_KEY
cat ~/.ssh/koupii-deploy
```

### 3. Test SSH Connection

```bash
ssh -i ~/.ssh/koupii-deploy user@your.vps.ip
```

## 🚀 Deployment Process

### Manual Deployment (First Time)

```bash
cd /srv/apps/koupii-api

# Copy deployment files
cp /path/to/deploy/docker-compose.yml .
cp /path/to/deploy/deploy.sh deploy/
chmod +x deploy/deploy.sh

# Set environment variables
export GITHUB_SHA=latest
export REGISTRY_USERNAME=your_github_username
export REGISTRY_TOKEN=your_github_token

# Run deployment
sudo -E ./deploy/deploy.sh
```

### Automated Deployment

-   Push to `main` branch triggers automatic deployment
-   Monitor deployment in GitHub Actions tab
-   Check deployment status at: `https://api-koupii.magercoding.com/api/health`

## 📊 Monitoring & Maintenance

### Health Checks

```bash
# Application health
curl -f https://api-koupii.magercoding.com/api/health

# Container status
docker compose ps

# Container logs
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f scheduler
```

### Log Management

```bash
# Application logs
tail -f /srv/apps/koupii-api/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/koupii-api.access.log
tail -f /var/log/nginx/koupii-api.error.log

# Container logs
docker compose logs --tail=100 -f
```

### Database Maintenance

```bash
# Run migrations
docker compose exec app php artisan migrate --force

# Clear caches
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Queue management
docker compose exec app php artisan queue:restart
```

## 🔒 Security Considerations

### SSL/TLS Setup (with Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d api-koupii.magercoding.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Firewall Configuration

```bash
# Enable UFW
sudo ufw enable

# Allow necessary ports
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS

# Block direct access to application port
sudo ufw deny 8081/tcp
```

### Regular Updates

```bash
# System updates
sudo apt update && sudo apt upgrade -y

# Docker updates
sudo apt update docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Container updates happen automatically via CI/CD
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Container Won't Start

```bash
# Check logs
docker compose logs app

# Check environment file
cat .env

# Verify database connection
docker compose exec app php artisan migrate:status
```

#### 2. Health Check Fails

```bash
# Check application logs
docker compose logs app

# Test internal health endpoint
docker compose exec app curl -f http://localhost:8080/api/health

# Check container networking
docker network ls
docker network inspect koupii-network
```

#### 3. Permission Issues

```bash
# Fix storage permissions
sudo chown -R 1000:1000 /srv/apps/koupii-api/storage
sudo chmod -R 775 /srv/apps/koupii-api/storage
```

#### 4. Queue Not Processing

```bash
# Check queue worker
docker compose logs queue

# Restart queue worker
docker compose restart queue

# Check failed jobs
docker compose exec app php artisan queue:failed
```

### Emergency Rollback

```bash
# Find previous image
docker images | grep koupii-api

# Update docker-compose.yml with previous tag
export GITHUB_SHA=previous_commit_sha
docker compose up -d --remove-orphans
```

## 📈 Performance Optimization

### FrankenPHP Workers

```bash
# Adjust worker count based on CPU cores
# In .env file:
FRANKENPHP_WORKERS=4  # 2x CPU cores recommended
```

### Database Optimization

```bash
# MySQL configuration for Laravel
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add:
[mysqld]
innodb_buffer_pool_size = 512M
innodb_log_file_size = 128M
query_cache_type = 1
query_cache_size = 64M
```

### Redis Configuration

```bash
# Optimize Redis for Laravel
sudo nano /etc/redis/redis.conf

# Set:
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## 📞 Support

For deployment issues:

1. Check application logs: `docker compose logs app`
2. Check Nginx logs: `/var/log/nginx/koupii-api.error.log`
3. Verify environment configuration: `cat .env`
4. Test health endpoint: `curl -f https://api-koupii.magercoding.com/api/health`

---

**Last Updated:** $(date)  
**Version:** 1.0.0
