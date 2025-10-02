# Well-Being Screening Application

Aplikasi screening kesejahteraan mental dengan sistem workflow berurutan: Responden → Relawan → Psikolog.

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Blade Templates with Tailwind CSS
- **Admin Panel**: Filament v4
- **Authentication**: Laravel Breeze
- **Permissions**: Spatie Permission + Filament Shield
- **Database**: MySQL/SQLite
- **File Storage**: Laravel Storage (Public Disk)

## Features

- ✅ Screening kesejahteraan mental dengan scoring otomatis
- ✅ Role-based access control (Super Admin, Admin, Relawan, Psikolog, Responden)
- ✅ Workflow berurutan: Responden mengisi → Relawan menangani → Psikolog memberikan diagnosis
- ✅ File attachment system (CSV, Excel, PDF)
- ✅ Dashboard admin dengan Filament
- ✅ Notifikasi dan status tracking

## Deployment ke VPS

### 1. Prerequisites

Pastikan VPS Anda memiliki:
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Nginx/Apache
- Git

### 2. Clone Repository

```bash
# Clone repository
git clone https://github.com/hilmania/well-being-screening.git
cd well-being-screening

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build assets untuk production
npm run build
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit file .env
nano .env
```

**Konfigurasi penting di `.env`:**

```env
APP_NAME="Well-Being Screening"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wellbeing_screening
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=public
```

### 5. Generate Application Key

```bash
# Generate Laravel application key
php artisan key:generate
```

### 6. Database Setup

```bash
# Buat database MySQL
mysql -u root -p
CREATE DATABASE wellbeing_screening;
CREATE USER 'wellbeing_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON wellbeing_screening.* TO 'wellbeing_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Jalankan migrasi
php artisan migrate

# Jalankan seeder (opsional, untuk data dummy)
php artisan db:seed
```

### 7. Storage Setup

```bash
# Buat symbolic link untuk storage
php artisan storage:link

# Set permissions untuk storage
sudo chmod -R 775 storage
sudo chmod -R 775 public/storage
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data public/storage

# Buat direktori untuk attachments
mkdir -p storage/app/public/volunteer-attachments
mkdir -p storage/app/public/psychologist-attachments
sudo chmod -R 775 storage/app/public/
sudo chown -R www-data:www-data storage/app/public/
```

### 8. Filament Shield Setup

```bash
# Install Filament Shield permissions
php artisan shield:install

# Generate permissions untuk semua resources
php artisan shield:generate --all

# Publish Filament Shield migrations jika diperlukan
php artisan vendor:publish --tag="filament-shield-migrations"
php artisan migrate
```

### 9. Create Filament Admin User

#### Opsi 1: Menggunakan Filament Command (Recommended)

```bash
# Buat user admin untuk mengakses panel Filament
php artisan make:filament-user
```

**Command ini akan meminta input:**
- **Name**: Masukkan nama lengkap (misal: "Super Administrator")
- **Email**: Masukkan email admin (misal: "admin@yourdomain.com")
- **Password**: Masukkan password yang kuat

**Setelah user dibuat, assign role super_admin:**

```bash
php artisan tinker
```

**Di dalam tinker:**
```php
use App\Models\User;

// Ambil user yang baru dibuat
$user = User::where('email', 'admin@yourdomain.com')->first();

// Assign role super_admin
$user->assignRole('super_admin');

// Verifikasi role
echo "User: " . $user->name . " has roles: " . $user->roles->pluck('name')->join(', ');

exit;
```

#### Opsi 2: Membuat User Manual dengan Tinker

```bash
php artisan tinker
```

**Di dalam tinker:**
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Buat user baru
$user = User::create([
    'name' => 'Super Administrator',
    'email' => 'admin@yourdomain.com',
    'password' => Hash::make('your-secure-password'),
    'email_verified_at' => now(),
]);

// Assign role super_admin
$user->assignRole('super_admin');

echo "Admin user created successfully!";
echo "Email: " . $user->email;
echo "Role: " . $user->roles->pluck('name')->join(', ');

exit;
```

#### Opsi 3: Menggunakan Database Seeder (Development)

User admin sudah otomatis dibuat melalui `DatabaseSeeder` dengan credentials:
- **Email**: `admin@example.com`
- **Password**: `password` (default dari factory)
- **Role**: `super_admin`

**Akses Admin Panel:**
- URL: `https://yourdomain.com/admin`
- Login dengan credentials admin yang telah dibuat
```

### 10. Cache Optimization

```bash
# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 11. Queue Setup (Opsional)

Jika menggunakan queue untuk background jobs:

```bash
# Install supervisor
sudo apt install supervisor

# Buat konfigurasi worker
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

**Isi file supervisor:**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 12. Web Server Configuration

#### Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/wellbeing-screening
```

**Isi konfigurasi Nginx:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/your/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/wellbeing-screening /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 13. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Generate SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### 14. Firewall Setup

```bash
# Configure UFW
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 15. Monitoring & Logs

```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Monitor Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# Monitor PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

## Roles & Permissions

Aplikasi menggunakan 5 role utama:

1. **super_admin** - Akses penuh ke semua fitur
2. **admin** - Manajemen sistem dan user
3. **relawan** - Menangani screening responden
4. **psikolog** - Memberikan diagnosis dan rekomendasi
5. **responden** - Mengisi form screening

## Post-Deployment Checklist

- [ ] ✅ Database terkoneksi dengan benar
- [ ] ✅ Storage symlink berfungsi
- [ ] ✅ File upload/download berfungsi
- [ ] ✅ Email SMTP configured
- [ ] ✅ SSL certificate aktif
- [ ] ✅ Super admin dapat login ke `/admin`
- [ ] ✅ Role permissions berfungsi
- [ ] ✅ Screening form dapat diakses
- [ ] ✅ Workflow responden → relawan → psikolog berjalan
- [ ] ✅ Backup database setup

## Backup Strategy

```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u wellbeing_user -p wellbeing_screening > /backups/db_backup_$DATE.sql

# File backup
tar -czf /backups/files_backup_$DATE.tar.gz /path/to/your/app/storage

# Add to crontab for daily backup
0 2 * * * /path/to/backup-script.sh
```

## Troubleshooting

### Common Issues:

1. **Permission Issues:**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Storage Link Issues:**
   ```bash
   php artisan storage:link --force
   ```

3. **Cache Issues:**
   ```bash
   php artisan optimize:clear
   ```

4. **Database Connection:**
   ```bash
   php artisan migrate:status
   php artisan tinker
   # Test: DB::connection()->getPdo()
   ```

## Support

Untuk pertanyaan atau masalah, silakan buat issue di repository GitHub atau hubungi tim development.

---

**Developed by:** Cherudim
**Version:** 1.0.0
**Last Updated:** October 2025
