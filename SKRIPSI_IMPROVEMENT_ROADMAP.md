# 📚 ROADMAP PENINGKATAN APLIKASI UNTUK SKRIPSI

**Durasi:** 10-15 hari kerja | **Target Completion:** 95%+ aplikasi lengkap

---

## 🎯 FASE 1: FITUR CRITICAL (7-10 Hari)

### 1. VIDEO CONSULTATION (2-3 Hari)

**Status Saat Ini:**
- ✅ JitsiTokenService sudah ada
- ✅ VideoSessionController sudah ada
- ❌ Frontend component belum ada
- ❌ Real-time video flow belum lengkap

**Yang Perlu Dilakukan:**

**Backend Tasks:**
```php
// 1. Complete VideoSessionController
// File: app/Http/Controllers/Api/VideoSessionController.php
// Needs: start(), end(), getToken(), recordingToggle() methods

// 2. Video Recording Model & Migration
// NEW: app/Models/VideoRecording.php
// Create: database/migrations/create_video_recordings_table.php

// 3. Recording consent checking
// Add: app/Policies/VideoRecordingPolicy.php
```

**Database Schema:**
```sql
CREATE TABLE video_recordings (
    id BIGINT PRIMARY KEY,
    consultation_id BIGINT,
    doctor_id BIGINT,
    patient_id BIGINT,
    storage_path VARCHAR(255),
    duration INT, -- seconds
    file_size BIGINT, -- bytes
    is_deleted BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    deleted_at TIMESTAMP nullable,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    FOREIGN KEY (patient_id) REFERENCES users(id)
);

CREATE TABLE video_recording_consents (
    id BIGINT PRIMARY KEY,
    consultation_id BIGINT,
    patient_id BIGINT,
    consented_to_recording BOOLEAN,
    consent_given_at TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id),
    FOREIGN KEY (patient_id) REFERENCES users(id)
);
```

**Frontend Tasks:**
```vue
// NEW: resources/js/components/VideoConsultation/VideoCallModal.vue
// - Initialize Jitsi instance
// - Handle start/stop recording
// - Display duration & quality metrics
// - Error handling & reconnection

// NEW: resources/js/components/VideoConsultation/RecordingConsent.vue
// - Ask patient permission before recording
// - Save consent to database
// - Display clear warning

// MODIFY: resources/js/pages/Dashboard.vue
// - Add "Start Video Call" button when appointment active
// - Show video status badge
```

**Test Coverage (Target: 20+ test cases):**
```
✓ POST /api/video/start - Initiate video session
✓ GET /api/video/token - Get Jitsi token
✓ POST /api/video/recording/consent - Save recording consent
✓ POST /api/video/end - End session & save recording
✓ GET /api/video/recordings - List patient recordings
✓ DELETE /api/video/recordings/:id - Delete recording
✓ Video session persists through network interruption
✓ Recording consent required before recording starts
✓ Only doctor & patient can access their recordings
✓ Recording deleted when consultation deleted
```

**Documentation:**
- Video requirements (bandwidth: 2.5 Mbps, browser: Chrome/Firefox)
- Recording retention policy (30 days after consultation)
- GDPR/privacy compliance for recordings

**Estimated Effort:** 2-3 hari

---

### 2. DOCTOR AVAILABILITY & SCHEDULING (3-4 Hari)

**Status Saat Ini:**
- ❌ Completely missing
- ❌ No availability calendar

**Implementation Plan:**

**Database Schema:**
```sql
CREATE TABLE doctor_working_hours (
    id BIGINT PRIMARY KEY,
    doctor_id BIGINT,
    day_of_week INT, -- 0=Sunday to 6=Saturday
    start_time TIME,
    end_time TIME,
    is_available BOOLEAN DEFAULT true,
    break_start TIME nullable,
    break_end TIME nullable,
    created_at TIMESTAMP,
    UNIQUE KEY unique_day_per_doctor (doctor_id, day_of_week)
);

CREATE TABLE time_slots (
    id BIGINT PRIMARY KEY,
    doctor_id BIGINT,
    slot_date DATE,
    slot_time TIME,
    slot_duration INT DEFAULT 30, -- minutes
    is_booked BOOLEAN DEFAULT false,
    consultation_id BIGINT nullable,
    created_at TIMESTAMP,
    UNIQUE KEY unique_slot (doctor_id, slot_date, slot_time)
);

CREATE TABLE appointment_slots_available_view (
    -- This is an indexed view, not a table
    -- Query untuk select available slots
    -- Optimize: slot_date >= today AND is_booked = false AND doctor_id = ?
);
```

**Backend Controllers:**
```php
// NEW: app/Http/Controllers/Api/Doctor/AvailabilityController.php
// Methods:
// - getWorkingHours(doctor_id) - GET
// - setWorkingHours(doctor_id) - POST/PUT
// - generateTimeSlots(doctor_id, date_range) - POST (run daily/weekly)
// - getAvailableSlots(doctor_id, date) - GET

// MODIFY: app/Http/Controllers/Api/AppointmentController.php
// Add:
// - bookAppointment($doctorId, $slotTime)
// - getAvailableDoctorsAndSlots($specialization, $date)
// - getRescheduleOptions($appointmentId)
```

**Frontend Components:**
```vue
// NEW: resources/js/components/Doctor/SetAvailability.vue
// - Doctor interface untuk set working hours
// - Weekly view with time pickers
// - Save & publish

// NEW: resources/js/components/Patient/SelectAppointmentSlot.vue
// - Calendar view of doctor availability
// - Filter by date, show available slots
// - Click to book, confirm booking

// MODIFY: resources/js/pages/AppointmentBooking.vue
// - Integrate availability checking
// - Real-time slot availability
// - Show doctor photo & details
```

**Business Logic (Service):**
```php
// NEW: app/Services/AppointmentSlotService.php
class AppointmentSlotService {
    public function generateSlotsForDoctor($doctorId, $startDate, $endDate) {
        // 1. Fetch doctor's working hours
        // 2. Exclude breaks
        // 3. Create 30-minute intervals
        // 4. Exclude existing appointments
        // 5. Save to time_slots table
        // Performance: Batch insert, max 1000 slots per call
    }
    
    public function getAvailableSlots($doctorId, $date) {
        // Return only non-booked slots for that date
        // Cache for 5 minutes
    }
    
    public function blockSlot($slotId, $consultationId) {
        // Mark slot as booked
        // Lock to prevent race condition (use database transaction)
    }
}
```

**Test Cases (Target: 25+ test cases):**
```
✓ Doctor can set working hours
✓ Multiple working hours per week supported
✓ Break time excluded from slots
✓ System generates slots correctly (30-min intervals)
✓ Max 6 months ahead scheduling
✓ Patient sees only available slots
✓ Concurrent bookings prevented (race condition)
✓ Double-booked slot prevented
✓ Past dates not bookable
✓ Slot marked booked when appointment created
✓ Cancelled appointment frees slot
✓ Slot released after doctor deletes working hour
✓ Time zone handling correct
```

**Estimated Effort:** 3-4 hari

---

### 3. APPOINTMENT REMINDERS & NOTIFICATIONS (2-3 Hari)

**Status Saat Ini:**
- ⚠️ SMS infrastructure exists
- ❌ Scheduled reminders not implemented
- ❌ Multi-channel reminders (email, push) missing

**Implementation:**

**Jobs:**
```php
// Modify: app/Jobs/SendAppointmentReminderSMS.php
// Trigger: 1 day before & 1 hour before appointment

// NEW: app/Jobs/SendAppointmentReminderEmail.php
// Trigger: 1 day before appointment

// NEW: app/Jobs/SendAppointmentReminderPush.php
// Trigger: 1 hour before appointment (if app installed)

// NEW: app/Jobs/SendConsultationSurvey.php
// Trigger: 30 minutes after consultation ends
```

**Models & Database:**
```php
// MODIFY: Consultation model
// Add: reminder_sent_at, survey_sent_at

// NEW: ReminderPreference model
// - per user notification settings
// - which channels enabled/disabled
```

**Scheduled Tasks:**
```php
// app/Console/Kernel.php
// Schedule::command('appointment:send-reminders')
//     ->hourly()
//     ->withoutOverlapping();
```

**Frontend:**
```vue
// NEW: resources/js/components/ReminderPreferences.vue
// Allow patient/doctor to set:
// - Enable/disable SMS
// - Enable/disable Email
// - Enable/disable Push
// - Reminder timing (1 day, 1 hour, 15 minutes)
```

**Reminder Messages:**
```
SMS 1 Day Before:
"Appointment Anda besok pukul 14:00 dengan Dr. Ahmad Suryanto. 
Klik link untuk video call: [short-url]"

SMS 1 Hour Before:
"Appointment Anda dimulai dalam 1 jam. Siap untuk video call? 
Tap untuk join sekarang: [short-url]"

Email:
Subject: "Appointment Reminder - Tomorrow at 2:00 PM"
Body: HTML email dengan doctor details, appointment time, join link

Push Notification:
"Your appointment starts in 1 hour - Tap to join video call"
```

**Test Cases (Target: 15+ test cases):**
```
✓ SMS reminder sent 1 day before
✓ SMS reminder sent 1 hour before
✓ Email reminder sent 1 day before
✓ Push notification sent 1 hour before
✓ Patient can disable SMS reminders
✓ Patient can disable email reminders
✓ Reminders only for confirmed appointments
✓ No reminders for cancelled appointments
✓ No reminders for past appointments
✓ Reminder sent in correct timezone
✓ Doctor also receives reminder
```

**Estimated Effort:** 2-3 hari

---

## 🎯 FASE 2: HIGH-PRIORITY FEATURES (5-7 Hari)

### 4. MEDICAL RECORD & HISTORY (3-4 Hari)

**NEW: MedicalRecordController**
```php
// GET /api/medical-records - All records (paginated)
// GET /api/medical-records/:id - Detail view
// GET /api/medical-records/:id/export - Export to PDF
// GET /api/consultations/:id/notes - Doctor's notes
```

**Frontend:**
```vue
// NEW: MedicalRecordList.vue
// - Show all consultations in table
// - Filter by date, doctor, type
// - Click for detail view

// NEW: MedicalRecordViewer.vue
// - Show full consultation details
// - Show prescriptions
// - Show attachments
// - Download PDF button

// NEW: MedicalRecordExport.vue
// - Export to PDF (with doctor signature)
// - Email export to patient
```

**PDF Export Includes:**
```
- Consultation date & time
- Doctor name & license
- Diagnosis & treatment
- Medications prescribed
- Doctor digital signature
- Patient name & ID
- Watermark: "Patient Copy"
```

**Test Cases (Target: 12+ test cases):**
```
✓ Patient can view own medical records
✓ Doctor can view patient's medical records
✓ Admin can audit medical records
✓ Non-owner cannot view medical records
✓ Export to PDF works correctly
✓ PDF includes all required information
✓ PDF tamper-protected
✓ Email export works
✓ Records paginated (50 per page)
✓ Filter by date range works
```

**Estimated Effort:** 3-4 hari

---

### 5. APPOINTMENT RESCHEDULING (2-3 Hari)

**Flow:**
```
1. Patient clicks "Reschedule" button
2. Show current appointment details
3. Show available time slots (next 30 days)
4. Patient selects new time
5. System validates:
   - Doctor available at new time
   - Slot not already booked
   - No conflict with other patient appointments
6. Update database:
   - Update consultation.scheduled_at
   - Free old time slot
   - Book new time slot
7. Send notifications:
   - SMS/Email to patient: "Appointment rescheduled to [date/time]"
   - SMS/Email to doctor: "[Patient] rescheduled to [date/time]"
8. Keep history:
   - Log old & new times
   - Track reschedule count
```

**Database:**
```sql
ALTER TABLE consultations ADD COLUMN original_scheduled_at TIMESTAMP nullable;
ALTER TABLE consultations ADD COLUMN reschedule_count INT DEFAULT 0;

CREATE TABLE consultation_history (
    id BIGINT PRIMARY KEY,
    consultation_id BIGINT,
    action VARCHAR(50), -- 'scheduled', 'rescheduled', 'cancelled', 'completed'
    scheduled_at TIMESTAMP,
    created_at TIMESTAMP
);
```

**Controller:**
```php
// POST /api/consultations/:id/reschedule
// Payload: { new_slot_id: 123 }
// Response: { success: true, new_appointment: {...} }
```

**Estimated Effort:** 2-3 hari

---

### 6. DIGITAL PRESCRIPTION SIGNATURE (3-4 Hari)

**Why Important for Telemedicine Compliance:**
- Legal requirement in many jurisdictions
- Tamper detection & authenticity verification
- Audit trail for regulatory compliance

**Implementation:**

**Certificate Generation (One-time per doctor):**
```php
// NEW: app/Console/Commands/GenerateDoctorSignatureCertificate.php
// Generates:
// - Private key (stored encrypted in database)
// - Public certificate (stored in filesystem)
// Only admin can run this command
```

**Signing Prescription PDF:**
```php
// MODIFY: app/Services/PrescriptionPDFService.php
// Add method: signPrescriptionPDF()
// Uses private key to digitally sign PDF
// Embeds certificate in PDF
// Adds signature timestamp

public function signPrescriptionPDF($prescriptionId) {
    $prescription = Prescription::findOrFail($prescriptionId);
    $doctor = $prescription->consultation->doctor;
    
    // 1. Generate PDF
    $pdf = $this->generatePDF($prescription);
    
    // 2. Get doctor's private key
    $privateKey = decrypt($doctor->signature_private_key);
    
    // 3. Sign PDF
    $signedPdf = $this->signPDFWithKey($pdf, $privateKey);
    
    // 4. Save signed PDF
    Storage::put("prescriptions/{$prescriptionId}/signed.pdf", $signedPdf);
    
    // 5. Update database
    $prescription->update([
        'is_signed' => true,
        'signed_at' => now(),
        'signature_certificate' => $doctor->signature_certificate
    ]);
}
```

**Verification:**
```php
// Verify signature when patient views
// Check tampering
// Show certificate info
// Display signature timestamp
```

**Frontend:**
```vue
// MODIFY: PrescriptionViewer.vue
// Add:
// - "Digitally Signed" badge
// - Doctor's name & license from certificate
// - Signature timestamp
// - Verification button (shows cert info)
// - Warning if signature verification fails
```

**Compliance Notes:**
- Signature algorithm: SHA-256 with RSA-2048
- Certificate: X.509 standard
- Retention: 7 years (per HIPAA)
- Audit log: Every signature creation/verification

**Test Cases (Target: 12+ test cases):**
```
✓ Doctor can sign prescription
✓ Signature verification passes
✓ Tampered PDF fails verification
✓ Certificate embedded in PDF
✓ Signature timestamp recorded
✓ Patient sees "Signed" badge
✓ Patient can verify signature
✓ Signature persists through email
✓ Only doctor who prescribed can sign
✓ Signature cannot be removed/altered
```

**Estimated Effort:** 3-4 hari

---

## 📊 JADWAL IMPLEMENTASI UNTUK SKRIPSI

```
Minggu 1:
  Hari 1-2: Video Consultation
  Hari 3-4: Doctor Availability (Part 1)
  Hari 5  : Doctor Availability (Part 2)

Minggu 2:
  Hari 6-7: Appointment Reminders
  Hari 8-9: Medical Record Access
  Hari 10  : Appointment Rescheduling

Minggu 3:
  Hari 11-12: Digital Prescription Signature
  Hari 13   : Compliance Documentation
  Hari 14   : Testing & Bug Fixes
  Hari 15   : Final Testing & Demo Preparation
```

---

## 🧪 TESTING FRAMEWORK

**For Each Feature:**

```
1. Unit Tests (60% coverage)
   - Controller tests: 150-200 lines
   - Service tests: 100-150 lines
   - Model tests: 50-100 lines

2. Feature Tests (80% coverage)
   - Happy path workflow
   - Error handling
   - Authorization/permissions
   - Edge cases

3. E2E Tests (Cypress)
   - Complete user journey
   - Real browser interaction
   - Database state verification

4. Load Tests (if applicable)
   - Database query performance
   - Concurrent operations (e.g., simultaneous bookings)
   - API response time
```

**Example: Video Consultation Test Suite**

```php
// tests/Feature/VideoConsultationTest.php
class VideoConsultationTest extends TestCase {
    /** @test */
    public function doctor_can_initiate_video_session() {
        $doctor = User::factory()->doctor()->create();
        $consultation = Consultation::factory()
            ->for($doctor)
            ->create(['status' => 'confirmed']);
        
        $response = $this->actingAs($doctor)
            ->post('/api/video/start', [
                'consultation_id' => $consultation->id
            ]);
        
        $response->assertStatus(200);
        $this->assertTrue($consultation->fresh()->video_started);
    }
    
    /** @test */
    public function patient_cannot_start_video_without_recording_consent() {
        // ... test logic
    }
    
    /** @test */
    public function concurrent_video_requests_handled_correctly() {
        // ... test race condition
    }
}
```

---

## 📈 METRICS & SUCCESS CRITERIA

| Metric | Target | Current | Gap |
|--------|--------|---------|-----|
| Features Complete | 95% | 70% | 6 features |
| Test Coverage | 90% | 85% | +5% |
| Code Duplication | < 3% | 2.8% | ✅ |
| API Response Time | < 500ms | 300-400ms | ✅ |
| Test Cases | 250+ | 150 | +100 |
| Documentation | Complete | 80% | +20% |
| Compliance Score | 95% | 85% | +10% |

---

## 🚀 POST-IMPLEMENTATION CHECKLIST

After completing all features:

- [ ] All tests passing (250+)
- [ ] Code coverage ≥ 90%
- [ ] No critical bugs or errors
- [ ] API documentation updated
- [ ] User manuals created
- [ ] Deployment guide ready
- [ ] Backup/recovery tested
- [ ] Security audit passed
- [ ] Performance benchmarks meet targets
- [ ] Compliance checklist 100% complete
- [ ] Ready for production deployment
- [ ] Ready for thesis submission & demo

---

## 💡 TIPS FOR SKRIPSI SUBMISSION

1. **Code Quality:**
   - Use Laravel best practices
   - PSR-12 coding standards
   - Meaningful variable/function names
   - Well-documented code

2. **Documentation:**
   - Create API documentation (Swagger/OpenAPI)
   - Write user manuals (in Indonesian)
   - Create admin guides
   - Include architecture diagrams

3. **Testing:**
   - 250+ test cases
   - > 90% code coverage
   - E2E tests for critical flows
   - Load testing results

4. **Compliance:**
   - Privacy policy document
   - Informed consent template
   - Security measures documentation
   - Data retention policy
   - Audit log samples

5. **Demo Preparation:**
   - Create demo video (5-10 minutes)
   - Prepare demo data (fake but realistic)
   - Test all features before demo
   - Practice explaining features

6. **Writing:**
   - Clear architecture explanation
   - Technology justification
   - Design pattern explanations
   - Performance optimization notes

---

**Expected Result:** A-grade worthy telemedicine application
**Timeline:** 2-3 minggu untuk development
**Grade Improvement:** A- → A+
