#!/bin/bash

# Deployment script to fix file upload issues

echo "🚀 Setting up storage directories and permissions..."

# Create necessary directories
mkdir -p storage/app/temp/imports
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 777 storage/app/temp/
chmod -R 777 storage/framework/

# Create storage symlink
php artisan storage:link

# Clear and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Storage setup completed!"

# Check PHP upload settings
echo "📋 Current PHP upload settings:"
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo 'max_file_uploads: ' . ini_get('max_file_uploads') . PHP_EOL;"
php -r "echo 'upload_tmp_dir: ' . ini_get('upload_tmp_dir') . PHP_EOL;"
