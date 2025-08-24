#!/bin/bash

# Koupii API Health Check Script
# This script performs comprehensive health checks on the deployed application

set -e

# Configuration
HEALTH_URL="https://api-koupii.magercoding.com/api/health"
LOCAL_HEALTH_URL="http://127.0.0.1:8081/api/health"
APP_DIR="/srv/apps/koupii-api"
MAX_RETRIES=5
RETRY_INTERVAL=10

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
    echo -e "${BLUE}[CHECK]${NC} $1"
}

# Check if running on the server
check_environment() {
    if [[ ! -d "$APP_DIR" ]]; then
        log_error "Application directory not found: $APP_DIR"
        log_error "This script should be run on the production server"
        exit 1
    fi
}

# Check Docker containers
check_containers() {
    log_step "Checking Docker containers..."

    cd "$APP_DIR"

    # Check if containers are running
    if ! docker compose ps --format json | jq -e '.State == "running"' > /dev/null 2>&1; then
        log_error "Some containers are not running"
        docker compose ps
        return 1
    fi

    # Check container health
    local unhealthy_containers=$(docker compose ps --format json | jq -r 'select(.Health == "unhealthy") | .Name')

    if [[ -n "$unhealthy_containers" ]]; then
        log_error "Unhealthy containers found:"
        echo "$unhealthy_containers"
        return 1
    fi

    log_info "All containers are running and healthy"
    return 0
}

# Check application health endpoint
check_app_health() {
    log_step "Checking application health endpoint..."

    local retries=0
    while [[ $retries -lt $MAX_RETRIES ]]; do
        # Try local endpoint first
        if curl -fsS "$LOCAL_HEALTH_URL" > /dev/null 2>&1; then
            local response=$(curl -s "$LOCAL_HEALTH_URL")
            log_info "Local health check passed: $response"

            # Try public endpoint
            if curl -fsS "$HEALTH_URL" > /dev/null 2>&1; then
                local public_response=$(curl -s "$HEALTH_URL")
                log_info "Public health check passed: $public_response"
                return 0
            else
                log_warn "Public health check failed, but local is working"
                return 0
            fi
        fi

        retries=$((retries + 1))
        log_warn "Health check failed (attempt $retries/$MAX_RETRIES), retrying in ${RETRY_INTERVAL}s..."
        sleep $RETRY_INTERVAL
    done

    log_error "Health check failed after $MAX_RETRIES attempts"
    return 1
}

# Check database connectivity
check_database() {
    log_step "Checking database connectivity..."

    cd "$APP_DIR"

    if docker compose exec -T app php artisan migrate:status > /dev/null 2>&1; then
        log_info "Database connection successful"
        return 0
    else
        log_error "Database connection failed"
        return 1
    fi
}

# Check Redis connectivity
check_redis() {
    log_step "Checking Redis connectivity..."

    cd "$APP_DIR"

    if docker compose exec -T app php artisan tinker --execute="Redis::ping()" 2>/dev/null | grep -q "PONG"; then
        log_info "Redis connection successful"
        return 0
    else
        log_warn "Redis connection failed or not configured"
        return 0  # Not critical if Redis is not used
    fi
}

# Check queue workers
check_queue() {
    log_step "Checking queue workers..."

    cd "$APP_DIR"

    # Check if queue container is running
    if docker compose ps queue --format json | jq -e '.State == "running"' > /dev/null 2>&1; then
        log_info "Queue worker is running"

        # Check for failed jobs
        local failed_jobs=$(docker compose exec -T app php artisan queue:failed --format=json 2>/dev/null | jq length 2>/dev/null || echo "0")

        if [[ "$failed_jobs" -gt 0 ]]; then
            log_warn "Found $failed_jobs failed jobs"
        else
            log_info "No failed jobs found"
        fi

        return 0
    else
        log_error "Queue worker is not running"
        return 1
    fi
}

# Check scheduler
check_scheduler() {
    log_step "Checking scheduler..."

    cd "$APP_DIR"

    # Check if scheduler container is running
    if docker compose ps scheduler --format json | jq -e '.State == "running"' > /dev/null 2>&1; then
        log_info "Scheduler is running"
        return 0
    else
        log_error "Scheduler is not running"
        return 1
    fi
}

# Check disk space
check_disk_space() {
    log_step "Checking disk space..."

    local usage=$(df /srv | tail -1 | awk '{print $5}' | sed 's/%//')

    if [[ $usage -gt 90 ]]; then
        log_error "Disk usage is critical: ${usage}%"
        return 1
    elif [[ $usage -gt 80 ]]; then
        log_warn "Disk usage is high: ${usage}%"
    else
        log_info "Disk usage is normal: ${usage}%"
    fi

    return 0
}

# Check memory usage
check_memory() {
    log_step "Checking memory usage..."

    local memory_info=$(free -m)
    local total_mem=$(echo "$memory_info" | awk 'NR==2{print $2}')
    local used_mem=$(echo "$memory_info" | awk 'NR==2{print $3}')
    local usage_percent=$((used_mem * 100 / total_mem))

    if [[ $usage_percent -gt 90 ]]; then
        log_error "Memory usage is critical: ${usage_percent}%"
        return 1
    elif [[ $usage_percent -gt 80 ]]; then
        log_warn "Memory usage is high: ${usage_percent}%"
    else
        log_info "Memory usage is normal: ${usage_percent}%"
    fi

    return 0
}

# Check log files
check_logs() {
    log_step "Checking application logs..."

    local log_file="$APP_DIR/storage/logs/laravel.log"

    if [[ -f "$log_file" ]]; then
        # Check for recent errors (last 100 lines)
        local error_count=$(tail -100 "$log_file" | grep -c "ERROR" || true)
        local critical_count=$(tail -100 "$log_file" | grep -c "CRITICAL" || true)

        if [[ $critical_count -gt 0 ]]; then
            log_error "Found $critical_count critical errors in recent logs"
            return 1
        elif [[ $error_count -gt 5 ]]; then
            log_warn "Found $error_count errors in recent logs"
        else
            log_info "No critical errors found in recent logs"
        fi
    else
        log_warn "Application log file not found"
    fi

    return 0
}

# Check SSL certificate
check_ssl() {
    log_step "Checking SSL certificate..."

    if command -v openssl &> /dev/null; then
        local cert_info=$(echo | openssl s_client -servername api-koupii.magercoding.com -connect api-koupii.magercoding.com:443 2>/dev/null | openssl x509 -noout -dates 2>/dev/null)

        if [[ -n "$cert_info" ]]; then
            local expiry_date=$(echo "$cert_info" | grep "notAfter" | cut -d= -f2)
            local expiry_epoch=$(date -d "$expiry_date" +%s 2>/dev/null || echo "0")
            local current_epoch=$(date +%s)
            local days_until_expiry=$(( (expiry_epoch - current_epoch) / 86400 ))

            if [[ $days_until_expiry -lt 7 ]]; then
                log_error "SSL certificate expires in $days_until_expiry days"
                return 1
            elif [[ $days_until_expiry -lt 30 ]]; then
                log_warn "SSL certificate expires in $days_until_expiry days"
            else
                log_info "SSL certificate is valid (expires in $days_until_expiry days)"
            fi
        else
            log_warn "Could not retrieve SSL certificate information"
        fi
    else
        log_warn "OpenSSL not available, skipping SSL check"
    fi

    return 0
}

# Generate health report
generate_report() {
    local total_checks=0
    local passed_checks=0
    local failed_checks=0
    local warnings=0

    echo
    echo "=================================="
    echo "    KOUPII API HEALTH REPORT"
    echo "=================================="
    echo "Generated: $(date)"
    echo

    # Run all checks
    local checks=(
        "check_containers:Container Status"
        "check_app_health:Application Health"
        "check_database:Database Connectivity"
        "check_redis:Redis Connectivity"
        "check_queue:Queue Workers"
        "check_scheduler:Task Scheduler"
        "check_disk_space:Disk Space"
        "check_memory:Memory Usage"
        "check_logs:Application Logs"
        "check_ssl:SSL Certificate"
    )

    for check_info in "${checks[@]}"; do
        local check_func=$(echo "$check_info" | cut -d: -f1)
        local check_name=$(echo "$check_info" | cut -d: -f2)

        total_checks=$((total_checks + 1))

        echo -n "Checking $check_name... "

        if $check_func > /dev/null 2>&1; then
            echo -e "${GREEN}PASS${NC}"
            passed_checks=$((passed_checks + 1))
        else
            echo -e "${RED}FAIL${NC}"
            failed_checks=$((failed_checks + 1))
        fi
    done

    echo
    echo "=================================="
    echo "Summary:"
    echo "  Total Checks: $total_checks"
    echo -e "  Passed: ${GREEN}$passed_checks${NC}"
    echo -e "  Failed: ${RED}$failed_checks${NC}"
    echo "=================================="

    if [[ $failed_checks -eq 0 ]]; then
        echo -e "${GREEN}✅ All checks passed! System is healthy.${NC}"
        return 0
    else
        echo -e "${RED}❌ $failed_checks checks failed! System needs attention.${NC}"
        return 1
    fi
}

# Main function
main() {
    log_info "Starting Koupii API health check..."

    check_environment

    if [[ "$1" == "--report" ]]; then
        generate_report
    else
        # Run individual checks with detailed output
        check_containers && \
        check_app_health && \
        check_database && \
        check_redis && \
        check_queue && \
        check_scheduler && \
        check_disk_space && \
        check_memory && \
        check_logs && \
        check_ssl

        if [[ $? -eq 0 ]]; then
            log_info "✅ All health checks passed!"
        else
            log_error "❌ Some health checks failed!"
            exit 1
        fi
    fi
}

# Run main function
main "$@"
