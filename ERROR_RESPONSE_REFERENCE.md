# Error Response Reference Guide

## Complete Error Response Examples

Panduan lengkap semua error response yang mungkin diterima dari API Telemedicine dengan contoh JSON lengkap.

---

## 1. HTTP 400 - Bad Request

**When**: Format request tidak valid atau data JSON corrupted

**Response Structure**:
```json
{
  "success": false,
  "error": {
    "code": "BAD_REQUEST",
    "message": "Deskripsi error dalam Indonesian",
    "details": {}
  }
}
```

### 400 Examples

#### Invalid JSON Format
```json
{
  "success": false,
  "error": {
    "code": "BAD_REQUEST",
    "message": "Request format tidak valid",
    "details": {
      "parse_error": "Invalid JSON syntax"
    }
  }
}
```

**Cause**: Sending malformed JSON
```json
// Invalid JSON (missing quotes)
{
  email: user@example.com,
  password: password123
}
```

**Fix**: Use valid JSON format
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

#### Missing Required Fields
```json
{
  "success": false,
  "error": {
    "code": "BAD_REQUEST",
    "message": "Email dan password harus diisi",
    "details": {}
  }
}
```

**Cause**: Sending request tanpa required fields
```json
{
  "email": "user@example.com"
  // missing password
}
```

#### Invalid Content-Type
```json
{
  "success": false,
  "error": {
    "code": "BAD_REQUEST",
    "message": "Content-Type harus application/json",
    "details": {}
  }
}
```

**Cause**: Sending dengan header `Content-Type: text/plain`

**Fix**: Use correct Content-Type
```
Content-Type: application/json
```

---

## 2. HTTP 401 - Unauthorized

**When**: Authentication gagal atau token invalid/expired

**Response Structure**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Deskripsi unauthorized error",
    "details": {
      "info": "Informasi tambahan untuk user"
    }
  }
}
```

### 401 Examples

#### Wrong Credentials
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Email atau password salah",
    "details": {
      "info": "Pastikan email dan password benar",
      "remaining_attempts": 2
    }
  }
}
```

**Cause**: Login dengan email/password yang tidak match
**Remediation**: 
- Double check password
- Use "Forgot Password" jika lupa password
- Remaining attempts menunjukkan berapa tries tersisa sebelum rate limit

#### Missing Authorization Header
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token tidak ditemukan",
    "details": {
      "info": "Tambahkan header Authorization: Bearer {token}"
    }
  }
}
```

**Cause**: Request ke protected endpoint tanpa Authorization header
**Fix**: Add Authorization header
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

#### Invalid Token Format
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Format token tidak valid",
    "details": {
      "info": "Token harus format Bearer {token}"
    }
  }
}
```

**Cause**: Authorization header dengan format salah
```
// Wrong format
Authorization: eyJ0eXAiOiJKV1QiLCJhbGc...
// Correct format
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

#### Expired Token
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token telah expired",
    "details": {
      "info": "Gunakan endpoint /auth/refresh untuk mendapatkan token baru",
      "expired_at": "2024-01-15T10:30:00Z",
      "current_time": "2024-01-15T11:30:00Z"
    }
  }
}
```

**Cause**: Token sudah expired
**Fix**: Call `/auth/refresh` endpoint untuk mendapatkan token baru
```bash
POST /auth/refresh
Authorization: Bearer {expired_token}
```

#### Invalid Token Signature
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token tidak valid",
    "details": {
      "info": "Silakan login kembali"
    }
  }
}
```

**Cause**: Token telah dimodify atau dari sumber berbeda
**Fix**: Login kembali untuk mendapatkan token baru

---

## 3. HTTP 403 - Forbidden

**When**: User tidak memiliki permission atau kondisi khusus

**Response Structure**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Deskripsi forbidden error",
    "details": {
      "error_code": "Error code spesifik"
    }
  }
}
```

### 403 Examples

#### Email Not Verified
```json
{
  "success": false,
  "error": {
    "code": "EMAIL_NOT_VERIFIED",
    "message": "Email belum diverifikasi. Silakan cek email Anda untuk link verifikasi.",
    "details": {
      "error_code": "EMAIL_NOT_VERIFIED",
      "verification_email": "user@example.com",
      "can_resend_after": "2024-01-15T10:15:00Z"
    }
  }
}
```

**Cause**: Login sebagai dokter dengan email belum verified
**Fix**: 
- Check email untuk verification link
- Call `/auth/resend-verification-email` untuk resend email

#### Insufficient Permissions
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Anda tidak memiliki akses ke resource ini",
    "details": {
      "error_code": "INSUFFICIENT_PERMISSIONS",
      "required_role": "dokter",
      "user_role": "pasien"
    }
  }
}
```

**Cause**: Mengakses endpoint yang memerlukan role tertentu
**Example**: Pasien mencoba akses `/dokter/schedule`

#### Account Disabled
```json
{
  "success": false,
  "error": {
    "code": "ACCOUNT_DISABLED",
    "message": "Akun Anda telah dinonaktifkan",
    "details": {
      "error_code": "ACCOUNT_DISABLED",
      "reason": "Violating terms of service",
      "disabled_at": "2024-01-15T09:00:00Z",
      "contact_support": "support@telemedicine.com"
    }
  }
}
```

**Cause**: Admin menonaktifkan akun karena violation
**Fix**: Hubungi support team

---

## 4. HTTP 404 - Not Found

**When**: Resource yang diminta tidak ditemukan

**Response Structure**:
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Resource tidak ditemukan",
    "details": {
      "resource_type": "User",
      "resource_id": 999
    }
  }
}
```

### 404 Examples

#### User Not Found
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "User dengan ID 999 tidak ditemukan",
    "details": {
      "resource_type": "User",
      "resource_id": 999
    }
  }
}
```

**Cause**: Mengakses user ID yang tidak ada
**Fix**: Verify user ID sebelum request

#### Appointment Not Found
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Appointment tidak ditemukan",
    "details": {
      "resource_type": "Appointment",
      "appointment_id": "apt_12345"
    }
  }
}
```

**Cause**: Mengakses appointment ID yang tidak ada

---

## 5. HTTP 422 - Unprocessable Entity (Validation Error)

**When**: Field validation gagal

**Response Structure**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "validation_errors": {
        "field_name": [
          "Error message 1",
          "Error message 2"
        ]
      }
    }
  }
}
```

### 422 Examples

#### Email Validation Error
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "validation_errors": {
        "email": [
          "Email sudah terdaftar",
          "Format email tidak valid"
        ]
      }
    }
  }
}
```

**Possible Errors**:
- "Email sudah terdaftar" → Email already exists in database
- "Format email tidak valid" → Email format tidak sesuai RFC 5322
- "Email harus diisi" → Missing email field

#### Password Validation Error
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "validation_errors": {
        "password": [
          "Password minimal 8 karakter",
          "Password harus mengandung huruf besar"
        ]
      }
    }
  }
}
```

**Possible Errors**:
- "Password minimal 8 karakter" → Password < 8 chars
- "Password harus mengandung huruf besar" → No uppercase
- "Password harus mengandung angka" → No digit
- "Password harus mengandung karakter spesial" → No special char

#### Complete Registration Validation Error
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "validation_errors": {
        "email": [
          "Email sudah terdaftar"
        ],
        "password": [
          "Password minimal 8 karakter"
        ],
        "password_confirmation": [
          "Password tidak sesuai"
        ],
        "name": [
          "Nama wajib diisi",
          "Nama maksimal 255 karakter"
        ],
        "role": [
          "Role hanya boleh pasien atau dokter"
        ]
      }
    }
  }
}
```

**Testing**: Submit registration dengan:
```json
{
  "email": "existing@example.com",
  "password": "short",
  "password_confirmation": "different",
  "name": "",
  "role": "admin"
}
```

#### Multiple Fields Error
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "validation_errors": {
        "phone": [
          "Format nomor telepon tidak valid"
        ],
        "date_of_birth": [
          "Umur minimal 18 tahun"
        ],
        "nik": [
          "NIK sudah terdaftar"
        ]
      }
    }
  }
}
```

**How to Fix**:
1. Check `validation_errors` object
2. Untuk setiap field, check error messages
3. Adjust input sesuai requirement
4. Retry request

---

## 6. HTTP 429 - Too Many Requests (Rate Limited)

**When**: Terlalu banyak requests dalam waktu singkat

**Response Structure**:
```json
{
  "success": false,
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "message": "Terlalu banyak upaya. Silakan coba lagi dalam X menit.",
    "details": {
      "retry_after": 900,
      "remaining": 0,
      "info": "Rate limit description"
    }
  }
}
```

### 429 Examples

#### Login Rate Limited
```json
{
  "success": false,
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "message": "Terlalu banyak upaya login. Silakan coba lagi dalam 15 menit.",
    "details": {
      "retry_after": 900,
      "remaining": 0,
      "info": "Rate limit 5 percobaan per IP per 15 menit"
    }
  }
}
```

**When**: After 5 failed login attempts
**retry_after**: 900 seconds = 15 minutes
**remaining**: 0 (sudah habis)
**Fix**: Wait 15 minutes atau gunakan IP/device berbeda

#### Registration Rate Limited
```json
{
  "success": false,
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "message": "Terlalu banyak upaya registrasi",
    "details": {
      "retry_after": 900,
      "remaining": 0,
      "info": "Rate limit 3 registrasi per email per 15 menit"
    }
  }
}
```

**When**: After 3 registration attempts dengan email sama
**Rate Limit**: 3 registrations per email per 15 minutes
**Fix**: 
- Use different email
- Wait 15 minutes
- Try lagi dengan email yang sama

#### API Rate Limited (General)
```json
{
  "success": false,
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "message": "API rate limit exceeded",
    "details": {
      "retry_after": 60,
      "remaining": 0,
      "limit": 1000,
      "window": "1 hour",
      "info": "Rate limit 1000 requests per IP per jam"
    }
  }
}
```

**When**: Terlalu banyak API requests (general limit)
**Fix**: 
- Implement exponential backoff
- Wait `retry_after` seconds sebelum retry
- Cache results jika possible

#### Rate Limit Headers
```
HTTP/1.1 429 Too Many Requests
Content-Type: application/json
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1705329000
Retry-After: 900

{
  "success": false,
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "message": "Rate limit exceeded",
    "details": {
      "retry_after": 900,
      "remaining": 0
    }
  }
}
```

**Headers Explanation**:
- `X-RateLimit-Limit`: 5 (max attempts allowed)
- `X-RateLimit-Remaining`: 0 (remaining attempts)
- `X-RateLimit-Reset`: 1705329000 (Unix timestamp when limit resets)
- `Retry-After`: 900 (seconds to wait before retry)

**Testing Rate Limit**:
```bash
# Attempt 1-5: Success atau fails, tergantung password
POST /auth/login with wrong password → 401 or 200

# Attempt 6: Rate limited
POST /auth/login → 429

# Check when to retry
retry_after = 900 seconds
next_available = now() + 900

# Wait atau use different IP
# After 900 seconds, can try again
```

---

## 7. HTTP 500 - Internal Server Error

**When**: Server-side error terjadi

**Response Structure**:
```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_SERVER_ERROR",
    "message": "Terjadi kesalahan pada server",
    "details": {
      "request_id": "req_87654321",
      "timestamp": "2024-01-15T10:30:00Z"
    }
  }
}
```

### 500 Examples

#### Database Connection Error
```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_SERVER_ERROR",
    "message": "Terjadi kesalahan pada server",
    "details": {
      "request_id": "req_87654321",
      "timestamp": "2024-01-15T10:30:00Z",
      "info": "Database connection failed. Hubungi support jika masalah berlanjut."
    }
  }
}
```

**Cause**: Database server down atau tidak bisa connect
**Fix**: 
- Wait dan retry
- Check server status
- Contact support dengan request_id

#### Mail Service Error
```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_SERVER_ERROR",
    "message": "Gagal mengirim email verifikasi",
    "details": {
      "request_id": "req_87654321",
      "timestamp": "2024-01-15T10:30:00Z",
      "service": "mail",
      "action": "send_verification_email"
    }
  }
}
```

**Cause**: Mail service tidak available
**Fix**: 
- Retry nanti
- Check inbox (email mungkin sudah sent)

#### Payment Service Error
```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_SERVER_ERROR",
    "message": "Gagal memproses pembayaran",
    "details": {
      "request_id": "req_87654321",
      "timestamp": "2024-01-15T10:30:00Z",
      "service": "payment_gateway",
      "error_code": "gateway_timeout"
    }
  }
}
```

**Cause**: Payment gateway timeout
**Fix**: 
- Retry payment
- Contact support dengan request_id

---

## 8. Error Code Reference

### Authentication Errors

| Code | HTTP | Meaning | Remediation |
|------|------|---------|-------------|
| UNAUTHORIZED | 401 | Invalid credentials atau token | Login ulang |
| EMAIL_NOT_VERIFIED | 403 | Email belum diverifikasi | Verify email |
| ACCOUNT_DISABLED | 403 | Akun dinonaktifkan | Contact support |

### Validation Errors

| Code | HTTP | Meaning | Remediation |
|------|------|---------|-------------|
| VALIDATION_ERROR | 422 | Field validation failed | Check error details |
| BAD_REQUEST | 400 | Invalid request format | Check JSON format |

### Rate Limiting

| Code | HTTP | Meaning | Remediation |
|------|------|---------|-------------|
| TOO_MANY_REQUESTS | 429 | Rate limit exceeded | Wait `retry_after` |

### Resource Errors

| Code | HTTP | Meaning | Remediation |
|------|------|---------|-------------|
| NOT_FOUND | 404 | Resource tidak ada | Verify resource ID |

### Server Errors

| Code | HTTP | Meaning | Remediation |
|------|------|---------|-------------|
| INTERNAL_SERVER_ERROR | 500 | Server error | Contact support |
| SERVICE_UNAVAILABLE | 503 | Service maintenance | Wait dan retry |

---

## 9. Error Handling Best Practices

### For Frontend/Client

```javascript
// Handle different error codes
fetch('/api/auth/login', options)
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new ApiError(data.error.code, data.error.message);
      });
    }
    return response.json();
  })
  .catch(error => {
    switch(error.code) {
      case 'UNAUTHORIZED':
        // Show login form
        showLoginModal();
        break;
      
      case 'EMAIL_NOT_VERIFIED':
        // Show verification prompt
        showVerificationPrompt(error.details);
        break;
      
      case 'VALIDATION_ERROR':
        // Show field errors
        showValidationErrors(error.details.validation_errors);
        break;
      
      case 'TOO_MANY_REQUESTS':
        // Show retry-after message
        showRetryAfterMessage(error.details.retry_after);
        break;
      
      case 'INTERNAL_SERVER_ERROR':
        // Show generic error dengan request_id
        showServerError(error.details.request_id);
        break;
      
      default:
        showErrorMessage(error.message);
    }
  });
```

### Retry Logic

```javascript
async function apiCallWithRetry(url, options, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const response = await fetch(url, options);
      
      if (response.status === 429) {
        const data = await response.json();
        const retryAfter = data.error.details.retry_after;
        
        // Wait before retry
        await sleep(retryAfter * 1000);
        continue; // Retry
      }
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      
      return await response.json();
      
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      
      // Exponential backoff untuk server errors
      const delay = Math.pow(2, i) * 1000;
      await sleep(delay);
    }
  }
}

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}
```

---

## 10. Postman Error Testing Examples

### Test 400 Error
```
POST /auth/login
Content-Type: application/json

{invalid json}

Expected: 400 BAD_REQUEST
```

### Test 401 Error
```
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "wrongpassword"
}

Expected: 401 UNAUTHORIZED with remaining_attempts
```

### Test 422 Error
```
POST /auth/register
Content-Type: application/json

{
  "email": "invalid-email",
  "password": "123",
  "password_confirmation": "456",
  "name": "",
  "role": "invalid"
}

Expected: 422 VALIDATION_ERROR with validation_errors for all fields
```

### Test 429 Error
```
// Attempt 1-5: Login dengan wrong password
POST /auth/login with password="wrong"
// → 401 (attempts decreasing)

// Attempt 6: Rate limited
POST /auth/login with password="wrong"
// → 429 TOO_MANY_REQUESTS
```

---

**Last Updated**: 2024-01-15  
**API Version**: 1.0.0  
**Status**: Complete
