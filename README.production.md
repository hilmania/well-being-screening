# Well-Being Screening Application - Production Deployment

This guide covers deploying the Well-Being Screening application to production using Docker and Docker Compose.

## 🚀 Quick Start

1. **Clone and setup:**
   ```bash
   git clone <repository-url>
   cd well-being-screening
   ```

2. **Configure environment:**
   ```bash
   cp .env.production .env.prod
   # Edit .env.prod with your production settings
   ```

3. **Deploy:**
   ```bash
   ./deploy.sh
   ```

## 📋 Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- OpenSSL (for SSL certificate generation)
- 2GB+ RAM
- 10GB+ disk space

## ⚙️ Configuration

### Environment Variables

Copy `.env.production` and configure these required variables:

```bash
# Application
APP_KEY=base64:your_generated_app_key
APP_URL=https://yourdomain.com

# Database
DB_PASSWORD=your_secure_db_password
DB_ROOT_PASSWORD=your_secure_root_password

# Redis
REDIS_PASSWORD=your_redis_password

# Email
MAIL_HOST=smtp.yourdomain.com
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
```

### SSL Certificate

For production, replace the self-signed certificate with a valid SSL certificate:

1. **Using Let's Encrypt:**
   ```bash
   # Install certbot
   sudo apt install certbot
   
   # Generate certificate
   sudo certbot certonly --standalone -d yourdomain.com
   
   # Copy to project
   sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/certs/localhost.pem
   sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/certs/localhost-key.pem
   ```

2. **Using commercial certificate:**
   ```bash
   # Copy your certificate files
   cp your-certificate.pem docker/certs/localhost.pem
   cp your-private-key.pem docker/certs/localhost-key.pem
   ```

## 🏗️ Architecture

### Services

- **proxy**: Nginx reverse proxy with SSL termination
- **server**: FrankenPHP application server
- **worker**: Laravel queue workers (2 instances)
- **scheduler**: Laravel task scheduler
- **mysql**: MySQL 8.0 database
- **redis**: Redis cache and session store

### Volumes

- `mysql-data`: Database persistence
- `redis-data`: Redis persistence
- `app-storage`: Application storage
- `app-logs`: Application logs
- `nginx-logs`: Web server logs

## 🔒 Security Features

- SSL/TLS encryption
- Security headers (HSTS, CSP, etc.)
- Rate limiting for API and login endpoints
- Hidden server tokens
- Secure PHP configuration
- Database and Redis authentication
- File access restrictions

## 📊 Monitoring

### Health Checks

All services include health checks:
- Application: `GET /health`
- Proxy: `GET /health`
- Database: MySQL ping
- Redis: Redis ping

### Manual Monitoring

```bash
# Check service status
docker compose -f docker-compose.production.yml ps

# View logs
docker compose -f docker-compose.production.yml logs -f

# Run health check
./monitor.sh

# Check resource usage
docker stats
```

### Automated Monitoring

Set up a cron job for automated health checks:

```bash
# Add to crontab
*/5 * * * * /path/to/your/app/monitor.sh

# For alerts, configure webhook URL
export ALERT_WEBHOOK="https://hooks.slack.com/your/webhook/url"
```

## 🚀 Deployment Commands

### Initial Deployment
```bash
./deploy.sh
```

### Updates
```bash
git pull origin main
./deploy.sh
```

### Rollback
```bash
# Stop current deployment
docker compose -f docker-compose.production.yml down

# Deploy previous version
git checkout <previous-commit>
./deploy.sh
```

## 🛠️ Maintenance

### Database Backup
```bash
docker compose -f docker-compose.production.yml exec mysql \
  mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > backup.sql
```

### Database Restore
```bash
docker compose -f docker-compose.production.yml exec -T mysql \
  mysql -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} < backup.sql
```

### Log Rotation
```bash
# Setup logrotate for application logs
sudo tee /etc/logrotate.d/app-logs << EOF
/var/lib/docker/volumes/well-being-screening_app-logs/_data/*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
}
EOF
```

### Cache Management
```bash
# Clear application cache
docker compose -f docker-compose.production.yml exec server php artisan cache:clear

# Clear Redis cache
docker compose -f docker-compose.production.yml exec redis redis-cli FLUSHALL
```

## 🔧 Troubleshooting

### Common Issues

1. **SSL Certificate Errors**
   ```bash
   # Regenerate certificate
   openssl req -x509 -newkey rsa:2048 -keyout docker/certs/localhost-key.pem \
     -out docker/certs/localhost.pem -sha256 -days 365 -nodes \
     -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Production/OU=IT/CN=yourdomain.com"
   ```

2. **Database Connection Issues**
   ```bash
   # Check database logs
   docker compose -f docker-compose.production.yml logs mysql
   
   # Test connection
   docker compose -f docker-compose.production.yml exec server \
     php artisan tinker -c "DB::connection()->getPdo()"
   ```

3. **High Memory Usage**
   ```bash
   # Check container resource usage
   docker stats
   
   # Restart services
   docker compose -f docker-compose.production.yml restart
   ```

### Performance Tuning

1. **Database Optimization**
   - Adjust `innodb_buffer_pool_size` in `docker/mysql/my.cnf`
   - Monitor slow queries
   - Add database indexes

2. **PHP Optimization**
   - Increase `opcache.memory_consumption`
   - Adjust `memory_limit` based on usage
   - Monitor PHP-FPM processes

3. **Redis Optimization**
   - Adjust `maxmemory` based on available RAM
   - Monitor Redis memory usage
   - Consider Redis clustering for high traffic

## 📞 Support

For issues and questions:
- Check application logs: `docker compose -f docker-compose.production.yml logs app`
- Run health check: `./monitor.sh`
- Monitor resource usage: `docker stats`
- Review security headers: `curl -I https://yourdomain.com`
