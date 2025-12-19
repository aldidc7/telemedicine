# File Upload System dengan Batasan Ukuran

## Ringkasan

Sistem file upload yang comprehensive dengan batasan ukuran ketat untuk mencegah storage penuh terlalu cepat. Dirancang khusus untuk telemedicine dengan kategori file medis, foto, dokumen, dan resep.

---

## Fitur Utama

### 1. **Batasan Ukuran File per Kategori**

| Kategori | Ukuran Maksimal | Kegunaan |
|----------|----------------|----------|
| `profile_photo` | 5 MB | Foto profil dokter/pasien |
| `medical_document` | 10 MB | Dokumen medis (PDF, Word) |
| `medical_image` | 15 MB | Foto hasil lab/radiologi |
| `prescription` | 5 MB | Resep digital |
| `consultation_file` | 8 MB | File dalam chat konsultasi |
| `message_attachment` | 10 MB | Attachment dalam pesan |

### 2. **Batasan Total Storage per User**

| Role | Quota | Catatan |
|------|-------|---------|
| Patient | 500 MB | Total semua file milik pasien |
| Doctor | 1 GB | Total semua file milik dokter |
| Hospital | 10 GB | Total untuk rumah sakit |
| Admin | Unlimited | Tanpa batasan |

### 3. **Tipe File yang Diizinkan**

**Foto/Gambar:**
- JPEG, PNG, GIF, WebP, ICO

**Dokumen:**
- PDF, Word (.doc, .docx), Excel (.xls, .xlsx), Text, CSV

**Audio/Video:**
- MP3, WAV, MP4, MOV

**Arsip:**
- ZIP, RAR, 7Z

### 4. **File yang Diblok**

Executable, Script, Library, dan Macro files:
- `.exe`, `.bat`, `.php`, `.py`, `.sh`, `.dll`, `.msi`, `.deb`

### 5. **Soft Delete & Retention**

```
Upload → Active (unlimited time)
   ↓
Delete → Trash (30 days)
   ↓
Auto Delete → Permanently deleted
```

---

## Instalasi & Konfigurasi

### 1. Publish Configuration File

```bash
php artisan config:publish file-upload
```

### 2. Jalankan Migration

```bash
php artisan migrate
```

Ini akan membuat 3 tabel:
- `file_uploads` - Tracking setiap upload
- `user_storage_quotas` - Quota per user
- `file_cleanup_logs` - History cleanup

### 3. Setup Storage Directory

```bash
# Buat direktori public storage jika belum ada
php artisan storage:link

# Set permission
chmod -R 775 storage/app/public
```

---

## API Endpoints

### 1. Upload File

**Endpoint:** `POST /api/v1/files/upload`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```
file: <binary file data>
category: profile_photo|medical_document|medical_image|prescription|consultation_file|message_attachment
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "File berhasil diupload",
  "data": {
    "path": "uploads/profile_photo/1/2025/12/19/photo-1671452000-abc123.jpg",
    "url": "/storage/uploads/profile_photo/1/2025/12/19/photo-1671452000-abc123.jpg",
    "filename": "photo.jpg",
    "size": 2048000,
    "mime_type": "image/jpeg",
    "uploaded_at": "2025-12-19T10:30:00Z"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "File terlalu besar. Maksimal: 5 MB",
  "error_code": "FILE_UPLOAD_ERROR"
}
```

### 2. Get Storage Info

**Endpoint:** `GET /api/v1/files/storage-info`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "max_storage": 524288000,
    "current_usage": 262144000,
    "remaining_storage": 262144000,
    "usage_percent": 50.0,
    "max_storage_formatted": "500 MB",
    "current_usage_formatted": "250 MB",
    "remaining_storage_formatted": "250 MB"
  }
}
```

### 3. Delete File (Soft Delete)

**Endpoint:** `DELETE /api/v1/files/{filePath}`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body (URL encoded):**
```
filePath: uploads/profile_photo/1/2025/12/19/photo.jpg
```

**Response (200):**
```json
{
  "success": true,
  "message": "File berhasil dihapus"
}
```

### 4. Get Size Limits

**Endpoint:** `GET /api/v1/files/size-limits`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "profile_photo": {
      "bytes": 5242880,
      "formatted": "5 MB"
    },
    "medical_document": {
      "bytes": 10485760,
      "formatted": "10 MB"
    },
    "medical_image": {
      "bytes": 15728640,
      "formatted": "15 MB"
    },
    "prescription": {
      "bytes": 5242880,
      "formatted": "5 MB"
    },
    "consultation_file": {
      "bytes": 8388608,
      "formatted": "8 MB"
    },
    "message_attachment": {
      "bytes": 10485760,
      "formatted": "10 MB"
    }
  }
}
```

---

## Vue.js Implementation Examples

### 1. File Upload Component

```vue
<template>
  <div class="file-upload">
    <!-- File Input -->
    <div class="form-group">
      <label>Pilih File</label>
      <input 
        type="file" 
        @change="onFileSelected"
        :accept="acceptedFormats"
        ref="fileInput"
      >
    </div>

    <!-- File Info -->
    <div v-if="selectedFile" class="file-info">
      <p><strong>File:</strong> {{ selectedFile.name }}</p>
      <p><strong>Ukuran:</strong> {{ formatBytes(selectedFile.size) }}</p>
      <p v-if="isFileTooLarge" class="error">
        ⚠️ File terlalu besar. Maksimal: {{ maxFileSize }}
      </p>
    </div>

    <!-- Upload Progress -->
    <div v-if="isUploading" class="progress">
      <div class="progress-bar" :style="{ width: uploadProgress + '%' }">
        {{ uploadProgress }}%
      </div>
    </div>

    <!-- Upload Button -->
    <button 
      @click="uploadFile"
      :disabled="!selectedFile || isFileTooLarge || isUploading"
      class="btn btn-primary"
    >
      {{ isUploading ? 'Uploading...' : 'Upload' }}
    </button>

    <!-- Error Message -->
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  category: {
    type: String,
    default: 'consultation_file',
    validator: (value) => [
      'profile_photo',
      'medical_document',
      'medical_image',
      'prescription',
      'consultation_file',
      'message_attachment'
    ].includes(value)
  }
})

const emit = defineEmits(['upload-success', 'upload-error'])

const selectedFile = ref(null)
const isUploading = ref(false)
const uploadProgress = ref(0)
const errorMessage = ref('')
const successMessage = ref('')
const fileInput = ref(null)

const sizeLimits = {
  profile_photo: 5 * 1024 * 1024,      // 5 MB
  medical_document: 10 * 1024 * 1024,  // 10 MB
  medical_image: 15 * 1024 * 1024,     // 15 MB
  prescription: 5 * 1024 * 1024,       // 5 MB
  consultation_file: 8 * 1024 * 1024,  // 8 MB
  message_attachment: 10 * 1024 * 1024 // 10 MB
}

const maxFileSize = computed(() => formatBytes(sizeLimits[props.category]))

const acceptedFormats = computed(() => {
  const formats = {
    profile_photo: 'image/jpeg,image/png,image/gif,image/webp',
    medical_document: '.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv',
    medical_image: 'image/jpeg,image/png',
    prescription: '.pdf,.jpg,.png',
    consultation_file: '.pdf,.jpg,.png,.doc,.docx',
    message_attachment: '*/*'
  }
  return formats[props.category] || '*/*'
})

const isFileTooLarge = computed(() => {
  if (!selectedFile.value) return false
  return selectedFile.value.size > sizeLimits[props.category]
})

const onFileSelected = (event) => {
  selectedFile.value = event.target.files[0]
  errorMessage.value = ''
  successMessage.value = ''
}

const uploadFile = async () => {
  if (!selectedFile.value) {
    errorMessage.value = 'Pilih file terlebih dahulu'
    return
  }

  if (isFileTooLarge.value) {
    errorMessage.value = `File terlalu besar. Maksimal: ${maxFileSize.value}`
    return
  }

  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('category', props.category)

  isUploading.value = true
  uploadProgress.value = 0
  errorMessage.value = ''

  try {
    const response = await axios.post('/api/v1/files/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round(
          (progressEvent.loaded / progressEvent.total) * 100
        )
      }
    })

    if (response.data.success) {
      successMessage.value = 'File berhasil diupload ✓'
      selectedFile.value = null
      fileInput.value.value = ''
      emit('upload-success', response.data.data)

      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    }
  } catch (error) {
    errorMessage.value = 
      error.response?.data?.message || 
      'Upload gagal. Silakan coba lagi'
    
    emit('upload-error', errorMessage.value)
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
  }
}

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

<style scoped>
.file-upload {
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.file-info {
  margin: 15px 0;
  padding: 10px;
  background: #f5f5f5;
  border-radius: 4px;
}

.file-info p {
  margin: 5px 0;
}

.error {
  color: #dc3545 !important;
}

.progress {
  margin: 15px 0;
  height: 20px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: width 0.3s ease;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.alert {
  margin-top: 15px;
  padding: 12px;
  border-radius: 4px;
}

.alert-danger {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}
</style>
```

### 2. Storage Info Component

```vue
<template>
  <div class="storage-info">
    <h3>Storage Usage</h3>
    
    <div class="storage-stats">
      <p><strong>Used:</strong> {{ storageInfo.current_usage_formatted }}</p>
      <p><strong>Total:</strong> {{ storageInfo.max_storage_formatted }}</p>
      <p><strong>Remaining:</strong> {{ storageInfo.remaining_storage_formatted }}</p>
    </div>

    <!-- Storage Bar -->
    <div class="storage-bar">
      <div 
        class="storage-usage"
        :class="{ 'warning': storageInfo.usage_percent > 70, 'danger': storageInfo.usage_percent > 90 }"
        :style="{ width: storageInfo.usage_percent + '%' }"
      >
        {{ storageInfo.usage_percent }}%
      </div>
    </div>

    <p class="usage-text">
      <span v-if="storageInfo.usage_percent <= 70" class="badge badge-success">OK</span>
      <span v-else-if="storageInfo.usage_percent <= 90" class="badge badge-warning">⚠️ Hampir Penuh</span>
      <span v-else class="badge badge-danger">❌ Penuh</span>
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const storageInfo = ref({
  current_usage_formatted: '0 MB',
  max_storage_formatted: '0 MB',
  remaining_storage_formatted: '0 MB',
  usage_percent: 0
})

onMounted(async () => {
  try {
    const response = await axios.get('/api/v1/files/storage-info', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    
    if (response.data.success) {
      storageInfo.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching storage info:', error)
  }
})
</script>

<style scoped>
.storage-info {
  padding: 20px;
  background: #f9f9f9;
  border-radius: 4px;
}

.storage-stats {
  margin: 15px 0;
}

.storage-stats p {
  margin: 5px 0;
}

.storage-bar {
  height: 30px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin: 15px 0;
}

.storage-usage {
  height: 100%;
  background: #28a745;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.storage-usage.warning {
  background: #ffc107;
  color: #000;
}

.storage-usage.danger {
  background: #dc3545;
}

.badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}

.badge-success {
  background: #28a745;
  color: white;
}

.badge-warning {
  background: #ffc107;
  color: #000;
}

.badge-danger {
  background: #dc3545;
  color: white;
}
</style>
```

---

## Console Command

### Cleanup Expired Files

```bash
# Run cleanup (delete files from trash yang sudah 30 hari)
php artisan files:cleanup

# Dry run (hanya lihat file yang akan dihapus)
php artisan files:cleanup --dry-run
```

---

## Database Schema

### file_uploads Table

```sql
- id: bigint (primary key)
- user_id: bigint (foreign key)
- filename: string (nama file unik)
- original_filename: string (nama asli dari user)
- path: string (path di storage)
- category: string (tipe file)
- status: enum (active, trashed, deleted)
- file_size: bigint (bytes)
- mime_type: string
- ip_address: string (nullable)
- user_agent: text (nullable)
- uploaded_at: timestamp
- deleted_at: timestamp (nullable)
- permanently_deleted_at: timestamp (nullable)

Indexes:
- user_id
- category
- status
- uploaded_at
```

### user_storage_quotas Table

```sql
- id: bigint (primary key)
- user_id: bigint (foreign key, unique)
- user_role: enum (patient, doctor, hospital, admin)
- max_storage: bigint (bytes)
- used_storage: bigint (bytes)
- last_sync: timestamp
- created_at: timestamp
- updated_at: timestamp

Indexes:
- user_role
- user_id (unique)
```

---

## File Organization di Storage

```
storage/app/public/
├── uploads/
│   ├── profiles/
│   │   └── {user_id}/
│   │       └── {year}/{month}/{day}/
│   │           └── photo-{timestamp}-{random}.jpg
│   │
│   ├── documents/
│   │   └── {user_id}/
│   │       └── {year}/{month}/{day}/
│   │
│   ├── medical-images/
│   │   └── {user_id}/
│   │
│   ├── prescriptions/
│   │   └── {user_id}/
│   │
│   ├── consultations/
│   │   └── {user_id}/
│   │
│   ├── messages/
│   │   └── {user_id}/
│   │
│   └── trash/
│       └── {year}/{month}/{day}/
│           └── (deleted files, auto-remove after 30 days)
```

---

## Testing

### Upload File Test

```bash
# Create test file
dd if=/dev/zero of=test_file.jpg bs=1M count=2

# Upload menggunakan curl
curl -X POST http://localhost:8000/api/v1/files/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test_file.jpg" \
  -F "category=profile_photo"
```

### Size Limit Test

```bash
# Create 6MB file (exceeds 5MB limit)
dd if=/dev/zero of=large_file.jpg bs=1M count=6

# Try to upload (should fail)
curl -X POST http://localhost:8000/api/v1/files/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@large_file.jpg" \
  -F "category=profile_photo"
```

---

## Best Practices

1. **Check Storage Before Upload**
   - Always call `/files/storage-info` untuk check quota
   - Tampilkan warning jika mendekati batas

2. **Progressive Upload**
   - Gunakan chunked upload untuk file besar
   - Tampilkan progress bar kepada user

3. **File Validation**
   - Validasi di client-side (quick feedback)
   - Validasi di server-side (security)

4. **Cleanup Schedule**
   - Set cron job untuk jalankan cleanup harian
   - Backup penting sebelum cleanup

5. **Monitoring**
   - Monitor storage usage per user
   - Alert jika ada user yang mencurigakan

---

## Konfigurasi Environment

Tambahkan ke `.env`:

```env
# File Upload Configuration
FILE_UPLOAD_ENABLED=true
VIRUS_SCAN_ENABLED=false
VIRUS_SCAN_ENGINE=clamav
CLEANUP_SCHEDULE_ENABLED=true
CLEANUP_FREQUENCY=daily
CLEANUP_TIME=02:00
```

---

## Troubleshooting

### Upload Gagal: "File Terlalu Besar"

**Solusi:**
- Check ukuran file vs limit di config
- Update `max_upload_sizes` di `config/file-upload.php` jika perlu

### Storage Penuh

**Solusi:**
- Jalankan: `php artisan files:cleanup`
- Check user storage quota
- Increase quota jika memang kurang

### File Tidak Bisa Diakses

**Solusi:**
- Check permission: `chmod -R 775 storage/app/public`
- Run: `php artisan storage:link`
- Check symbolic link: `ls -la public/storage`

### Cleanup Tidak Jalan

**Solusi:**
- Add to crontab: `* * * * * /usr/bin/php /path/to/artisan schedule:run`
- Check Laravel log: `tail -f storage/logs/laravel.log`

---

## Summary

✅ Batasan ukuran file per kategori (5-15 MB)
✅ Batasan total storage per user (500 MB - 10 GB)
✅ Soft delete dengan 30 hari retention
✅ Auto cleanup file expired
✅ Full audit trail
✅ Vue.js components ready to use
✅ Production-ready implementation
