<!-- INTEGRATION TESTING GUIDE FOR TELEMEDICINE APPLICATION -->
<!-- This file documents all integration tests and manual testing procedures -->

# Integration Testing Guide

## 1. API Endpoints Testing (via Postman)

### Authentication Endpoints
- [ ] POST `/api/login` - User login
- [ ] POST `/api/logout` - User logout  
- [ ] POST `/api/register` - User registration
- [ ] POST `/api/refresh-token` - Token refresh

### Doctor Verification Endpoints
- [ ] POST `/api/doctor/verification/upload` - Upload verification document
- [ ] GET `/api/doctor/verification/documents` - List doctor's documents
- [ ] GET `/api/doctor/verification/status` - Get verification status
- [ ] GET `/api/admin/verification/pending` - Admin list pending documents
- [ ] POST `/api/doctor-verification-documents/{id}/approve` - Approve document
- [ ] POST `/api/doctor-verification-documents/{id}/reject` - Reject document

### Analytics Endpoints
- [ ] GET `/api/analytics/admin-dashboard` - Admin analytics
- [ ] GET `/api/analytics/doctor-dashboard` - Doctor analytics
- [ ] GET `/api/analytics/stats` - Statistics

### Consultation Endpoints
- [ ] POST `/api/consultations` - Create consultation
- [ ] GET `/api/consultations` - List consultations
- [ ] GET `/api/consultations/{id}` - Get consultation details
- [ ] PUT `/api/consultations/{id}` - Update consultation
- [ ] DELETE `/api/consultations/{id}` - Delete consultation

### Documentation
- [ ] GET `/api/docs` - Swagger UI
- [ ] GET `/api/docs/openapi.json` - OpenAPI specification

## 2. Frontend Component Integration Tests

### LoadingSpinner Component
- [ ] Test appears when isLoading = true
- [ ] Test hides when isLoading = false
- [ ] Test displays custom message
- [ ] Test different spinner types (default, dots, bars, pulse)
- [ ] Test overlay mode

Test file: `RiwayatKonsultasiPage.vue`
```vue
<LoadingSpinner 
  :isLoading="isLoading" 
  message="Loading consultations..."
  type="default"
  :overlay="true"
/>
```

### ErrorMessage Component
- [ ] Test displays error when errorMessage is set
- [ ] Test closes on button click
- [ ] Test displays different error levels
- [ ] Test auto-dismiss after timeout

Test file: `ManagePasienPage.vue`
```vue
<ErrorMessage 
  v-if="errorMessage" 
  :message="errorMessage"
  @close="errorMessage = null"
/>
```

### NotificationSystem Component
- [ ] Test displays success notification
- [ ] Test displays error notification
- [ ] Test displays warning notification
- [ ] Test auto-dismiss
- [ ] Test manual close

### Composables Integration
- [ ] useLoading() in data fetching
- [ ] useError() in error handling
- [ ] useAsync() in API calls
- [ ] useForm() in form submission
- [ ] useNotification() in user feedback

## 3. End-to-End Test Flows

### Doctor Verification Flow
1. [ ] Doctor navigates to profile
2. [ ] Doctor uploads SIP document
3. [ ] Upload progress shows spinner
4. [ ] Success notification displayed
5. [ ] Document appears in pending list
6. [ ] Admin navigates to verification page
7. [ ] Admin sees pending document
8. [ ] Admin reviews and approves
9. [ ] Doctor sees status updated to approved
10. [ ] Notification sent to doctor

Steps:
```
1. Login as Dokter
2. Go to Profile > Verification Documents
3. Click "Upload Document"
4. Select PDF file
5. Submit form
6. Verify spinner appears
7. Check success message
8. Logout, login as Admin
9. Go to Admin > Doctor Verification
10. Click "Approve" on document
11. Verify status changes
12. Login back as Dokter
13. Check updated status
```

### Analytics Dashboard Flow
1. [ ] Admin logs in
2. [ ] Navigates to Analytics Dashboard
3. [ ] Dashboard loads with spinner
4. [ ] Charts render with data
5. [ ] Statistics display correctly
6. [ ] Filter by date range works
7. [ ] Export function works (if applicable)
8. [ ] Doctor views their analytics
9. [ ] Doctor sees only their data
10. [ ] Refresh shows updated data

### Offline Mode Flow
1. [ ] User loads page online
2. [ ] Data cached locally
3. [ ] User goes offline
4. [ ] Cached data displayed
5. [ ] Offline indicator shows
6. [ ] User performs action
7. [ ] Action queued locally
8. [ ] User comes back online
9. [ ] Sync queue processes
10. [ ] Changes sync to server
11. [ ] Confirmation shown

## 4. Performance Testing

### Load Time Benchmarks
- [ ] Admin Dashboard: < 500ms
- [ ] Doctor Dashboard: < 300ms
- [ ] Analytics Page: < 800ms
- [ ] Consultation List: < 400ms

### Database Query Count
- [ ] Admin Dashboard: < 10 queries
- [ ] Doctor Dashboard: < 8 queries
- [ ] Analytics Page: < 12 queries

### Memory Usage
- [ ] Frontend JS bundle: < 500KB (gzipped)
- [ ] Initial page load: < 2MB
- [ ] API response: < 1MB

## 5. Security Testing

### Authentication
- [ ] Unauthenticated users cannot access protected routes
- [ ] Token expiration works correctly
- [ ] Refresh token mechanism works
- [ ] Logout clears session

### Authorization
- [ ] Admin can access admin routes
- [ ] Doctor can only access their data
- [ ] Patient can only access their data
- [ ] Role-based route protection works

### Input Validation
- [ ] File upload rejects invalid types
- [ ] File size limits enforced
- [ ] API request validation works
- [ ] CORS policy enforced

### Data Protection
- [ ] Sensitive data not exposed in responses
- [ ] Passwords properly hashed
- [ ] API keys secured
- [ ] HTTPS enforced in production

## 6. Browser Compatibility Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

## 7. Regression Testing Checklist

After each feature deployment:
- [ ] All existing routes still work
- [ ] No 500 errors in console
- [ ] No broken links
- [ ] All images load
- [ ] All forms submit correctly
- [ ] Navigation works
- [ ] Responsive design intact
- [ ] No layout shifts
- [ ] Accessibility maintained

## 8. Test Execution Commands

```bash
# Run all PHP tests
php artisan test

# Run specific test file
php artisan test tests/Feature/DoctorVerificationTest.php

# Run with coverage
php artisan test --coverage

# Run Vue component tests (after setup)
npm run test

# Run e2e tests (with Cypress)
npm run test:e2e

# Performance profiling
php artisan tinker
// Then run queries from AnalyticsOptimizationGuide
```

## 9. Manual Testing Procedures

### Test 1: Doctor Upload Verification Document
```
1. Open http://localhost:5173
2. Login as doctor (spesialisasi dokter)
3. Go to Profile page
4. Click "Upload Verification Document"
5. Select a PDF file (< 10MB)
6. Choose document type (SIP/Izin/Sertifikat)
7. Click Submit
8. Verify: Loading spinner appears
9. Verify: Success message shows
10. Verify: Document appears in "My Documents"
```

### Test 2: Admin Review & Approve Document
```
1. Open http://localhost:5173
2. Login as admin
3. Go to Admin > Doctor Verification
4. Verify: Pending documents listed
5. Click "View Document" to preview
6. Click "Approve" button
7. Verify: Status changes to "Approved"
8. Verify: Verified timestamp shows
9. Click back to dokter, verify status updated
```

### Test 3: Admin Analytics Dashboard
```
1. Login as admin
2. Go to Admin > Analytics Dashboard
3. Verify: Charts load correctly
4. Verify: KPI cards show metrics
5. Verify: Doctor list shows correct counts
6. Change date range filters
7. Verify: Charts update
8. Verify: No console errors
9. Check browser DevTools for slow requests
```

### Test 4: Offline Functionality
```
1. Load application online
2. Open DevTools > Network
3. Throttle to offline
4. Try to load consultation list
5. Verify: Cached data displays
6. Try to send message
7. Verify: Message queued locally
8. Go back online
9. Verify: Queue processes
10. Verify: Message synced
```

## 10. Known Issues & Workarounds

### Issue: LoadingSpinner not displaying
- Workaround: Check CSS scoped styles
- Solution: Ensure z-index properly set

### Issue: API timeout on large analytics
- Workaround: Reduce date range
- Solution: Implement pagination/caching

## 11. Test Report Template

```
Date: _______________
Tester: _______________
Environment: [dev/staging/production]

PASSED: ___ / ___ tests

FAILED TESTS:
1. _______________
   Issue: _______________
   Severity: [High/Medium/Low]
   
RECOMMENDATIONS:
_______________
_______________

Signed: _______________
```

## 12. Continuous Integration Setup

For automated testing in CI/CD:

```yaml
# .github/workflows/test.yml
name: Run Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test
```

## Next Steps

1. ✅ Create test files
2. ✅ Document test procedures
3. 📋 Execute manual tests
4. 📋 Run automated tests
5. 📋 Performance profiling
6. 📋 Security audit
7. 📋 Deploy to staging
8. 📋 Production deployment
