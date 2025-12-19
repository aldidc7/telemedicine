## 🚀 EXECUTION CHECKLIST - DEPLOYMENT KE PRODUCTION
**Telemedicine System - Step-by-step dengan timing**

---

## 📅 PRE-DEPLOYMENT PHASE (1 hari sebelum deployment)

### Hari H-1: Morning (08:00 - 12:00)
**Durasi: 4 jam**

- [ ] **Backup current database** ⏱️ 30 min
  ```bash
  mysqldump -u root -p telemedicine_prod > /backup/telemedicine_backup_$(date +%s).sql
  ```
  
- [ ] **Review all configuration files** ⏱️ 30 min
  - Check .env.production
  - Verify all API keys present
  - Confirm database credentials
  
- [ ] **Test database migration** ⏱️ 20 min
  ```bash
  php artisan migrate:status --env=production
  ```
  
- [ ] **Verify API endpoints** ⏱️ 30 min
  - Load Postman collection
  - Test 5-10 critical endpoints
  - Verify response structure
  
- [ ] **Run full test suite** ⏱️ 60 min
  ```bash
  php artisan test --env=production
  ```
  
- [ ] **Build frontend assets** ⏱️ 30 min
  ```bash
  npm run build
  composer validate
  ```
  
- [ ] **Create deployment checklist** ⏱️ 20 min
  - Print this document
  - Assign roles to team members
  - Prepare communication template

- [ ] **Communication prep** ⏱️ 30 min
  - Draft deployment announcement
  - Notify users about maintenance window
  - Prepare downtime message

### Hari H-1: Afternoon (13:00 - 17:00)
**Durasi: 4 jam - Rest & Prepare**

- [ ] **Final document review** ⏱️ 60 min
  - Review DEPLOYMENT_PRODUCTION_GUIDE.md
  - Review MONITORING_OBSERVABILITY_GUIDE.md
  - Identify potential issues

- [ ] **Setup staging verification** ⏱️ 60 min
  - Deploy to staging server
  - Run smoke tests
  - Verify real-time features
  
- [ ] **Prepare rollback strategy** ⏱️ 60 min
  - Document current version
  - Test rollback procedure
  - Have backup ready
  
- [ ] **Team meeting** ⏱️ 60 min
  - Go through deployment plan
  - Assign responsibilities
  - Set communication channels
  - Establish escalation procedure

**By end of day:**
✅ All checks passed
✅ Team ready
✅ Communication prepared
✅ Rollback plan ready

---

## 🚀 DEPLOYMENT DAY (H-day)

### Phase 1: Preparation (Before Maintenance Window)

**Time: 2 hours before maintenance**

- [ ] **Final health check** ⏱️ 15 min
  ```bash
  # Check current system status
  curl -I https://your-domain.com
  redis-cli ping
  mysql -u user -p -e "SELECT 1;" -D telemedicine_prod
  ```

- [ ] **Notify all users** ⏱️ 15 min
  - Post maintenance window notice
  - Notify support team
  - Prepare "maintenance in progress" page

- [ ] **Enable maintenance mode** ⏱️ 10 min
  ```bash
  # If using Laravel
  php artisan down --secret="your-secret-key" \
    --message="Telemedicine sedang di-update. Akan kembali dalam 1 jam."
  ```

- [ ] **Final backup** ⏱️ 10 min
  ```bash
  mysqldump -u root -p telemedicine_prod > /backup/backup-before-deploy.sql
  git status  # Ensure clean working directory
  ```

- [ ] **Team positions** ⏱️ 5 min
  - Monitoring team ready
  - Support team on standby
  - Rollback team ready
  - Documentation team ready

---

### Phase 2: Actual Deployment (Maintenance Window)

**⏱️ Total Expected Time: 45-60 minutes**

**Step 1: Pull latest code** ⏱️ 5 min
```bash
cd /var/www/telemedicine
git fetch origin
git reset --hard origin/main  # or your branch
git log --oneline -5  # Verify latest code
```

**Step 2: Install dependencies** ⏱️ 10 min
```bash
composer install --no-dev --optimize-autoloader
npm ci --production
```

**Step 3: Update environment** ⏱️ 5 min
```bash
cp .env.production .env
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Step 4: Run migrations** ⏱️ 10 min
```bash
php artisan migrate --force
# Monitor for errors
tail -f storage/logs/laravel.log
```

**Step 5: Build frontend** ⏱️ 10 min
```bash
npm run build
# Verify dist files created
ls -la public/dist/
```

**Step 6: Restart services** ⏱️ 10 min
```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart Nginx/Apache
sudo systemctl restart nginx
# OR
sudo systemctl restart apache2

# Clear Redis cache
redis-cli FLUSHDB

# Restart queue workers
sudo supervisorctl restart telemedicine-queue:*
```

**Step 7: Seed data (if needed)** ⏱️ 5 min
```bash
# Only if new reference data needed
php artisan db:seed --class=ReferenceDataSeeder
```

**Step 8: Verify application** ⏱️ 5 min
```bash
# Check Laravel app is running
php artisan tinker
> DB::connection()->getPdo();  // Test DB connection
> exit

# Check queue is running
ps aux | grep "queue:work"

# Check Redis connection
redis-cli ping  # Should return PONG
```

**Step 9: Disable maintenance mode** ⏱️ 2 min
```bash
php artisan up
```

**Total Time:** 45-60 minutes

---

### Phase 3: Post-Deployment Verification

**⏱️ Duration: 30 minutes - DO NOT SKIP THIS!**

**Immediately after deployment:**

- [ ] **Health check API** ⏱️ 5 min
  ```bash
  curl -s https://your-domain.com/api/health | jq .
  
  # Should see:
  # {
  #   "status": "ok",
  #   "database": "connected",
  #   "redis": "connected",
  #   "queue": "running"
  # }
  ```

- [ ] **Test critical endpoints** ⏱️ 10 min
  ```bash
  # Login endpoint
  curl -X POST https://your-domain.com/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@test.com","password":"password"}'
  
  # Get doctors list
  curl -X GET "https://your-domain.com/api/dokter?limit=5" \
    -H "Authorization: Bearer TOKEN"
  
  # Get consultations (for authenticated user)
  curl -X GET "https://your-domain.com/api/consultations" \
    -H "Authorization: Bearer TOKEN"
  ```

- [ ] **Check log files for errors** ⏱️ 5 min
  ```bash
  # Check Laravel logs
  tail -50 /var/log/telemedicine/laravel.log | grep -i "error\|critical\|exception"
  
  # Check web server logs
  tail -20 /var/log/nginx/error.log
  
  # Check queue logs
  tail -20 /var/log/supervisor/telemedicine-queue.log
  ```

- [ ] **Verify database integrity** ⏱️ 5 min
  ```bash
  mysql -u user -p telemedicine_prod -e "SHOW TABLES;" | wc -l  # Count tables
  mysql -u user -p telemedicine_prod -e "SELECT COUNT(*) FROM users;" # Check data
  ```

- [ ] **Test real-time features** ⏱️ 5 min
  - Open browser console
  - Navigate to consultation chat
  - Send test message
  - Verify it appears in real-time
  - Check browser console for WebSocket errors

- [ ] **Email test** ⏱️ 2 min
  ```bash
  php artisan tinker
  > Mail::raw('Test email', function($message) { 
      $message->to('your-email@example.com')->subject('Test'); 
    });
  > exit
  
  # Check if you receive the email
  ```

**By end of Phase 3:**
✅ All systems responding
✅ No critical errors in logs
✅ API endpoints working
✅ Real-time features working
✅ Ready for users

---

### Phase 4: Re-enable Users

**⏱️ Duration: 10 minutes**

- [ ] **Notify users** ⏱️ 5 min
  ```
  "Sistem telah berhasil di-upgrade!
  Silakan refresh page dan login kembali.
  Terima kasih atas kesabarannya!"
  ```

- [ ] **Announce on homepage** ⏱️ 2 min
  - Update status page
  - Show "System Operational" message

- [ ] **Monitor user access** ⏱️ 3 min
  - Watch error logs
  - Monitor CPU/Memory usage
  - Ensure queue is processing

---

## 📊 DURING DEPLOYMENT - MONITORING DASHBOARD

**Keep terminal windows open monitoring these:**

```bash
# Terminal 1: Application Logs
tail -f /var/log/telemedicine/laravel.log | grep -v "GET /nova"

# Terminal 2: Server Resources
watch -n 2 'free -h && echo "---" && df -h | grep -E "Filesystem|/$|/var"'

# Terminal 3: Redis & Queue
watch -n 5 'redis-cli INFO stats | head -20 && echo "---" && ps aux | grep queue'

# Terminal 4: MySQL Connections
watch -n 5 'mysql -u root -p -e "SHOW PROCESSLIST \G" -D telemedicine_prod | head -30'

# Terminal 5: Nginx Status (if available)
curl -s http://localhost/nginx_status
```

---

## ⚠️ TROUBLESHOOTING DURING DEPLOYMENT

### If Database Migration Fails
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Re-run migration with output
php artisan migrate --verbose

# If still fails - contact DB admin
```

### If Services Won't Start
```bash
# Check PHP-FPM status
systemctl status php8.2-fpm
sudo systemctl start php8.2-fpm

# Check Nginx status
systemctl status nginx
sudo systemctl start nginx

# Check permissions
ls -la /var/www/telemedicine/storage
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### If Queue is not processing
```bash
# Check supervisor status
sudo supervisorctl status telemedicine-queue:*

# View supervisor logs
tail -f /var/log/supervisor/telemedicine-queue.log

# Restart supervisor
sudo supervisorctl restart telemedicine-queue:*
```

### If Real-time Not Working
```bash
# Check Pusher credentials
grep "PUSHER" .env

# Test Pusher connection
php artisan tinker
> Pusher\Pusher::testConnection();

# Check browser console for WebSocket errors
# Clear browser cache and reload
```

### Quick Rollback (If Critical Error)
```bash
# 1. Stop application
sudo systemctl stop nginx
sudo supervisorctl stop telemedicine-queue:*

# 2. Restore backup
git reset --hard HEAD~1  # Go to previous version
php artisan migrate:rollback

# 3. Restart
sudo systemctl start nginx
sudo supervisorctl start telemedicine-queue:*

# 4. Verify
curl https://your-domain.com/api/health
```

---

## ✅ POST-DEPLOYMENT CHECKLIST (Next 24 Hours)

### Within 1 Hour
- [ ] Monitor error logs continuously
- [ ] Watch CPU/Memory/Disk usage
- [ ] Check database query performance
- [ ] Monitor real-time connections
- [ ] Verify backup was created
- [ ] No customer complaints logged

### Within 4 Hours
- [ ] Run load test on critical endpoints
- [ ] Verify email notifications working
- [ ] Test file uploads
- [ ] Test payment flow (if applicable)
- [ ] Monitor rate limiting
- [ ] Check queue job completion rate

### Within 24 Hours
- [ ] Verify all scheduled jobs running
- [ ] Check analytics data ingestion
- [ ] Review security logs
- [ ] Confirm backup integrity
- [ ] Get user feedback
- [ ] Update documentation

---

## 📋 COMMUNICATION TEMPLATE

### At Start of Maintenance Window
```
MAINTENANCE NOTIFICATION

Sistem Telemedicine sedang di-upgrade untuk peningkatan performa dan fitur baru.
Perkiraan durasi: 1 jam (08:00 - 09:00 WIB)

Kami mohon maaf atas ketidaknyamanannya.

Status: MAINTENANCE IN PROGRESS
Estimated completion: [time]

Terima kasih,
Tech Team
```

### After Deployment Complete
```
MAINTENANCE COMPLETE ✅

Sistem Telemedicine telah berhasil di-upgrade!

Yang baru:
✅ Performa lebih cepat
✅ Fitur-fitur baru
✅ Keamanan ditingkatkan

Silakan refresh page dan login kembali.

Jika ada masalah, hubungi support: support@telemedicine.com

Terima kasih,
Tech Team
```

### If Critical Issue Found
```
CRITICAL ISSUE DETECTED

Kami menemukan masalah dan sedang melakukan rollback.
Sistem akan kembali dalam 15 menit.

Status: ROLLING BACK
Expected return: [time]

Kami mohon maaf atas ketidaknyamanannya.

Terima kasih,
Tech Team
```

---

## 🎯 SUCCESS CRITERIA

### Deployment is SUCCESSFUL when:
- ✅ All services started without errors
- ✅ API health check returns 200 OK
- ✅ All 3 role systems can login
- ✅ Real-time messaging working
- ✅ File uploads working
- ✅ Notifications sending
- ✅ Queue processing jobs
- ✅ Zero critical errors in logs
- ✅ Response time < 500ms
- ✅ Users can access system

### Deployment is FAILED if ANY of these occur:
- ❌ Database migration error
- ❌ Services won't start
- ❌ API returning 500 errors
- ❌ Real-time not working
- ❌ Multiple authentication failures
- ❌ Queue not processing
- ❌ Critical errors in logs
- ❌ Users locked out

---

## 👥 TEAM ROLES & RESPONSIBILITIES

### Deployment Lead
- Overall coordination
- Decision making
- Communication with stakeholders
- **Timeline: H-1 afternoon through H+1**

### Database Admin
- Database backup & verification
- Run migrations
- Verify data integrity
- **Timeline: H-1 morning & H-day**

### DevOps Engineer
- Server configuration
- Service management
- Performance monitoring
- **Timeline: H-1 morning & H-day**

### QA/Tester
- Post-deployment verification
- Test critical flows
- Identify issues
- **Timeline: H-day afternoon**

### Support/Operations
- Monitor logs
- Handle user issues
- Escalate problems
- **Timeline: H-day onwards**

### Communications
- User notifications
- Status updates
- Issue reporting
- **Timeline: Throughout**

---

## 🎓 LEARNING FROM DEPLOYMENT

### After deployment, document:
- [ ] What went well?
- [ ] What took longer than expected?
- [ ] What issues occurred?
- [ ] How were they resolved?
- [ ] What improvements for next deployment?
- [ ] Lessons learned

---

## 📞 EMERGENCY CONTACTS

```
On-Call Team:
- Lead: [Name] - [Phone]
- DB Admin: [Name] - [Phone]
- DevOps: [Name] - [Phone]
- Support: [Department] - [Phone]

Escalation:
- Manager: [Name] - [Phone]
- Director: [Name] - [Phone]

Vendors:
- Hosting Provider: [Contact]
- Database Support: [Contact]
- Pusher Support: [Contact]
```

---

## 📌 FINAL REMINDERS

✅ **Double-check before pressing the button:**
- Backup taken?
- Team ready?
- Communication sent?
- Maintenance window confirmed?
- Rollback plan ready?

✅ **During deployment:**
- Monitor logs continuously
- Don't rush
- Verify each step
- Keep team updated

✅ **After deployment:**
- Monitor for 24 hours
- Be ready to rollback
- Document everything

---

**Good luck with your deployment! 🚀**

**Current Date:** 19 Desember 2025
**Status:** ✅ READY FOR DEPLOYMENT
**Confidence Level:** HIGH ⭐⭐⭐⭐⭐
