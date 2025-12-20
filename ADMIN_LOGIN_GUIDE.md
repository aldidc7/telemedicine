# 🔐 Admin Login Guide

## ✅ Fixed Issue

**Problem:** Admin user tidak bisa login karena `email_verified_at` tidak terisi.

**Root Cause:** AdminSeeder membuat admin user tanpa men-set `email_verified_at`, namun AuthService memerlukan field ini untuk admin dan dokter agar bisa login.

**Solution:** Updated AdminSeeder untuk men-set `email_verified_at = now()` saat membuat admin user.

---

## 📝 Login Credentials

**Email:** `admin@telemedicine.local`
**Password:** `Rsud123!`

---

## 🚀 Login Flow

### 1. Reset Database (Fresh Start)
```bash
php artisan migrate:fresh --seed
```

### 2. Login via API
```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@telemedicine.local",
    "password": "Rsud123!"
  }'
```

### 3. Expected Response
```json
{
  "success": true,
  "pesan": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin Telemedicine",
      "email": "admin@telemedicine.local",
      "role": "admin",
      "is_active": true
    },
    "token": "xxx_token_xxx",
    "token_type": "Bearer"
  }
}
```

---

## 🔍 Verification Steps

### Check Admin User in Database
```sql
SELECT id, name, email, role, is_active, email_verified_at 
FROM users 
WHERE role = 'admin';
```

Should show:
```
id | name                 | email                    | role  | is_active | email_verified_at
1  | Admin Telemedicine   | admin@telemedicine.local | admin | 1         | 2025-12-21 ...
```

### Check Admin Profile
```sql
SELECT * FROM admins WHERE user_id = 1;
```

Should show:
```
id | user_id | permission_level | notes
1  | 1       | 3                | Super admin dengan akses penuh
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: "Email not verified"
**Cause:** `email_verified_at` is NULL in users table
**Solution:** Already fixed in AdminSeeder - run `php artisan migrate:fresh --seed`

### Issue 2: "User is inactive"
**Cause:** `is_active` is false
**Solution:** Check that `is_active` is set to true in seeder (it is)

### Issue 3: "Invalid email or password"
**Cause:** Wrong credentials
**Solution:** Use exact credentials:
- Email: `admin@telemedicine.local`
- Password: `Rsud123!`

### Issue 4: "Too many login attempts"
**Cause:** Rate limiting triggered (5 attempts per 15 min)
**Solution:** Wait 15 minutes or manually delete from rate limit cache:
```bash
php artisan tinker
> Cache::forget('login:admin@telemedicine.local:127.0.0.1')
```

---

## 🔐 Security Features

✅ Email verification mandatory for admin/doctor
✅ Rate limiting on login (5 attempts per 15 min)
✅ Password hashing with bcrypt
✅ JWT token generation
✅ Activity logging for failed attempts
✅ User active/inactive status checking

---

## 📋 Related Files Changed

- `database/seeders/AdminSeeder.php` - Added `email_verified_at` field
- `app/Services/AuthService.php` - Email verification check for admin (no change needed, working as designed)
- `app/Http/Controllers/Api/AuthController.php` - Login endpoint (no change needed)

---

## 🧪 Testing Login

### Using Postman
1. Set request to POST
2. URL: `http://localhost:8000/api/v1/auth/login`
3. Headers:
   ```
   Content-Type: application/json
   ```
4. Body (raw JSON):
   ```json
   {
     "email": "admin@telemedicine.local",
     "password": "Rsud123!"
   }
   ```
5. Click Send

### Using Laravel Tinker
```bash
php artisan tinker

# Test if admin exists
> User::where('email', 'admin@telemedicine.local')->first()

# Check password
> Hash::check('Rsud123!', User::where('email', 'admin@telemedicine.local')->first()->password)
# Should return true

# Check email verification
> User::where('email', 'admin@telemedicine.local')->first()->email_verified_at
# Should show a timestamp, not null
```

---

Generated: December 21, 2025
