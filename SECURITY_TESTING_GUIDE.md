# Security Hardening - Testing & Verification Guide

**Purpose**: Validate all security implementations work correctly  
**Duration**: 2-3 hours for full testing  
**Tools**: Postman, curl, online security header checkers

---

## 🧪 Test 1: File Upload Validation

### 1.1 Test Valid Uploads (Should Accept)

```bash
# Test 1: Valid JPEG
curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/photo.jpg"

Expected Response:
{
  "success": true,
  "message": "File uploaded successfully",
  "url": "storage/uploads/photo.jpg"
}

# Test 2: Valid PDF
curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/document.pdf"

Expected: 200 OK

# Test 3: Valid DOCX
curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/resume.docx"

Expected: 200 OK
```

### 1.2 Test Invalid Uploads (Should Reject)

```bash
# Test 1: Executable file (.exe)
curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/malware.exe"

Expected Response:
{
  "success": false,
  "message": "File validation failed: Extension not allowed"
}
Status: 422 Unprocessable Entity

# Test 2: PHP webshell
curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/shell.php"

Expected: 422 - Extension not allowed

# Test 3: Double extension (.jpg.exe)
# Create test file: 
echo "MZ" > test.jpg.exe  # MZ is .exe magic number

curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test.jpg.exe"

Expected: 422 - File type validation failed

# Test 4: File size exceeds 5MB
curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/large_file.iso"

Expected: 422 - File size exceeds 5MB limit
```

### 1.3 Magic Number Verification Test

```bash
# Create a fake JPEG with .exe magic number
# This is most dangerous attack - should be blocked

# On Linux/Mac:
printf '\xFF\xD8\xFF\xE0' > fake_image.jpg

# This file:
# - Has .jpg extension
# - Has JPEG magic number (FF D8 FF E0)
# - But we'll replace with .exe magic number to test

printf 'MZ\x90\x00' > fake.jpg  # .exe magic (MZ)

curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@fake.jpg"

Expected: 422 - Magic number doesn't match extension
```

### 1.4 Logging Verification

Check that all upload attempts are logged:

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

Expected log entries:
[2025-12-15 10:30:45] local.INFO: File upload validation passed: 
  user_id=1, filename=photo.jpg, size=204800, mime=image/jpeg

[2025-12-15 10:31:20] local.WARNING: File upload validation failed: 
  user_id=1, filename=malware.exe, reason=Extension not allowed
```

---

## 🌐 Test 2: CORS Configuration

### 2.1 Test Allowed Origins (Should Accept)

```bash
# Development origin (should work)
curl -X OPTIONS http://localhost:8000/api/appointments \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET"

Expected Response Headers:
✓ Access-Control-Allow-Origin: http://localhost:3000
✓ Access-Control-Allow-Credentials: true
✓ Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS

# Another development origin
curl -X OPTIONS http://localhost:8000/api/appointments \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: POST"

Expected: 200 OK with CORS headers
```

### 2.2 Test Blocked Origins (Should Reject)

```bash
# Malicious origin (should fail)
curl -X OPTIONS http://localhost:8000/api/appointments \
  -H "Origin: http://evil.com" \
  -H "Access-Control-Request-Method: GET"

Expected Response:
⚠️ No Access-Control-Allow-Origin header
⚠️ CORS request blocked by browser

# Another malicious origin
curl -X OPTIONS http://localhost:8000/api/appointments \
  -H "Origin: https://hacker.io" \
  -H "Access-Control-Request-Method: POST"

Expected: No CORS headers in response
```

### 2.3 Wildcard Origin Test (Must Fail)

```bash
# Test wildcard origin is blocked
curl -X OPTIONS http://localhost:8000/api/appointments \
  -H "Origin: *" \
  -H "Access-Control-Request-Method: GET"

Expected: ❌ No CORS headers
(Wildcard origin is not supported, must use specific origins)
```

### 2.4 Postman CORS Test

```javascript
// In Postman, create a test for CORS:

1. Go to: GET http://localhost:8000/api/appointments
2. Add Headers:
   Origin: http://localhost:3000

3. Go to Tests tab, add:
   pm.test("CORS headers present", function () {
       pm.response.to.have.header("Access-Control-Allow-Origin", "http://localhost:3000");
       pm.response.to.have.header("Access-Control-Allow-Credentials", "true");
   });

4. Send request

Expected: ✅ CORS headers present test passes
```

---

## 🔐 Test 3: Security Headers Verification

### 3.1 Check All Security Headers

```bash
# Get response headers from API endpoint
curl -I https://api.telemedicine.com/health

# Or for local development:
curl -I http://localhost:8000/api/health

Expected Headers (all must be present):
✓ Content-Security-Policy: default-src 'self'...
✓ X-Content-Type-Options: nosniff
✓ X-Frame-Options: DENY
✓ X-XSS-Protection: 1; mode=block
✓ Referrer-Policy: strict-origin-when-cross-origin
✓ Permissions-Policy: geolocation=(), camera=(), microphone=()...
✓ Strict-Transport-Security: max-age=31536000... (production only)

Missing Headers (should NOT be present):
❌ X-Powered-By (should be removed)
❌ Server (should be removed)
```

### 3.2 Online Security Header Checker

Visit: https://securityheaders.com

1. Enter: `http://localhost:8000` (or your domain)
2. Click "Scan"
3. Should show:
   - ✅ Content-Security-Policy: A or B
   - ✅ X-Content-Type-Options: A or B
   - ✅ X-Frame-Options: A or B
   - ✅ X-XSS-Protection: (deprecated but good to have)
   - ✅ Referrer-Policy: A or B
   - ✅ Permissions-Policy: A or B

Grade should be A or A+

### 3.3 CSP Validation

```bash
# Test CSP is working (blocks inline scripts)

# Create a test page with inline script:
# <script>alert('This should be blocked')</script>

# If CSP is working:
✓ Script blocked in browser console
✓ CSP violation report in console:
  "Content Security Policy: The page's settings blocked the loading 
   of a resource at inline..."

# If CSP not working:
❌ Script executes (security issue!)
```

### 3.4 X-Frame-Options Test

```bash
# Test that page cannot be embedded in iframe

# Create test HTML:
<!DOCTYPE html>
<html>
<head><title>Iframe Test</title></head>
<body>
  <iframe src="http://localhost:8000/api/health" width="100%" height="600"></iframe>
</body>
</html>

# Open in browser:
✓ If X-Frame-Options: DENY is working:
  Iframe shows blank/blocked message

❌ If not working:
  Iframe shows API response (security issue!)
```

### 3.5 SSL Labs Test (Production Only)

Visit: https://www.ssllabs.com/ssltest/

1. Enter: `https://telemedicine.com`
2. Wait for scan to complete
3. Check:
   - Overall Rating: A or A+ ✓
   - Protocol Support: TLS 1.2+ ✓
   - Key Exchange: 2048-bit RSA or better ✓
   - Cipher Strength: 128-bit or better ✓

---

## ✅ Test 4: Input Sanitization (XSS Prevention)

### 4.1 Test Text Sanitization

```bash
# Test 1: Script injection in name field
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<script>alert(\"XSS\")</script>",
    "email": "test@example.com",
    "password": "password123"
  }'

Expected in Database:
✓ name stored as: "&lt;script&gt;alert(\"XSS\")&lt;/script&gt;"
✓ When displayed: Shows as text, not executed

# Test 2: Event handler injection
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<img src=x onerror=alert(\"XSS\")>",
    "email": "test@example.com",
    "password": "password123"
  }'

Expected:
✓ Stored as: "&lt;img src=x onerror=alert(\"XSS\")&gt;"
✓ Not executed
```

### 4.2 Test HTML Sanitization (Allowed Tags)

```bash
# Test 1: Safe HTML tags (should keep)
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "message": "<p>Hello <b>friend</b>, this is <em>important</em></p>",
    "to_user_id": 2
  }'

Expected in Database:
✓ message stored as: "<p>Hello <b>friend</b>, this is <em>important</em></p>"
✓ Safe HTML tags preserved

# Test 2: Dangerous tags (should remove)
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "message": "<p>Click <a href=\"javascript:alert(1)\">here</a></p>",
    "to_user_id": 2
  }'

Expected:
✓ javascript: URL blocked
✓ <a> tag removed or href sanitized
```

### 4.3 Test URL Sanitization

```bash
# Test 1: JavaScript protocol (should block)
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "message": "Check this: javascript:void(0)",
    "attachment_url": "javascript:alert(1)"
  }'

Expected:
✓ attachment_url becomes empty string or blocked
✓ No JavaScript executed

# Test 2: Data protocol (should block)
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "attachment_url": "data:text/html,<script>alert(1)</script>"
  }'

Expected:
✓ Data URI protocol blocked
✓ URL becomes empty or error returned

# Test 3: Valid URLs (should accept)
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "attachment_url": "https://example.com/file.pdf"
  }'

Expected:
✓ URL stored as-is (valid)
✓ Can be accessed
```

### 4.4 Test Email Sanitization

```bash
# Test 1: Valid email (should accept)
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'

Expected:
✓ Email stored as: john@example.com
✓ User created successfully

# Test 2: Email with special chars (should sanitize)
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john+test<script>@example.com",
    "password": "password123"
  }'

Expected:
✓ Email sanitized to: john+test@example.com
✓ Invalid chars removed
```

### 4.5 Test Number Sanitization

```bash
# Test 1: Numbers with special chars
curl -X POST http://localhost:8000/api/appointments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "doctor_id": "123abc456",
    "patient_id": "789<script>"
  }'

Expected:
✓ doctor_id becomes: 123456
✓ patient_id becomes: 789
✓ Non-numeric chars removed
```

---

## 🛡️ Test 5: FormRequest Integration Tests

### 5.1 Test RegisterRequest Sanitization

```bash
# All fields should be sanitized
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John<b>Doe</b>",
    "email": "john@example.com",
    "password": "password123",
    "bio": "I am a<script>alert(1)</script> developer",
    "specialization": "Cardiology<iframe>",
    "nik": "32050123456789abc"
  }'

Expected:
✓ name: "John&lt;b&gt;Doe&lt;/b&gt;"
✓ bio: Dangerous tags removed
✓ nik: "32050123456789"
✓ No validation errors
```

### 5.2 Test AppointmentRequest Sanitization

```bash
# Reason and notes should be sanitized
curl -X POST http://localhost:8000/api/appointments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "doctor_id": 1,
    "appointment_date": "2025-12-20 10:00:00",
    "reason": "Chest pain<img src=x onerror=alert(1)>",
    "notes": "JavaScript:alert(1) prescription"
  }'

Expected:
✓ reason: Dangerous content removed
✓ notes: Escaped/sanitized
✓ Appointment created successfully
```

### 5.3 Test MessageRequest Sanitization

```bash
# Message should allow HTML, URL should be validated
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "to_user_id": 2,
    "message": "<b>Important:</b> Check the attachment",
    "attachment_url": "https://example.com/docs.pdf"
  }'

Expected:
✓ message: "<b>Important:</b> Check the attachment"
✓ attachment_url: Valid HTTPS URL
✓ Message sent successfully

# Test dangerous URL
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "to_user_id": 2,
    "message": "Click here",
    "attachment_url": "javascript:void(alert(1))"
  }'

Expected:
✓ attachment_url blocked or empty
✓ Error response or sanitized
```

### 5.4 Test PrescriptionRequest Sanitization

```bash
# Complex array sanitization
curl -X POST http://localhost:8000/api/prescriptions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "appointment_id": 1,
    "medicines": [
      {
        "name": "Aspirin<script>",
        "dosage": "500mg",
        "frequency": "3x daily"
      },
      {
        "name": "Paracetamol",
        "dosage": "1000mg<iframe>",
        "frequency": "2x daily"
      }
    ],
    "notes": "Take with food<img src=x onerror=alert(1)>"
  }'

Expected:
✓ All medicine names sanitized
✓ All dosages sanitized
✓ All notes sanitized
✓ Dangerous content removed
```

---

## 📋 Test Execution Checklist

### Phase 1: File Upload (30 minutes)
- [ ] Valid JPEG uploads
- [ ] Valid PDF uploads
- [ ] Valid DOCX uploads
- [ ] Reject .exe files
- [ ] Reject .php files
- [ ] Reject .exe with fake extension
- [ ] Reject oversized files (>5MB)
- [ ] Check upload logs
- [ ] Verify magic number check works

### Phase 2: CORS (20 minutes)
- [ ] Allow localhost:3000
- [ ] Allow localhost:5173
- [ ] Block evil.com
- [ ] Block hacker.io
- [ ] Verify CORS headers present for allowed origins
- [ ] Verify CORS headers absent for blocked origins
- [ ] Test wildcard origin blocked

### Phase 3: Security Headers (20 minutes)
- [ ] CSP header present
- [ ] X-Content-Type-Options present
- [ ] X-Frame-Options: DENY
- [ ] X-XSS-Protection present
- [ ] Referrer-Policy present
- [ ] Permissions-Policy present
- [ ] HSTS present (production)
- [ ] No X-Powered-By header
- [ ] No Server header
- [ ] Test with securityheaders.com

### Phase 4: Input Sanitization (40 minutes)
- [ ] Script tags in name field
- [ ] Event handlers removed
- [ ] Safe HTML tags preserved
- [ ] Dangerous HTML tags removed
- [ ] JavaScript URLs blocked
- [ ] Data URIs blocked
- [ ] Valid URLs accepted
- [ ] Emails sanitized
- [ ] Numbers extracted from strings

### Phase 5: FormRequest Integration (30 minutes)
- [ ] RegisterRequest sanitization
- [ ] AppointmentRequest sanitization
- [ ] MessageRequest sanitization
- [ ] PrescriptionRequest sanitization
- [ ] RatingRequest sanitization
- [ ] UpdateProfileRequest sanitization

**Total Time**: 2-3 hours

---

## 📊 Test Results Template

Use this template to document test results:

```
TEST: File Upload Validation
============================
Date: 2025-12-15
Tester: [Name]

Valid Files:
✓ JPEG: PASS
✓ PDF: PASS
✓ DOCX: PASS

Invalid Files:
✓ .exe: BLOCKED ✓
✓ .php: BLOCKED ✓
✓ .jpg.exe: BLOCKED ✓
✓ >5MB: BLOCKED ✓

Logging:
✓ Upload logged correctly ✓

Overall Result: PASS ✅
Issues Found: None
```

---

## 🚀 Automated Testing (Optional)

Create Postman collection with automated tests:

```json
{
  "info": {
    "name": "Security Tests",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "File Upload - Valid JPEG",
      "event": [
        {
          "listen": "test",
          "script": {
            "exec": [
              "pm.test('Status code is 200', function () {",
              "    pm.response.to.have.status(200);",
              "});",
              "pm.test('Upload successful', function () {",
              "    var jsonData = pm.response.json();",
              "    pm.expect(jsonData.success).to.eql(true);",
              "});"
            ]
          }
        }
      ],
      "request": {
        "method": "POST",
        "url": "{{base_url}}/api/upload",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ]
      }
    }
  ]
}
```

---

## ✅ Sign-off

Once all tests pass:

- [ ] File upload validation ✅
- [ ] CORS configuration ✅
- [ ] Security headers ✅
- [ ] Input sanitization ✅
- [ ] FormRequest integration ✅

**Status**: All security hardening implementations verified and working correctly.

**Date Completed**: _____________  
**Tester Name**: _____________  
**Issues Found**: None / [specify any issues]  
**Approved by**: _____________
