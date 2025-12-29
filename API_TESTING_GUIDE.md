# API Testing Guide - Telemedicine Platform

## Overview

Panduan komprehensif untuk testing API telemedicine dengan fokus pada error handling, validation, dan rate limiting. Semua contoh endpoint dan response tersedia di Swagger UI dan dapat diimport ke Postman.

---

## 1. Swagger UI Access

### Endpoints
- **Swagger UI**: `http://localhost:8000/api/docs`
- **OpenAPI Specification**: `http://localhost:8000/api/docs/openapi.json`
- **ReDoc**: `http://localhost:8000/api/docs/redoc`

### Cara Mengakses
1. Buka browser ke `http://localhost:8000/api/docs`
2. Semua endpoint terdapat dengan lengkap beserta:
   - Request/Response examples
   - Error response codes dan descriptions
   - Field validation rules
   - Rate limiting info

---

## 2. Authentication Endpoints

### 2.1 Register User

**Endpoint**: `POST /auth/register`

**Request Body** (Valid):
```json
{
  "email": "user@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "name": "John Doe",
  "role": "pasien"
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "User berhasil terdaftar",
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "role": "pasien",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

#### Error Scenarios

**400 - Bad Request** (Invalid JSON format):
```json
{
  "success": false,
  "error": {
    "code": "BAD_REQUEST",
    "message": "Request format tidak valid",
    "details": {}
  }
}
```

**422 - Validation Error** (Invalid field values):
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
          "Nama wajib diisi"
        ],
        "role": [
          "Role hanya boleh pasien atau dokter"
        ]
      }
    }
  }
}
```

**429 - Too Many Requests** (Rate limited):
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

**500 - Internal Server Error**:
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

---

### 2.2 Login User

**Endpoint**: `POST /auth/login`

**Request Body** (Valid):
```json
{
  "email": "user@example.com",
  "password": "SecurePass123!"
}
```

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "email": "user@example.com",
      "name": "John Doe",
      "role": "pasien",
      "phone": "08123456789",
      "avatar_url": "https://..."
    }
  }
}
```

#### Error Scenarios

**400 - Bad Request**:
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

**401 - Unauthorized** (Wrong credentials):
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

**403 - Forbidden** (Email not verified):
```json
{
  "success": false,
  "error": {
    "code": "EMAIL_NOT_VERIFIED",
    "message": "Email belum diverifikasi. Silakan cek email Anda untuk link verifikasi.",
    "details": {
      "error_code": "EMAIL_NOT_VERIFIED"
    }
  }
}
```

**422 - Validation Error**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "validation_errors": {
        "email": ["Format email tidak valid"],
        "password": ["Password harus diisi"]
      }
    }
  }
}
```

**429 - Too Many Requests**:
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

---

### 2.3 Get Current User Profile

**Endpoint**: `GET /auth/me`

**Headers Required**:
```
Authorization: Bearer {token}
```

**Success Response (200)**:
```json
{
  "success": true,
  "message": "User profile retrieved",
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "role": "pasien",
    "phone": "08123456789",
    "avatar_url": "https://example.com/avatar.jpg",
    "email_verified_at": "2024-01-10T15:30:00Z",
    "created_at": "2024-01-10T10:00:00Z"
  }
}
```

#### Error Scenarios

**401 - Unauthorized** (Missing or invalid token):
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token tidak valid atau expired",
    "details": {
      "info": "Silakan login kembali untuk mendapatkan token baru"
    }
  }
}
```

**403 - Forbidden**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Anda tidak memiliki akses ke resource ini",
    "details": {
      "error_code": "INSUFFICIENT_PERMISSIONS"
    }
  }
}
```

---

### 2.4 Refresh Token

**Endpoint**: `POST /auth/refresh`

**Headers Required**:
```
Authorization: Bearer {old_token}
```

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Token berhasil diperbarui",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

#### Error Scenarios

**401 - Unauthorized**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token tidak valid atau expired",
    "details": {
      "info": "Silakan login kembali untuk mendapatkan token baru"
    }
  }
}
```

---

### 2.5 Logout

**Endpoint**: `POST /auth/logout`

**Headers Required**:
```
Authorization: Bearer {token}
```

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": {}
}
```

#### Error Scenarios

**401 - Unauthorized**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token tidak valid atau expired",
    "details": {
      "info": "Silakan login kembali"
    }
  }
}
```

---

## 3. Rate Limiting

### Rate Limit Policies

| Operation | Max Attempts | Time Window | Headers |
|-----------|-------------|-------------|---------|
| Register | 3 | 15 minutes | `X-RateLimit-*` |
| Login | 5 | 15 minutes | `X-RateLimit-*` |
| Forgot Password | 3 | 1 hour | `X-RateLimit-*` |
| Password Reset | 5 | 1 hour | `X-RateLimit-*` |

### Rate Limit Response Headers

```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 2
X-RateLimit-Reset: 1705329000
Retry-After: 900
```

### Testing Rate Limiting

1. **Make 5 consecutive login requests** with wrong credentials
2. **6th request** akan return 429 dengan:
   - `retry_after`: Berapa detik untuk menunggu
   - `remaining`: Sisa attempts (0 untuk rate limited)
   - `info`: Deskripsi rate limit policy

---

## 4. Postman Testing Setup

### 4.1 Import OpenAPI Specification

1. Buka Postman
2. Pilih **File** → **Import**
3. Masukkan URL: `http://localhost:8000/api/docs/openapi.json`
4. Klik **Import**

### 4.2 Environment Variables Setup

Buat environment baru dengan variables:

```json
{
  "base_url": "http://localhost:8000/api",
  "token": "",
  "user_email": "test@example.com",
  "user_password": "TestPass123!"
}
```

### 4.3 Test Scenarios

#### Scenario 1: Complete Auth Flow

1. **Register New User**
   - POST `/auth/register`
   - Save token dari response ke `{{token}}`

2. **Login**
   - POST `/auth/login`
   - Verify token matches register token
   - Save untuk next requests

3. **Get Current User**
   - GET `/auth/me`
   - Use `{{token}}` in Authorization header

4. **Refresh Token**
   - POST `/auth/refresh`
   - Get new token
   - Verify old token expired

5. **Logout**
   - POST `/auth/logout`
   - Verify token invalidated
   - Try `/auth/me` again → should get 401

#### Scenario 2: Error Handling

1. **400 - Bad Request**
   - POST `/auth/login` dengan body tidak valid
   - Verify error code: `BAD_REQUEST`

2. **401 - Unauthorized**
   - POST `/auth/login` dengan email/password salah
   - Verify error code: `UNAUTHORIZED`
   - Verify `remaining_attempts` count

3. **403 - Forbidden**
   - GET `/auth/me` tanpa token
   - Verify error code: `FORBIDDEN`

4. **422 - Validation Error**
   - POST `/auth/register` dengan email invalid
   - Verify error code: `VALIDATION_ERROR`
   - Verify `validation_errors` object dengan field errors

5. **429 - Too Many Requests**
   - Buat 5 login attempts dengan wrong password
   - 6th attempt → 429
   - Verify `retry_after` dan `remaining` = 0

#### Scenario 3: Rate Limiting

1. **Test Login Rate Limit**
   ```bash
   # Loop 5 times dengan wrong password
   # Attempt 6 → 429 error
   ```

2. **Test Registration Rate Limit**
   ```bash
   # Buat 3 registrations dengan email berbeda
   # Attempt 4 dengan email sama → 429 error
   ```

---

## 5. Validation Rules Reference

### Register Endpoint

| Field | Rule | Error Message |
|-------|------|---------------|
| email | required, email, unique | "Email sudah terdaftar" |
| password | required, min:8, confirmed | "Password minimal 8 karakter" |
| password_confirmation | required, same:password | "Password tidak sesuai" |
| name | required, string, max:255 | "Nama wajib diisi" |
| role | required, in:pasien,dokter | "Role hanya boleh pasien atau dokter" |

### Login Endpoint

| Field | Rule | Error Message |
|-------|------|---------------|
| email | required, email | "Format email tidak valid" |
| password | required, string | "Password harus diisi" |

---

## 6. Status Codes Reference

### Success Codes
- **200 OK**: Request berhasil diproses
- **201 Created**: Resource baru berhasil dibuat

### Client Error Codes
- **400 Bad Request**: Format request tidak valid
- **401 Unauthorized**: Authentication failed atau token invalid
- **403 Forbidden**: Tidak memiliki permission atau email not verified
- **404 Not Found**: Resource tidak ditemukan
- **422 Unprocessable Entity**: Validation error pada field
- **429 Too Many Requests**: Rate limit exceeded

### Server Error Codes
- **500 Internal Server Error**: Server error terjadi
- **503 Service Unavailable**: Server sedang maintenance

---

## 7. Testing Checklist

### Authentication
- [ ] Register dengan data valid → 201
- [ ] Register dengan email sudah terdaftar → 422
- [ ] Register dengan password terlalu pendek → 422
- [ ] Register tanpa confirmation → 422
- [ ] Login dengan credentials valid → 200
- [ ] Login dengan password salah → 401
- [ ] Login dengan email belum verifikasi → 403
- [ ] Get current user dengan token valid → 200
- [ ] Get current user tanpa token → 401
- [ ] Get current user dengan token expired → 401
- [ ] Refresh token dengan token valid → 200
- [ ] Refresh token dengan token invalid → 401
- [ ] Logout dengan token valid → 200
- [ ] Logout tanpa token → 401

### Rate Limiting
- [ ] 5 failed login attempts → success
- [ ] 6th login attempt → 429
- [ ] 3 registrations dengan email berbeda → success
- [ ] 4th registration dengan email sama → 429
- [ ] Wait `retry_after` seconds → dapat login lagi

### Error Responses
- [ ] Semua 400 responses punya `error.code`
- [ ] Semua 422 responses punya `validation_errors`
- [ ] Semua 429 responses punya `retry_after`
- [ ] Semua 500 responses punya `request_id`
- [ ] Response examples dapat di-copy dan digunakan

---

## 8. Common Testing Issues

### Issue: Token Not Working After Logout
- **Cause**: Token masih disimpan di variable
- **Solution**: Clear `{{token}}` variable setelah logout

### Issue: Rate Limited Terus
- **Cause**: Masih dalam rate limit window
- **Solution**: Tunggu `retry_after` seconds atau gunakan email/IP berbeda

### Issue: Email Not Verified Error
- **Cause**: Fake email atau tidak ada mail service
- **Solution**: Skip email verification di local testing atau mock email

### Issue: Response Format Berbeda
- **Cause**: Version API berbeda
- **Solution**: Check OpenAPI spec di `/api/docs/openapi.json`

---

## 9. Quick Reference URLs

```
# Development
Base URL: http://localhost:8000/api
Swagger UI: http://localhost:8000/api/docs
OpenAPI JSON: http://localhost:8000/api/docs/openapi.json
ReDoc: http://localhost:8000/api/docs/redoc

# Endpoints
POST /auth/register
POST /auth/login
GET /auth/me
POST /auth/refresh
POST /auth/logout
```

---

## 10. Sample Postman Pre-request Script

```javascript
// Set Authorization header with saved token
const token = pm.environment.get("token");
if (token) {
    pm.request.headers.add({
        key: "Authorization",
        value: `Bearer ${token}`
    });
}

// Log request details
console.log("Request: " + pm.request.method + " " + pm.request.url);
```

---

## 11. Sample Postman Test Script

```javascript
// Test status code
pm.test("Status code is correct", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 201, 400, 401, 403, 404, 422, 429, 500]);
});

// Test response has success field
pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property("success");
});

// Save token if present
var jsonData = pm.response.json();
if (jsonData.data && jsonData.data.token) {
    pm.environment.set("token", jsonData.data.token);
}
```

---

## 12. Documentation Files

- [API_TESTING_GUIDE.md](./API_TESTING_GUIDE.md) - Panduan testing ini
- [SWAGGER_DOCUMENTATION.md](./SWAGGER_DOCUMENTATION.md) - Detail dokumentasi Swagger
- [ERROR_RESPONSE_REFERENCE.md](./ERROR_RESPONSE_REFERENCE.md) - Reference error responses
- [RATE_LIMITING_GUIDE.md](./RATE_LIMITING_GUIDE.md) - Detail rate limiting

---

**Last Updated**: 2024-01-15  
**API Version**: 1.0.0  
**Documentation**: L5-Swagger with OpenAPI 3.0
