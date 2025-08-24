#!/bin/bash

# Koupii API Deployment Script

set -e

# Configuration
APP_DIR="/srv/apps/koupii-api"
COMPOSE_FILE="$APP_DIR/docker-compose.yml"
ENV_FILE="$APP_DIR/.env"
HEALTH_URL="http://127.0.0.1:8081/api/health"
MAX_HEALTH_RETRIES=30
HEALTH_RETRY_INTERVAL=10

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
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

# Check if running as root or with sudo
check_permissions() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root or with sudo"
        exit 1
    fi
}

# Ensure required files exist
check_prerequisites() {
    log_info "Checking prerequisites..."

    if [[ ! -f "$COMPOSE_FILE" ]]; then
        log_error "Docker Compose file not found: $COMPOSE_FILE"
        exit 1
    fi

    if [[ ! -f "$ENV_FILE" ]]; then
        log_error "Environment file not found: $ENV_FILE"
        log_error "Please create $ENV_FILE with production configuration"
        exit 1
    fi

    # Check if Docker and Docker Compose are installed
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed"
        exit 1
    fi

    if ! command -v docker compose &> /dev/null; then
        log_error "Docker Compose is not installed"
        exit 1
    fi

    log_info "Prerequisites check passed"
}

# Login to GitHub Container Registry
login_ghcr() {
    log_info "Logging into GitHub Container Registry..."

    if [[ -z "$REGISTRY_USERNAME" ]] || [[ -z "$REGISTRY_TOKEN" ]]; then
        log_warn "REGISTRY_USERNAME or REGISTRY_TOKEN not set, skipping GHCR login"
        log_warn "Make sure the image is public or login manually"
        return 0
    fi

    echo "$REGISTRY_TOKEN" | docker login ghcr.io -u "$REGISTRY_USERNAME" --password-stdin
    log_info "Successfully logged into GHCR"
}

# Get current running image tag for rollback
get_current_image() {
    docker compose -f "$COMPOSE_FILE" ps --format json app 2>/dev/null | \
        jq -r '.Image' 2>/dev/null || echo ""
}

# Pull latest images
pull_images() {
    log_info "Pulling latest Docker images..."

    cd "$APP_DIR"
    if ! docker compose pull; then
        log_error "Failed to pull Docker images"
        exit 1
    fi

    log_info "Successfully pulled images"
}

# Create required directories
create_directories() {
    log_info "Creating required directories..."

    mkdir -p "$APP_DIR/storage/app/public"
    mkdir -p "$APP_DIR/storage/framework/cache"
    mkdir -p "$APP_DIR/storage/framework/sessions"
    mkdir -p "$APP_DIR/storage/framework/views"
    mkdir -p "$APP_DIR/storage/logs"
    mkdir -p "$APP_DIR/bootstrap-cache"

    # Set proper permissions
    chown -R 1000:1000 "$APP_DIR/storage" "$APP_DIR/bootstrap-cache"
    chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap-cache"

    log_info "Directories created and permissions set"
}

# Deploy application
deploy() {
    log_info "Starting deployment..."

    cd "$APP_DIR"

    # Start services
    if ! docker compose up -d --remove-orphans; then
        log_error "Failed to start Docker containers"
        exit 1
    fi

    log_info "Docker containers started"
}

# Health check
health_check() {
    log_info "Performing health check..."

    local retries=0
    while [[ $retries -lt $MAX_HEALTH_RETRIES ]]; do
        if curl -fsS "$HEALTH_URL" > /dev/null 2>&1; then
            log_info "Health check passed"
            return 0
        fi

        retries=$((retries + 1))
        log_warn "Health check failed (attempt $retries/$MAX_HEALTH_RETRIES), retrying in ${HEALTH_RETRY_INTERVAL}s..."
        sleep $HEALTH_RETRY_INTERVAL
    done

    log_error "Health check failed after $MAX_HEALTH_RETRIES attempts"
    return 1
}

# Rollback to previous version
rollback() {
    local previous_image="$1"

    if [[ -z "$previous_image" ]]; then
        log_error "No previous image available for rollback"
        return 1
    fi

    log_warn "Rolling back to previous image: $previous_image"

    cd "$APP_DIR"

    # Set the previous image tag
    export GITHUB_SHA="${previous_image##*:}"

    if docker compose up -d --remove-orphans; then
        log_info "Rollback completed"
        return 0
    else
        log_error "Rollback failed"
        return 1
    fi
}

# Clean up old Docker images
cleanup() {
    log_info "Cleaning up old Docker images..."

    # Remove dangling images
    docker image prune -f

    # Keep only the last 3 versions of our app images
    docker images --format "table {{.Repository}}\t{{.Tag}}\t{{.CreatedAt}}" | \
        grep "ghcr.io/.*/koupii-api" | \
        tail -n +4 | \
        awk '{print $1":"$2}' | \
        xargs -r docker rmi || true

    log_info "Cleanup completed"
}

# Main deployment function
main() {
    log_info "Starting Koupii API deployment..."

    # Store current image for potential rollback
    local current_image
    current_image=$(get_current_image)

    check_permissions
    check_prerequisites
    login_ghcr
    create_directories
    pull_images
    deploy

    # Perform health check
    if health_check; then
        log_info "Deployment successful!"
        cleanup
    else
        log_error "Deployment failed health check"

        if [[ -n "$current_image" ]]; then
            if rollback "$current_image"; then
                log_warn "Rolled back to previous version"
                exit 1
            else
                log_error "Rollback also failed"
                exit 2
            fi
        else
            log_error "No previous version to rollback to"
            exit 1
        fi
    fi
}

# Script entry point
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
