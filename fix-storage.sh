#!/bin/bash

# Script untuk fix storage issues di production
echo "=== Checking Storage Issues ==="

# 1. Check symbolic link
echo "1. Checking symbolic link..."
ls -la public/storage

# 2. Recreate symbolic link if needed
echo "2. Recreating storage link..."
rm -f public/storage
php artisan storage:link

# 3. Check permissions
echo "3. Checking permissions..."
ls -la storage/app/public/volunteer-attachments/

# 4. Fix permissions
echo "4. Fixing permissions..."
chmod -R 755 storage/
chmod -R 755 public/storage/

# 5. Check if file exists
echo "5. Checking specific file..."
ls -la storage/app/public/volunteer-attachments/01K7HKKP6PB5XNJN9T0157Q6ED.csv

echo "=== Done ==="
