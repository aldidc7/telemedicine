## 🚀 PANDUAN DEPLOYMENT KE PRODUCTION
**Telemedicine System - Step-by-Step**

---

## PRE-DEPLOYMENT CHECKLIST

### 1. Konfigurasi Environment
```bash
# .env.production

# Database
DB_CONNECTION=mysql
DB_HOST=your-production-db.com
DB_PORT=3306
DB_DATABASE=telemedicine_prod
DB_USERNAME=prod_user
DB_PASSWORD=strong_password

# App Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=redis_password

# Queue
QUEUE_DRIVER=redis

# Mail Service
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@telemedicine.com
MAIL_FROM_NAME="Telemedicine App"

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=telemedicine-uploads

# Real-time Broadcasting
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1

# File Upload
UPLOAD_PATH=/storage/uploads
MAX_UPLOAD_SIZE=10485760  # 10MB

# Sanctum (API Tokens)
SANCTUM_STATEFUL_DOMAINS=telemedicine.com
```

### 2. Pre-Deployment Testing
```bash
# Run all tests
php artisan test --env=production

# Check security
php artisan security:check

# Verify database migrations
php artisan migrate:status --env=production

# Check dependencies
composer validate

# Verify Laravel version compatibility
php artisan --version
```

### 3. Build Assets
```bash
# For Vue.js frontend
npm run build

# Verify build output
ls -la public/build/

# Compress and minify
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

---

## DEPLOYMENT STEPS

### Step 1: Prepare Server
```bash
# SSH into server
ssh user@your-server.com

# Create app directory
mkdir -p /var/www/telemedicine
cd /var/www/telemedicine

# Clone repository
git clone https://github.com/your-repo/telemedicine.git .

# Or if private repo
git clone https://github.com:your-repo/telemedicine.git .
```

### Step 2: Install Dependencies
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies (if using Vue)
npm install --production

# Build frontend
npm run build

# Verify installations
composer validate
npm list
```

### Step 3: Configure Application
```bash
# Copy environment file
cp .env.example .env.production
# Edit with production values
nano .env.production

# Generate application key
php artisan key:generate --env=production

# Create symbolic link for storage
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Verify permissions
ls -la storage/
```

### Step 4: Database Setup
```bash
# Create database
mysql -u root -p
> CREATE DATABASE telemedicine_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> EXIT;

# Run migrations
php artisan migrate:fresh --seed --env=production

# Verify migrations
php artisan migrate:status --env=production

# Create superadmin (if needed)
php artisan tinker
> User::create(['name' => 'Admin', 'email' => 'admin@telemedicine.com', 'password' => Hash::make('strong_password'), 'role' => 'admin']);
> exit
```

### Step 5: Configure Web Server

#### For Nginx:
```nginx
# /etc/nginx/sites-available/telemedicine

server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/telemedicine/public;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/telemedicine/public;

    # SSL Certificates
    ssl_certificate /etc/ssl/certs/your-domain.com.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.com.key;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Laravel
    index index.php;
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # Cache static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/telemedicine /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### For Apache:
```apache
# /etc/apache2/sites-available/telemedicine.conf

<VirtualHost *:80>
    ServerName your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/telemedicine/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/your-domain.com.crt
    SSLCertificateKeyFile /etc/ssl/private/your-domain.com.key

    <Directory /var/www/telemedicine/public>
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php?$1 [L,QSA]
        </IfModule>
    </Directory>

    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    <IfModule mod_headers.c>
        Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
    </IfModule>
</VirtualHost>
```

```bash
# Enable the site
sudo a2ensite telemedicine
sudo a2enmod rewrite headers ssl
sudo apache2ctl configtest
sudo systemctl restart apache2
```

### Step 6: SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Generate certificate
sudo certbot certonly --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Step 7: Setup Queue Worker
```bash
# Test queue
php artisan queue:work redis --tries=3 --timeout=90

# Create supervisor config for persistent queue
sudo nano /etc/supervisor/conf.d/telemedicine-queue.conf

# Content:
[program:telemedicine-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/telemedicine/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/telemedicine-queue.log
stopwaitsecs=3600

# Restart supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start telemedicine-queue:*
```

### Step 8: Setup Cron Job
```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * /usr/bin/php /var/www/telemedicine/artisan schedule:run >> /dev/null 2>&1

# Verify
sudo service cron restart
```

### Step 9: Configure Redis (if using)
```bash
# Install Redis
sudo apt-get install redis-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test connection
redis-cli ping
# Should return: PONG

# Configure password (optional but recommended)
sudo nano /etc/redis/redis.conf
# Add: requirepass your_redis_password

# Restart
sudo systemctl restart redis-server
```

### Step 10: Setup Monitoring & Logging
```bash
# Check logs
tail -f storage/logs/laravel.log

# Setup logrotate
sudo nano /etc/logrotate.d/telemedicine

# Content:
/var/www/telemedicine/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}

# Test
sudo logrotate -f /etc/logrotate.d/telemedicine
```

---

## POST-DEPLOYMENT VERIFICATION

### 1. Test API Endpoints
```bash
# Test login
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@telemedicine.com","password":"password"}'

# Test protected endpoint (replace token)
curl -X GET https://your-domain.com/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN"

# Test WebSocket/Real-time
# Open browser console and connect to Pusher
```

### 2. Verify Services
```bash
# Check if app is running
curl -I https://your-domain.com

# Check database connection
php artisan tinker
> DB::connection()->getPdo();

# Check Redis connection
redis-cli ping

# Check mail service
php artisan tinker
> Mail::raw('Test', function($message) { $message->to('your-email@example.com'); });

# Check storage
php artisan tinker
> Storage::disk('s3')->put('test.txt', 'test content');
```

### 3. Monitor Performance
```bash
# Memory usage
free -h

# CPU usage
top

# Disk usage
df -h

# Process status
ps aux | grep php
ps aux | grep nginx

# Check errors
tail -f storage/logs/laravel.log
```

### 4. Security Checks
```bash
# SSL certificate status
ssl-test your-domain.com

# Security headers
curl -I https://your-domain.com | grep -i "strict-transport\|x-content"

# CORS configuration
curl -H "Origin: http://localhost" -H "Access-Control-Request-Method: GET" -X OPTIONS https://your-domain.com/api/health
```

---

## TROUBLESHOOTING

### Issue: 500 Internal Server Error
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

### Issue: Database Connection Failed
```bash
# Test connection
mysql -h DB_HOST -u DB_USER -p
# Enter DB_PASSWORD

# Check .env variables
cat .env | grep DB_

# Verify credentials in production
php artisan tinker
> DB::connection()->getPdo();
```

### Issue: Queue Not Processing
```bash
# Check supervisor status
sudo supervisorctl status telemedicine-queue:*

# Check logs
tail -f /var/log/telemedicine-queue.log

# Restart
sudo supervisorctl restart telemedicine-queue:*
```

### Issue: Real-time Not Working
```bash
# Check Pusher credentials in .env
cat .env | grep PUSHER_

# Test Pusher connection
php artisan tinker
> Pusher\Pusher::testConnection();

# Check channel authorization
tail -f storage/logs/laravel.log | grep -i channel
```

---

## MAINTENANCE TASKS

### Daily
- [ ] Monitor error logs
- [ ] Check disk space
- [ ] Verify backup completion

### Weekly
- [ ] Review performance metrics
- [ ] Check for security updates
- [ ] Test email delivery

### Monthly
- [ ] Review logs for patterns
- [ ] Update dependencies
- [ ] Run security audits
- [ ] Backup database

### Quarterly
- [ ] Penetration testing
- [ ] Performance optimization review
- [ ] User feedback analysis
- [ ] Upgrade PHP/Laravel version

---

## ROLLBACK PROCEDURE

### If something goes wrong:
```bash
# Stop application
sudo systemctl stop nginx
sudo supervisorctl stop telemedicine-queue:*

# Backup current code
cp -r /var/www/telemedicine /var/www/telemedicine-backup-$(date +%s)

# Restore from git
cd /var/www/telemedicine
git reset --hard HEAD~1

# Rollback database if needed
php artisan migrate:rollback --step=1

# Start application
sudo systemctl start nginx
sudo supervisorctl start telemedicine-queue:*

# Verify
curl https://your-domain.com
```

---

## SUCCESS INDICATORS ✅

When deployment is complete, verify:
- [x] API responding on HTTPS
- [x] Database migrations complete
- [x] Real-time features working
- [x] Email service operational
- [x] File uploads working
- [x] Queue processing jobs
- [x] No errors in logs (after initial load)
- [x] Response time < 500ms
- [x] SSL certificate valid
- [x] All tests passing

---

**Status:** READY FOR DEPLOYMENT 🚀
**Next Step:** Follow the steps above and monitor logs during deployment
