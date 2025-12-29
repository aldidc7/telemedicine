# Postman Collection Testing Guide

## Overview

Panduan lengkap untuk setup dan testing API Telemedicine menggunakan Postman dengan semua error scenarios dan examples.

---

## 1. Import OpenAPI Specification

### Method 1: Direct Import from URL
1. Open Postman
2. Click **File** → **Import**
3. Select **Link** tab
4. Paste URL: `http://localhost:8000/api/docs/openapi.json`
5. Click **Continue** → **Import**

### Method 2: Download and Import
1. Visit `http://localhost:8000/api/docs/openapi.json`
2. Save as `telemedicine-api.json`
3. In Postman: **File** → **Import** → Select file
4. Click **Import**

---

## 2. Environment Setup

### Create New Environment

1. Click **Environments** (left sidebar)
2. Click **Create New** button
3. Name: `Telemedicine Local`
4. Add these variables:

```
Variable Name          | Initial Value                | Current Value
-----------------------|------------------------------|---------------------------
base_url              | http://localhost:8000/api   | http://localhost:8000/api
token                 | (empty)                     | (empty)
user_email            | test@example.com            | test@example.com
user_password         | TestPass123!                | TestPass123!
user_id               | (empty)                     | (empty)
appointment_id        | (empty)                     | (empty)
doctor_id             | (empty)                     | (empty)
```

5. Click **Save**

### Using Variables in Requests

**In URL**:
```
{{base_url}}/auth/login
```

**In Headers**:
```
Authorization: Bearer {{token}}
```

**In Body**:
```json
{
  "email": "{{user_email}}",
  "password": "{{user_password}}"
}
```

---

## 3. Pre-request Scripts Setup

### Global Pre-request Script

1. Click **Environment** (top-right)
2. Select your environment
3. In **Pre-request Scripts** tab, add:

```javascript
// Automatically add Authorization header if token exists
const token = pm.environment.get("token");

if (token) {
    pm.request.headers.add({
        key: "Authorization",
        value: `Bearer ${token}`,
        disabled: false
    });
}

// Log request details for debugging
const timestamp = new Date().toISOString();
console.log(`[${timestamp}] ${pm.request.method} ${pm.request.url}`);
console.log(`Token: ${token ? 'Present' : 'Missing'}`);
```

### Request-specific Pre-request Scripts

**For endpoints requiring Authorization**:

```javascript
const token = pm.environment.get("token");

if (!token) {
    pm.sendRequest({
        url: pm.environment.get("base_url") + "/auth/login",
        method: 'POST',
        header: {
            'Content-Type': 'application/json'
        },
        body: {
            mode: 'raw',
            raw: JSON.stringify({
                email: pm.environment.get("user_email"),
                password: pm.environment.get("user_password")
            })
        }
    }, function (err, response) {
        if (!err) {
            const data = response.json();
            pm.environment.set("token", data.data.token);
        }
    });
}
```

---

## 4. Tests Setup

### Global Tests Script

1. Click **Collections** (left sidebar)
2. Select **Telemedicine API** collection
3. Click **...** → **Edit**
4. Go to **Tests** tab
5. Add:

```javascript
// Test response structure
pm.test("Response has required fields", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property("success");
    pm.expect(jsonData).to.have.property("error").or.have.property("data");
});

// Test status codes
pm.test("Status code is valid", function () {
    const statusCode = pm.response.code;
    pm.expect([200, 201, 400, 401, 403, 404, 422, 429, 500]).to.include(statusCode);
});

// Save token if present in response
const jsonData = pm.response.json();
if (jsonData.data && jsonData.data.token) {
    pm.environment.set("token", jsonData.data.token);
    pm.environment.set("user_id", jsonData.data.user.id);
    console.log("Token saved: " + jsonData.data.token.substring(0, 20) + "...");
}

// Log response for debugging
console.log("Response Status: " + pm.response.code);
if (pm.response.code >= 400) {
    console.log("Error: " + JSON.stringify(pm.response.json().error, null, 2));
}
```

### Specific Tests for Auth Endpoints

**Test for /auth/register (Success)**:
```javascript
pm.test("Register returns token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.equal(true);
    pm.expect(jsonData.data).to.have.property("token");
    pm.expect(jsonData.data).to.have.property("user");
    pm.expect(jsonData.data.user).to.have.property("id");
    pm.expect(jsonData.data.user).to.have.property("email");
});
```

**Test for /auth/login (Success)**:
```javascript
pm.test("Login returns valid token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.equal(true);
    pm.expect(jsonData.data).to.have.property("token");
    pm.expect(jsonData.data).to.have.property("token_type").to.equal("Bearer");
    pm.expect(jsonData.data).to.have.property("expires_in").to.be.a("number");
});
```

**Test for Validation Errors (422)**:
```javascript
pm.test("Validation error has field errors", function () {
    var jsonData = pm.response.json();
    pm.expect(pm.response.code).to.equal(422);
    pm.expect(jsonData.success).to.equal(false);
    pm.expect(jsonData.error.code).to.equal("VALIDATION_ERROR");
    pm.expect(jsonData.error.details).to.have.property("validation_errors");
});
```

**Test for Rate Limiting (429)**:
```javascript
pm.test("Rate limit response has retry_after", function () {
    var jsonData = pm.response.json();
    pm.expect(pm.response.code).to.equal(429);
    pm.expect(jsonData.error.code).to.equal("TOO_MANY_REQUESTS");
    pm.expect(jsonData.error.details).to.have.property("retry_after");
    pm.expect(jsonData.error.details).to.have.property("remaining");
});
```

---

## 5. Testing Workflows

### Workflow 1: Complete Authentication Flow

#### Step 1: Register New User
```
Request: POST {{base_url}}/auth/register
Headers: Content-Type: application/json

Body:
{
  "email": "testuser_{{$timestamp}}@example.com",
  "password": "TestPass123!",
  "password_confirmation": "TestPass123!",
  "name": "Test User",
  "role": "pasien"
}

Expected: 201 Created
Auto-save token to {{token}}
```

**Tests**:
```javascript
pm.test("Status code is 201", function () {
    pm.expect(pm.response.code).to.equal(201);
});

pm.test("Response has token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property("token");
    pm.environment.set("token", jsonData.data.token);
});

pm.test("User data is correct", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.user.role).to.equal("pasien");
    pm.expect(jsonData.data.user.name).to.equal("Test User");
});
```

#### Step 2: Get Current User
```
Request: GET {{base_url}}/auth/me
Headers: Authorization: Bearer {{token}}

Expected: 200 OK
```

**Tests**:
```javascript
pm.test("Current user matches registered user", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.role).to.equal("pasien");
});
```

#### Step 3: Refresh Token
```
Request: POST {{base_url}}/auth/refresh
Headers: Authorization: Bearer {{token}}

Expected: 200 OK (new token returned)
```

**Tests**:
```javascript
pm.test("Token refreshed successfully", function () {
    var jsonData = pm.response.json();
    var newToken = jsonData.data.token;
    
    // Token should be different
    pm.expect(newToken).to.not.equal(pm.environment.get("token"));
    
    // Save new token
    pm.environment.set("token", newToken);
});
```

#### Step 4: Logout
```
Request: POST {{base_url}}/auth/logout
Headers: Authorization: Bearer {{token}}

Expected: 200 OK
```

**Tests**:
```javascript
pm.test("Logout successful", function () {
    pm.expect(pm.response.code).to.equal(200);
    pm.environment.set("token", "");
});

pm.test("Token invalidated after logout", function () {
    var token = pm.environment.get("token");
    pm.expect(token).to.equal("");
});
```

#### Step 5: Verify Token Invalidated
```
Request: GET {{base_url}}/auth/me
Expected: 401 Unauthorized
```

**Tests**:
```javascript
pm.test("Cannot access protected endpoint without token", function () {
    pm.expect(pm.response.code).to.equal(401);
    var jsonData = pm.response.json();
    pm.expect(jsonData.error.code).to.equal("UNAUTHORIZED");
});
```

### Workflow 2: Error Handling - Login Attempts

#### Setup
Create a folder **"Error Scenarios"** with sub-folder **"Login Errors"**

#### Test 1: Correct Login (Success)
```
POST {{base_url}}/auth/login

{
  "email": "user@example.com",
  "password": "TestPass123!"
}

Expected: 200
Response: Token returned
```

#### Test 2: Wrong Password - Attempt 1
```
POST {{base_url}}/auth/login

{
  "email": "user@example.com",
  "password": "WrongPassword"
}

Expected: 401
Response: remaining_attempts = 4
```

**Tests**:
```javascript
pm.test("Wrong password returns 401", function () {
    pm.expect(pm.response.code).to.equal(401);
});

pm.test("Shows remaining attempts", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.error.details).to.have.property("remaining_attempts");
    console.log("Remaining attempts: " + jsonData.error.details.remaining_attempts);
});
```

#### Test 3: Wrong Password - Attempts 2-5
Repeat Test 2, observe remaining_attempts count down to 1

#### Test 4: Wrong Password - Attempt 6 (Rate Limited)
```
POST {{base_url}}/auth/login

{
  "email": "user@example.com",
  "password": "WrongPassword"
}

Expected: 429 Too Many Requests
Response: retry_after = 900, remaining = 0
```

**Tests**:
```javascript
pm.test("Rate limit returns 429", function () {
    pm.expect(pm.response.code).to.equal(429);
});

pm.test("Rate limit has retry_after", function () {
    var jsonData = pm.response.json();
    var retryAfter = jsonData.error.details.retry_after;
    pm.expect(retryAfter).to.equal(900);
    console.log("Wait " + retryAfter + " seconds before retry");
});
```

### Workflow 3: Validation Errors

#### Test: Invalid Registration Data
```
POST {{base_url}}/auth/register

{
  "email": "invalid-email",
  "password": "short",
  "password_confirmation": "different",
  "name": "",
  "role": "superadmin"
}

Expected: 422 Unprocessable Entity
```

**Tests**:
```javascript
pm.test("Returns validation error for all fields", function () {
    var jsonData = pm.response.json();
    var errors = jsonData.error.details.validation_errors;
    
    pm.expect(errors).to.have.property("email");
    pm.expect(errors).to.have.property("password");
    pm.expect(errors).to.have.property("password_confirmation");
    pm.expect(errors).to.have.property("name");
    pm.expect(errors).to.have.property("role");
});

pm.test("Email error message is present", function () {
    var jsonData = pm.response.json();
    var emailErrors = jsonData.error.details.validation_errors.email;
    
    pm.expect(emailErrors).to.be.an("array").to.have.lengthOf.at.least(1);
    console.log("Email errors: " + JSON.stringify(emailErrors));
});
```

---

## 6. Runner Configuration

### Setup Test Runner

1. Click **Collections** (left sidebar)
2. Select **Telemedicine API**
3. Click **Run** button (or right-click → Run collection)
4. Configure:

```
Collection: Telemedicine API
Environment: Telemedicine Local
Iterations: 1
Delay: 100ms (between requests)
Data: (none)
```

### Create Test Suite

Create folders for organized testing:

```
Telemedicine API (Collection)
├── Authentication
│   ├── Register
│   ├── Login
│   ├── Get Me
│   ├── Refresh Token
│   └── Logout
├── Error Scenarios
│   ├── 400 Bad Request
│   ├── 401 Unauthorized
│   ├── 403 Forbidden
│   ├── 422 Validation
│   └── 429 Rate Limited
├── Rate Limiting
│   ├── Login Rate Limit
│   ├── Register Rate Limit
│   └── API Rate Limit
└── Success Cases
    ├── Complete Flow
    ├── Profile Updates
    └── Token Refresh
```

---

## 7. Manual Testing Checklist

### Authentication
- [ ] Register dengan email baru → 201
- [ ] Register dengan email yang sudah ada → 422
- [ ] Register dengan password pendek → 422
- [ ] Register dengan password tidak match → 422
- [ ] Login dengan credentials benar → 200
- [ ] Login dengan password salah → 401
- [ ] Login 5 kali password salah → rate limited 429
- [ ] Get /auth/me dengan token valid → 200
- [ ] Get /auth/me tanpa token → 401
- [ ] Refresh token dengan token valid → 200 (new token)
- [ ] Logout dengan token valid → 200
- [ ] Get /auth/me setelah logout → 401

### Error Responses
- [ ] 400 error punya code dan message
- [ ] 401 error punya remaining_attempts
- [ ] 403 error punya error_code
- [ ] 404 error punya resource_type
- [ ] 422 error punya validation_errors object
- [ ] 429 error punya retry_after dan remaining
- [ ] 500 error punya request_id

### Response Structure
- [ ] Semua success response punya success: true
- [ ] Semua error response punya success: false
- [ ] Semua success response punya data field
- [ ] Semua error response punya error field
- [ ] Error field punya code, message, details

---

## 8. Example Test Data

### Test Accounts

```
Role: Pasien
Email: pasien@example.com
Password: TestPass123!

Role: Dokter
Email: dokter@example.com
Password: TestPass123!

Role: Admin
Email: admin@example.com
Password: TestPass123!
```

### Dynamic Email Generation

In Postman, use `$timestamp` variable:

```json
{
  "email": "user_{{$timestamp}}@example.com",
  "password": "TestPass123!"
}
```

This creates unique emails: `user_1705329000@example.com`

---

## 9. Monitoring & Logs

### View Request/Response

1. After each request, click **Response** tab
2. Options:
   - **Pretty**: Formatted JSON
   - **Raw**: Raw text
   - **Preview**: Rendered HTML
   - **Visualize**: Custom visualization

### View Console Logs

1. Click **View** (top menu)
2. Select **Show Postman Console**
3. View all console.log() outputs from scripts

### Export Results

1. Run collection with **Runner**
2. After completion, click **Export Results**
3. Choose format (JSON, CSV)
4. Save untuk reporting

---

## 10. Troubleshooting

### Token Not Being Saved

**Problem**: `{{token}}` empty after login

**Solution**:
```javascript
// Check if response has token
pm.test("Debug - Check response structure", function () {
    console.log(JSON.stringify(pm.response.json(), null, 2));
});

// Manually save in Tests script
const data = pm.response.json();
if (data.data && data.data.token) {
    pm.environment.set("token", data.data.token);
    console.log("Token saved successfully");
} else {
    console.error("Token not found in response");
    console.error(JSON.stringify(data, null, 2));
}
```

### Authorization Header Not Being Added

**Problem**: 401 error pada protected endpoints

**Solution**: Check Authorization header
```
Headers should show:
Authorization: Bearer {actual_token}

If showing "Bearer {{token}}", variable not replaced
```

**Fix**: 
1. Ensure variable name is correct
2. Use syntax: `Bearer {{token}}` (with space)
3. Check environment is selected (top-right)
4. Refresh environment (F5)

### Rate Limit Testing Issues

**Problem**: Can't test rate limiting because limit reset

**Solution**: 
- Use different email addresses
- Use different IP addresses (if testing production)
- Reset rate limiting in database:
  ```sql
  DELETE FROM rate_limits WHERE key LIKE 'login:%';
  ```

### Response 500 Errors

**Problem**: Always getting 500 errors

**Solution**: Check logs
1. SSH to server: `ssh user@server`
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Look for error details
4. Copy `request_id` dari response
5. Search logs for request_id

---

## 11. Performance Testing

### Load Testing Setup

1. In **Runner**, set:
   - **Iterations**: 10 (or more)
   - **Delay**: 100ms (atau sesuai kebutuhan)

2. Run against endpoint:
   ```
   GET {{base_url}}/auth/me
   ```

3. Monitor response times
4. Check for consistent performance
5. Watch for rate limiting kicking in

---

## 12. API Documentation Links

- **Swagger UI**: `http://localhost:8000/api/docs`
- **OpenAPI JSON**: `http://localhost:8000/api/docs/openapi.json`
- **ReDoc**: `http://localhost:8000/api/docs/redoc`

---

**Last Updated**: 2024-01-15  
**API Version**: 1.0.0  
**Postman Version**: v10.0+
