# POSTMAN COLLECTION SETUP GUIDE

## 📥 Importing Collection

1. Open Postman
2. Click **Import** button (top left)
3. Choose **File** tab
4. Select: `Telemedicine_API_Collection.postman_collection.json` (already in project root)
5. Click **Import**

## 🔑 Environment Setup

Create a new Postman Environment with these variables:

```json
{
  "base_url": "http://localhost:8000",
  "api_url": "http://localhost:8000/api",
  "token": "",
  "user_id": "",
  "dokter_id": "",
  "admin_id": "",
  "pasien_id": ""
}
```

### How to Set Variables:

1. Click **Environments** (left panel)
2. Click **Create Environment**
3. Add the variables above
4. Save as "Telemedicine Dev"
5. Select it in top-right dropdown

## 🔐 Authentication Flow

### 1. Register New User (Pasien)
```
POST {{api_url}}/register
Body (raw JSON):
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "pasien"
}
```

**Expected Response:** 201 Created with user data and token

### 2. Login User
```
POST {{api_url}}/login
Body (raw JSON):
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Expected Response:** 200 OK with token

**Action:** Copy the `token` from response and set environment variable

### 3. Using Token
All protected endpoints require:
```
Headers:
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

## 📋 TEST ENDPOINTS

### Health Check
```
GET {{api_url}}/health
Headers: None
Expected: 200 OK
Response: { "status": "ok" }
```

### API Documentation
```
GET {{base_url}}/api/docs
Open in browser or Postman
Shows Swagger UI with all endpoints
```

## 👨‍⚕️ DOCTOR VERIFICATION ENDPOINTS

### 1. Upload Document
```
POST {{api_url}}/doctor/verification/upload
Headers:
  - Authorization: Bearer {{token}}
  - Content-Type: multipart/form-data

Body (form-data):
  - document_type: "sip" (or "izin", "sertifikat")
  - file: <SELECT FILE>

Expected: 201 Created
Response: { "success": true, "data": { "id": 1, "status": "pending", ... } }
```

### 2. List Doctor's Documents
```
GET {{api_url}}/doctor/verification/documents
Headers:
  - Authorization: Bearer {{token}}

Expected: 200 OK
Response: { "success": true, "data": [...], "meta": { "pagination": {...} } }
```

### 3. Get Verification Status
```
GET {{api_url}}/doctor/verification/status
Headers:
  - Authorization: Bearer {{token}}

Expected: 200 OK
Response: { "success": true, "data": { "overall_status": "pending", "documents": [...] } }
```

### 4. Admin List Pending Documents
```
GET {{api_url}}/admin/verification/pending
Headers:
  - Authorization: Bearer {{token}}
  - Role: admin

Expected: 200 OK
Response: { "success": true, "data": [...] }
```

### 5. Admin Approve Document
```
POST {{api_url}}/admin/verification/{{doc_id}}/approve
Headers:
  - Authorization: Bearer {{token}}
  - Role: admin

Expected: 200 OK
Response: { "success": true, "message": "Document approved", "data": {...} }
```

### 6. Admin Reject Document
```
POST {{api_url}}/admin/verification/{{doc_id}}/reject
Headers:
  - Authorization: Bearer {{token}}
  - Role: admin

Body (raw JSON):
{
  "rejection_reason": "Invalid document format"
}

Expected: 200 OK
Response: { "success": true, "message": "Document rejected", "data": {...} }
```

### 7. Download Document
```
GET {{api_url}}/verification/{{doc_id}}/download
Headers:
  - Authorization: Bearer {{token}}

Expected: 200 OK with file content
```

## 📊 ANALYTICS ENDPOINTS

### 1. Admin Analytics Dashboard
```
GET {{api_url}}/analytics/admin-dashboard
Headers:
  - Authorization: Bearer {{token}}
  - Role: admin

Query Parameters (optional):
  - from_date: 2025-01-01
  - to_date: 2025-12-31
  - specialization: Umum

Expected: 200 OK
Response: { "success": true, "data": { "total_consultations": 100, "charts": {...}, ... } }
```

### 2. Doctor Analytics Dashboard
```
GET {{api_url}}/analytics/doctor-dashboard
Headers:
  - Authorization: Bearer {{token}}
  - Role: dokter

Query Parameters (optional):
  - from_date: 2025-01-01
  - to_date: 2025-12-31

Expected: 200 OK
Response: { "success": true, "data": { "my_consultations": 50, "my_ratings": {...}, ... } }
```

### 3. General Statistics
```
GET {{api_url}}/analytics/stats
Headers:
  - Authorization: Bearer {{token}}

Query Parameters (optional):
  - period: monthly | yearly | all

Expected: 200 OK
Response: { "success": true, "data": { "doctors": 20, "patients": 500, "consultations": 1200 } }
```

## 👥 CONSULTATION ENDPOINTS (Existing)

### 1. Create Consultation
```
POST {{api_url}}/consultations
Headers:
  - Authorization: Bearer {{token}}

Body (raw JSON):
{
  "dokter_id": 1,
  "pasien_id": 1,
  "keluhan": "Sakit kepala",
  "consultation_type": "online_chat"
}

Expected: 201 Created
```

### 2. List Consultations
```
GET {{api_url}}/consultations
Headers:
  - Authorization: Bearer {{token}}

Query Parameters:
  - page: 1
  - per_page: 10
  - status: completed

Expected: 200 OK
```

### 3. Get Consultation Details
```
GET {{api_url}}/consultations/{{consultation_id}}
Headers:
  - Authorization: Bearer {{token}}

Expected: 200 OK
```

### 4. Update Consultation
```
PUT {{api_url}}/consultations/{{consultation_id}}
Headers:
  - Authorization: Bearer {{token}}

Body (raw JSON):
{
  "status": "completed"
}

Expected: 200 OK
```

### 5. Delete Consultation
```
DELETE {{api_url}}/consultations/{{consultation_id}}
Headers:
  - Authorization: Bearer {{token}}

Expected: 200 OK (deleted)
```

## 🔍 TESTING SCENARIOS

### Scenario 1: Doctor Verification Workflow
1. ✅ Create new doctor account
2. ✅ Upload SIP document
3. ✅ Switch to admin account
4. ✅ List pending documents
5. ✅ Approve document
6. ✅ Switch back to doctor
7. ✅ Check status (should be approved)

### Scenario 2: Admin Analytics
1. ✅ Create multiple consultations (as different users)
2. ✅ Create ratings for those consultations
3. ✅ Login as admin
4. ✅ GET /analytics/admin-dashboard
5. ✅ Verify data aggregation is correct

### Scenario 3: Doctor Personal Analytics
1. ✅ Login as doctor
2. ✅ GET /analytics/doctor-dashboard
3. ✅ Verify only own consultations shown
4. ✅ Verify ratings calculated correctly

## 🐛 DEBUGGING TIPS

### Check Request Headers
- Click **Header** section
- Verify **Authorization** token is present
- Ensure **Content-Type** is correct (application/json or multipart/form-data)

### Check Response Code
- 200/201 = Success
- 400 = Bad Request (check body)
- 401 = Unauthorized (check token)
- 403 = Forbidden (check role/permissions)
- 404 = Not Found (check resource ID)
- 422 = Validation Error (check required fields)
- 500 = Server Error (check Laravel logs)

### View Response
- Click **Body** tab in response
- Check **Pretty** mode for formatted JSON
- Look for **success** field (should be true/false)

### Check Logs
```bash
# In application terminal
tail -f storage/logs/laravel.log
```

## 📈 PERFORMANCE TESTING

### Load Test Endpoint
```
GET {{api_url}}/analytics/admin-dashboard?from_date=2025-01-01&to_date=2025-12-31
```

Expected:
- Response time: < 500ms
- Status: 200
- No errors in response

### Pagination Test
```
GET {{api_url}}/consultations?page=1&per_page=50
```

Expected:
- Response includes pagination metadata
- Data array contains items
- Total count matches

## 🔐 SECURITY TESTING

### Test 401 Unauthorized
```
GET {{api_url}}/consultations
(No Authorization header)

Expected: 401 Unauthorized
```

### Test 403 Forbidden
```
POST {{api_url}}/admin/verification/1/approve
(With dokter token, not admin)

Expected: 403 Forbidden
```

### Test Input Validation
```
POST {{api_url}}/doctor/verification/upload
Body:
{
  "document_type": "invalid_type",
  "file": <invalid file>
}

Expected: 422 Unprocessable Entity with validation errors
```

## 📝 COLLECTION EXPORT

To update the collection in the project:
1. Click **Collections** > **Telemedicine API**
2. Click **...** > **Export**
3. Save as `Telemedicine_API_Collection.postman_collection.json`
4. Commit to git

## 🚀 QUICK START

```bash
# 1. Start Laravel server
php artisan serve

# 2. Open Postman
# 3. Import collection
# 4. Create environment
# 5. Register new user
# 6. Get token
# 7. Test endpoints!
```

---

**Last Updated:** December 20, 2025  
**Collection Version:** 1.0.0
