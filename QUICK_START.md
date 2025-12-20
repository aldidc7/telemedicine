# Quick Start Guide - New Features Implementation

**Last Updated:** December 20, 2025

---

## 🚀 Getting Started

Setelah implementasi ini, ada beberapa setup yang perlu dilakukan:

### 1. Run Database Migration

```bash
php artisan migrate
```

Ini akan membuat tabel `doctor_verification_documents` untuk menyimpan dokumen verifikasi dokter.

---

## 2. API Response Format

### Old Way ❌
```php
return response()->json([
    'success' => true,
    'pesan' => 'Success',
    'data' => $data,
], 200);
```

### New Way ✅
```php
return ApiResponseFormatter::success($data, 'Success message');
```

**Update semua controller untuk menggunakan `ApiResponseFormatter`:**

```php
use App\Http\Responses\ApiResponseFormatter;

// Success
return ApiResponseFormatter::success($data, 'User retrieved');

// Paginated
return ApiResponseFormatter::paginated(
    $items,
    ['total' => $total, 'per_page' => $per_page, 'current_page' => $current],
);

// Errors
return ApiResponseFormatter::notFound('User not found');
return ApiResponseFormatter::unauthorized('Invalid token');
return ApiResponseFormatter::unprocessable('Validation failed', $errors);
```

---

## 3. API Documentation

### View Swagger UI
```
http://localhost:8000/api/docs
```

### Health Check
```
GET http://localhost:8000/api/health
```

### Update OpenAPI Spec
Edit `app/OpenAPI/ApiDocumentation.php` untuk menambah endpoint baru

---

## 4. Validation Rules

### Old Way ❌
```php
$request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
```

### New Way ✅
```php
use App\Helpers\ValidationRuleHelper;

$request->validate([
    'email' => ValidationRuleHelper::email(),
    'password' => ValidationRuleHelper::strongPassword(),
    'phone' => ValidationRuleHelper::phoneNumber(),
]);
```

**Available Rules:**
- `sanitizedString(min, max)`
- `phoneNumber()`
- `email()`
- `strongPassword()`
- `medicalText(min, max)`
- `specialization()`
- `consultationType()`
- `documentFile(maxSize)`
- `rating()`
- `ktpNumber()`
- `skpNumber()`

---

## 5. Pagination Helper

```php
use App\Helpers\PaginationHelper;

public function index()
{
    $items = Model::paginate(10);
    
    return ApiResponseFormatter::paginated(
        $items->items(),
        PaginationHelper::toArray($items),
    );
}
```

---

## 6. Analytics Dashboard

### Access Doctor Analytics
```
GET /api/v1/analytics/doctor-dashboard
```

### Access Admin Analytics
```
GET /api/v1/analytics/admin-dashboard
```

### Get Consultation Reports
```
GET /api/v1/analytics/consultation-report?start_date=2025-01-01&end_date=2025-12-31
```

### Frontend Component
```vue
<template>
  <AnalyticsDashboardPage />
</template>

<script setup>
import AnalyticsDashboardPage from '@/views/admin/AnalyticsDashboardPage.vue'
</script>
```

---

## 7. Doctor Verification Documents

### Upload Document (Doctor)
```javascript
const formData = new FormData()
formData.append('document_type', 'ktp')
formData.append('file', fileInput.files[0])

const response = await axios.post(
  '/api/v1/doctor/verification/upload',
  formData,
  {
    headers: {
      'Content-Type': 'multipart/form-data',
      'Authorization': 'Bearer ' + token,
    }
  }
)
```

### Get Verification Status (Doctor)
```
GET /api/v1/doctor/verification/status
```

### List Pending Documents (Admin)
```
GET /api/v1/admin/verification/pending
```

### Approve Document (Admin)
```
POST /api/v1/admin/verification/{document_id}/approve
```

### Reject Document (Admin)
```
POST /api/v1/admin/verification/{document_id}/reject
Body: { "reason": "Invalid document format" }
```

---

## 8. Offline Mode

### In Vue Component

```vue
<script setup>
import { ref, onMounted } from 'vue'
import { offlineService, cacheService, setupOfflineListener } from '@/services/offlineService'

const doctors = ref([])
const isOnline = ref(true)

const fetchDoctors = async () => {
  try {
    doctors.value = await offlineService.fetch(
      '/api/v1/dokter',
      { cacheDuration: 30 * 60 * 1000 } // 30 minutes
    )
  } catch (error) {
    console.error('Error:', error.message)
  }
}

const createConsultation = async (data) => {
  try {
    const result = await offlineService.post(
      '/api/v1/konsultasi',
      data
    )
    
    if (result.offline) {
      alert('Consultation queued. Will sync when online.')
    }
  } catch (error) {
    console.error('Error:', error)
  }
}

onMounted(() => {
  fetchDoctors()
  
  // Setup offline listener
  setupOfflineListener((online) => {
    isOnline.value = online
  })
})
</script>

<template>
  <div>
    <p v-if="!isOnline" class="offline-badge">
      ⚠️ You are offline. Changes will sync when online.
    </p>
    <!-- Content -->
  </div>
</template>
```

---

## 9. Loading & Error States

### In Vue Component

```vue
<script setup>
import { ref } from 'vue'
import { useForm, validationRules } from '@/composables/useAsync'
import { useNotification } from '@/composables/useAsync'

const { 
  formData, 
  errors, 
  isLoading, 
  submitAsync 
} = useForm({ email: '', password: '' })

const { success, error } = useNotification()

const handleLogin = async () => {
  await submitAsync(
    async (data) => {
      const response = await api.post('/login', data)
      success('Login successful!')
      return response
    },
    {
      email: validationRules.required(),
      password: validationRules.required(),
    }
  )
}
</script>

<template>
  <form @submit.prevent="handleLogin">
    <LoadingSpinner :isLoading="isLoading" message="Logging in..." />
    
    <div class="form-group">
      <input 
        v-model="formData.email"
        type="email"
        placeholder="Email"
      />
      <ErrorMessage v-if="errors.email" :error="errors.email" />
    </div>

    <div class="form-group">
      <input 
        v-model="formData.password"
        type="password"
        placeholder="Password"
      />
      <ErrorMessage v-if="errors.password" :error="errors.password" />
    </div>

    <button type="submit" :disabled="isLoading">Login</button>
  </form>

  <NotificationSystem :notifications="notifications" />
</template>
```

---

## 10. CORS Configuration

Update `.env`:
```env
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173
```

Register middleware in `bootstrap/app.php` (Laravel 11) atau `app/Http/Kernel.php` (Laravel 10):

```php
'api' => [
    \App\Http\Middleware\EnhancedCorsMiddleware::class,
    // ... other middleware
],
```

---

## 📋 Checklist

- [ ] Run `php artisan migrate` untuk membuat tabel
- [ ] Update semua controller ke gunakan `ApiResponseFormatter`
- [ ] Update validation menggunakan `ValidationRuleHelper`
- [ ] Test API documentation di `/api/docs`
- [ ] Implement offline service di mobile features
- [ ] Add loading states dan error messages ke UI
- [ ] Setup doctor verification upload form
- [ ] Test analytics dashboard
- [ ] Configure CORS origins di `.env`
- [ ] Test pagination di list endpoints

---

## 🧪 Testing

### Test API Response Format
```bash
curl -X GET http://localhost:8000/api/v1/dokter
```

Should return:
```json
{
  "success": true,
  "message": "Doctors retrieved",
  "data": [...],
  "meta": {
    "pagination": {...}
  }
}
```

### Test Offline Mode (Browser)
1. Open DevTools → Network
2. Check "Offline" checkbox
3. Try to fetch data
4. Should show cached data or queued request

### Test Validation
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"invalid","password":"weak"}'
```

Should return validation errors with proper format

---

## 📞 Troubleshooting

### Issue: Migration fails
**Solution:** Check if `doctors` table exists, or run all migrations:
```bash
php artisan migrate:fresh --seed
```

### Issue: API returns old format
**Solution:** Ensure controller imports and uses `ApiResponseFormatter`

### Issue: Offline mode not working
**Solution:** Check browser console for localStorage errors

### Issue: Analytics shows no data
**Solution:** Ensure database has consultation records

### Issue: CORS errors
**Solution:** Add origin to `CORS_ALLOWED_ORIGINS` in `.env`

---

## 📚 Documentation Files

- `IMPLEMENTATION_DOCUMENTATION.md` - Detailed feature documentation
- `README.md` - Project overview
- API Docs - `GET /api/docs`

---

**Version:** 1.0.0  
**Status:** Ready for Production  
**Last Updated:** December 20, 2025
