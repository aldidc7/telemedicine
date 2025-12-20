# 🏥 Kebijakan Privasi dan Perlindungan Data Telemedicine

**Effective Date:** 2025  
**Last Updated:** 2025  
**Language:** Indonesian & English

---

## 📋 Daftar Isi | Table of Contents

1. [Pendahuluan](#pendahuluan--introduction)
2. [Data yang Dikumpulkan](#data-yang-dikumpulkan--data-collection)
3. [Penggunaan Data](#penggunaan-data--data-usage)
4. [Pihak yang Memiliki Akses](#pihak-yang-memiliki-akses--data-handlers)
5. [Keamanan Data](#keamanan-data--data-security)
6. [Penyimpanan dan Retensi](#penyimpanan-dan-retensi--storage-and-retention)
7. [Hak-Hak Pasien](#hak-hak-pasien--patient-rights)
8. [Informasi Telemedicine](#informasi-telemedicine--telemedicine-information)
9. [Perubahan Kebijakan](#perubahan-kebijakan--policy-changes)
10. [Kontak](#kontak--contact)

---

## PENDAHULUAN | INTRODUCTION

### Bahasa Indonesia

Kebijakan Privasi ini menjelaskan bagaimana Aplikasi Telemedicine mengumpulkan, menggunakan, melindungi, dan menyimpan informasi pribadi Anda, khususnya informasi kesehatan (data medis sensitif).

**Kami berkomitmen untuk:**
- ✅ Melindungi privasi dan kerahasiaan data pasien
- ✅ Mematuhi regulasi kesehatan Indonesia (JKN/BPJS, Peraturan Kesehatan)
- ✅ Mengikuti standar telemedicine internasional (WHO, India 2020 Guidelines)
- ✅ Menyediakan transparansi penuh tentang penggunaan data
- ✅ Memberikan kontrol kepada pasien atas data mereka

### English

This Privacy Policy explains how the Telemedicine Application collects, uses, protects, and stores your personal information, particularly health information (sensitive medical data).

**We are committed to:**
- ✅ Protecting patient privacy and data confidentiality
- ✅ Complying with Indonesian healthcare regulations (JKN/BPJS, Health Regulations)
- ✅ Following international telemedicine standards (WHO, India 2020 Guidelines)
- ✅ Providing full transparency about data usage
- ✅ Giving patients control over their data

---

## DATA YANG DIKUMPULKAN | DATA COLLECTION

### Kategori Data | Data Categories

| Kategori | Contoh | Tujuan |
|----------|---------|--------|
| **Data Identitas** | Nama, TTL, NIK, nomor rekam medis | Identifikasi dan verifikasi pasien |
| **Data Kontak** | Email, nomor telepon, alamat | Komunikasi dan notifikasi |
| **Data Medis** | Keluhan, diagnosis, riwayat kesehatan, resep | Layanan konsultasi dan perawatan |
| **Data Biometrik** | (Jika ada) Foto profil untuk verifikasi | Keamanan akun |
| **Data Teknis** | IP address, browser, perangkat | Keamanan dan analitik |
| **Data Lokasi** | (Opsional) Lokasi user | Rekomendasi dokter terdekat |
| **Data Finansial** | (Jika ada) Data pembayaran | Pemrosesan transaksi |

### Bagaimana Data Dikumpulkan | Data Collection Methods

✅ **Langsung dari Anda**: Saat Anda mendaftar, membuat profil, booking konsultasi
✅ **Dari Dokter**: Catatan medis yang dibuat dokter selama konsultasi  
✅ **Otomatis dari Sistem**: IP address, timestamp, perangkat yang digunakan
✅ **Tidak pernah**: Kami tidak membeli data dari pihak ketiga

---

## PENGGUNAAN DATA | DATA USAGE

### Tujuan Penggunaan | Purpose of Use

**WAJIB** (Legal Requirements):
1. ✅ Menyediakan layanan telemedicine (konsultasi dokter-pasien)
2. ✅ Menciptakan dan mengelola rekam medis pasien
3. ✅ Memproses pembayaran dan billing
4. ✅ Mematuhi kewajiban hukum dan regulasi kesehatan
5. ✅ Audit dan compliance tracking (minimal 7-10 tahun per standar JCI)

**Kepentingan Sah** (Legitimate Interest):
1. ✅ Meningkatkan kualitas layanan berdasarkan feedback
2. ✅ Keamanan sistem dan pencegahan fraud
3. ✅ Analytics dan statistik (dalam bentuk aggregate/anonimisasi)
4. ✅ Komunikasi tentang perubahan layanan

**TIDAK PERNAH**:
- ❌ Menjual data pasien ke pihak ketiga
- ❌ Menggunakan data untuk marketing tanpa izin
- ❌ Membagikan data medis dengan pihak yang tidak terkait
- ❌ Menggunakan untuk kepentingan komersial di luar layanan kesehatan

---

## PIHAK YANG MEMILIKI AKSES | DATA HANDLERS

### Pengguna Internal | Internal Users

| Role | Akses | Alasan |
|------|-------|--------|
| **Dokter** | Lihat data medis pasiennya | Memberikan konsultasi dan perawatan |
| **Pasien** | Lihat data medis mereka sendiri | Hak akses kepada rekam medis |
| **Admin** | Kelola sistem, user, verifikasi dokter | Administrasi dan compliance |
| **Support** | Lihat minimal data (nama, kontak saja) | Customer support |

**Semua akses dicatat dalam audit log dan tidak dapat dihapus.**

### Pihak Ketiga / Third-Party Vendors

#### 1. **Cloud Infrastructure Provider**
- **Penyedia**: [Hosting Provider - sesuaikan sesuai infrastruktur Anda]
- **Akses**: Database server, backup, disaster recovery
- **Perlindungan**: Enkripsi data in transit, server-level security
- **Perjanjian**: Data Processing Agreement (DPA) ditandatangani

#### 2. **Real-Time Messaging Service**
- **Penyedia**: Pusher (untuk chat real-time)
- **Akses**: Pesan chat antara dokter-pasien
- **Perlindungan**: TLS encryption, secure connection
- **Privacy**: Pusher tidak menyimpan pesan secara permanen

#### 3. **Email Service** (Jika ada)
- **Penyedia**: [Email Provider]
- **Akses**: Notifikasi, password reset
- **Perlindungan**: Enkripsi, secure protocol
- **Perjanjian**: Data Processing Agreement

#### 4. **Payment Gateway** (Jika ada)
- **Penyedia**: [Payment Provider]
- **Akses**: Data pembayaran (PCI DSS compliant)
- **Perlindungan**: PCI DSS Level 1, tokenization
- **Perjanjian**: Payment processor agreement

### Data Handler Responsibilities | Tanggung Jawab Penyedia

Semua penyedia layanan wajib:
- ✅ Menjaga kerahasiaan data
- ✅ Menggunakan hanya untuk tujuan yang disetujui
- ✅ Menerapkan security measures yang sama
- ✅ Melaporkan breach dalam 24 jam
- ✅ Menghapus atau mengembalikan data saat kontrak selesai

---

## KEAMANAN DATA | DATA SECURITY

### Enkripsi | Encryption

**Data in Transit** (Ketika dikirim):
- ✅ **HTTPS/TLS 1.2+** untuk semua komunikasi website
- ✅ **API Security**: Bearer token authentication + CORS validation
- ✅ **Real-time Chat**: Encrypted via Pusher TLS
- ✅ **Database Connection**: Secure connection (SSL/TLS)

**Data at Rest** (Saat disimpan):
- ✅ **Database-level encryption** untuk field sensitif
  - Password: Laravel bcrypt (tidak bisa didekripsi)
  - Medical records: Encrypted with application key
  - Contact details: Encrypted in database
- ✅ **Backup encryption**: Encrypted backups disimpan
- ✅ **File uploads**: Encrypted storage, tidak accessible secara publik

### Kontrol Akses | Access Control

- ✅ **Role-based Access Control (RBAC)**
  - Pasien hanya lihat data mereka
  - Dokter hanya lihat data pasien mereka
  - Admin verifikasi dan monitoring saja
  
- ✅ **Authentication**
  - Password strength validation (minimum 8 karakter)
  - 2FA jika diimplementasikan
  - Session timeout untuk keamanan
  
- ✅ **Authorization**
  - Setiap request diverifikasi permissions
  - Audit log semua akses
  - Rate limiting untuk API

### Audit Logging | Pencatatan Audit

**Setiap akses dicatat**:
- ✅ Siapa yang akses (user ID + role)
- ✅ Apa yang diakses (resource + field)
- ✅ Kapan (timestamp)
- ✅ Dari mana (IP address)
- ✅ Menggunakan apa (device + browser)

**Log tidak bisa dihapus atau diubah** - immutable untuk compliance

**Retained untuk**: Minimal 1 tahun, medical data 7-10 tahun per JCI standard

### Incident Response | Respons Insiden

Jika ada security breach:
1. ✅ Deteksi dan isolasi (< 1 jam)
2. ✅ Investigasi (< 24 jam)
3. ✅ Notifikasi pengguna yang terkena
4. ✅ Lapor ke pihak berwenang (jika required)
5. ✅ Public disclosure (jika required)

---

## PENYIMPANAN DAN RETENSI | STORAGE AND RETENTION

### Data Retention Policy

| Jenis Data | Durasi | Alasan |
|-----------|--------|--------|
| **Rekam Medis Pasien** | 7-10 tahun | JCI Standard, kebutuhan klinis |
| **Aktivitas Login** | 2 tahun | Security dan compliance |
| **Audit Log** | 7 tahun | Legal dan compliance |
| **Chat History** | 5 tahun | Rekam medis, kontinuitas perawatan |
| **Dokumen Verifikasi Dokter** | Selama registered | License requirement |
| **Deleted Accounts** | 30 hari (soft delete) | Recovery, compliance |

### Soft Delete Policy

Kami menggunakan **soft delete** (tidak menghapus data selamanya):

✅ **Kenapa?**
- Rekam medis harus tersedia untuk audit, legal, dan continuity of care
- Regulasi kesehatan melarang menghapus rekam medis pasien
- Data diperlukan untuk verifikasi riwayat perawatan

✅ **Bagaimana?**
- Data ditandai sebagai "deleted" tapi tetap dalam database
- Hanya user dan authorized staff yang bisa lihat
- Tidak bisa dipulihkan oleh pengguna normal
- Admin/System tetap bisa lihat untuk audit

### Penghapusan Data | Data Deletion

**Data Pasien yang Bisa Dihapus**:
- ❌ Rekam medis pasien (tidak bisa dihapus)
- ❌ Riwayat konsultasi (tidak bisa dihapus)
- ✅ Akun pengguna (soft delete - data tetap untuk audit)
- ✅ Data kontak sekunder (alamat tambahan, telepon alternatif)
- ✅ Preferensi (notification settings)

**Cara Menghapus Akun**:
1. Buka Settings → Privacy & Data
2. Klik "Request Account Deletion"
3. Konfirmasi via email
4. Data akan di-soft-delete dalam 30 hari

---

## HAK-HAK PASIEN | PATIENT RIGHTS

### Hak Akses | Right to Access

✅ **Pasien berhak**:
- Mengakses semua data medis mereka kapan saja
- Download copy rekam medis dalam format elektronik
- Melihat history perubahan data mereka

📥 **Cara akses**:
```
1. Login ke aplikasi
2. Menu: Profile → My Medical Records → Download
3. Atau request via menu: Privacy → Request Data Export
```

### Hak Koreksi | Right to Correction

✅ **Pasien berhak**:
- Meminta koreksi data yang tidak akurat
- Menambahkan informasi penting yang ketinggalan
- Menandai data yang dipercaya kurang lengkap

🔧 **Cara koreksi**:
```
1. Identifikasi data yang salah
2. Menu: Privacy → Request Correction
3. Jelaskan koreksi yang diminta
4. Admin akan verifikasi dan update dalam 5 hari kerja
5. Update dicatat dalam audit trail
```

### Hak Penghapusan | Right to Deletion

⚠️ **Pembatasan**:
- Rekam medis **TIDAK** bisa dihapus (regulasi kesehatan)
- Riwayat konsultasi **TIDAK** bisa dihapus (untuk continuity of care)
- Data lain bisa dihapus per peraturan

✅ **Data yang bisa dihapus**:
- Akun pengguna (soft delete)
- Data kontak sekunder
- Foto/dokumen non-medis
- Preferensi

### Hak Portabilitas | Right to Portability

✅ **Pasien berhak**:
- Menerima data dalam format standar (PDF, CSV)
- Memberikan data ke provider lain
- Tidak ada hambatan teknis

📤 **Cara export**:
```
1. Menu: Privacy & Data → Export My Data
2. Pilih format (PDF, CSV, XML)
3. Pilih rentang data (all, last 1 year, etc)
4. Download otomatis atau email link
```

### Hak Informasi | Right to Information

✅ **Pasien berhak tahu**:
- Siapa akses data mereka
- Kapan data diakses
- Untuk tujuan apa
- Apakah ada data breach

📊 **Laporan akses**:
```
1. Menu: Privacy & Data → Access Log
2. Lihat setiap akses ke data Anda
3. Filter berdasarkan dokter, tanggal, tipe akses
```

---

## INFORMASI TELEMEDICINE | TELEMEDICINE INFORMATION

### Informed Consent untuk Telemedicine

**Sebelum konsultasi pertama, pasien HARUS**:
1. ✅ Membaca penjelasan telemedicine
2. ✅ Memahami keterbatasan telemedicine
3. ✅ Menyetujui terms telemedicine
4. ✅ Memberikan informed consent (digital)

**Penjelasan Keterbatasan Telemedicine**:
- ⚠️ Telemedicine berbasis teks/chat tidak dapat menggantikan pemeriksaan fisik lengkap
- ⚠️ Untuk kasus kompleks, dokter mungkin rekomendasikan pemeriksaan tatap muka
- ⚠️ Emergency medis harus ditangani di rumah sakit, bukan via telemedicine
- ⚠️ Kualitas layanan tergantung koneksi internet

**Keuntungan Telemedicine**:
- ✅ Akses dokter dari mana saja
- ✅ Lebih cepat daripada appointment tatap muka
- ✅ Lebih nyaman untuk konsultasi non-emergency
- ✅ Rekam medis terintegrasi

### Persetujuan Data Processing

**Pasien setuju data mereka digunakan untuk**:
1. ✅ Memberikan konsultasi dan perawatan
2. ✅ Menciptakan rekam medis
3. ✅ Integrasi dengan SIMRS (hospital system)
4. ✅ Audit dan compliance
5. ✅ Keamanan dan fraud prevention
6. ✅ Improvement layanan (analytics aggregate)

**Pasien DAPAT MENOLAK**:
- ❌ Marketing communications → opt-out dalam settings
- ❌ Analytics non-essential → opt-out dalam settings
- ❌ Data sharing dengan pihak ketiga (kecuali untuk perawatan)

---

## PERUBAHAN KEBIJAKAN | POLICY CHANGES

### Jika Kebijakan Berubah

- ✅ Kami akan memberikan notifikasi 30 hari sebelumnya
- ✅ Untuk perubahan minor (format, contact): update langsung
- ✅ Untuk perubahan major (data usage baru): perlu re-consent
- ✅ Pasien bisa opt-out dari fitur baru

### Versi Kebijakan

| Versi | Tanggal | Perubahan |
|-------|---------|----------|
| 1.0 | 2025 | Initial version - Telemedicine launch |

---

## PERATURAN YANG BERLAKU | APPLICABLE LAWS

Kebijakan privasi ini mematuhi:
- ✅ **Indonesia**: Undang-undang Kesehatan No. 36 Tahun 2009
- ✅ **Indonesia**: Regulasi Telemedicine dari Kemenkes
- ✅ **Indonesia**: JKN/BPJS Kesehatan Standards (data protection)
- ✅ **Indonesia**: Standar Akreditasi Rumah Sakit (JCI)
- ✅ **International**: WHO Telemedicine Guidelines
- ✅ **International**: India Telemedicine Practice Guidelines 2020
- ✅ **International**: Security best practices (if serving international users)

---

## KONTAK | CONTACT

### Untuk Pertanyaan Privasi | Privacy Questions

📧 **Email**: privacy@telemedicine-app.com  
📞 **Telepon**: +62-XXX-XXX-XXXX  
📬 **Alamat**: [Kantor Aplikasi Telemedicine]  
🕐 **Response time**: 2-3 hari kerja

### Untuk Melaporkan Breach | Report Security Breach

🚨 **URGENT - Email**: security@telemedicine-app.com  
🚨 **Response time**: URGENT (< 24 jam)

**Sertakan**:
- Deskripsi incident
- Data/rekam medis yang terkena
- Waktu incident
- Kontak Anda untuk follow-up

### Data Protection Officer (DPO) | Petugas Perlindungan Data

**Nama**: [DPO Name]  
**Email**: dpo@telemedicine-app.com  
**Telepon**: [DPO Phone]

---

## PENGAKUAN PENGGUNA | USER ACKNOWLEDGMENT

Dengan menggunakan Aplikasi Telemedicine, Anda:

- ✅ **Mengakui** telah membaca kebijakan privasi ini
- ✅ **Memahami** bagaimana data Anda digunakan
- ✅ **Setuju** dengan terms dan conditions
- ✅ **Memberikan izin** untuk data processing sebagaimana dijelaskan
- ✅ **Memahami** bahwa informed consent diperlukan sebelum telemedicine

**Jika tidak setuju**, jangan gunakan aplikasi. Hubungi support untuk discuss concerns.

---

## LAMPIRAN | APPENDIX

### A. Standar Internasional yang Dipatuhi

1. **WHO Telemedicine Framework**
   - Store-and-forward (asynchronous)
   - Real-time interactive
   - Remote patient monitoring
   - Mobile health (mHealth)

2. **India Telemedicine Practice Guidelines (2020)**
   - Informed consent requirement
   - Doctor-patient relationship establishment
   - Medical record structure (properly structured, preferably electronic)

3. **Ryan Haight Act (US, 2008)** - Jika applicable
   - Valid telemedicine consultation required before prescriptions
   - Documented relationship with patient

4. **GDPR** - Jika serving EU citizens
   - Right to access, correction, deletion
   - Data breach notification
   - Privacy by design

---

## 📝 FINAL NOTES

Kebijakan privasi ini adalah komitmen kami untuk:
- 🏥 Melayani pasien dengan integritas
- 🔒 Melindungi data medis dengan standar tertinggi
- 📋 Mematuhi semua regulasi kesehatan yang berlaku
- 👥 Memberikan transparansi penuh kepada pasien
- ⚖️ Menyeimbangkan inovasi dengan perlindungan privasi

**Terima kasih telah mempercayai kami dengan informasi kesehatan Anda.**

---

**Version**: 1.0  
**Effective Date**: 2025  
**Next Review Date**: [1 tahun sejak effective date]  
**Document Classification**: Public (dapat dibagikan dengan pasien)

