# Security Hardening Deployment Guide

**Purpose**: Step-by-step guide for deploying security hardening to production  
**Audience**: DevOps, System Administrators  
**Last Updated**: December 15, 2025

---

## 📋 Pre-Deployment Checklist

### 1. Environment Setup

```bash
# Verify production environment
APP_ENV=production
APP_DEBUG=false
HTTPS=true

# Check .env file
cat .env | grep APP_ENV  # Should be: production
cat .env | grep APP_DEBUG  # Should be: false
```

### 2. Update Configuration

```bash
# Update CORS for production domains
# File: config/cors.php

'allowed_origins' => [
    'https://telemedicine.com',
    'https://app.telemedicine.com',
    'https://www.telemedicine.com',
],

# Verify you REMOVED development origins:
# ❌ http://localhost:3000
# ❌ http://localhost:5173
# ❌ http://localhost:8000
```

### 3. HTTPS Configuration

```bash
# Install SSL Certificate (Let's Encrypt recommended)
# Using certbot:
sudo apt-get install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d telemedicine.com -d app.telemedicine.com

# Configure HSTS in .env
HSTS_ENABLED=true
HSTS_MAX_AGE=31536000
HSTS_INCLUDE_SUBDOMAINS=true

# Test HTTPS:
curl -I https://api.telemedicine.com/health
# Should show: Strict-Transport-Security: max-age=31536000
```

### 4. Verify Security Headers

```bash
# Check all security headers present:
curl -I https://api.telemedicine.com/health

Expected headers:
✓ Content-Security-Policy: default-src 'self'...
✓ X-Content-Type-Options: nosniff
✓ X-Frame-Options: DENY
✓ X-XSS-Protection: 1; mode=block
✓ Referrer-Policy: strict-origin-when-cross-origin
✓ Permissions-Policy: geolocation=(), microphone=()...
✓ Strict-Transport-Security: max-age=31536000...

Missing headers (must not be present):
❌ X-Powered-By
❌ Server
```

---

## 🔐 File Upload Setup

### 1. Storage Configuration

```bash
# Configure file storage for production
# Option 1: Local storage with proper permissions
mkdir -p storage/app/uploads/documents
mkdir -p storage/app/uploads/photos
chmod 750 storage/app/uploads/documents
chmod 750 storage/app/uploads/photos

# Option 2: AWS S3 (recommended for production)
# Edit .env:
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=telemedicine-uploads
AWS_URL=https://telemedicine-uploads.s3.amazonaws.com
```

### 2. Verify File Upload Limits

```bash
# Edit: config/filesystems.php
'max_upload_size' => 5242880, // 5MB in bytes

# Edit: .htaccess or Nginx config
php_value upload_max_filesize 5M
php_value post_max_size 5M

# Nginx:
client_max_body_size 5M;
```

### 3. Set Up Antivirus Scanning (Optional)

```bash
# Install ClamAV (optional but recommended)
sudo apt-get install clamav clamav-daemon

# Update virus definitions
sudo freshclam

# Configure in Laravel (add to ValidateFileUpload middleware)
# ClamAV library: arnaud-lb/php-clamav
composer require arnaud-lb/php-clamav
```

---

## 🌐 CORS Production Setup

### 1. Verify Whitelist

```php
// config/cors.php

'allowed_origins' => [
    'https://telemedicine.com',
    'https://app.telemedicine.com',
    'https://www.telemedicine.com',
],

// NOT:
// 'https://*.telemedicine.com', // ❌ Avoid wildcard
// '*', // ❌ NEVER in production

// For multiple subdomains, list explicitly:
'allowed_origins' => [
    'https://telemedicine.com',
    'https://app.telemedicine.com',
    'https://admin.telemedicine.com',
    'https://api.telemedicine.com',
    'https://www.telemedicine.com',
],
```

### 2. Test CORS

```bash
# Test allowed origin
curl -X OPTIONS https://api.telemedicine.com/appointments \
  -H "Origin: https://telemedicine.com" \
  -H "Access-Control-Request-Method: GET"

Expected:
✓ Access-Control-Allow-Origin: https://telemedicine.com

# Test blocked origin
curl -X OPTIONS https://api.telemedicine.com/appointments \
  -H "Origin: https://evil.com" \
  -H "Access-Control-Request-Method: GET"

Expected:
❌ No Access-Control-Allow-Origin header
```

---

## 📝 Security Headers Production Config

### 1. Content-Security-Policy (CSP)

```php
// app/Http/Middleware/AddSecurityHeaders.php

private function getCSP(): string
{
    $isDev = app()->environment('local', 'testing');
    
    if ($isDev) {
        // Relaxed CSP for development
        return "default-src 'self' http://localhost:*; script-src 'self' 'unsafe-inline'";
    }
    
    // Strict CSP for production
    return "default-src 'self'; " .
           "script-src 'self' https://cdn.jsdelivr.net https://pusher.com; " .
           "style-src 'self' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "connect-src 'self' https://pusher.com https://api.github.com; " .
           "img-src 'self' https: data:; " .
           "frame-ancestors 'none'; " .
           "base-uri 'self'; " .
           "form-action 'self'";
}
```

### 2. HSTS Configuration

```bash
# Nginx configuration
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

# Apache (.htaccess)
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"

# Preload list registration (optional)
# Visit: https://hstspreload.org
# Add: https://telemedicine.com
```

### 3. Test with Online Tools

```bash
# Use Security Headers Checker
https://securityheaders.com/?q=https://telemedicine.com

# Use SSL Labs
https://www.ssllabs.com/ssltest/?d=telemedicine.com

# Use Mozilla Observatory
https://observatory.mozilla.org/?https%3A%2F%2Ftelemedicine.com
```

---

## 🔒 Database & Sensitive Data

### 1. Database Encryption

```bash
# Enable encryption for sensitive fields
# File: database/migrations/YYYY_MM_DD_create_users_table.php

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password'); // Already hashed
    // For encryption at rest (optional):
    // $table->string('ssn')->nullable()->encrypted();
});

# Using Laravel encryption:
// In model
protected $encrypted = ['ssn', 'phone'];
```

### 2. Environment Variables

```bash
# Ensure all secrets in .env, not in code
# .env (NOT in version control)
DB_HOST=prod.db.example.com
DB_USERNAME=secure_user
DB_PASSWORD=very_strong_password
MAIL_PASSWORD=smtp_password
API_SECRET_KEY=generated_secret

# .env.example (in version control - without secrets)
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=YOUR_PASSWORD_HERE
```

### 3. Secrets Management (Recommended)

```bash
# Use Laravel Vault or similar
composer require laravel/vault

# Or use HashiCorp Vault / AWS Secrets Manager
# Configuration handled separately
```

---

## 📊 Logging & Monitoring

### 1. Log Configuration

```bash
# File: config/logging.php

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'days' => 30, // Keep 30 days of logs
        'level' => 'debug',
    ],
    // For production, add external logging
    'sentry' => [
        'driver' => 'sentry',
        'dsn' => env('SENTRY_LARAVEL_DSN'),
    ],
],

# Enable structured logging
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'days' => 90,
    ],
],
```

### 2. Log Rotation

```bash
# Configure log rotation with logrotate
# File: /etc/logrotate.d/telemedicine

/var/www/telemedicine/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}

# Test rotation
sudo logrotate -f /etc/logrotate.d/telemedicine
```

### 3. Monitoring Setup

```bash
# Use external monitoring service (Sentry, Datadog, etc.)

# For Sentry:
composer require sentry/sentry-laravel

# Configure in .env
SENTRY_LARAVEL_DSN=https://key@sentry.io/project-id

# For local monitoring, set up alerts
# Check /storage/logs/laravel.log regularly
watch -n 60 'tail -20 storage/logs/laravel.log'
```

---

## ✅ Production Deployment Steps

### Step 1: Pre-Deployment Testing (Production Environment)

```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run database migrations
php artisan migrate --force

# Generate application key (if new)
php artisan key:generate

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 2: Security Verification

```bash
# Run security checks
composer audit

# Check for known vulnerabilities
php artisan security:check

# Verify security headers
curl -I https://api.telemedicine.com/health

# Test file upload validation
php artisan test --filter=FileUploadTest

# Test CORS configuration
php artisan test --filter=CorsTest

# Test input sanitization
php artisan test --filter=SanitizationTest
```

### Step 3: Deployment

```bash
# Health check before deployment
curl https://api.telemedicine.com/health

# Deploy (using your deployment tool)
# Examples:
# - GitHub Actions: git push triggers deployment
# - Docker: docker pull && docker run
# - Traditional: rsync or FTP

# Post-deployment verification
curl -I https://api.telemedicine.com/health
# Should return 200 OK

# Check security headers
curl https://api.telemedicine.com/health -i
# Verify all security headers present
```

### Step 4: Post-Deployment Testing

```bash
# Test critical endpoints
curl -X GET https://api.telemedicine.com/api/health
curl -X POST https://api.telemedicine.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test file upload
curl -X POST https://api.telemedicine.com/api/upload \
  -H "Authorization: Bearer TOKEN" \
  -F "file=@test.jpg"

# Monitor logs for errors
tail -f storage/logs/laravel.log

# Check PHP error logs
tail -f /var/log/php-fpm/error.log
```

---

## 🚨 Rollback Procedure

If issues occur after deployment:

```bash
# Quick rollback to previous version
git revert HEAD
git push origin main

# Or restore from backup
git checkout previous-version
php artisan migrate:refresh --force

# Monitor logs
tail -f storage/logs/laravel.log

# Health check
curl -I https://api.telemedicine.com/health
```

---

## 📈 Post-Deployment Monitoring

### Daily Checks

```bash
# Check application health
curl https://api.telemedicine.com/health

# Review security logs
grep -i "fail\|error\|warn" storage/logs/laravel.log

# Check API response times
# (Use monitoring tool like Datadog, New Relic, etc.)

# Monitor file uploads
ls -lh storage/app/uploads/
# Check for suspicious files
find storage/app/uploads/ -type f ! -name "*.jpg" ! -name "*.pdf" ! -name "*.docx"
```

### Weekly Checks

```bash
# Security header verification
curl -I https://api.telemedicine.com/health | grep -E "^[a-Z-]+:"

# Rate limiting verification
# Make 100 requests in rapid succession
for i in {1..100}; do curl https://api.telemedicine.com/api/health; done

# CORS testing
curl -X OPTIONS https://api.telemedicine.com/appointments \
  -H "Origin: https://telemedicine.com"

# Database backups check
ls -lh /backups/database/
```

### Monthly Checks

```bash
# Dependency vulnerability scan
composer audit

# Security patch updates
composer update --with-all-dependencies

# Log rotation verification
ls -lh storage/logs/ | head -5

# Review failed login attempts
grep "failed" storage/logs/laravel.log | wc -l
```

---

## 🔄 Maintenance Schedule

| Task | Frequency | Owner |
|------|-----------|-------|
| Security log review | Daily | Ops Team |
| Health checks | Daily | Monitoring |
| SSL certificate renewal | Before expiry | Ops Team |
| Composer audit | Weekly | Dev Team |
| Dependency updates | Weekly | Dev Team |
| Security patches | Immediate | Dev Team |
| Full security audit | Quarterly | Security |
| Penetration testing | Annually | External |
| Backup verification | Weekly | Ops Team |

---

## 🆘 Incident Response

If security breach is suspected:

### Immediate (0-1 hour)

```bash
# 1. Isolate the system
sudo systemctl stop nginx
sudo systemctl stop php-fpm

# 2. Enable debug logging
# Edit .env
APP_DEBUG=true
LOG_LEVEL=debug

# 3. Restart services
sudo systemctl start nginx
sudo systemctl start php-fpm

# 4. Collect logs
tar -czf incident_logs.tar.gz storage/logs/
```

### Short-term (1-4 hours)

```bash
# 5. Analyze logs for compromises
grep "fail\|error\|warn\|attack" storage/logs/laravel.log

# 6. Check file integrity
find app/ -type f -mtime -1 # Files modified in last 24 hours

# 7. Review database
mysql> SELECT user, email, last_login FROM users WHERE last_login > NOW() - INTERVAL 1 HOUR;

# 8. Rotate secrets
php artisan key:generate
# Change database passwords
# Update API tokens
```

### Medium-term (4-24 hours)

```bash
# 9. Patch vulnerabilities
composer audit --fix

# 10. Update dependencies
composer update

# 11. Reset passwords
php artisan command:reset-all-user-passwords

# 12. Audit permissions
find storage/app/ -type f ! -perm 640

# 13. Notify affected users
php artisan command:notify-security-incident
```

### Long-term (1-7 days)

```bash
# 14. Post-incident analysis
# Document what happened
# Review security controls
# Implement fixes
# Update documentation

# 15. External audit
# Hire security firm for assessment

# 16. Communication
# Notify stakeholders
# Update security documentation
```

---

## 📞 Support & Escalation

### Escalation Path
1. **Level 1**: On-call engineer (response: 15 min)
2. **Level 2**: Security team (response: 30 min)
3. **Level 3**: VP Engineering (response: 1 hour)
4. **Level 4**: Legal/Compliance (if data breach)

### Contact Information
- Security Team: security@telemedicine.com
- On-Call: Check Slack #on-call channel
- Incident Hotline: +1-555-SECURITY

---

## ✅ Deployment Approval

Before deploying to production, ensure:

- [ ] All tests passing (`composer test`)
- [ ] Security audit passed (`composer audit`)
- [ ] CORS configured for production
- [ ] HTTPS enabled with valid certificate
- [ ] Security headers verified
- [ ] Database backups configured
- [ ] Logging configured
- [ ] Monitoring active
- [ ] Team notified of deployment
- [ ] Rollback plan documented

**Deployed By**: ________________  
**Date**: ________________  
**Verified By**: ________________  
**Notes**: ________________

---

**Status**: 🟢 PRODUCTION DEPLOYMENT READY
