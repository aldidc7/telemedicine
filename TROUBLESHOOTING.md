# 🔧 Telemedicine Web - Troubleshooting Guide

Jika website tidak bisa dibuka, ikuti langkah-langkah di bawah ini:

---

## 📋 Checklist Penyebab Umum

### 1. ✅ Ensure Database is Set Up
```bash
# Create/verify database file exists
touch database/database.sqlite

# Run migrations
php artisan migrate:fresh --seed

# Check if successful
echo "Database setup complete"
```

### 2. ✅ Clear Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### 3. ✅ Set Storage Permissions
```bash
# Windows PowerShell - give full permissions
icacls "storage" /grant:r "%USERNAME%:F" /t
icacls "bootstrap/cache" /grant:r "%USERNAME%:F" /t
```

### 4. ✅ Install Dependencies
```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install

# Build assets
npm run build
```

### 5. ✅ Start Development Server
```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Vite Dev Server (untuk assets)
npm run dev
```

---

## 🌐 Access Locations

Once running, access at:

| Service | URL | Purpose |
|---------|-----|---------|
| **Web App** | http://localhost:8000 | Main application |
| **API Docs** | http://localhost:8000/api/documentation | Swagger API docs |
| **Health Check** | http://localhost:8000/api/v1/health | API status |

---

## 🔍 Debugging Steps

### Check Laravel is Running
```bash
# Check if server process is running
netstat -ano | findstr ":8000"

# If nothing shows, server is NOT running
# Start it with: php artisan serve
```

### Check Vite is Running  
```bash
# Check if Vite dev server is running
netstat -ano | findstr ":5173"

# If nothing shows, start with: npm run dev
```

### Check Database
```bash
php artisan tinker

# Inside tinker:
>>> Illuminate\Support\Facades\DB::connection()->getPdo()
// Should return a PDO object, not an error

>>> User::count()
// Should return a number, not an error
```

### Check Logs
```bash
# View latest errors
Get-Content storage/logs/laravel.log -Tail 50

# Or watch live
Get-Content -Path storage/logs/laravel.log -Wait
```

---

## 🚀 Complete Fresh Start

If still having issues, do a complete fresh start:

```bash
# 1. Clear everything
php artisan migrate:fresh
php artisan db:seed

# 2. Clear all caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# 3. Regenerate app key (if needed)
php artisan key:generate

# 4. Restart servers
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev
```

---

## ❌ Common Errors & Fixes

### Error: "SQLSTATE[HY000]: General error..."
```bash
# Database file missing or corrupted
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --seed
```

### Error: "Class not found"
```bash
# Re-install dependencies
composer install
php artisan optimize:clear
```

### Error: "npm command not found"
```bash
# Check if Node is installed
node --version

# If not, install Node.js from https://nodejs.org/
# Then restart terminal and try again
```

### Error: "Port 8000 already in use"
```bash
# Kill process on port 8000
Get-Process | Where-Object { $_.Port -eq 8000 } | Stop-Process

# Or use different port
php artisan serve --port=8001
```

---

## 📱 Access from Another Device

To access from another computer on same network:

```bash
# Find your IP address
ipconfig /all
# Look for "IPv4 Address" under your network adapter

# Start server listening on all interfaces
php artisan serve --host=0.0.0.0 --port=8000

# Then access from other device:
# http://<YOUR_IP>:8000
```

---

## ✅ Verification Checklist

Run this checklist to verify everything:

- [ ] Database file exists at `database/database.sqlite`
- [ ] `composer install` completed successfully
- [ ] `npm install` completed successfully
- [ ] `npm run build` or `npm run dev` ran without errors
- [ ] `php artisan migrate:fresh --seed` completed successfully
- [ ] No errors in `storage/logs/laravel.log`
- [ ] Laravel server running on port 8000
- [ ] Vite dev server running on port 5173
- [ ] Can access http://localhost:8000
- [ ] Can access http://localhost:8000/api/v1/health
- [ ] Swagger docs load at http://localhost:8000/api/documentation

---

## 🆘 Still Not Working?

Provide these details:

1. **What error message do you see?** (screenshot or exact message)
2. **What's in the logs?** (tail of storage/logs/laravel.log)
3. **What command did you run?** (php artisan serve, npm run dev, etc)
4. **What OS are you on?** (Windows, Mac, Linux)
5. **What Node/PHP versions?** (node --version, php --version)

Then we can debug further!

---

## 📚 Related Docs

- [PHASE_1_QUICK_START.md](PHASE_1_QUICK_START.md) - Setup guide
- [PHASE_1_IMPLEMENTATION_GUIDE.md](PHASE_1_IMPLEMENTATION_GUIDE.md) - Detailed setup
- [README.md](README.md) - Project overview
