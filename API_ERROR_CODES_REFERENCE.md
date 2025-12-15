# API Error Codes & Status Reference

Complete reference for all API responses, error codes, and status meanings.

## HTTP Status Codes

### Success Responses

| Code | Status | Meaning |
|------|--------|---------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request successful, no response body |

### Client Error Responses

| Code | Status | Meaning |
|------|--------|---------|
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated but not authorized for this resource |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Resource conflict (e.g., double booking) |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |

### Server Error Responses

| Code | Status | Meaning |
|------|--------|---------|
| 500 | Internal Server Error | Unexpected server error |
| 503 | Service Unavailable | Server temporarily unavailable |

## API Error Response Format

All error responses follow this format:

```json
{
  "message": "Error description",
  "error": "ERROR_CODE",
  "status": 422,
  "errors": {
    "field_name": ["Error message for field"]
  }
}
```

## Error Codes

### Authentication Errors (1000-1099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 1001 | INVALID_CREDENTIALS | 401 | Email or password incorrect |
| 1002 | INVALID_TOKEN | 401 | JWT token is invalid or expired |
| 1003 | TOKEN_EXPIRED | 401 | Authentication token has expired |
| 1004 | MISSING_TOKEN | 401 | Authorization header missing |
| 1005 | UNAUTHORIZED_ACTION | 403 | User not authorized for this action |
| 1006 | ROLE_REQUIRED | 403 | Specific role required for this endpoint |

**Example Response:**
```json
{
  "message": "Invalid credentials provided",
  "error": "INVALID_CREDENTIALS",
  "status": 401
}
```

### Validation Errors (2000-2099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 2001 | VALIDATION_FAILED | 422 | Request validation failed |
| 2002 | INVALID_EMAIL | 422 | Email format invalid |
| 2003 | EMAIL_EXISTS | 422 | Email already registered |
| 2004 | INVALID_PASSWORD | 422 | Password requirements not met |
| 2005 | INVALID_DATE | 422 | Invalid date format or value |
| 2006 | INVALID_PHONE | 422 | Invalid phone number format |
| 2007 | REQUIRED_FIELD_MISSING | 422 | Required field is missing |

**Example Response:**
```json
{
  "message": "Validation failed",
  "error": "VALIDATION_FAILED",
  "status": 422,
  "errors": {
    "email": ["Email must be a valid email address"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

### Appointment Errors (3000-3099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 3001 | DOCTOR_NOT_FOUND | 404 | Doctor does not exist |
| 3002 | APPOINTMENT_NOT_FOUND | 404 | Appointment does not exist |
| 3003 | SLOT_UNAVAILABLE | 409 | Time slot already booked |
| 3004 | DOUBLE_BOOKING | 409 | Doctor already has appointment at that time |
| 3005 | INVALID_APPOINTMENT_TIME | 422 | Appointment time is in the past or outside working hours |
| 3006 | APPOINTMENT_CANNOT_BE_CANCELLED | 422 | Appointment cannot be cancelled in current status |
| 3007 | APPOINTMENT_CANNOT_BE_CONFIRMED | 422 | Appointment cannot be confirmed in current status |
| 3008 | INVALID_STATUS_TRANSITION | 422 | Cannot transition from current status to requested status |

**Example Response:**
```json
{
  "message": "Time slot already booked for this doctor",
  "error": "SLOT_UNAVAILABLE",
  "status": 409
}
```

### Consultation Errors (4000-4099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 4001 | CONSULTATION_NOT_FOUND | 404 | Consultation does not exist |
| 4002 | INVALID_CONSULTATION_STATUS | 422 | Consultation status is invalid |
| 4003 | CONSULTATION_ALREADY_STARTED | 422 | Consultation is already in progress |
| 4004 | CONSULTATION_NOT_STARTED | 422 | Consultation has not started yet |
| 4005 | CONSULTATION_ALREADY_ENDED | 422 | Consultation is already completed |
| 4006 | MISSING_DIAGNOSIS | 422 | Diagnosis is required to end consultation |
| 4007 | MISSING_TREATMENT | 422 | Treatment is required to end consultation |
| 4008 | ONLY_DOCTOR_CAN_START | 403 | Only doctor can start consultation |

**Example Response:**
```json
{
  "message": "Only doctor can start consultation",
  "error": "ONLY_DOCTOR_CAN_START",
  "status": 403
}
```

### Prescription Errors (5000-5099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 5001 | PRESCRIPTION_NOT_FOUND | 404 | Prescription does not exist |
| 5002 | INVALID_DOSAGE | 422 | Dosage format is invalid |
| 5003 | INVALID_MEDICATION | 422 | Medication is not available |
| 5004 | INVALID_DURATION | 422 | Duration format is invalid |

### Rating Errors (6000-6099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 6001 | RATING_NOT_FOUND | 404 | Rating does not exist |
| 6002 | INVALID_RATING_VALUE | 422 | Rating must be between 1-5 |
| 6003 | APPOINTMENT_NOT_COMPLETED | 422 | Can only rate completed appointments |
| 6004 | ALREADY_RATED | 422 | You have already rated this appointment |
| 6005 | CANNOT_RATE_OWN_APPOINTMENT | 422 | Doctors cannot rate their own appointments |

**Example Response:**
```json
{
  "message": "Rating must be between 1 and 5",
  "error": "INVALID_RATING_VALUE",
  "status": 422
}
```

### Business Logic Errors (7000-7099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 7001 | INSUFFICIENT_PERMISSIONS | 403 | User lacks required permissions |
| 7002 | RESOURCE_ACCESS_DENIED | 403 | Cannot access other user's resource |
| 7003 | CONCURRENT_MODIFICATION | 409 | Resource was modified by another request |
| 7004 | LOCK_TIMEOUT | 500 | Database lock timeout (retry later) |
| 7005 | TRANSACTION_FAILED | 500 | Database transaction failed |
| 7006 | CACHE_ERROR | 500 | Cache operation failed |
| 7007 | RATE_LIMIT_EXCEEDED | 429 | Too many requests, please retry later |

**Example Response:**
```json
{
  "message": "Too many requests from this IP, please retry later",
  "error": "RATE_LIMIT_EXCEEDED",
  "status": 429,
  "retry_after": 60
}
```

### File/Media Errors (8000-8099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 8001 | FILE_NOT_FOUND | 404 | Requested file does not exist |
| 8002 | INVALID_FILE_TYPE | 422 | File type not allowed |
| 8003 | FILE_TOO_LARGE | 422 | File exceeds maximum size |
| 8004 | FILE_UPLOAD_FAILED | 500 | File upload failed |
| 8005 | INVALID_IMAGE | 422 | Image is corrupted or invalid |

### System Errors (9000-9099)

| Code | Name | HTTP | Meaning |
|------|------|------|---------|
| 9001 | DATABASE_ERROR | 500 | Database operation failed |
| 9002 | SERVICE_UNAVAILABLE | 503 | External service is unavailable |
| 9003 | INTERNAL_ERROR | 500 | Unexpected internal error |
| 9004 | CONFIG_ERROR | 500 | Application configuration error |
| 9005 | CACHE_UNAVAILABLE | 503 | Cache service is unavailable |

## Status Values

### Appointment Statuses

```
pending → confirmed → in_progress → completed
  ↓              ↓                    ↓
  └─→ cancelled   └────→ cancelled ←─┘
```

| Status | Meaning |
|--------|---------|
| pending | Appointment created, awaiting doctor confirmation |
| confirmed | Doctor confirmed appointment, ready to proceed |
| in_progress | Consultation is currently happening |
| completed | Consultation finished successfully |
| cancelled | Appointment cancelled by patient or doctor |

### Consultation Statuses

```
scheduled → in_progress → completed
   ↓            ↓
   └→ cancelled ←┘
```

| Status | Meaning |
|--------|---------|
| scheduled | Appointment confirmed, consultation not started |
| in_progress | Doctor-patient consultation is happening |
| completed | Consultation finished with diagnosis/treatment |
| cancelled | Consultation was cancelled |

### Prescription Statuses

| Status | Meaning |
|--------|---------|
| active | Prescription is active and valid |
| completed | Patient has completed the course |
| discontinued | Doctor discontinued the prescription |

## Rate Limiting

### Rate Limit Headers

All responses include rate limit information:

```
X-RateLimit-Limit: 100           # Requests allowed per hour
X-RateLimit-Remaining: 87        # Requests remaining
X-RateLimit-Reset: 1703086800    # Unix timestamp of reset
```

### Rate Limit Tiers

| Role | Requests/Hour | Requests/Day |
|------|---------------|--------------|
| Patient | 100 | 1000 |
| Doctor | 300 | 3000 |
| Admin | 1000 | 10000 |
| Unauthenticated | 20 | 200 |

## Pagination

All list endpoints support pagination:

```
GET /api/appointments?page=1&per_page=15
```

**Response:**
```json
{
  "data": [...],
  "pagination": {
    "total": 150,
    "per_page": 15,
    "current_page": 1,
    "last_page": 10,
    "from": 1,
    "to": 15
  }
}
```

## Filtering

### Appointment Filtering

```
GET /api/appointments?status=pending&date_from=2024-12-01&date_to=2024-12-31
```

Supported filters:
- `status`: pending, confirmed, in_progress, completed, cancelled
- `doctor_id`: Specific doctor
- `date_from`, `date_to`: Date range
- `type`: online, offline

### Consultation Filtering

```
GET /api/consultations?status=completed&doctor_id=5&limit=50
```

## Common Response Patterns

### Success Response
```json
{
  "data": {...},
  "message": "Operation successful",
  "timestamp": "2024-12-20T10:30:00Z"
}
```

### List Response
```json
{
  "data": [...],
  "pagination": {...},
  "meta": {
    "total": 100,
    "timestamp": "2024-12-20T10:30:00Z"
  }
}
```

### Error Response
```json
{
  "message": "Error description",
  "error": "ERROR_CODE",
  "status": 422,
  "errors": {
    "field": ["validation error"]
  },
  "timestamp": "2024-12-20T10:30:00Z"
}
```

## Versioning

Current API version: **2.0.0**

Version is indicated in:
- URL: `/api/v2/appointments`
- Header: `X-API-Version: 2.0.0`
- OpenAPI spec: `info.version: 2.0.0`

## Examples by Endpoint

### Book Appointment - Successful

```bash
curl -X POST http://localhost:8000/api/appointments \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "doctor_id": 5,
    "scheduled_at": "2024-12-25T10:00:00Z",
    "type": "online",
    "notes": "Regular checkup"
  }'
```

**Response (201):**
```json
{
  "data": {
    "id": 42,
    "doctor_id": 5,
    "patient_id": 10,
    "scheduled_at": "2024-12-25T10:00:00Z",
    "type": "online",
    "status": "pending",
    "created_at": "2024-12-20T10:30:00Z"
  },
  "message": "Appointment booked successfully"
}
```

### Book Appointment - Double Booking Error

**Response (409):**
```json
{
  "message": "Time slot already booked for this doctor",
  "error": "SLOT_UNAVAILABLE",
  "status": 409
}
```

### Validation Error

**Response (422):**
```json
{
  "message": "Validation failed",
  "error": "VALIDATION_FAILED",
  "status": 422,
  "errors": {
    "scheduled_at": ["Appointment must be scheduled for a future date"],
    "type": ["Type must be either online or offline"]
  }
}
```

---

**Last Updated**: Session 5
**API Version**: 2.0.0
**Status**: Complete API documentation
