#!/bin/bash

# dev.sh - Development environment script

set -e

echo "🚀 Starting development environment..."

# Configuration
COMPOSE_FILE="docker-compose.yml"
ENV_FILE=".env"

# Validate environment file
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Environment file $ENV_FILE not found!"
    echo "Please copy .env.example to .env and configure your settings."
    exit 1
fi

echo "✅ Environment file found"

# Stop any running containers
echo "🛑 Stopping existing containers..."
docker compose -f $COMPOSE_FILE down

# Build and start services
echo "🏗️  Building development images..."
docker compose -f $COMPOSE_FILE build

echo "🔄 Starting development services..."
docker compose -f $COMPOSE_FILE up -d

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 20

# Check if services are running
echo "🔍 Checking service status..."
if ! docker compose -f $COMPOSE_FILE ps | grep -q "Up"; then
    echo "❌ Some services failed to start!"
    docker compose -f $COMPOSE_FILE logs
    exit 1
fi

# Run development setup
echo "🎯 Setting up development environment..."

# Install dependencies if not exists
if [ ! -d "vendor" ]; then
    echo "📦 Installing PHP dependencies..."
    docker compose -f $COMPOSE_FILE exec server composer install
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Installing Node dependencies..."
    docker compose -f $COMPOSE_FILE exec app npm install
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    docker compose -f $COMPOSE_FILE exec server php artisan key:generate
fi

# Run migrations
echo "🗄️  Running database migrations..."
docker compose -f $COMPOSE_FILE exec server php artisan migrate

# Seed database if empty
echo "🌱 Seeding database..."
docker compose -f $COMPOSE_FILE exec server php artisan db:seed --class=ScreeningQuestionSeeder
docker compose -f $COMPOSE_FILE exec server php artisan db:seed --class=SampleDataSeeder

# Create storage link
echo "🔗 Creating storage link..."
docker compose -f $COMPOSE_FILE exec server php artisan storage:link

# Start frontend development server
echo "🎨 Starting frontend development..."
docker compose -f $COMPOSE_FILE exec -d app npm run dev

echo "🎉 Development environment ready!"
echo ""
echo "📋 Development URLs:"
echo "   - Application: http://localhost:8001"
echo "   - Direct PHP: http://localhost:8000"
echo "   - Vite Dev: http://localhost:5173"
echo "   - MySQL: localhost:3306"
echo ""
echo "🔧 Useful commands:"
echo "   - View logs: docker compose logs -f"
echo "   - Stop: docker compose down"
echo "   - Shell: docker compose exec server bash"
echo "   - Artisan: docker compose exec server php artisan"
echo "   - Composer: docker compose exec server composer"
echo "   - NPM: docker compose exec app npm"
