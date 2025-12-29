# API Documentation Quick Reference

## Endpoints At A Glance

### Authentication (5 endpoints)

#### Register
```
POST /auth/register
No auth required
201 Created | 400 Bad Request | 422 Validation Error | 429 Rate Limited | 500 Server Error
```

#### Login
```
POST /auth/login
No auth required
200 OK | 400 Bad Request | 401 Unauthorized | 403 Forbidden | 422 Validation | 429 Rate Limited | 500 Server Error
```

#### Get Current User
```
GET /auth/me
Requires: Bearer Token
200 OK | 401 Unauthorized | 403 Forbidden | 500 Server Error
```

#### Refresh Token
```
POST /auth/refresh
Requires: Bearer Token
200 OK | 401 Unauthorized | 500 Server Error
```

#### Logout
```
POST /auth/logout
Requires: Bearer Token
200 OK | 401 Unauthorized | 500 Server Error
```

---

## Response Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| **200** | OK - Request successful | Login, Get profile, Refresh token |
| **201** | Created - Resource created | Register user |
| **400** | Bad Request - Invalid format | Malformed JSON, missing fields |
| **401** | Unauthorized - Auth failed | Wrong password, expired token |
| **403** | Forbidden - Access denied | Email not verified, no permission |
| **404** | Not Found - Resource missing | User doesn't exist |
| **422** | Validation Error - Invalid data | Email invalid, password too short |
| **429** | Too Many Requests - Rate limited | 6th login attempt in 15 min |
| **500** | Server Error - Internal error | Database down, mail service error |

---

## Error Response Structure

All error responses follow this format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable message",
    "details": {
      // Additional error-specific info
    }
  }
}
```

### Common Error Codes

| Code | HTTP | Meaning |
|------|------|---------|
| BAD_REQUEST | 400 | Invalid request format |
| UNAUTHORIZED | 401 | Authentication failed |
| EMAIL_NOT_VERIFIED | 403 | User email not verified |
| NOT_FOUND | 404 | Resource not found |
| VALIDATION_ERROR | 422 | Field validation failed |
| TOO_MANY_REQUESTS | 429 | Rate limit exceeded |
| INTERNAL_SERVER_ERROR | 500 | Server error |

---

## Validation Rules

### Register
- **email**: Required, valid format, unique
- **password**: Required, min 8 chars, must contain uppercase, number, special char
- **password_confirmation**: Required, must match password
- **name**: Required, max 255 chars
- **role**: Required, only "pasien" or "dokter"

### Login
- **email**: Required, valid email format
- **password**: Required, non-empty string

---

## Rate Limiting

| Operation | Limit | Window | Retry After |
|-----------|-------|--------|-------------|
| Register | 3 per email | 15 min | 900 sec |
| Login | 5 per IP | 15 min | 900 sec |
| Forgot Password | 3 per email | 1 hour | 3600 sec |
| API (general) | 1000 per IP | 1 hour | varies |

**429 Response**:
```json
{
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "details": {
      "retry_after": 900,  // Seconds to wait
      "remaining": 0       // Remaining attempts
    }
  }
}
```

---

## Response Examples

### Register Success (201)
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

### Login Success (200)
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
      "role": "pasien"
    }
  }
}
```

### Validation Error (422)
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "validation_errors": {
        "email": ["Email sudah terdaftar"],
        "password": ["Password minimal 8 karakter"]
      }
    }
  }
}
```

### Rate Limit Error (429)
```json
{
  "success": false,
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "message": "Terlalu banyak upaya login",
    "details": {
      "retry_after": 900,
      "remaining": 0,
      "info": "Rate limit 5 percobaan per IP per 15 menit"
    }
  }
}
```

### Server Error (500)
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

## Postman Setup

### 1. Import OpenAPI Spec
```
File → Import → Link → http://localhost:8000/api/docs/openapi.json
```

### 2. Create Environment
```
Name: Telemedicine Local
Variables:
- base_url: http://localhost:8000/api
- token: (empty - auto-saved)
- user_email: test@example.com
- user_password: TestPass123!
```

### 3. Quick Test
1. Register: `POST {{base_url}}/auth/register`
2. Login: `POST {{base_url}}/auth/login`
3. Get Profile: `GET {{base_url}}/auth/me` (with Bearer token)
4. Logout: `POST {{base_url}}/auth/logout` (with Bearer token)

---

## Common Issues & Solutions

### Issue: 401 Unauthorized on /auth/me
**Cause**: Missing or invalid token

**Solution**: 
1. Login first to get token
2. Save token to environment variable
3. Add header: `Authorization: Bearer {{token}}`

### Issue: 422 Validation Error on Register
**Cause**: Email already exists or password too weak

**Solution**: Use new email and password with:
- Min 8 characters
- At least one uppercase letter
- At least one number
- At least one special character

### Issue: 429 Too Many Requests
**Cause**: Rate limited

**Solution**: 
- Wait `retry_after` seconds (usually 900 = 15 minutes)
- Or use different email/IP address

### Issue: 500 Server Error
**Cause**: Server-side error

**Solution**:
- Check `request_id` in response
- Search logs for request_id
- Contact support with request_id
- Retry after brief wait

---

## Headers

### Authentication Header
```
Authorization: Bearer {token}
```

### Content-Type
```
Content-Type: application/json
```

### Rate Limit Headers (in response)
```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 2
X-RateLimit-Reset: 1705329000
Retry-After: 900
```

---

## Testing Workflow

### Complete Flow
1. **Register** → Get token
2. **Login** → Verify token works
3. **Get /me** → Verify profile
4. **Refresh** → Get new token
5. **Logout** → Invalidate token
6. **Get /me again** → Should fail (401)

### Error Testing
1. **Wrong Password** → 401 with remaining_attempts
2. **5 Failed Logins** → Still 401
3. **6th Failed Login** → 429 Rate Limited
4. **Invalid Email on Register** → 422 with validation_errors
5. **Already Registered Email** → 422 Email sudah terdaftar
6. **No Authorization Header** → 401

---

## API Documentation Links

| Resource | URL |
|----------|-----|
| Swagger UI | http://localhost:8000/api/docs |
| OpenAPI JSON | http://localhost:8000/api/docs/openapi.json |
| ReDoc | http://localhost:8000/api/docs/redoc |

---

## File References

| Document | Purpose |
|----------|---------|
| [API_TESTING_GUIDE.md](API_TESTING_GUIDE.md) | Complete testing guide with workflows |
| [ERROR_RESPONSE_REFERENCE.md](ERROR_RESPONSE_REFERENCE.md) | Detailed error response examples |
| [POSTMAN_TESTING_GUIDE_ADVANCED.md](POSTMAN_TESTING_GUIDE_ADVANCED.md) | Postman setup and automation |
| [API_DOCUMENTATION_ENHANCEMENT_REPORT.md](API_DOCUMENTATION_ENHANCEMENT_REPORT.md) | This project's completion report |

---

## Quick Test with cURL

### Register
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "TestPass123!",
    "password_confirmation": "TestPass123!",
    "name": "Test User",
    "role": "pasien"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "TestPass123!"
  }'
```

### Get Profile (with token)
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {token}"
```

### Logout (with token)
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer {token}"
```

---

## Success Indicators

✅ Swagger UI shows all endpoints with examples  
✅ Can import OpenAPI spec in Postman  
✅ All 5 auth endpoints documented  
✅ Response examples for all status codes  
✅ Validation error examples present  
✅ Rate limit info documented  
✅ Authorization working with Bearer tokens  
✅ Can complete full auth flow  

---

**Last Updated**: 2024-01-15  
**API Version**: 1.0.0  
**Status**: ✅ Complete and Ready for Testing
