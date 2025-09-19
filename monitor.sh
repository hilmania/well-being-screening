#!/bin/bash

# monitor.sh - Production monitoring script

COMPOSE_FILE="docker-compose.production.yml"
LOG_FILE="/var/log/app-monitor.log"

# Function to log with timestamp
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Function to check service health
check_health() {
    local service=$1
    local url=$2
    
    if curl -f -s "$url" > /dev/null; then
        log "✅ $service is healthy"
        return 0
    else
        log "❌ $service is unhealthy"
        return 1
    fi
}

# Function to check container status
check_containers() {
    log "🔍 Checking container status..."
    
    local unhealthy=$(docker compose -f $COMPOSE_FILE ps | grep -c "unhealthy" || true)
    local exited=$(docker compose -f $COMPOSE_FILE ps | grep -c "Exit" || true)
    
    if [ "$unhealthy" -gt 0 ] || [ "$exited" -gt 0 ]; then
        log "❌ Found $unhealthy unhealthy and $exited exited containers"
        docker compose -f $COMPOSE_FILE ps
        return 1
    else
        log "✅ All containers are running properly"
        return 0
    fi
}

# Function to check disk space
check_disk_space() {
    local threshold=80
    local usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    
    if [ "$usage" -gt "$threshold" ]; then
        log "⚠️  Disk usage is ${usage}% (threshold: ${threshold}%)"
        return 1
    else
        log "✅ Disk usage is ${usage}%"
        return 0
    fi
}

# Function to check memory usage
check_memory() {
    local threshold=80
    local usage=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
    
    if [ "$usage" -gt "$threshold" ]; then
        log "⚠️  Memory usage is ${usage}% (threshold: ${threshold}%)"
        return 1
    else
        log "✅ Memory usage is ${usage}%"
        return 0
    fi
}

# Main monitoring function
main() {
    log "🚀 Starting health check..."
    
    local errors=0
    
    # Check containers
    if ! check_containers; then
        ((errors++))
    fi
    
    # Check application health
    if ! check_health "Application" "http://localhost/health"; then
        ((errors++))
    fi
    
    # Check proxy health
    if ! check_health "Proxy" "http://localhost:80/health"; then
        ((errors++))
    fi
    
    # Check system resources
    if ! check_disk_space; then
        ((errors++))
    fi
    
    if ! check_memory; then
        ((errors++))
    fi
    
    # Summary
    if [ "$errors" -eq 0 ]; then
        log "🎉 All checks passed successfully"
    else
        log "⚠️  Health check completed with $errors errors"
        
        # Send alert if configured
        if [ -n "$ALERT_WEBHOOK" ]; then
            curl -X POST "$ALERT_WEBHOOK" \
                -H "Content-Type: application/json" \
                -d "{\"text\":\"🚨 Production health check failed with $errors errors\"}"
        fi
    fi
    
    return $errors
}

# Run main function
main "$@"
