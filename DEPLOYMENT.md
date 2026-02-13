# Production Deployment Guide

**Laravel 11 Order Management System - Production Deployment**

This guide provides step-by-step instructions for deploying the Laravel 11 Order Management System to a production environment with full security hardening.

---

## I Prerequisites

### Server Requirements

**Minimum Server Specifications:**
- CPU: 2 cores
- RAM: 4GB
- Storage: 20GB SSD
- OS: Ubuntu 22.04 LTS or similar

**Software Requirements:**
- Docker 20.10+ & Docker Compose 2.x
- OR: PHP 8.3, Nginx/Apache, MySQL 8.0
- SSL/TLS certificate (Let's Encrypt recommended)
- Firewall (UFW or iptables)

---

## II. Deployment Methods

### Option A: Docker Deployment (Recommended)

#### Step 1: Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version
```

#### Step 2: Deploy Application

```bash
# Clone repository
git clone <repository-url> /var/www/laravel-app
cd /var/www/laravel-app

# Copy production environment
cp .env.production.example .env

# Edit .env with production settings
nano .env
```

**Critical .env Settings:**
```env
APP_NAME="Order Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Generate unique key (run: php artisan key:generate)
APP_KEY=base64:GENERATED_KEY_HERE

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=order_db
DB_USERNAME=dbuser
DB_PASSWORD=CHANGE_THIS_STRONG_PASSWORD

# Root password for MySQL
DB_ROOT_PASSWORD=CHANGE_THIS_ROOT_PASSWORD

# Session Security
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Logging
LOG_CHANNEL=stack
LOG_SECURITY_DAYS=90

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

#### Step 3: Build and Start Containers

```bash
# Build images
docker-compose build --no-cache

# Start containers
docker-compose up -d

# Verify containers are running
docker-compose ps
```

#### Step 4: Initialize Application

```bash
# Install dependencies
docker-compose exec app composer install --no-dev --optimize-autoloader --no-interaction

# Generate application key (if not done)
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed database (optional, for demo data)
docker-compose exec app php artisan db:seed --force

# Create session table
docker-compose exec app php artisan session:table
docker-compose exec app php artisan migrate --force

# Cache configuration
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Set permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

#### Step 5: SSL/TLS Configuration

**Using Let's Encrypt:**

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Update docker/nginx/default.conf with SSL
```

**Nginx SSL Configuration** (add to `docker/nginx/default.conf`):
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # ... rest of configuration
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

#### Step 6: Firewall Configuration

```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Check status
sudo ufw status
```

---

### Option B: Traditional Deployment (PHP-FPM + Nginx)

#### Step 1: Install PHP 8.3

```bash
# Add PPA repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 and extensions
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-redis \
    php8.3-opcache php8.3-gd
```

#### Step 2: Install MySQL 8.0

```bash
# Install MySQL
sudo apt install mysql-server -y

# Secure installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE order_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dbuser'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON order_db.* TO 'dbuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 3: Install Nginx

```bash
sudo apt install nginx -y

# Create Nginx configuration
sudo nano /etc/nginx/sites-available/laravel
```

**Nginx Configuration**:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/laravel-app/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Step 4: Deploy Application

```bash
# Clone to web directory
sudo git clone <repository-url> /var/www/laravel-app
cd /var/www/laravel-app

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data /var/www/laravel-app
sudo chmod -R 755 /var/www/laravel-app/storage

# Configure environment
cp .env.production.example .env
nano .env
php artisan key:generate

# Run migrations
php artisan migrate --force
php artisan db:seed --force

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## III. Post-Deployment Configuration

### 1. OPcache Configuration

Edit `/etc/php/8.3/fpm/conf.d/10-opcache.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

### 2. Cronjob Configuration

```bash
# Edit crontab
sudo crontab -e -u www-data
```

Add Laravel scheduler:
```cron
* * * * * cd /var/www/laravel-app && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Log Rotation

Create `/etc/logrotate.d/laravel`:
```
/var/www/laravel-app/storage/logs/*.log {
    daily
    rotate 90
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
    postrotate
        php /var/www/laravel-app/artisan cache:clear > /dev/null 2>&1
    endscript
}
```

### 4. Database Backup

```bash
# Create backup script
sudo nano /usr/local/bin/backup-laravel-db.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/mysql"
mkdir -p $BACKUP_DIR

# Backup database
docker-compose exec -T db mysqldump -u dbuser -pDATABASE_PASSWORD order_db | gzip > $BACKUP_DIR/order_db_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-laravel-db.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```
```cron
0 2 * * * /usr/local/bin/backup-laravel-db.sh >> /var/log/backup.log 2>&1
```

---

## IV. Monitoring & Maintenance

### Health Checks

```bash
# Application health
curl https://your-domain.com/up

# Docker container status
docker-compose ps

# Application logs
docker-compose logs -f app

# Security logs
docker-compose exec app tail -f storage/logs/security.log
```

### Performance Monitoring

**Install Laravel Telescope (Development/Staging Only)**:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Production Monitoring Tools**:
- New Relic APM
- Datadog
- Sentry (error tracking)

---

## V. Security Hardening Checklist

### Application Level
- ✅ `APP_DEBUG=false`
- ✅ `APP_ENV=production`
- ✅ Unique `APP_KEY` generated
- ✅ `SESSION_SECURE_COOKIE=true`
- ✅ `SESSION_SAME_SITE=strict`
- ✅ Rate limiting enabled on auth routes
- ✅ CSRF protection on all forms
- ✅ Security headers configured
- ✅ Logs rotated and retained for 90 days

### Server Level
- ✅ Firewall configured (only ports 22, 80, 443)
- ✅ SSH key authentication only (disable password login)
- ✅ SSL/TLS certificates installed
- ✅ TLS 1.2+ only (disable TLS 1.0/1.1)
- ✅ Database user has minimal privileges
- ✅ Database not exposed publicly
- ✅ File permissions: 755 for directories, 644 for files
- ✅ Storage directory writable by www-data only

### Docker Level
- ✅ Non-root user in containers
- ✅ Read-only file systems where possible
- ✅ Security scanning (Docker Scout or Trivy)
- ✅ Secrets managed via environment variables
- ✅ Container images from trusted sources only

---

## VI. Rollback Procedure

In case of deployment failure:

```bash
# Stop new containers
docker-compose down

# Restore database from backup
gunzip -c /backups/mysql/order_db_LATEST.sql.gz | docker-compose exec -T db mysql -u root -pROOT_PASSWORD order_db

# Checkout previous version
git checkout <previous-tag>

# Rebuild and restart
docker-compose up -d --build

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

---

## VII. Zero-Downtime Deployment (Advanced)

For production systems requiring zero downtime:

1. Use **blue-green deployment** pattern
2. Set up load balancer (Nginx, HAProxy, or AWS ELB)
3. Deploy to secondary environment
4. Run tests and health checks
5. Switch traffic to new environment
6. Keep old environment for quick rollback

---

## VIII. Support & Troubleshooting

### Common Issues

**Issue**: 500 Internal Server Error
- **Fix**: Check `storage/logs/laravel.log`, ensure permissions correct

**Issue**: Database connection refused
- **Fix**: Verify database container running, check credentials

**Issue**: Session expired immediately
- **Fix**: Ensure `SESSION_DOMAIN` matches your domain

**Issue**: CSRF token mismatch
- **Fix**: Clear cache (`php artisan cache:clear`), check cookie settings

---

## IX. Maintenance Windows

Recommended maintenance schedule:

- **Daily**: Automated backups at 2 AM
- **Weekly**: Security log review
- **Monthly**: Dependency updates (`composer update`)
- **Quarterly**: SSL certificate renewal check
- **Annually**: Full security audit

---

**Last Updated**: 2025  
**Document Version**: 1.0
