.PHONY: dev prod build up down logs shell artisan composer npm clean help

# Development environment
dev:
	@echo "🚀 Starting development environment..."
	./dev.sh

# Production environment  
prod:
	@echo "🚀 Starting production environment..."
	./deploy.sh

# Build containers
build:
	@echo "🏗️ Building containers..."
	docker compose build

# Start services
up:
	@echo "▶️ Starting services..."
	docker compose up -d

# Stop services
down:
	@echo "⏹️ Stopping services..."
	docker compose down

# View logs
logs:
	@echo "📋 Viewing logs..."
	docker compose logs -f

# Access server shell
shell:
	@echo "🐚 Accessing server shell..."
	docker compose exec server bash

# Run artisan commands
artisan:
	@echo "⚡ Running artisan..."
	docker compose exec server php artisan $(filter-out $@,$(MAKECMDGOALS))

# Run composer commands
composer:
	@echo "📦 Running composer..."
	docker compose exec server composer $(filter-out $@,$(MAKECMDGOALS))

# Run npm commands
npm:
	@echo "📦 Running npm..."
	docker compose exec app npm $(filter-out $@,$(MAKECMDGOALS))

# Clean up
clean:
	@echo "🧹 Cleaning up..."
	docker compose down -v
	docker system prune -f

# Show help
help:
	@echo "Well-Being Screening - Development Commands"
	@echo ""
	@echo "Available commands:"
	@echo "  dev      - Start development environment (HTTP only)"
	@echo "  prod     - Start production environment (HTTPS)"
	@echo "  build    - Build Docker containers"
	@echo "  up       - Start services"
	@echo "  down     - Stop services"
	@echo "  logs     - View container logs"
	@echo "  shell    - Access server shell"
	@echo "  artisan  - Run artisan commands"
	@echo "  composer - Run composer commands"
	@echo "  npm      - Run npm commands"
	@echo "  clean    - Clean up containers and volumes"
	@echo ""
	@echo "Examples:"
	@echo "  make dev"
	@echo "  make artisan migrate"
	@echo "  make composer install"
	@echo "  make npm run dev"

# Catch-all target for artisan, composer, npm
%:
	@:
