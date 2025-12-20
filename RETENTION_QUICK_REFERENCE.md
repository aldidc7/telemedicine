# Quick Reference: Medical Data Retention

## 🎯 Jawaban Singkat

**Q: Boleh menghapus data pasien?**
```
❌ TIDAK - Data medis tidak boleh dihapus sepenuhnya
✅ GUNAKAN: Soft Delete (archive, bukan hapus)
✅ SIMPAN: Minimum 7-10 tahun untuk compliance
```

---

## 📋 Regulasi Internasional (Perbandingan)

```
┌──────────────┬─────────────┬──────────────────────────┐
│ Negara       │ Retention   │ Catatan                  │
├──────────────┼─────────────┼──────────────────────────┤
│ 🇬🇧 UK       │ 7+ tahun    │ Anak sampai 20 tahun     │
│ 🇨🇦 Canada   │ 15 tahun    │ Legal document           │
│ 🇩🇪 Germany  │ 10 tahun    │ Untuk future treatment   │
│ 🇺🇸 USA      │ 3-7 tahun   │ Varies by state          │
│ 🇮🇩 Indonesia│ Permenkes   │ Wajib simpan (tdk ditentukan)
│ 🏥 JCI       │ 5-10 tahun  │ Hospital standard        │
└──────────────┴─────────────┴──────────────────────────┘
```

---

## 💾 Pattern: Soft Delete vs Hard Delete

### ❌ **Hard Delete** (DILARANG)
```sql
DELETE FROM patients WHERE id = 123;
-- ❌ Data hilang selamanya
-- ❌ Tidak bisa audit
-- ❌ Melanggar regulasi
```

### ✅ **Soft Delete** (RECOMMENDED)
```sql
UPDATE patients SET deleted_at = NOW() WHERE id = 123;
-- ✅ Data tersimpan
-- ✅ Bisa di-restore
-- ✅ Sesuai compliance
-- ✅ Audit trail ada
```

---

## 📊 Lifecycle Data Pasien

```
PASIEN AKTIF
     ↓
[Konsultasi, Diagnosis, Treatment]
     ↓
Pasien tidak aktif lagi?
     ↓
     ├─→ SOFT DELETE (Archive)
     │   Status: "archived"
     │   deleted_at: FILLED
     │   ✅ Data tetap ada di DB
     │   ✅ Tidak tampil di list
     │   ✅ Bisa di-restore
     │
     └─→ Tunggu 7-10 TAHUN
         ├─→ Cek apakah diperlukan untuk case baru
         │
         └─→ HARD DELETE (Permanent)
             status: deleted
             ✅ Baru boleh hapus setelah retention period
```

---

## 🔧 Implementasi Rekomendasi

### **1️⃣ Patient Model (Sudah Benar)**
```php
// app/Models/Patient.php
use SoftDeletes;

class Patient extends Model {
    use SoftDeletes;
    protected $dates = ['deleted_at'];
}
```
✅ Sudah soft delete, jangan diubah

---

### **2️⃣ ManagePasienPage.vue**

**Status badges:**
```
🟢 ACTIVE    → Pasien aktif
🟡 INACTIVE  → Sudah tidak praktek tapi data tersimpan
⚫ ARCHIVED   → Di-arsipkan karena alasan tertentu
```

**Delete button:**
```vue
<!-- SEBELUM: -->
<button @click="deletePatient">❌ Delete</button>

<!-- SESUDAH: -->
<button @click="archivePatient">📦 Arsipkan Dari Daftar</button>
<span class="text-xs">Data tetap tersimpan untuk compliance</span>
```

---

### **3️⃣ Procedures/Tindakan (PENTING)**

**JANGAN gunakan delete untuk procedures!**

```php
// ❌ JANGAN
$procedure->delete();

// ✅ GUNAKAN STATUS
$procedure->update([
    'status' => 'cancelled',  // atau 'void', 'completed'
    'cancellation_reason' => 'Pasien tidak datang',
    'cancelled_at' => now()
]);
```

---

## ⚖️ Compliance Requirements

```
✅ MUST HAVE:
  □ Soft delete untuk patient
  □ Archive status untuk inactive patients
  □ Retention policy 7+ tahun
  □ Audit log lengkap
  □ Backup reguler
  □ Access control

✅ NICE TO HAVE:
  □ Document retention schedule
  □ Purge automation (setelah 7 tahun)
  □ Privacy policy statement
  □ Encryption untuk sensitive data

❌ JANGAN:
  □ Hard delete patient records
  □ Delete consultation/diagnosis history
  □ Delete tanpa reason & timestamp
  □ Tanpa audit trail
```

---

## 📌 Untuk Thesis

**Explain to evaluators:**

> "Aplikasi menggunakan **Soft Delete Pattern** untuk patient management, 
> yang memenuhi standar **HIPAA, PHIPA, dan regulasi kesehatan Indonesia**. 
> Data pasien tidak dihapus sepenuhnya (hard delete) melainkan di-archive 
> (soft delete) untuk mempertahankan audit trail dan compliance dengan 
> peraturan permenkes tentang rekam medis minimum 7-10 tahun."

---

## 🎓 Key Concept untuk Discussion

**Yang sebaiknya dijelaskan di thesis:**

1. **Why Not Hard Delete?**
   - Medical records adalah legal documents
   - Diperlukan untuk future care continuity
   - Audit trail untuk compliance & medico-legal

2. **Soft Delete Benefits:**
   - Data tetap ada tapi tidak tampil di UI
   - Bisa di-restore jika ada error
   - Sesuai regulasi kesehatan

3. **Retention Policy:**
   - Keep 7-10 years (per international standard)
   - Archive, bukan delete
   - Hard delete hanya setelah retention period

4. **Audit Trail:**
   - Track siapa delete, kapan, alasan apa
   - Mandatory untuk healthcare systems
   - Untuk compliance & investigation

---

## 🚀 Next Steps

1. **Update ManagePasienPage.vue**
   - Ubah "Delete" button menjadi "Archive"
   - Tambahkan reason modal
   - Show archived patients dengan restore option

2. **Add Archive Status Column**
   - Migration: add `status` column (active/inactive/archived)
   - Add `archived_at` & `archive_reason`

3. **Create AuditLog Service**
   - Track setiap perubahan data
   - Log deletion dengan reason

4. **Document Retention Policy**
   - Buat dokumen untuk compliance
   - Outline untuk 7-10 year retention

5. **Add Tests**
   - Test soft delete functionality
   - Test restore functionality
   - Test audit logging

---

**Kesimpulannya:** Anda membuat keputusan yang benar dengan tidak menghapus data pasien sepenuhnya. 
Ini adalah best practice dalam healthcare systems yang compliant dengan regulasi internasional. ✅
