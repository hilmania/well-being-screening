#!/bin/bash

# deploy.sh - Production deployment script

set -e

echo "🚀 Starting production deployment..."

# Configuration
COMPOSE_FILE="docker-compose.production.yml"
ENV_FILE=".env.production"

# Validate environment file
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Environment file $ENV_FILE not found!"
    echo "Please copy .env.production and configure your settings."
    exit 1
fi

# Load environment variables
export $(cat $ENV_FILE | grep -v '^#' | xargs)

# Validate required variables
REQUIRED_VARS=("APP_KEY" "DB_PASSWORD" "DB_ROOT_PASSWORD" "REDIS_PASSWORD")
for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        echo "❌ Required environment variable $var is not set!"
        exit 1
    fi
done

echo "✅ Environment validation passed"

# Generate SSL certificate if not exists
if [ ! -f "docker/certs/localhost.pem" ]; then
    echo "🔒 Generating SSL certificate..."
    mkdir -p docker/certs
    openssl req -x509 -newkey rsa:2048 \
        -keyout docker/certs/localhost-key.pem \
        -out docker/certs/localhost.pem \
        -sha256 -days 365 -nodes \
        -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Production/OU=IT/CN=yourdomain.com"
    echo "✅ SSL certificate generated"
fi

# Build and start services
echo "🏗️  Building production images..."
docker compose -f $COMPOSE_FILE build --no-cache

echo "🔄 Starting services..."
docker compose -f $COMPOSE_FILE up -d

# Wait for services to be healthy
echo "⏳ Waiting for services to be healthy..."
sleep 30

# Check service health
echo "🔍 Checking service health..."
if docker compose -f $COMPOSE_FILE ps | grep -q "unhealthy"; then
    echo "❌ Some services are unhealthy!"
    docker compose -f $COMPOSE_FILE logs
    exit 1
fi

echo "✅ All services are healthy"

# Run post-deployment tasks
echo "🎯 Running post-deployment tasks..."
docker compose -f $COMPOSE_FILE exec -T server php artisan migrate --force
docker compose -f $COMPOSE_FILE exec -T server php artisan db:seed --class=ScreeningQuestionSeeder --force

echo "🧹 Cleaning up old images..."
docker image prune -f

echo "🎉 Deployment completed successfully!"
echo ""
echo "📋 Service URLs:"
echo "   - HTTPS: https://localhost"
echo "   - HTTP: http://localhost (redirects to HTTPS)"
echo ""
echo "🔧 Management commands:"
echo "   - View logs: docker compose -f $COMPOSE_FILE logs -f"
echo "   - Stop services: docker compose -f $COMPOSE_FILE down"
echo "   - Update: ./deploy.sh"
