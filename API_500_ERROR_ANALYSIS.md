# API 500 Error Analysis - PasienController & DokterController

## Summary
Found **3 critical issues** causing 500 errors on `/api/v1/pasien` and `/api/v1/dokter` endpoints.

---

## Issue #1: CRITICAL - Relationship Name Mismatch in PasienController

### Location
[PasienController.php](app/Http/Controllers/Api/PasienController.php#L48-L60)

### Problem
The `index()` method calls `$this->pasienService->getAllPasien()` which uses the relationship name `pengguna`:
```php
// In PasienService.php line 28
$query = Pasien::with('pengguna', 'konsultasi', 'rekamMedis');
```

But the actual relationship in the Pasien model is named `user`, NOT `pengguna`:
```php
// In Pasien.php line 83-88
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
```

**Result**: When eager loading `pengguna` (which doesn't exist), Laravel throws an exception → **500 error**

---

## Issue #2: CRITICAL - Relationship Name Mismatch in DokterController

### Location
[DokterService.php](app/Services/DokterService.php#L25-27)

### Problem
The `getAllDokter()` method in DokterService tries to eager load:
```php
$query = Dokter::with('pengguna', 'konsultasi');
```

But the Dokter model defines the relationship as `user`, NOT `pengguna`:
```php
// In Dokter.php line 104-109
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
```

**Result**: When eager loading `pengguna` (which doesn't exist), Laravel throws an exception → **500 error**

---

## Issue #3: CRITICAL - Wrong Relationship Name in DokterService Filter

### Location
[DokterService.php](app/Services/DokterService.php#L45-48)

### Problem
When filtering by status, the code tries to use:
```php
$query->whereHas('pengguna', function ($q) use ($status) {
    $q->where('is_active', $status === 'aktif');
});
```

Again, the relationship should be `user`, not `pengguna`.

---

## Secondary Issues Found

### Issue #4: Column Name Mismatch in DokterService Filter
[DokterService.php](app/Services/DokterService.php#L35)
```php
->orWhere('nip', 'like', "%{$search}%");
```

The Dokter model doesn't have an `nip` column, but might have it. This could cause a database error if the column doesn't exist.

---

## Summary Table

| File | Issue | Current | Should Be | Impact |
|------|-------|---------|-----------|--------|
| PasienService | Relationship | `->with('pengguna')` | `->with('user')` | 500 Error |
| DokterService | Relationship (load) | `->with('pengguna')` | `->with('user')` | 500 Error |
| DokterService | Relationship (filter) | `->whereHas('pengguna')` | `->whereHas('user')` | 500 Error |
| DokterService | Column reference | `'nip'` | Check if exists | Potential Error |

---

## Files Affected
1. `app/Services/PasienService.php` - Line 28
2. `app/Services/DokterService.php` - Lines 25, 45
3. `app/Services/DokterService.php` - Line 35 (potential issue)

---

## Recommended Fixes
All relationship references from `pengguna` need to be changed to `user` to match the actual relationship definitions in the models.
