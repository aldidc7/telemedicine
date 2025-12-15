# API Documentation Implementation Guide

Comprehensive guide untuk OpenAPI/Swagger documentation dan API specifications.

## Overview

- ✅ OpenAPI 3.0 specification
- ✅ Automated documentation with Swagger UI
- ✅ Comprehensive error code reference
- ✅ Complete endpoint documentation
- ✅ Request/response examples
- ✅ Authentication guide

## Installation

### Step 1: Install L5-Swagger Package

```bash
composer require darkaonline/l5-swagger
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

### Step 3: Generate Documentation

```bash
php artisan l5-swagger:generate
```

## Accessing Documentation

### Development

```
http://localhost:8000/api/documentation
```

### Production

```
https://api.telemedicine.local/api/documentation
```

## API Endpoints (Complete Reference)

### Authentication

#### Register
- **Method**: POST
- **Path**: `/api/auth/register`
- **Auth**: None
- **Status**: 201 Created
- **Errors**: 422 Validation Failed

#### Login
- **Method**: POST
- **Path**: `/api/auth/login`
- **Auth**: None
- **Status**: 200 OK
- **Returns**: `access_token`, `token_type`, `expires_in`

#### Logout
- **Method**: POST
- **Path**: `/api/auth/logout`
- **Auth**: Required (Bearer Token)
- **Status**: 200 OK

### Appointments

#### Get Available Slots
- **Method**: GET
- **Path**: `/api/appointments/available-slots`
- **Auth**: Required
- **Parameters**:
  - `doctor_id` (int, required)
  - `date` (date, required)
- **Status**: 200 OK
- **Returns**: Array of time slots (e.g., ["09:00", "09:30", ...])

#### Book Appointment
- **Method**: POST
- **Path**: `/api/appointments`
- **Auth**: Required
- **Body**:
  ```json
  {
    "doctor_id": 5,
    "scheduled_at": "2024-12-25T10:00:00Z",
    "type": "online",
    "notes": "Optional notes"
  }
  ```
- **Status**: 201 Created
- **Errors**: 409 Slot Unavailable, 422 Validation Failed

#### Get Appointment
- **Method**: GET
- **Path**: `/api/appointments/{id}`
- **Auth**: Required
- **Status**: 200 OK

#### List Appointments
- **Method**: GET
- **Path**: `/api/appointments`
- **Auth**: Required
- **Query Parameters**:
  - `page` (int, default: 1)
  - `per_page` (int, default: 15, max: 100)
  - `status` (string: pending, confirmed, completed, cancelled)
  - `date_from` (date)
  - `date_to` (date)
- **Status**: 200 OK

#### Confirm Appointment
- **Method**: POST
- **Path**: `/api/appointments/{id}/confirm`
- **Auth**: Required (Doctor only)
- **Status**: 200 OK

#### Cancel Appointment
- **Method**: POST
- **Path**: `/api/appointments/{id}/cancel`
- **Auth**: Required
- **Body**: `{ "reason": "optional cancellation reason" }`
- **Status**: 200 OK

#### Mark Completed
- **Method**: POST
- **Path**: `/api/appointments/{id}/complete`
- **Auth**: Required (Doctor only)
- **Status**: 200 OK

### Consultations

#### Start Consultation
- **Method**: POST
- **Path**: `/api/consultations/{id}/start`
- **Auth**: Required (Doctor only)
- **Status**: 200 OK
- **Errors**: 403 Not Authorized, 404 Not Found

#### End Consultation
- **Method**: POST
- **Path**: `/api/consultations/{id}/end`
- **Auth**: Required (Doctor only)
- **Body**:
  ```json
  {
    "diagnosis": "Patient diagnosis",
    "treatment": "Treatment plan",
    "notes": "Additional notes"
  }
  ```
- **Status**: 200 OK
- **Errors**: 422 Missing Required Fields

#### Get Consultation
- **Method**: GET
- **Path**: `/api/consultations/{id}`
- **Auth**: Required
- **Status**: 200 OK

#### List Consultations
- **Method**: GET
- **Path**: `/api/consultations`
- **Auth**: Required
- **Query Parameters**:
  - `page` (int)
  - `per_page` (int)
  - `status` (string)
  - `type` (string: doctor, patient)
- **Status**: 200 OK

#### Cancel Consultation
- **Method**: POST
- **Path**: `/api/consultations/{id}/cancel`
- **Auth**: Required
- **Status**: 200 OK

### Prescriptions

#### Create Prescription
- **Method**: POST
- **Path**: `/api/prescriptions`
- **Auth**: Required (Doctor only)
- **Body**:
  ```json
  {
    "consultation_id": 10,
    "medication": "Paracetamol",
    "dosage": "2x500mg",
    "duration": "7 days",
    "notes": "Take after meals"
  }
  ```
- **Status**: 201 Created

#### Get Prescriptions
- **Method**: GET
- **Path**: `/api/prescriptions/{consultationId}`
- **Auth**: Required
- **Status**: 200 OK

### Ratings

#### Create Rating
- **Method**: POST
- **Path**: `/api/ratings`
- **Auth**: Required (Patient only)
- **Body**:
  ```json
  {
    "appointment_id": 42,
    "rating": 5,
    "comment": "Excellent consultation"
  }
  ```
- **Status**: 201 Created
- **Errors**: 422 Invalid Rating, 409 Already Rated

#### Get Doctor Ratings
- **Method**: GET
- **Path**: `/api/doctors/{id}/ratings`
- **Auth**: Optional
- **Query**: `page`, `per_page`
- **Status**: 200 OK

### Profile

#### Get Profile
- **Method**: GET
- **Path**: `/api/profile`
- **Auth**: Required
- **Status**: 200 OK

#### Update Profile
- **Method**: PUT
- **Path**: `/api/profile`
- **Auth**: Required
- **Body**:
  ```json
  {
    "name": "John Doe",
    "phone": "+6281234567890",
    "bio": "Brief bio",
    "specialization": "Cardiology" // Doctor only
  }
  ```
- **Status**: 200 OK

### Doctors

#### List Doctors
- **Method**: GET
- **Path**: `/api/doctors`
- **Auth**: Optional
- **Query**:
  - `specialization` (string)
  - `page`, `per_page`
  - `search` (name, email)
- **Status**: 200 OK

#### Get Doctor Details
- **Method**: GET
- **Path**: `/api/doctors/{id}`
- **Auth**: Optional
- **Status**: 200 OK
- **Returns**: Doctor info + average rating

### Dashboard

#### Doctor Dashboard
- **Method**: GET
- **Path**: `/api/doctor/dashboard`
- **Auth**: Required (Doctor only)
- **Status**: 200 OK
- **Returns**:
  ```json
  {
    "today_appointments": 5,
    "total_patients": 150,
    "average_rating": 4.8,
    "recent_consultations": [...],
    "upcoming_appointments": [...]
  }
  ```

#### Patient Dashboard
- **Method**: GET
- **Path**: `/api/patient/dashboard`
- **Auth**: Required (Patient only)
- **Status**: 200 OK
- **Returns**:
  ```json
  {
    "upcoming_appointments": [...],
    "past_consultations": [...],
    "active_prescriptions": [...]
  }
  ```

### System

#### Health Check
- **Method**: GET
- **Path**: `/api/health`
- **Auth**: None
- **Status**: 200 OK
- **Returns**:
  ```json
  {
    "status": "ok",
    "timestamp": "2024-12-20T10:30:00Z",
    "database": "ok",
    "cache": "ok"
  }
  ```

## Authentication

### Bearer Token Authentication

All protected endpoints require Bearer token in Authorization header:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Token Lifecycle

1. **Obtain Token** - POST `/api/auth/login`
   - Returns: `access_token`, `expires_in` (seconds)
   
2. **Use Token** - Include in Authorization header
   - Sent with every authenticated request
   
3. **Refresh Token** - POST `/api/auth/refresh`
   - Get new token when expired
   
4. **Logout** - POST `/api/auth/logout`
   - Invalidate current token

### Token Expiration

- Token TTL: 24 hours
- Refresh token: 30 days
- Revoked on logout

## Request Examples

### cURL

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"doctor@test.com","password":"password"}'

# Book Appointment
curl -X POST http://localhost:8000/api/appointments \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "doctor_id": 5,
    "scheduled_at": "2024-12-25T10:00:00Z",
    "type": "online"
  }'

# Get Dashboard
curl -X GET http://localhost:8000/api/doctor/dashboard \
  -H "Authorization: Bearer TOKEN"
```

### JavaScript/Fetch

```javascript
// Login
const response = await fetch('/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'doctor@test.com',
    password: 'password'
  })
});
const { access_token } = await response.json();

// Use token
const appointmentResponse = await fetch('/api/appointments', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${access_token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    doctor_id: 5,
    scheduled_at: '2024-12-25T10:00:00Z',
    type: 'online'
  })
});
```

### Python/Requests

```python
import requests

# Login
response = requests.post(
    'http://localhost:8000/api/auth/login',
    json={'email': 'doctor@test.com', 'password': 'password'}
)
token = response.json()['access_token']

# Book Appointment
headers = {'Authorization': f'Bearer {token}'}
response = requests.post(
    'http://localhost:8000/api/appointments',
    json={
        'doctor_id': 5,
        'scheduled_at': '2024-12-25T10:00:00Z',
        'type': 'online'
    },
    headers=headers
)
```

## Response Headers

All responses include standard headers:

```
Content-Type: application/json
X-API-Version: 2.0.0
X-Response-Time: 145ms
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 87
X-RateLimit-Reset: 1703086800
X-Request-ID: req_abc123def456
```

## Testing the API

### Using Postman

1. Import OpenAPI spec: `http://localhost:8000/api/openapi.json`
2. Create environment with `BASE_URL` and `TOKEN`
3. Set up pre-request scripts to refresh token

### Using Swagger UI

Access at: `http://localhost:8000/api/documentation`

Features:
- ✅ Try it out functionality
- ✅ Parameter auto-fill
- ✅ Response visualization
- ✅ Schema documentation

### Using REST Client (VS Code)

Create `test-api.http`:

```http
### Login
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "email": "doctor@test.com",
  "password": "password"
}

### Get Available Slots
GET http://localhost:8000/api/appointments/available-slots?doctor_id=5&date=2024-12-25
Authorization: Bearer TOKEN
```

## API Versioning

### Current Version: 2.0.0

Version strategy:
- **MAJOR**: Breaking changes (incompatible)
- **MINOR**: New features (backwards compatible)
- **PATCH**: Bug fixes (backwards compatible)

Deprecation policy:
- New major version announces deprecations
- 6-month notice before removal
- Old version supported for 12 months

## Rate Limiting

API rate limits per role:

```
Patient:        100 requests/hour
Doctor:         300 requests/hour
Admin:         1000 requests/hour
Unauthenticated: 20 requests/hour
```

When limit exceeded:
- HTTP 429 Too Many Requests
- Headers include: `Retry-After`, `X-RateLimit-Reset`

## CORS Configuration

API supports CORS for:
- Origin: `http://localhost:3000` (dev)
- Methods: GET, POST, PUT, DELETE, OPTIONS
- Headers: Content-Type, Authorization
- Credentials: Included

## Security

### Best Practices

1. ✅ Always use HTTPS in production
2. ✅ Never expose tokens in logs
3. ✅ Rotate tokens regularly
4. ✅ Use short token expiration
5. ✅ Validate all input
6. ✅ Implement rate limiting
7. ✅ Log security events
8. ✅ Use CORS properly

### Headers

- ✅ `X-Content-Type-Options: nosniff`
- ✅ `X-Frame-Options: DENY`
- ✅ `X-XSS-Protection: 1; mode=block`
- ✅ `Strict-Transport-Security: max-age=31536000`

---

**Last Updated**: Session 5
**API Version**: 2.0.0
**Maturity**: 98% (documentation complete)
