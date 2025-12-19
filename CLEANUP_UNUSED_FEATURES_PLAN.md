# 🧹 CLEANUP PLAN - REMOVE UNUSED FEATURES

**Target:** Hapus Email Notifications, SMS, Payment, Video Consultation
**Status:** Planning phase

---

## 📋 FILES TO DELETE

### Email Notification Service
```
✓ app/Services/EmailNotificationService.php
```

### Mail Templates
```
✓ app/Mail/AppointmentReminderMail.php
✓ app/Mail/AppointmentConfirmedMail.php
✓ app/Mail/AppointmentCancelledMail.php
✓ app/Mail/ConsultationEndedMail.php
✓ app/Mail/AppointmentBookedMail.php
✓ app/Mail/AnnouncementMail.php
✓ app/Mail/RatingReceivedMail.php
✓ app/Mail/TestEmail.php
✓ app/Mail/PrescriptionCreatedMail.php
✓ app/Mail/PasswordResetMail.php
✓ app/Mail/GenericNotificationMail.php
✓ app/Mail/DoctorRejectedMail.php
✓ app/Mail/DoctorApprovedMail.php
✓ app/Mail/ConsultationStartedMail.php
✓ app/Mail/VerifyEmailMail.php
✓ app/Mail/WelcomeEmail.php
```

### Email Views
```
✓ resources/views/emails/*.blade.php (all email templates)
```

### Code References to Clean

#### Models - Remove fields:
- Konsultasi: payment_status, payment_method
- Appointment: payment_status
- User: video_consultation_enabled

#### Controllers - Remove references:
- AppointmentController: 'video_call' type validation
- AnalyticsService: payment_status checks
- AdminDashboardService: payment references

#### Config - Remove:
- Mailgun configuration references
- Email queue configuration
- Payment-related settings

#### Routes - Remove:
- Email test routes
- Payment routes (if any)
- Video call routes (if any)

#### Jobs - Remove:
- Email send jobs (if any)
- Payment processing jobs (if any)

---

## ✅ CODE CHANGES NEEDED

### 1. Models - Konsultasi
Remove fields from migration:
- `payment_status`
- `payment_method`
- Add to fillable: only keep essential

### 2. Models - Appointment
Remove fields:
- `payment_status`

### 3. Models - User
Remove fields:
- `video_consultation_enabled`

### 4. Services - AnalyticsService
Remove payment-related calculations:
- `payment_status` filters
- `paidRevenue`, `pendingRevenue`
- `payment_completion_rate`

### 5. Services - AdminDashboardService
Remove payment references

### 6. Controllers - AppointmentController
Remove video_call validation from type field

### 7. Middleware - AddSecurityHeaders
Remove 'payment' from CSP header

### 8. Database Cleanup
- Check migrations for unused columns
- Clean up if needed

---

## 📊 IMPACT ANALYSIS

### What will be removed:
- 16 Mail classes
- Email views folder
- EmailNotificationService
- Payment-related code snippets
- Video consultation references
- SMS configuration (if any)

### What will stay:
- Core chat system ✅
- Consultation booking ✅
- Doctor management ✅
- Rating system ✅
- User authentication ✅
- File upload ✅
- All essential features ✅

---

## 🚀 EXECUTION PLAN

1. Delete Mail classes (16 files)
2. Delete email views folder
3. Delete EmailNotificationService
4. Update Models (remove fields)
5. Update Services (remove payment logic)
6. Update Controllers (remove validation)
7. Update Middleware (remove CSP)
8. Verify no broken imports
9. Test app still works
10. Commit & push

---

**Status:** Ready to execute
**Risk Level:** LOW (only removing unused features)
**Estimated Time:** 30-45 minutes
