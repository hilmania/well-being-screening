# File Upload Deployment Fixes

## 1. PHP Configuration (php.ini)
# Ensure these settings in your server's php.ini:
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
max_execution_time = 300
max_input_time = 300
memory_limit = 512M

# Temporary files directory
upload_tmp_dir = /tmp

## 2. Web Server Configuration

### For Nginx:
client_max_body_size 10M;

### For Apache (.htaccess):
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_file_uploads 20
php_value max_execution_time 300

## 3. Laravel Configuration
# Ensure storage directories exist and are writable:
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Create storage links
php artisan storage:link

## 4. Temporary Directory Permissions
# Ensure tmp directory is writable:
chmod 777 /tmp

## 5. Environment Variables
# Add to .env file:
FILESYSTEM_DISK=local
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local
