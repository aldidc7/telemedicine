# Panduan Login & Setup Test Data

## Problem: Login Tidak Bisa

Jika Anda mencoba login dengan email `pasien@test.com`, `dokter@test.com`, atau `admin@test.com` dan mendapat error, itu **NORMAL** karena test data belum di-seed.

## Solusi

### ✅ Opsi 1: Gunakan Seeder (Recommended)

Jalankan seeder untuk membuat test data secara otomatis:

```bash
# Jalankan migrations + seeding
php artisan migrate:fresh --seed

# Output akan menampilkan:
# ✅ Pasien seeder completed - 2 pasien created
# 📧 Email: ahmad.zaki@email.com
# 🔐 Password: password123
#
# ✅ Dokter seeder completed - X dokter created
# 📧 Email: drsuryanto@email.com
# 🔐 Password: password123
#
# ✅ Admin seeder completed - 1 admin created
# 📧 Email: admin@telemedicine
# 🔐 Password: Rsud123!
```

Setelah seeding selesai, gunakan credentials di atas untuk login.

### Test Credentials Setelah Seeding

| Role | Email | Password | Status |
|------|-------|----------|--------|
| Pasien 1 | ahmad.zaki@email.com | password123 | ✅ Ready |
| Pasien 2 | siti.aminah@email.com | password123 | ✅ Ready |
| Dokter | drsuryanto@email.com | password123 | ✅ Ready |
| Admin | admin@telemedicine | Rsud123! | ✅ Ready |

### ✅ Opsi 2: Register Akun Baru

Jika Anda ingin membuat akun sendiri:

1. Buka http://localhost:5173
2. Klik **"Register"** atau **"Daftar"**
3. Pilih role (Pasien atau Dokter)
4. Isi form:
   ```
   Nama: [Nama Anda]
   Email: [Email Anda]
   Password: [Min 8 karakter]
   No. Telepon: [Opsional]
   Tanggal Lahir: [Pilih tanggal]
   Gender: [Pilih gender]
   ```
5. Klik **"Register"** atau **"Submit"**
6. **Verifikasi Email**:
   - Cek email Anda untuk verification link
   - Atau cek console Laravel untuk verification token
7. Klik verification link
8. **Login** dengan email & password baru
9. **Selesai!**

## Verifikasi Email Otomatis (Development)

Di development environment, email verification sudah otomatis di-skip. Jadi Anda langsung bisa login setelah register tanpa perlu verify email.

## Troubleshooting

### Error: "Invalid Credentials"
- **Penyebab**: Email/password salah atau belum di-seed
- **Solusi**: 
  - Jalankan `php artisan migrate:fresh --seed`
  - Atau gunakan test credentials yang benar (lihat tabel di atas)

### Error: "Email Not Verified"
- **Penyebab**: Email belum diverifikasi (jarang terjadi di dev)
- **Solusi**: 
  - Cek email Anda untuk verification link
  - Atau jalankan seeders ulang

### Database Error / Migration Failed
- **Penyebab**: Database belum setup dengan benar
- **Solusi**:
  ```bash
  # Reset database completely
  php artisan migrate:fresh
  
  # Lalu seed data
  php artisan db:seed
  ```

### Cannot Find `/api/docs` (API Documentation)
- **Penyebab**: OpenAPI documentation belum di-generate
- **Solusi**:
  ```bash
  # Generate OpenAPI documentation
  php artisan l5-swagger:generate
  
  # Access di: http://localhost:8000/api/docs
  ```

## Database Check

Untuk memastikan test data sudah ada di database:

```bash
# Open Laravel Tinker
php artisan tinker

# Check Pasien count
>>> App\Models\Pasien::count()
# Output: 2

# Check Dokter count
>>> App\Models\Dokter::count()
# Output: X

# Check Admin count
>>> App\Models\Admin::count()
# Output: 1

# Check user dengan email tertentu
>>> App\Models\User::where('email', 'ahmad.zaki@email.com')->first()
# Output: User object jika ada
```

## Summary

| Task | Command | Status |
|------|---------|--------|
| Setup Database | `php artisan migrate:fresh --seed` | ✅ |
| Login dengan test data | Gunakan credentials di atas | ✅ |
| Register akun baru | Visit http://localhost:5173 → Register | ✅ |
| Access API Docs | http://localhost:8000/api/docs | ✅ |

---

**Pertanyaan?** Baca kembali bagian yang relevan atau jalankan seeder untuk membuat test data secara otomatis.
