#!/bin/bash

# Koupii API VPS Setup Script
# Run this script on a fresh Ubuntu 24.04 VPS to prepare it for deployment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root"
        exit 1
    fi
}

# Update system packages
update_system() {
    log_step "Updating system packages..."
    apt update && apt upgrade -y
    apt install -y curl wget git unzip jq htop vim nano ufw fail2ban
    log_info "System packages updated"
}

# Install Docker
install_docker() {
    log_step "Installing Docker..."

    if command -v docker &> /dev/null; then
        log_info "Docker is already installed"
        return 0
    fi

    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh

    # Add current user to docker group if not root
    if [[ -n "$SUDO_USER" ]]; then
        usermod -aG docker $SUDO_USER
        log_info "Added $SUDO_USER to docker group"
    fi

    # Install Docker Compose
    apt install -y docker-compose-plugin

    systemctl enable docker
    systemctl start docker

    log_info "Docker installed successfully"
}

# Install Nginx
install_nginx() {
    log_step "Installing Nginx..."

    if command -v nginx &> /dev/null; then
        log_info "Nginx is already installed"
        return 0
    fi

    apt install -y nginx
    systemctl enable nginx
    systemctl start nginx

    # Remove default site
    rm -f /etc/nginx/sites-enabled/default

    log_info "Nginx installed successfully"
}

# Install MySQL
install_mysql() {
    log_step "Installing MySQL..."

    if command -v mysql &> /dev/null; then
        log_info "MySQL is already installed"
        return 0
    fi

    # Set root password non-interactively
    MYSQL_ROOT_PASSWORD=$(openssl rand -base64 32)

    debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWORD"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWORD"

    apt install -y mysql-server
    systemctl enable mysql
    systemctl start mysql

    # Save root password
    echo "MySQL root password: $MYSQL_ROOT_PASSWORD" > /root/mysql-root-password.txt
    chmod 600 /root/mysql-root-password.txt

    # Secure installation
    mysql -u root -p$MYSQL_ROOT_PASSWORD << EOF
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

    log_info "MySQL installed and secured"
    log_warn "MySQL root password saved to /root/mysql-root-password.txt"
}

# Install Redis
install_redis() {
    log_step "Installing Redis..."

    if command -v redis-server &> /dev/null; then
        log_info "Redis is already installed"
        return 0
    fi

    apt install -y redis-server

    # Configure Redis
    sed -i 's/^supervised no/supervised systemd/' /etc/redis/redis.conf
    sed -i 's/^# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf
    sed -i 's/^# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf

    systemctl restart redis-server
    systemctl enable redis-server

    log_info "Redis installed and configured"
}

# Setup firewall
setup_firewall() {
    log_step "Configuring firewall..."

    # Reset UFW to defaults
    ufw --force reset

    # Default policies
    ufw default deny incoming
    ufw default allow outgoing

    # Allow SSH (be careful not to lock yourself out)
    ufw allow ssh
    ufw allow 22/tcp

    # Allow HTTP and HTTPS
    ufw allow 80/tcp
    ufw allow 443/tcp

    # Enable firewall
    ufw --force enable

    log_info "Firewall configured"
}

# Setup fail2ban
setup_fail2ban() {
    log_step "Configuring fail2ban..."

    # Create custom jail for nginx
    cat > /etc/fail2ban/jail.local << EOF
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/*error.log
maxretry = 3

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/*error.log
maxretry = 10
EOF

    systemctl enable fail2ban
    systemctl restart fail2ban

    log_info "Fail2ban configured"
}

# Create application directories
create_app_directories() {
    log_step "Creating application directories..."

    mkdir -p /srv/apps/koupii-api/{deploy,storage,bootstrap-cache}
    mkdir -p /srv/apps/koupii-api/storage/{app,framework,logs}
    mkdir -p /srv/apps/koupii-api/storage/framework/{cache,sessions,views}
    mkdir -p /srv/apps/koupii-api/storage/app/public

    # Set proper ownership
    if [[ -n "$SUDO_USER" ]]; then
        chown -R $SUDO_USER:$SUDO_USER /srv/apps/koupii-api
    fi

    chmod -R 775 /srv/apps/koupii-api/storage
    chmod -R 775 /srv/apps/koupii-api/bootstrap-cache

    log_info "Application directories created"
}

# Setup database for Koupii
setup_database() {
    log_step "Setting up Koupii database..."

    # Get MySQL root password
    if [[ ! -f /root/mysql-root-password.txt ]]; then
        log_error "MySQL root password file not found. Please set up MySQL first."
        return 1
    fi

    MYSQL_ROOT_PASSWORD=$(cat /root/mysql-root-password.txt | cut -d' ' -f4)
    DB_PASSWORD=$(openssl rand -base64 32)

    # Create database and user
    mysql -u root -p$MYSQL_ROOT_PASSWORD << EOF
CREATE DATABASE IF NOT EXISTS koupii_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'koupii_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON koupii_production.* TO 'koupii_user'@'localhost';
FLUSH PRIVILEGES;
EOF

    # Save database credentials
    cat > /srv/apps/koupii-api/.env.database << EOF
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koupii_production
DB_USERNAME=koupii_user
DB_PASSWORD=$DB_PASSWORD
EOF

    chmod 600 /srv/apps/koupii-api/.env.database

    if [[ -n "$SUDO_USER" ]]; then
        chown $SUDO_USER:$SUDO_USER /srv/apps/koupii-api/.env.database
    fi

    log_info "Database created and credentials saved to /srv/apps/koupii-api/.env.database"
}

# Install SSL certificate
install_ssl() {
    log_step "Installing SSL certificate..."

    # Install Certbot
    apt install -y certbot python3-certbot-nginx

    log_info "Certbot installed. Run the following command after setting up Nginx:"
    log_info "certbot --nginx -d api-koupii.magercoding.com"
}

# Main setup function
main() {
    log_info "Starting Koupii API VPS setup..."
    log_info "This will install Docker, Nginx, MySQL, Redis, and configure the system"

    check_root
    update_system
    install_docker
    install_nginx
    install_mysql
    install_redis
    setup_firewall
    setup_fail2ban
    create_app_directories
    setup_database
    install_ssl

    log_info "VPS setup completed successfully!"
    echo
    log_info "Next steps:"
    echo "1. Copy your SSH public key for GitHub Actions deployment"
    echo "2. Configure Nginx with the provided configuration file"
    echo "3. Create /srv/apps/koupii-api/.env file using the env.sample template"
    echo "4. Combine database credentials from /srv/apps/koupii-api/.env.database"
    echo "5. Set up SSL certificate: certbot --nginx -d api-koupii.magercoding.com"
    echo "6. Configure GitHub Secrets for CI/CD deployment"
    echo
    log_warn "Important files created:"
    echo "- MySQL root password: /root/mysql-root-password.txt"
    echo "- Database credentials: /srv/apps/koupii-api/.env.database"
    echo
    log_warn "Remember to:"
    echo "- Change default SSH port (optional but recommended)"
    echo "- Set up SSH key authentication"
    echo "- Disable password authentication"
    echo "- Configure automatic security updates"
}

# Run main function
main "$@"
