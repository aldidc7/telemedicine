# DEPLOYMENT CHECKLIST & IMPLEMENTATION GUIDE

## Pre-Deployment Verification ✅

### 1. Code Changes Verification

```bash
# All changes made to:
✅ app/Services/ConsultationService.php
✅ app/Services/DokterService.php
✅ app/Services/MedicalRecordService.php
✅ app/Services/PatientService.php
✅ app/Http/Controllers/Api/AdminController.php
✅ database/migrations/2025_12_20_add_performance_indexes.php
```

### 2. Test Results ✅

```
PASSED: 36/36 tests
FAILED: 0
TOTAL:  36 checks
SUCCESS RATE: 100%
```

### 3. Database Verification ✅

```
✅ Migration: 2025_12_20_add_performance_indexes.php executed
✅ Indexes created: 10+
✅ Foreign keys: OK
✅ Constraints: OK
✅ Data integrity: Verified
```

---

## Deployment Steps

### Step 1: Backup Database

```bash
# Create backup before deployment
cp database/database.sqlite database/database.sqlite.backup
```

### Step 2: Clear Caches

```bash
php artisan cache:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

### Step 3: Run Migrations

```bash
# Run pending migrations
php artisan migrate

# Or if needed, fresh migration:
# php artisan migrate:fresh --seed
```

### Step 4: Run Tests

```bash
# Verify everything still works
php test_core_features.php

# Expected output:
# PASSED: 36
# FAILED: 0
```

### Step 5: Verify Performance

```bash
# Run performance verification
php test_optimization_complete.php
```

### Step 6: Deploy to Production

```bash
# Push code to production
git push origin main

# On production server:
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
```

---

## Post-Deployment Verification

### 1. Check API Endpoints

```bash
# Test authentication
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test patient list
curl -X GET http://your-domain.com/api/pasien \
  -H "Authorization: Bearer YOUR_TOKEN"

# Test consultations
curl -X GET http://your-domain.com/api/konsultasi \
  -H "Authorization: Bearer YOUR_TOKEN"

# Test admin dashboard
curl -X GET http://your-domain.com/api/admin/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. Monitor Query Performance

Add to `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Monitor slow queries in production
        if (config('app.debug') || app()->environment('production')) {
            DB::listen(function (QueryExecuted $query) {
                // Log queries slower than 100ms
                if ($query->time > 100) {
                    \Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'time' => $query->time . 'ms',
                        'bindings' => $query->bindings,
                    ]);
                }
            });
        }
    }
}
```

### 3. Monitor Database Performance

```bash
# Check slow query log (if enabled)
tail -f storage/logs/laravel.log | grep "Slow query"

# Monitor active queries
mysql -u root -p -e "SHOW PROCESSLIST;" | grep -v "Sleep"
```

### 4. Performance Baseline

Expected metrics after deployment:

| Operation | Expected Time | Alert Threshold |
|-----------|---------------|-----------------|
| List Consultations | 50ms | > 200ms |
| Admin Dashboard | 200ms | > 500ms |
| List Doctors | 30ms | > 100ms |
| Patient Health Summary | 100ms | > 300ms |

---

## Rollback Procedure

If issues occur:

### 1. Immediate Rollback

```bash
# Revert code changes
git revert HEAD

# Or restore from backup
git checkout previous-version

# Restart application
php artisan serve
```

### 2. Database Rollback

```bash
# Revert database migration
php artisan migrate:rollback

# Or restore from backup
cp database/database.sqlite.backup database/database.sqlite
```

### 3. Clear Caches

```bash
php artisan cache:clear
php artisan config:cache
```

---

## Monitoring Dashboard

### Key Metrics to Monitor

1. **Query Performance**
   - Average query time per request
   - Slow queries (> 100ms)
   - Database connection pool utilization

2. **API Performance**
   - Response time per endpoint
   - Request throughput
   - Error rate

3. **Database Health**
   - Index usage
   - Query execution plans
   - Connection count

4. **Application Health**
   - Memory usage
   - CPU usage
   - Disk space

### Setup Query Logging

In `config/logging.php`:

```php
'channels' => [
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    'queries' => [
        'driver' => 'single',
        'path' => storage_path('logs/queries.log'),
    ],
],
```

---

## Performance Optimization Tips

### 1. Enable Query Caching

```php
// Cache frequently accessed data
\Cache::remember('dashboard_stats', now()->addMinutes(5), function () {
    return [
        'total_patients' => \App\Models\Pasien::count(),
        'total_doctors' => \App\Models\Dokter::count(),
        // ... other stats
    ];
});
```

### 2. Use Database Indexing

Verify indexes are being used:

```bash
# MySQL
EXPLAIN SELECT * FROM consultations WHERE status = 'active' AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);

# Should show: Using index on status and created_at
```

### 3. Implement Pagination

```php
// Always paginate list endpoints
return Model::paginate(15);  // Returns 15 items per page
```

### 4. Monitor Connection Pool

```php
// Check database connections
php artisan tinker
DB::connection()->getPdo();
// Should reuse connections, not create new ones
```

---

## Common Issues & Solutions

### Issue 1: Slow Dashboard Load

**Symptom:** Admin dashboard still loading slowly

**Solution:**
1. Check if indexes are being used: `EXPLAIN SELECT * FROM consultations WHERE status = 'active'`
2. Verify migrations ran: `php artisan migrate:status`
3. Clear query cache: `php artisan cache:clear`
4. Check for N+1 in logs: `tail -f storage/logs/laravel.log`

### Issue 2: High Memory Usage

**Symptom:** Memory usage increasing over time

**Solution:**
1. Check for memory leaks in services
2. Verify collections aren't being loaded unnecessarily
3. Use `chunk()` for large data sets:
   ```php
   Model::chunk(1000, function ($items) {
       // Process $items
   });
   ```

### Issue 3: Database Connection Errors

**Symptom:** "Too many connections" error

**Solution:**
1. Check connection pool size in `.env`
2. Verify connections are being closed properly
3. Monitor active connections: `SHOW PROCESSLIST;`

---

## Success Criteria

After deployment, verify:

- ✅ All 36 tests passing
- ✅ Dashboard loads in < 500ms
- ✅ Consultation list loads in < 100ms
- ✅ Doctor list loads in < 100ms
- ✅ No N+1 queries in logs
- ✅ All API endpoints responding
- ✅ Database integrity intact
- ✅ Indexes being used by queries

---

## Support & Troubleshooting

### Documentation
- See `N1_OPTIMIZATION_REPORT.md` for detailed optimization report
- See `INTEGRATION_N1_OPTIMIZATION_COMPLETE.md` for integration details
- See `START_HERE.md` for project overview

### Getting Help
1. Check logs: `tail -f storage/logs/laravel.log`
2. Run tests: `php test_core_features.php`
3. Check database: `php artisan tinker`
4. Review migration status: `php artisan migrate:status`

---

## Timeline

- **Pre-Deployment:** Run all tests and backups (5 minutes)
- **Deployment:** Push code and run migrations (10 minutes)
- **Verification:** Run tests and performance checks (10 minutes)
- **Monitoring:** Watch logs for first 24 hours
- **Optimization:** Fine-tune caching and indexes (ongoing)

---

**Last Updated:** 2024-12-20
**Status:** Ready for Production Deployment ✅
