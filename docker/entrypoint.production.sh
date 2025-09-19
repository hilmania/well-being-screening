#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database..."
until nc -z mysql 3306; do
  echo "Database is unavailable - sleeping"
  sleep 1
done
echo "Database is up - executing command"

# Wait for Redis to be ready
echo "Waiting for Redis..."
until nc -z redis 6379; do
  echo "Redis is unavailable - sleeping"
  sleep 1
done
echo "Redis is up - executing command"

# Run migrations and optimizations
if [ "$APP_ENV" = "production" ]; then
    echo "Running production setup..."
    
    # Cache optimization
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # Run migrations
    php artisan migrate --force
    
    # Storage link
    php artisan storage:link
    
    # Optimize
    php artisan optimize
fi

# Start cron daemon
service cron start

# Execute the main command
exec "$@"
