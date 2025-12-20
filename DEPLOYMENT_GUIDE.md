# 🚀 DEPLOYMENT & SETUP GUIDE

**Telemedicine API - Production Deployment**
**Language**: Indonesian (Bahasa Indonesia)
**Date**: December 20, 2025

---

## 📋 TABLE OF CONTENTS

1. [System Requirements](#system-requirements)
2. [Local Development Setup](#local-development-setup)
3. [Production Environment](#production-environment)
4. [Database Setup](#database-setup)
5. [Running the Application](#running-the-application)
6. [API Testing](#api-testing)
7. [Monitoring & Maintenance](#monitoring--maintenance)
8. [Troubleshooting](#troubleshooting)

---

## 🖥️ SYSTEM REQUIREMENTS

### Server
- **OS**: Linux (Ubuntu 20.04+) atau Windows Server 2019+
- **CPU**: Minimum 2 cores, recommended 4+
- **RAM**: Minimum 4GB, recommended 8GB+
- **Storage**: Minimum 10GB, recommended 50GB+
- **Bandwidth**: Stable internet connection (1Mbps+)

### Software Stack
- **PHP**: 8.1+ (tested dengan 8.2)
- **Laravel**: 11.x
- **Database**: MySQL 8.0+, PostgreSQL 13+, atau SQLite (development)
- **Node.js**: 18.x+ (untuk build)
- **npm**: 9.x+
- **Redis** (optional, untuk caching/sessions)

### Required PHP Extensions
```
- json (default)
- pdo
- pdo_mysql (atau pdo_pgsql)
- bcmath
- ctype
- curl
- dom
- fileinfo
- filter
- hash
- mbstring
- openssl
- pcre
- phar
- posix
- tokenizer
- xml
- xmlwriter
- zlib
```

---

## 🔧 LOCAL DEVELOPMENT SETUP

### Step 1: Clone Repository

```bash
cd d:\Aplications
git clone https://github.com/aldidc7/telemedicine.git
cd telemedicine
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Setup Environment File

```bash
# Copy .env example
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Environment

Edit `.env` file dengan konfigurasi lokal:

```env
APP_NAME="Telemedicine"
APP_ENV=local
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite untuk development)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Alternative: MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=telemedicine
# DB_USERNAME=root
# DB_PASSWORD=

# Mail (optional)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=dev@telemedicine.local

# Sanctum (API Authentication)
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
```

### Step 5: Create Database & Run Migrations

```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# (Optional) Seed dummy data
php artisan db:seed
```

### Step 6: Build Frontend Assets

```bash
npm run build
```

### Step 7: Start Development Server

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Build assets in watch mode
npm run dev
```

Aplikasi akan accessible di: **http://localhost:8000**

---

## 🏭 PRODUCTION ENVIRONMENT

### Step 1: Server Setup (Ubuntu 20.04)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y \
  curl \
  git \
  unzip \
  php8.2 \
  php8.2-cli \
  php8.2-fpm \
  php8.2-mysql \
  php8.2-pgsql \
  php8.2-curl \
  php8.2-gd \
  php8.2-mbstring \
  php8.2-xml \
  php8.2-zip \
  php8.2-bcmath \
  composer \
  nodejs \
  npm \
  mysql-server \
  nginx
```

### Step 2: Setup Application

```bash
# Clone ke production directory
cd /var/www
sudo git clone https://github.com/aldidc7/telemedicine.git
cd telemedicine

# Setup permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build

# Setup .env
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan optimize:clear
```

### Step 3: Configure .env untuk Production

```env
APP_NAME="Telemedicine"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://telemedicine.example.com

# Database (Production MySQL)
DB_CONNECTION=mysql
DB_HOST=db.example.com
DB_PORT=3306
DB_DATABASE=telemedicine_prod
DB_USERNAME=telemedicine_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Queue & Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=localhost
REDIS_PASSWORD=REDIS_PASSWORD

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=YOUR_APP_PASSWORD
MAIL_FROM_ADDRESS=noreply@telemedicine.com
MAIL_FROM_NAME="Telemedicine"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=telemedicine.example.com

# Encryption
APP_CIPHER=AES-256-CBC

# Security
FORCE_HTTPS=true
SECURE_HSTS_ENABLED=true
SECURE_HSTS_MAX_AGE=31536000
```

### Step 4: Setup Nginx (Reverse Proxy)

**File**: `/etc/nginx/sites-available/telemedicine`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name telemedicine.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name telemedicine.example.com;

    # SSL certificates (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/telemedicine.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/telemedicine.example.com/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # CORS headers
    add_header Access-Control-Allow-Origin "*" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;

    root /var/www/telemedicine/public;
    index index.php index.html index.htm;

    # Logging
    access_log /var/log/nginx/telemedicine_access.log;
    error_log /var/log/nginx/telemedicine_error.log;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1000;
    gzip_types text/plain text/css text/xml text/javascript application/json;

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/telemedicine /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 5: Setup SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y

# Generate certificate
sudo certbot certonly --nginx -d telemedicine.example.com

# Auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

### Step 6: Database Setup

```bash
# Create database & user
mysql -u root -p << EOF
CREATE DATABASE telemedicine_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'telemedicine_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON telemedicine_prod.* TO 'telemedicine_user'@'localhost';
FLUSH PRIVILEGES;
EOF

# Run migrations
cd /var/www/telemedicine
sudo -u www-data php artisan migrate --force
```

### Step 7: Setup Supervisor untuk Queue

**File**: `/etc/supervisor/conf.d/telemedicine-worker.conf`

```ini
[program:telemedicine-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/telemedicine/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
stopwaitsecs=60
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/telemedicine-worker.log
user=www-data
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## 📊 DATABASE SETUP

### MySQL Schema

```bash
# Connect to MySQL
mysql -u telemedicine_user -p telemedicine_prod

# Verify tables created
SHOW TABLES;

# Check specific table
DESCRIBE consent_records;
DESCRIBE doctor_patient_relationships;
DESCRIBE patient_data_access_log;
```

### Backup & Restore

```bash
# Backup database
mysqldump -u telemedicine_user -p telemedicine_prod > backup_$(date +%Y%m%d).sql

# Restore from backup
mysql -u telemedicine_user -p telemedicine_prod < backup_20251220.sql

# Setup automated backups
0 2 * * * /usr/bin/mysqldump -u telemedicine_user -p'PASSWORD' telemedicine_prod > /backups/telemedicine_$(date +\%Y\%m\%d).sql
```

---

## 🚀 RUNNING THE APPLICATION

### Development Mode

```bash
# Terminal 1: Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Frontend build
npm run dev

# Access: http://localhost:8000
```

### Production Mode

```bash
# Clear caches
php artisan optimize:clear

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify status
php artisan status
```

### Health Check

```bash
# Verify API is running
curl https://telemedicine.example.com/api/v1/health

# Expected response:
# {"status":"API is running"}
```

---

## 🧪 API TESTING

### Using Postman

1. Import Postman collection: `Telemedicine_API_Collection.postman_collection.json`
2. Setup environment variables:
   ```
   base_url: https://telemedicine.example.com
   access_token: YOUR_TOKEN_HERE
   ```
3. Run test suite

### Using cURL

```bash
# Health check
curl https://telemedicine.example.com/api/v1/health

# Login & get token
TOKEN=$(curl -s -X POST https://telemedicine.example.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "doctor@example.com",
    "password": "password"
  }' | jq -r '.data.token')

# Get patient's doctors
curl -s -X GET https://telemedicine.example.com/api/v1/doctor-patient-relationships/my-doctors \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📈 MONITORING & MAINTENANCE

### View Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx access logs
sudo tail -f /var/log/nginx/telemedicine_access.log

# Nginx error logs
sudo tail -f /var/log/nginx/telemedicine_error.log

# Queue worker logs
sudo tail -f /var/log/telemedicine-worker.log
```

### Database Maintenance

```bash
# Check table sizes
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'telemedicine_prod'
ORDER BY size_mb DESC;

# Optimize tables (monthly)
php artisan tinker
DB::statement('OPTIMIZE TABLE consent_records');
DB::statement('OPTIMIZE TABLE doctor_patient_relationships');
DB::statement('OPTIMIZE TABLE patient_data_access_log');
```

### Performance Monitoring

```bash
# Check slow queries
mysql -u root -p -e "SET GLOBAL slow_query_log = 'ON';"

# Monitor server resources
free -h  # Memory
df -h    # Disk space
top      # CPU usage
ps aux | grep php  # PHP processes
```

### Automated Tasks

```bash
# Setup cron job untuk cleanup
0 3 * * * cd /var/www/telemedicine && php artisan schedule:run >> /dev/null 2>&1

# Dalam app/Console/Kernel.php schedule:
$schedule->command('model:prune')->daily();  # Delete soft-deleted records
$schedule->command('auth:clear-resets')->daily();  # Clear password reset tokens
```

---

## 🔍 TROUBLESHOOTING

### Common Issues

**1. "SQLSTATE[HY000]: General error: 1030 Got error 28 from storage engine"**
```bash
# Disk space full
df -h  # Check disk
# Solution: Delete old logs, backups, or upgrade storage
```

**2. "FAILED to open stream: Permission denied"**
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/telemedicine
sudo chmod -R 755 storage bootstrap/cache
```

**3. "502 Bad Gateway (Nginx)"**
```bash
# PHP-FPM not running
sudo systemctl restart php8.2-fpm
# Check PHP-FPM status
sudo systemctl status php8.2-fpm
```

**4. "Too many open files"**
```bash
# Increase file limit
ulimit -n 65536
# Permanent: Edit /etc/security/limits.conf
www-data soft nofile 65536
www-data hard nofile 65536
```

**5. "Connection timeout to database"**
```bash
# Test MySQL connection
mysql -h db.example.com -u telemedicine_user -p
# Check MySQL is running
sudo systemctl status mysql
```

### Debug Commands

```bash
# Test artisan command
php artisan tinker

# Run specific query
php artisan tinker
>>> DB::table('users')->count();
>>> DB::table('consent_records')->count();

# Clear all caches
php artisan optimize:clear

# Reset database (⚠️ careful!)
php artisan migrate:fresh --force
```

---

## 📝 MAINTENANCE SCHEDULE

| Task | Frequency | Command |
|------|-----------|---------|
| Database backup | Daily | mysqldump script |
| Log rotation | Weekly | logrotate (automatic) |
| Cache clear | Weekly | `php artisan optimize:clear` |
| Database optimization | Monthly | `php artisan tinker` + OPTIMIZE |
| Security updates | Monthly | `composer update` |
| Full backup | Weekly | tar + compress entire /var/www |

---

## 🔒 SECURITY CHECKLIST

Before going live:
- [ ] SSL certificate installed & configured
- [ ] .env file has strong passwords
- [ ] APP_DEBUG=false in production
- [ ] Database credentials are strong
- [ ] File permissions are correct (755/775)
- [ ] Backups are automated
- [ ] Monitoring is setup
- [ ] Error logs not publicly accessible
- [ ] CORS properly configured
- [ ] Rate limiting enabled
- [ ] Security headers in place
- [ ] Encryption enabled for sensitive fields

---

**For detailed API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md)**

---

*Last Updated: December 20, 2025*
*Telemedicine Development Team*
