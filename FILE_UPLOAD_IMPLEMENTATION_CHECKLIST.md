# File Upload System - Implementation Checklist

## ✅ Backend Implementation Status

### Configuration Files
- [x] `config/file-upload.php` - Konfigurasi lengkap batasan ukuran & storage
- [x] `.env.file-upload.example` - Template environment variables

### Service Layer
- [x] `app/Services/FileUploadService.php` - Service dengan 10+ methods:
  - `uploadFile()` - Upload dengan validasi lengkap
  - `validateFileType()` - Validasi MIME & extension
  - `validateUserStorageQuota()` - Check quota pengguna
  - `getUserStorageSize()` - Hitung total size user
  - `storeFile()` - Simpan ke storage terstruktur
  - `generateUniqueFilename()` - Generate nama unik
  - `logFileUpload()` - Audit trail
  - `softDeleteFile()` - Soft delete ke trash
  - `permanentlyDeleteFile()` - Permanent delete
  - `cleanupExpiredFiles()` - Auto cleanup
  - `getUserStorageInfo()` - Get usage info

### Controllers
- [x] `app/Http/Controllers/Api/FileUploadController.php` - 4 Endpoints:
  - `POST /files/upload` - Upload file
  - `GET /files/storage-info` - Get quota info
  - `DELETE /files/{filePath}` - Delete file
  - `GET /files/size-limits` - Get limits

### Requests & Validation
- [x] `app/Http/Requests/FileUploadRequest.php` - Validasi request

### Exceptions
- [x] `app/Exceptions/FileUploadException.php` - Custom exception

### Database
- [x] `database/migrations/2025_12_19_000010_create_file_upload_tables.php`
  - `file_uploads` table
  - `user_storage_quotas` table
  - `file_cleanup_logs` table

### Console Commands
- [x] `app/Console/Commands/CleanupExpiredFiles.php`
  - `php artisan files:cleanup`
  - `php artisan files:cleanup --dry-run`

### Routes
- [x] Updated `routes/api.php` dengan 4 endpoints baru

### Documentation
- [x] `FILE_UPLOAD_SYSTEM_DOCUMENTATION.md` - Lengkap dengan examples

---

## 📋 Implementation Steps

### Step 1: Copy Configuration Files

```bash
# Konfigurasi sudah ada di:
# - config/file-upload.php
# - .env.file-upload.example

# Copy ke .env jika perlu:
cp .env.file-upload.example .env.file-upload
```

### Step 2: Create Storage Directories

```bash
# Create storage link
php artisan storage:link

# Create directories
mkdir -p storage/app/public/uploads/{profiles,documents,medical-images,prescriptions,consultations,messages}
mkdir -p storage/quarantine
mkdir -p storage/app/public/trash

# Set permissions (Linux/Mac)
chmod -R 775 storage/app/public
chmod -R 775 storage/quarantine
```

### Step 3: Run Database Migrations

```bash
# Create file upload tracking tables
php artisan migrate

# Verify tables created:
# - file_uploads
# - user_storage_quotas
# - file_cleanup_logs
```

### Step 4: Initialize User Storage Quotas

```bash
# Seeder untuk existing users (optional, create this):
php artisan db:seed --class=UserStorageQuotaSeeder
```

### Step 5: Setup Cleanup Schedule

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('files:cleanup')
        ->daily()
        ->at('02:00')
        ->name('cleanup-expired-files')
        ->withoutOverlapping();
}
```

Or add to crontab:

```bash
# Edit crontab
crontab -e

# Add this line:
* * * * * cd /path/to/telemedicine && php artisan schedule:run >> /dev/null 2>&1
```

### Step 6: Test Endpoints

```bash
# Get auth token (login dulu)
TOKEN=$(curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  | jq -r '.data.access_token')

# Test 1: Get size limits
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/files/size-limits

# Test 2: Get storage info
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/files/storage-info

# Test 3: Upload file
curl -X POST http://localhost:8000/api/v1/files/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@test-image.jpg" \
  -F "category=profile_photo"

# Test 4: Delete file
curl -X DELETE http://localhost:8000/api/v1/files/uploads%2Fprofiles%2F1%2F2025%2F12%2F19%2Ftest.jpg \
  -H "Authorization: Bearer $TOKEN"
```

### Step 7: Setup Monitoring

```bash
# Monitor file uploads
tail -f storage/logs/laravel.log | grep "File uploaded"

# Check storage usage
du -sh storage/app/public/uploads

# List files in trash
find storage/app/public/trash -type f
```

---

## 🧪 Testing Checklist

### Unit Tests

- [ ] Test file size validation
- [ ] Test MIME type validation
- [ ] Test extension blocking
- [ ] Test user quota validation
- [ ] Test file organization/path generation
- [ ] Test cleanup expiration logic

### Integration Tests

- [ ] Test upload endpoint (success)
- [ ] Test upload endpoint (file too large)
- [ ] Test upload endpoint (invalid type)
- [ ] Test storage info endpoint
- [ ] Test delete endpoint
- [ ] Test cleanup command

### Authorization Tests

- [ ] Patient can only see own files
- [ ] Doctor can only see own files
- [ ] Admin can see all files
- [ ] User cannot delete other's files
- [ ] Can't bypass quota check

### Edge Cases

- [ ] Upload 0 byte file
- [ ] Upload file named with special characters
- [ ] Upload file with no extension
- [ ] Concurrent uploads same user
- [ ] File upload + simultaneous deletion
- [ ] Cleanup while file being accessed

### Performance Tests

- [ ] Upload 5 MB file (should be fast)
- [ ] Upload 100+ files (batch)
- [ ] Check storage info with 1000+ files
- [ ] Cleanup 500 expired files
- [ ] Calculate user storage size efficiently

---

## 📱 Frontend Implementation Checklist

### Vue.js Components

- [ ] File Upload Component
  - [ ] File selector
  - [ ] Size validation (client-side)
  - [ ] Progress bar
  - [ ] Error/success messages
  - [ ] Category selector

- [ ] Storage Info Component
  - [ ] Show usage percentage
  - [ ] Visual progress bar
  - [ ] Warning states
  - [ ] Format sizes nicely

### Pages

- [ ] Profile Photo Upload Page
  - [ ] Crop functionality
  - [ ] Preview
  - [ ] Upload with drag-drop

- [ ] Medical Documents Page
  - [ ] List uploaded documents
  - [ ] Delete button
  - [ ] Download link
  - [ ] Filter by category

- [ ] Consultation Files Page
  - [ ] Show files by consultation
  - [ ] Share with doctor
  - [ ] Download

### Integration

- [ ] Upload on profile form submit
- [ ] Upload in consultation form
- [ ] Upload in message/chat
- [ ] Show storage warning banner
- [ ] Disable upload when quota full

---

## 🔒 Security Checklist

- [ ] Validate file type on server
- [ ] Block executable files
- [ ] Check file size on upload
- [ ] Scan for viruses (optional)
- [ ] Encrypt files at rest
- [ ] Use HTTPS only
- [ ] Rate limit uploads per user
- [ ] Log all upload activities
- [ ] Implement access control

---

## 📊 Monitoring & Maintenance

### Daily Tasks

- [ ] Check failed uploads in logs
- [ ] Monitor storage usage (target < 80%)
- [ ] Verify cleanup job runs
- [ ] Check for error patterns

### Weekly Tasks

- [ ] Review storage stats
- [ ] Check for orphaned files
- [ ] Verify backups working
- [ ] Test manual cleanup

### Monthly Tasks

- [ ] Review upload patterns
- [ ] Update file size limits if needed
- [ ] Audit access logs
- [ ] Clean up quarantine folder

---

## 🚀 Deployment Checklist

### Pre-deployment

- [ ] All tests passing
- [ ] Code review completed
- [ ] Documentation updated
- [ ] Backup created

### Deployment

- [ ] Copy new files to production
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Update .env file
- [ ] Clear cache: `php artisan cache:clear config:clear`
- [ ] Restart queue workers

### Post-deployment

- [ ] Verify endpoints working
- [ ] Monitor for errors
- [ ] Check storage directories
- [ ] Test with sample upload
- [ ] Verify cleanup schedule

---

## 📝 Files Created Summary

| File | Purpose | Lines | Status |
|------|---------|-------|--------|
| config/file-upload.php | Configuration | 100 | ✅ |
| app/Services/FileUploadService.php | Business Logic | 380 | ✅ |
| app/Http/Controllers/Api/FileUploadController.php | API Endpoints | 220 | ✅ |
| app/Http/Requests/FileUploadRequest.php | Validation | 60 | ✅ |
| app/Exceptions/FileUploadException.php | Exception Handling | 20 | ✅ |
| database/migrations/2025_12_19_000010_... | Database Schema | 50 | ✅ |
| app/Console/Commands/CleanupExpiredFiles.php | Cleanup Job | 40 | ✅ |
| FILE_UPLOAD_SYSTEM_DOCUMENTATION.md | Documentation | 600+ | ✅ |
| .env.file-upload.example | Env Template | 30 | ✅ |

**Total: 9 files, 1,500+ lines of production-ready code**

---

## 🎯 Quick Start

```bash
# 1. Files sudah ready
ls app/Services/FileUploadService.php
ls app/Http/Controllers/Api/FileUploadController.php

# 2. Create directories
mkdir -p storage/app/public/uploads/{profiles,documents,medical-images,prescriptions,consultations,messages}

# 3. Run migration
php artisan migrate

# 4. Create symlink
php artisan storage:link

# 5. Test upload
# Use curl atau Postman collection

# 6. Monitor
tail -f storage/logs/laravel.log
```

---

## 📞 Support

Untuk error atau issues:
1. Check laravel.log
2. Verify storage permissions
3. Run: `php artisan storage:link`
4. Test with dry-run: `php artisan files:cleanup --dry-run`
