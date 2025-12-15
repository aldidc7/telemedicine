# SQLite → MySQL Migration Guide

**Status**: READY TO EXECUTE  
**Date**: December 15, 2025  
**Estimated Time**: 1-2 hours

---

## Prerequisites

Sebelum mulai, pastikan:
- ✅ MySQL 8.0+ terinstall di sistem
- ✅ MySQL service running
- ✅ MySQL client akses (atau phpMyAdmin)
- ✅ Backup database SQLite (IMPORTANT!)

---

## Step 1: Backup Current Database

```bash
# Backup SQLite database
cp database/database.sqlite database/database.sqlite.backup

# Atau export data
php artisan tinker
# Di tinker:
# >>> $users = User::all();
# >>> dd($users); // Check data intact
```

---

## Step 2: Setup MySQL Database

### Option A: Local MySQL (Windows)

```bash
# Open MySQL command line
mysql -u root -p

# Create new database
CREATE DATABASE telemedicine_mysql CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Verify
SHOW DATABASES;
```

### Option B: Docker MySQL (Recommended)

```bash
# Run MySQL container
docker run --name mysql_telemedicine \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=telemedicine_mysql \
  -p 3306:3306 \
  -d mysql:8.0

# Verify
docker ps
```

### Option C: Cloud MySQL (AWS RDS / Google Cloud SQL)

```
Host: [your-db-instance].rds.amazonaws.com
User: admin
Password: [your-password]
Database: telemedicine_mysql
Port: 3306
```

---

## Step 3: Update .env Configuration

**File**: `.env`

Change:
```dotenv
# BEFORE
DB_CONNECTION=sqlite

# AFTER
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=telemedicine_mysql
DB_USERNAME=root
DB_PASSWORD=root
```

---

## Step 4: Create MySQL-Specific Migrations

**Issue**: Current migrations designed untuk SQLite, need to enhance untuk MySQL.

**New migration file**: `database/migrations/2025_12_15_create_mysql_constraints.php`

This akan add:
- ✅ Foreign key constraints
- ✅ Unique constraints
- ✅ Check constraints
- ✅ Proper indexes
- ✅ Character set & collation

---

## Step 5: Execute Migration

```bash
# Clear Artisan cache (important!)
php artisan optimize:clear

# Run fresh migrations (this creates all tables in MySQL)
php artisan migrate:fresh

# Verify tables created
mysql -u root -p telemedicine_mysql
SHOW TABLES;
DESC users;
DESC appointments;
```

---

## Step 6: Migrate Data (If Keeping Old Data)

Jika ingin migrate data dari SQLite ke MySQL:

```bash
# Export SQLite data ke CSV/JSON
php artisan tinker

# Contoh: Export users
$users = User::all();
Storage::put('exports/users.json', json_encode($users));

# Atau gunakan cara manual dengan script
```

**ATAU** jika fresh database OK:
```bash
# Just seed with faker data
php artisan db:seed
```

---

## Step 7: Test Database Connection

```bash
# Test MySQL connection
php artisan tinker

# Di tinker:
>>> DB::connection('mysql')->getPdo()
# Should return PDO object without error

>>> User::count()
# Should return number of users

>>> exit;
```

---

## Step 8: Verify Migrations

```bash
# Check migration status
php artisan migrate:status

# Should show all migrations as "Ran"

# Verify constraints exist
mysql -u root -p telemedicine_mysql

# Check foreign keys
SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE CONSTRAINT_SCHEMA = 'telemedicine_mysql' 
AND REFERENCED_TABLE_NAME IS NOT NULL;

# Check indexes
SHOW INDEXES FROM users;
SHOW INDEXES FROM appointments;
```

---

## Step 9: Test Application

```bash
# Start Laravel development server
php artisan serve

# Test endpoints
# 1. Register new user
POST http://localhost:8000/api/v1/auth/register
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "TestPass123!",
  "password_confirmation": "TestPass123!"
}

# 2. Login
POST http://localhost:8000/api/v1/auth/login
{
  "email": "test@example.com",
  "password": "TestPass123!"
}

# 3. List appointments
GET http://localhost:8000/api/v1/appointments

# 4. Check database
php artisan tinker
>>> User::with('appointments')->first();
```

---

## Step 10: Performance Tuning (Optional)

```bash
# Add MySQL-specific optimizations
# File: config/database.php

'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => 'InnoDB',  // ← Important for transactions
    'modes' => [
        'STRICT_TRANS_TABLES',
        'ERROR_FOR_DIVISION_BY_ZERO',
        'NO_ENGINE_SUBSTITUTION',
    ],
    'options' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
    ],
],
```

---

## Step 11: Cleanup & Verification

```bash
# Remove SQLite database (after verification)
# rm database/database.sqlite

# Verify all tests pass
php artisan test

# Check cache is cleared
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 15 database disk image is malformed"

**Cause**: SQLite configuration in config/database.php  
**Fix**: Ignore - this is from SQLite, MySQL won't have this

### Error: "SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'"

**Cause**: MySQL credentials wrong  
**Fix**: Check .env DB_USERNAME and DB_PASSWORD

### Error: "SQLSTATE[HY000] [2002] No such file or directory"

**Cause**: MySQL not running  
**Fix**: Start MySQL service

### Error: "Base table or view already exists"

**Cause**: Tables already exist  
**Fix**: 
```bash
# Drop all tables
php artisan migrate:reset

# Or manually
mysql -u root -p telemedicine_mysql < DROP_ALL_TABLES.sql

# Then migrate fresh
php artisan migrate:fresh
```

---

## Rollback Plan (If Something Goes Wrong)

```bash
# 1. Revert to SQLite
# Change .env back to:
DB_CONNECTION=sqlite

# 2. Restore from backup
cp database/database.sqlite.backup database/database.sqlite

# 3. Restart server
php artisan serve

# You're back to SQLite (temporary)
```

---

## Post-Migration Checklist

- [ ] Database: MySQL 8.0+ ready
- [ ] .env: Updated dengan MySQL credentials
- [ ] Migrations: All ran successfully
- [ ] Foreign keys: Exist and working
- [ ] Unique constraints: Applied (email, NIK, dll)
- [ ] Indexes: Created for common queries
- [ ] Application: Tested (register, login, appointments)
- [ ] Data: Migrated or seeded
- [ ] Performance: Verified fast
- [ ] Backup: SQLite backup saved

---

## Expected Benefits After Migration

✅ **Performance**: 10-50x faster queries  
✅ **Reliability**: Proper ACID transactions  
✅ **Scalability**: Support for 1000+ concurrent users  
✅ **Data Integrity**: Foreign key constraints enforced  
✅ **Full Text Search**: Possible with proper indexes  
✅ **Production Ready**: Enterprise-grade database  

---

## Timeline

- **Step 1-3**: 5 minutes (backup & setup)
- **Step 4-5**: 15 minutes (migrations & creation)
- **Step 6-7**: 10 minutes (data & testing)
- **Step 8-11**: 20 minutes (verification & cleanup)

**Total**: ~1 hour (mostly waiting for migration scripts)

---

## Next After MySQL Migration

Once MySQL is setup:
1. Rate limiting (15 minutes)
2. Input validation standardization (30 minutes)
3. WebSocket frontend (2-3 hours)

This akan bring aplikasi ke ~95% production readiness.

---

## Resources

- MySQL Documentation: https://dev.mysql.com/doc/
- Laravel Database: https://laravel.com/docs/database
- Laravel Migrations: https://laravel.com/docs/migrations
- InnoDB Guide: https://dev.mysql.com/doc/refman/8.0/en/innodb.html

---

**Ready to start? Let's begin!**
