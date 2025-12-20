## 💰 TECHNOLOGY STACK & COST ESTIMATION

### Untuk Implementasi Phase 6 Final + Production Ready
**Prepared For:** Telemedicine Skripsi/Thesis

---

## 🛠️ RECOMMENDED TECHNOLOGY STACK

### Backend (Already Using)
```
✅ Laravel 10 - Web Framework
✅ PHP 8.1+ - Programming Language
✅ MySQL 8.0 - Database
✅ Redis - Caching
✅ Composer - Dependency Manager
```

### Frontend (Existing)
```
✅ Vue.js 3 - UI Framework
✅ Axios - HTTP Client
✅ Tailwind CSS - Styling
✅ Vite - Build Tool
```

### NEW Services Required

#### 1. Video Conferencing: Jitsi
```
✅ Open Source & Free
✅ Easy Integration
✅ Self-hosted option available
✅ Recording capability
Cost: $0-500/month (if self-hosted)
```

#### 2. Payment Gateway
```
Option A: Stripe (International)
- Cost: 2.9% + $0.30 per transaction
- Min: $0/month (no fixed fee)
- Best for: International clients

Option B: GCash (Philippines)
- Cost: 2.9% per transaction
- Min: Free
- Best for: Philippine market

Option C: Local Bank Transfer (Indonesia)
- Cost: Bank charges (~Rp 10,000)
- Min: Free
- Best for: Indonesia market

RECOMMENDATION: Use Stripe + Local option
```

#### 3. SMS Service: Twilio
```
Cost: $0.0075 per SMS (US)
Estimated Usage: 500 SMS/month = $3.75/month
For heavy usage (10,000 SMS/month) = $75/month
Best for: Global coverage

Alternative: AWS SNS
Cost: $0.00645 per SMS (cheaper)
Or: Local SMS provider (SMSBROADCAST, etc)
```

#### 4. Cloud Hosting: AWS/Digital Ocean
```
Option A: AWS EC2
- t3.medium: $30/month
- RDS MySQL: $30/month
- S3 Storage: $0.023/GB
- Total: ~$70-100/month

Option B: Digital Ocean
- VPS: $6-12/month (basic)
- Managed DB: $15/month
- Total: ~$25-30/month

Option C: Heroku
- $7/month minimum
- Plus dynos & databases

RECOMMENDATION: Digital Ocean (cost-effective)
```

#### 5. Email Service
```
✅ Already: Laravel Mail (SMTP)
Alternative: SendGrid/Mailgun
Cost: $20-100/month for volume

Not required immediately
```

---

## 📦 INSTALLATION REQUIREMENTS

### For Development

```bash
# PHP Extensions
php-mysql
php-redis
php-gd (image processing)
php-zip
php-xml

# Global Tools
composer (dependency manager)
node.js + npm (frontend build)
docker (optional, for containers)
```

### For Production

```
- Web Server: Apache/Nginx
- PHP-FPM
- Redis Server
- MySQL Server
- SSL Certificate (Let's Encrypt - free)
- CDN (optional - CloudFlare free tier)
```

---

## 💻 SYSTEM REQUIREMENTS

### Development Machine
```
Minimum:
- CPU: 4 cores
- RAM: 8 GB
- Disk: 50 GB SSD
- OS: Linux, macOS, or Windows (WSL2)

Recommended:
- CPU: 8 cores
- RAM: 16 GB
- Disk: 256 GB SSD
```

### Production Server
```
Minimum:
- CPU: 2 cores
- RAM: 4 GB
- Disk: 50 GB SSD
- Bandwidth: Unlimited

Recommended:
- CPU: 4-8 cores
- RAM: 16 GB
- Disk: 500 GB SSD
- Bandwidth: Unlimited
- Load Balancer: Nginx
- Database: Managed RDS
```

---

## 📊 COST BREAKDOWN

### Monthly Recurring Costs

```
SMALL SCALE (100 users, 50 consultations/month)
┌────────────────────────┬──────────┐
│ Service                │ Cost     │
├────────────────────────┼──────────┤
│ Hosting (DO)           │ $25      │
│ Database               │ $15      │
│ SMS (Twilio)           │ $5       │
│ Video (Jitsi)          │ $0       │
│ Payment Gateway        │ $5       │
│ Email/Monitoring       │ $0       │
├────────────────────────┼──────────┤
│ TOTAL                  │ $50      │
└────────────────────────┴──────────┘

MEDIUM SCALE (1000 users, 500 consultations/month)
┌────────────────────────┬──────────┐
│ Service                │ Cost     │
├────────────────────────┼──────────┤
│ Hosting (AWS)          │ $100     │
│ Database (RDS)         │ $50      │
│ SMS (Twilio)           │ $50      │
│ Video (Jitsi)          │ $100     │
│ Payment Gateway        │ $50      │
│ Monitoring (DataDog)   │ $30      │
├────────────────────────┼──────────┤
│ TOTAL                  │ $380     │
└────────────────────────┴──────────┘

LARGE SCALE (10000 users, 5000 consultations/month)
┌────────────────────────┬──────────┐
│ Service                │ Cost     │
├────────────────────────┼──────────┤
│ Hosting (AWS)          │ $500     │
│ Database (RDS)         │ $200     │
│ SMS (Twilio)           │ $500     │
│ Video (Jitsi/Zoom)     │ $500     │
│ Payment Gateway        │ $500     │
│ CDN (CloudFront)       │ $50      │
│ Monitoring (NewRelic)  │ $100     │
│ Support & Maintenance  │ $200     │
├────────────────────────┼──────────┤
│ TOTAL                  │ $2,450   │
└────────────────────────┴──────────┘
```

### ONE-TIME SETUP COSTS

```
┌────────────────────────┬──────────┐
│ Item                   │ Cost     │
├────────────────────────┼──────────┤
│ Development           │ $0       │ (Your team)
│ Infrastructure Setup   │ $500     │ (DevOps engineer)
│ SSL Certificate       │ $0       │ (Let's Encrypt free)
│ Domain Name           │ $12      │ (Annual)
│ Logo & Branding       │ $0-500   │
│ Legal Compliance      │ $0-1000  │ (Lawyers)
├────────────────────────┼──────────┤
│ TOTAL                  │ $500-2000│
└────────────────────────┴──────────┘
```

---

## 📋 INSTALLATION CHECKLIST

### Phase 1: Environment Setup (1 day)

```bash
# 1. Install PHP & Tools
sudo apt-get install php php-mysql php-redis php-gd php-zip php-xml

# 2. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 3. Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install nodejs

# 4. Install Redis
sudo apt-get install redis-server

# 5. Install MySQL
sudo apt-get install mysql-server

# 6. Install Nginx
sudo apt-get install nginx

# 7. Create web directory
sudo mkdir -p /var/www/telemedicine
cd /var/www/telemedicine
```

### Phase 2: Application Setup (1 day)

```bash
# 1. Clone repository
git clone <your-repo> .

# 2. Install dependencies
composer install --optimize-autoloader
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate
php artisan db:seed

# 5. Build frontend
npm run build

# 6. Setup caching
php artisan cache:clear
php artisan config:cache

# 7. Queue setup
php artisan queue:install
```

### Phase 3: External Services Setup (2-3 days)

```
1. Jitsi Integration
   - [ ] Create Jitsi account
   - [ ] Generate JWT tokens
   - [ ] Test video functionality
   - [ ] Configure recording

2. Stripe Setup
   - [ ] Create Stripe account
   - [ ] Get API keys
   - [ ] Setup webhooks
   - [ ] Test payment flow
   - [ ] Configure error handling

3. Twilio Setup
   - [ ] Create Twilio account
   - [ ] Get phone number
   - [ ] Setup credentials
   - [ ] Test SMS sending
   - [ ] Configure numbers per country

4. Cloud Hosting
   - [ ] Create AWS/DO account
   - [ ] Setup instance
   - [ ] Configure security groups
   - [ ] Setup database
   - [ ] Setup backup strategy
```

### Phase 4: Deployment Setup (1 day)

```bash
# 1. Setup SSL
sudo apt-get install certbot python3-certbot-nginx
sudo certbot certonly --standalone -d yourdomain.com

# 2. Configure Nginx
sudo nano /etc/nginx/sites-available/telemedicine
# Add configuration (see below)

# 3. Enable site
sudo ln -s /etc/nginx/sites-available/telemedicine \
    /etc/nginx/sites-enabled/

# 4. Test Nginx
sudo nginx -t
sudo systemctl restart nginx

# 5. Setup monitoring
php artisan telescope:publish
php artisan migrate

# 6. Setup queue worker
sudo nano /etc/systemd/system/queue-worker.service
# Add service configuration (see below)

sudo systemctl enable queue-worker.service
sudo systemctl start queue-worker.service
```

### Nginx Configuration Example
```nginx
# /etc/nginx/sites-available/telemedicine
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    root /var/www/telemedicine/public;
    index index.php;
    
    client_max_body_size 50M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

### Queue Worker Service
```ini
# /etc/systemd/system/queue-worker.service
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/telemedicine
ExecStart=/usr/bin/php /var/www/telemedicine/artisan queue:work --sleep=3 --tries=3
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

---

## 🔧 CONFIGURATION CHECKLIST

### Environment Variables (.env)

```env
# Application
APP_NAME="Telemedicine"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=telemedicine
DB_USERNAME=root
DB_PASSWORD=secure_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Jitsi
JITSI_SERVER_URL=https://meet.jit.si
JITSI_APP_ID=your_app_id
JITSI_SECRET=your_secret

# Stripe
STRIPE_PUBLIC_KEY=pk_live_xxx
STRIPE_SECRET_KEY=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Twilio
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE_NUMBER=+1234567890

# AWS (optional)
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=cookie
SESSION_LIFETIME=120

# JWT (if using)
JWT_SECRET=your_jwt_secret

# New Relic (optional monitoring)
NEWRELIC_ENABLED=false
NEWRELIC_LICENSE_KEY=your_key

# Feature Flags
ENABLE_VIDEO_CONSULTATIONS=true
ENABLE_PAYMENTS=true
ENABLE_SMS_NOTIFICATIONS=true
```

---

## 🔐 SECURITY SETUP

### SSL/TLS Certificate
```bash
# Using Let's Encrypt (Free)
sudo apt-get install certbot python3-certbot-nginx
sudo certbot certonly --webroot -w /var/www/telemedicine/public \
    -d yourdomain.com -d www.yourdomain.com
```

### Firewall Configuration
```bash
# Enable firewall
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow specific IP for admin
sudo ufw allow from 192.168.1.100 to any port 22

# Status
sudo ufw status
```

### Fail2Ban (Intrusion Prevention)
```bash
sudo apt-get install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 📊 PERFORMANCE OPTIMIZATION

### Caching Strategy
```php
// .env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=cookie

// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'ttl' => 3600,
],
```

### Database Optimization
```sql
-- Add indexes for frequently-queried columns
ALTER TABLE consultations ADD INDEX idx_patient_id (patient_id);
ALTER TABLE consultations ADD INDEX idx_doctor_id (doctor_id);
ALTER TABLE consultations ADD INDEX idx_status (status);
ALTER TABLE consultations ADD INDEX idx_created_at (created_at);

-- Add indexes for analytics
ALTER TABLE payment_transactions ADD INDEX idx_status (status);
ALTER TABLE payment_transactions ADD INDEX idx_created_at (created_at);
ALTER TABLE audit_logs ADD INDEX idx_user_id (user_id);
```

### CDN Setup (CloudFlare - Free)
```
1. Create CloudFlare account
2. Point domain to CloudFlare nameservers
3. Enable:
   - Auto HTTPS
   - Gzip compression
   - Browser caching
   - Page rules for API rate limiting
```

---

## 🔄 BACKUP & DISASTER RECOVERY

### Automated Database Backups
```bash
# Daily backup script
# /usr/local/bin/backup-mysql.sh
#!/bin/bash
BACKUP_DIR="/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR
mysqldump -u root -p$MYSQL_PASSWORD --all-databases > \
    $BACKUP_DIR/backup_$DATE.sql

# Compress
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

# Upload to S3 (optional)
aws s3 cp $BACKUP_DIR/backup_$DATE.sql.gz s3://your-bucket/backups/
```

### Cron Job
```bash
# /etc/cron.d/backup-database
0 2 * * * root /usr/local/bin/backup-mysql.sh
```

---

## 📈 MONITORING & ALERTING

### Tools Setup
```
1. Monitoring:
   - [ ] New Relic (Paid: $100/month)
   - [ ] DataDog (Paid: $30/month)
   - [ ] Self-hosted: Prometheus + Grafana ($0)

2. Log Management:
   - [ ] ELK Stack (Elasticsearch, Logstash, Kibana)
   - [ ] Sentry for error tracking
   - [ ] CloudWatch (AWS)

3. Uptime Monitoring:
   - [ ] StatusPage.io
   - [ ] Pingdom
   - [ ] UptimeRobot (Free)
```

### Laravel Horizon (Queue Monitoring)
```bash
php artisan horizon:install
php artisan migrate

# Access at /horizon
```

---

## 📋 DEPLOYMENT CHECKLIST

```
Pre-Deployment:
- [ ] All tests passing
- [ ] Code coverage > 80%
- [ ] Security scan completed
- [ ] Performance optimized
- [ ] Documentation updated
- [ ] Backup tested
- [ ] Staging environment verified

Deployment:
- [ ] Database migrations run
- [ ] Cache cleared
- [ ] Asset compilation done
- [ ] Queue workers restarted
- [ ] SSL certificate valid
- [ ] Environment variables set
- [ ] Monitoring enabled

Post-Deployment:
- [ ] Health check passed
- [ ] Functionality verified
- [ ] Performance baseline established
- [ ] Alerts configured
- [ ] Team notified
- [ ] Documentation updated
- [ ] Rollback plan ready
```

---

## 🎓 THESIS DELIVERABLES

### For Submission
```
1. Source Code (GitHub)
   - Clean, documented code
   - Deployment guide
   - API documentation

2. Documentation
   - Architecture diagrams
   - Database schema
   - API specification
   - Security analysis
   - Compliance report

3. Testing Results
   - Test coverage report (>80%)
   - Security test results
   - Performance benchmarks
   - Load test results

4. Demo
   - Video walkthrough
   - Live demonstration
   - User scenarios
```

---

## ✅ FINAL CHECKLIST

- [ ] All services integrated
- [ ] Environment configured
- [ ] Security hardened
- [ ] Monitoring setup
- [ ] Backup strategy implemented
- [ ] Disaster recovery tested
- [ ] Documentation complete
- [ ] Team trained
- [ ] Go-live ready

---

**Status: Ready for Production Deployment**
**Timeline: 2 weeks from current state**
**Cost: $50-500/month depending on scale**
