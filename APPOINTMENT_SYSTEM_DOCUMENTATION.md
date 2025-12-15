# APPOINTMENT/BOOKING SYSTEM DOCUMENTATION

## Overview

Complete appointment/consultation booking system untuk telemedicine platform. Memungkinkan pasien untuk menjadwalkan konsultasi dengan dokter dan dokter mengelola ketersediaan mereka.

**Status**: ✅ COMPLETED (Commit: d19563d)

## Features

### 1. Appointment Booking
- Pasien dapat membuat appointment dengan dokter
- Validasi konflik jadwal otomatis
- Support 3 tipe konsultasi: text_consultation, video_call, phone_call
- Integrasi harga dan status pembayaran

### 2. Availability Management
- Sistem slot ketersediaan dokter
- Slot default: 9 AM - 5 PM, 30 menit per slot
- Automatic conflict detection
- Real-time availability updates

### 3. Appointment Workflow
```
pending → confirmed → in_progress → completed
   ↓          ↓            ↓             ↓
 (reject)  (cancel)   (cancel)    (automatically set)
```

**Status Flow:**
- **pending**: Baru dibuat oleh pasien, menunggu konfirmasi dokter
- **confirmed**: Dokter telah confirm appointment
- **in_progress**: Konsultasi sedang berlangsung
- **completed**: Konsultasi selesai
- **cancelled**: Dibatalkan oleh pasien atau dokter
- **rejected**: Ditolak oleh dokter (dari pending)

### 4. Appointment Management
- Patient dapat reschedule appointment (max 1 jam sebelum jadwal)
- Patient dapat cancel appointment (max 1 jam sebelum jadwal)
- Doctor dapat confirm/reject appointment
- Doctor dapat start/end appointment
- Doctor dapat menambahkan notes setelah selesai

### 5. Statistics & Dashboard
- Per-user appointment statistics (total, by status)
- Today's appointments view
- Upcoming appointments tracking
- Appointment history dengan search & filter

## Database Schema

### Appointments Table

```sql
CREATE TABLE appointments (
    id BIGINT PRIMARY KEY,
    patient_id BIGINT FOREIGN KEY -> users.id,
    doctor_id BIGINT FOREIGN KEY -> users.id,
    scheduled_at DATETIME,
    started_at DATETIME NULLABLE,
    ended_at DATETIME NULLABLE,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'rejected'),
    type ENUM('text_consultation', 'video_call', 'phone_call'),
    reason TEXT NULLABLE,
    notes TEXT NULLABLE,
    consultation_link VARCHAR(255) NULLABLE,
    duration_minutes INT DEFAULT 30,
    price DECIMAL(10,2) NULLABLE,
    payment_status VARCHAR(50) DEFAULT 'pending',
    confirmed_at TIMESTAMP NULLABLE,
    cancelled_at TIMESTAMP NULLABLE,
    cancellation_reason TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    -- Indexes for performance
    INDEX (patient_id),
    INDEX (doctor_id),
    INDEX (scheduled_at),
    INDEX (status),
    INDEX (created_at),
    UNIQUE (doctor_id, scheduled_at) -- Prevent double booking
);
```

## API Endpoints

### Base URL: `/api/v1/appointments`

### 1. Book Appointment
```http
POST /api/v1/appointments
Content-Type: application/json
Authorization: Bearer {patient_token}

{
    "doctor_id": 2,
    "scheduled_at": "2025-01-20 14:00:00",
    "type": "text_consultation",
    "reason": "Konsultasi kesehatan umum",
    "price": 100000
}

Response (201):
{
    "message": "Appointment berhasil dibuat",
    "data": {
        "id": 1,
        "patient_id": 1,
        "doctor_id": 2,
        "scheduled_at": "2025-01-20 14:00:00",
        "status": "pending",
        "type": "text_consultation",
        "reason": "Konsultasi kesehatan umum",
        "price": 100000,
        "payment_status": "pending",
        "created_at": "2025-01-15 10:00:00"
    }
}
```

**Validation:**
- `doctor_id`: Required, must exist in users table with role=dokter
- `scheduled_at`: Required, must be in future
- `type`: Required, must be one of: text_consultation, video_call, phone_call
- `reason`: Optional, max 500 chars
- `price`: Optional, numeric, min 0

**Business Rules:**
- Patient tidak bisa book di waktu yang sudah ada appointment dokter
- Appointment harus dijadwalkan di masa depan

---

### 2. List User's Appointments
```http
GET /api/v1/appointments
GET /api/v1/appointments?status=pending
GET /api/v1/appointments?status=confirmed&page=1&per_page=10
GET /api/v1/appointments?search=Dr.+Ahmad&date_from=2025-01-01&date_to=2025-01-31
Authorization: Bearer {token}

Response (200):
{
    "data": [
        {
            "id": 1,
            "patient_id": 1,
            "doctor_id": 2,
            "doctor": { "id": 2, "name": "Dr. Ahmad" },
            "scheduled_at": "2025-01-20 14:00:00",
            "status": "confirmed",
            "type": "text_consultation"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 1,
        "last_page": 1
    }
}
```

**Query Parameters:**
- `status`: Filter by status (pending, confirmed, completed, cancelled, etc)
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15)
- `search`: Search by doctor/patient name or reason
- `date_from`: Filter appointments from date (Y-m-d)
- `date_to`: Filter appointments until date (Y-m-d)

---

### 3. Get Appointment Detail
```http
GET /api/v1/appointments/{id}
Authorization: Bearer {token}

Response (200):
{
    "data": {
        "id": 1,
        "patient_id": 1,
        "doctor_id": 2,
        "patient": { "id": 1, "name": "Budi" },
        "doctor": { "id": 2, "name": "Dr. Ahmad" },
        "scheduled_at": "2025-01-20 14:00:00",
        "started_at": null,
        "ended_at": null,
        "status": "confirmed",
        "type": "text_consultation",
        "reason": "Konsultasi kesehatan umum",
        "notes": null,
        "consultation_link": null,
        "duration_minutes": 30,
        "price": 100000,
        "payment_status": "pending",
        "confirmed_at": "2025-01-15 10:30:00",
        "created_at": "2025-01-15 10:00:00"
    }
}
```

**Authorization:**
- Patient hanya bisa lihat appointment mereka
- Doctor hanya bisa lihat appointment mereka
- Admin bisa lihat semua

---

### 4. Get Available Slots
```http
GET /api/v1/doctor/{doctorId}/available-slots?date=2025-01-20

Response (200):
{
    "date": "2025-01-20",
    "available_slots": [
        "2025-01-20 09:00:00",
        "2025-01-20 09:30:00",
        "2025-01-20 10:00:00",
        ...
    ],
    "total_slots": 8
}
```

**Query Parameters:**
- `date`: Required, format Y-m-d, must be after today

**Notes:**
- Returns slots berdasarkan working hours (default 9 AM - 5 PM)
- 30-minute slots
- Automatically excludes booked times
- Public endpoint (no auth required)

---

### 5. Confirm Appointment (Doctor Only)
```http
POST /api/v1/appointments/{id}/confirm
Authorization: Bearer {doctor_token}

Response (200):
{
    "message": "Appointment berhasil di-confirm",
    "data": {
        "id": 1,
        "status": "confirmed",
        "confirmed_at": "2025-01-15 10:30:00"
    }
}
```

**Authorization:** Doctor only (role=dokter)
**Preconditions:** 
- Appointment status must be "pending"
- Doctor must own the appointment

**Triggers:**
- Notification sent to patient

---

### 6. Reject Appointment (Doctor Only)
```http
POST /api/v1/appointments/{id}/reject
Authorization: Bearer {doctor_token}

{
    "reason": "Waktu tidak tersedia, silakan pilih waktu lain"
}

Response (200):
{
    "message": "Appointment berhasil di-reject",
    "data": {
        "id": 1,
        "status": "rejected",
        "cancellation_reason": "Waktu tidak tersedia, silakan pilih waktu lain",
        "cancelled_at": "2025-01-15 10:30:00"
    }
}
```

**Validation:**
- `reason`: Required, max 500 chars

**Triggers:**
- Notification sent to patient

---

### 7. Cancel Appointment
```http
POST /api/v1/appointments/{id}/cancel
Authorization: Bearer {token}

{
    "reason": "Sudah merasa sehat"
}

Response (200):
{
    "message": "Appointment berhasil di-cancel",
    "data": {
        "id": 1,
        "status": "cancelled",
        "cancellation_reason": "Sudah merasa sehat",
        "cancelled_at": "2025-01-15 10:30:00"
    }
}
```

**Authorization:** 
- Patient atau Doctor yang related dengan appointment

**Preconditions:**
- Status must be: pending, confirmed, or in_progress
- Must be min 1 hour before scheduled time
- Cannot cancel completed/already cancelled

**Triggers:**
- Notification sent to other party

---

### 8. Reschedule Appointment (Patient Only)
```http
POST /api/v1/appointments/{id}/reschedule
Authorization: Bearer {patient_token}

{
    "scheduled_at": "2025-01-21 14:00:00"
}

Response (200):
{
    "message": "Appointment berhasil di-reschedule",
    "data": {
        "id": 1,
        "scheduled_at": "2025-01-21 14:00:00",
        "status": "pending"
    }
}
```

**Authorization:** Patient only
**Preconditions:**
- Status must be: pending or confirmed
- Min 1 hour before original scheduled time
- New time must not have conflict

**Notes:**
- Status resets to "pending" after reschedule
- Doctor needs to confirm again

**Triggers:**
- Notification sent to doctor

---

### 9. Start Appointment (Doctor Only)
```http
POST /api/v1/appointments/{id}/start
Authorization: Bearer {doctor_token}

Response (200):
{
    "message": "Appointment dimulai",
    "data": {
        "id": 1,
        "status": "in_progress",
        "started_at": "2025-01-20 14:00:00"
    }
}
```

**Preconditions:**
- Status must be "confirmed"
- Doctor owns appointment

**Triggers:**
- Notification sent to patient

---

### 10. End Appointment (Doctor Only)
```http
POST /api/v1/appointments/{id}/end
Authorization: Bearer {doctor_token}

{
    "notes": "Konsultasi selesai, pasien diberikan resep"
}

Response (200):
{
    "message": "Appointment selesai",
    "data": {
        "id": 1,
        "status": "completed",
        "ended_at": "2025-01-20 14:30:00",
        "notes": "Konsultasi selesai, pasien diberikan resep"
    }
}
```

**Validation:**
- `notes`: Optional, max 1000 chars

**Preconditions:**
- Status must be "in_progress"

**Triggers:**
- Notification sent to patient

---

### 11. Get Appointment Statistics
```http
GET /api/v1/appointments/stats
Authorization: Bearer {token}

Response (200):
{
    "data": {
        "total": 5,
        "pending": 1,
        "confirmed": 2,
        "completed": 2,
        "cancelled": 0,
        "rejected": 0,
        "upcoming": 3,
        "past": 2
    }
}
```

**Notes:**
- Doctor dan Patient melihat appointment mereka sendiri
- Breakdown by status dan time period

---

### 12. Get Today's Appointments
```http
GET /api/v1/appointments/today
Authorization: Bearer {token}

Response (200):
{
    "data": [
        {
            "id": 1,
            "patient": { "name": "Budi" },
            "doctor": { "name": "Dr. Ahmad" },
            "scheduled_at": "2025-01-20 14:00:00",
            "status": "confirmed"
        }
    ],
    "count": 1
}
```

---

## Model Methods

### Appointment Model

```php
// Check appointment states
$appointment->isUpcoming(): bool
$appointment->canBeModified(): bool
$appointment->canBeCancelled(): bool
$appointment->isPast(): bool

// Actions
$appointment->confirm()
$appointment->reject($reason)
$appointment->cancel($reason)
$appointment->start()
$appointment->complete()

// Get duration (hanya jika sudah selesai)
$appointment->getDuration(): ?int

// Scopes
Appointment::upcoming()
Appointment::past()
Appointment::forPatient($patientId)
Appointment::forDoctor($doctorId)
Appointment::onDate($date)
```

## Service Methods

### AppointmentService

```php
// Booking
bookAppointment(patientId, doctorId, scheduledAt, type, reason, price): Appointment

// Availability
getAvailableSlots(doctorId, date, slotDuration=30): array

// Workflow
confirmAppointment(appointmentId, doctorId): Appointment
rejectAppointment(appointmentId, doctorId, reason): Appointment
cancelAppointment(appointmentId, userId, reason): Appointment
rescheduleAppointment(appointmentId, newScheduledAt, userId): Appointment
startAppointment(appointmentId, doctorId): Appointment
endAppointment(appointmentId, doctorId, notes=null): Appointment

// Retrieval
getAppointmentDetail(appointmentId): Appointment
getPatientAppointments(patientId, status=null, page=1, perPage=15): Paginator
getDoctorAppointments(doctorId, status=null, page=1, perPage=15): Paginator
getUpcomingAppointments(doctorId, days=7): Collection
getTodayAppointments(userId, role): Collection

// Statistics
getAppointmentStats(userId, role): array
searchAppointments(userId, role, search=null, status=null, dateFrom=null, dateTo=null): Paginator
```

## Integration Points

### 1. Notifications
- `notifyAppointmentCreated`: Ketika pasien book appointment
- `notifyAppointmentConfirmed`: Ketika doctor confirm
- `notifyAppointmentRejected`: Ketika doctor reject
- `notifyAppointmentCancelled`: Ketika appointment di-cancel
- `notifyAppointmentRescheduled`: Ketika appointment di-reschedule
- `notifyAppointmentStarted`: Ketika appointment dimulai
- `notifyAppointmentCompleted`: Ketika appointment selesai

### 2. User Authentication
- Integration dengan auth middleware
- Sanctum token validation
- Role-based access (patient vs doctor)

### 3. Database Relationships
- `Appointment.patient()` - belongs to User as patient
- `Appointment.doctor()` - belongs to User as doctor

## Error Handling

### Common Errors

**422 Unprocessable Entity:**
```json
{
    "error": "Doctor sudah memiliki appointment pada waktu tersebut"
}
```

**403 Forbidden:**
```json
{
    "error": "Hanya pasien dapat membuat appointment"
}
```

**Error Messages (Bahasa Indonesia):**
- "User harus dokter" - Selected user is not a doctor
- "User harus pasien" - Selected user is not a patient
- "Doctor sudah memiliki appointment pada waktu tersebut" - Time slot conflict
- "Hanya doctor dapat confirm appointment" - Authorization error
- "Hanya appointment pending yang dapat di-confirm" - Invalid status
- "Anda tidak berhak cancel appointment ini" - Authorization error
- "Appointment tidak bisa di-cancel" - Already past or not cancellable
- "Doctor sudah memiliki appointment pada waktu baru" - Reschedule conflict
- "Hanya patient dapat reschedule appointment" - Authorization error
- "Appointment tidak bisa di-reschedule" - Invalid status or too close to start

## Testing

### Test File: `test_appointments.php`

Comprehensive test script covering:
1. Patient login
2. Doctor login
3. Get available slots
4. Book appointment
5. Get appointment detail
6. Confirm appointment (doctor)
7. Get statistics
8. List doctor's appointments
9. Reschedule appointment
10. Start appointment
11. End appointment
12. Cancel appointment
13. Reject appointment

**Run tests:**
```bash
php test_appointments.php
```

## Example Usage

### Scenario: Patient books and completes consultation

```php
// Patient books appointment
$appointmentService->bookAppointment(
    patientId: 1,
    doctorId: 2,
    scheduledAt: '2025-01-20 14:00:00',
    type: 'text_consultation',
    reason: 'Konsultasi kesehatan umum'
);

// Doctor confirms
$appointmentService->confirmAppointment(
    appointmentId: 1,
    doctorId: 2
);

// At appointment time, doctor starts
$appointmentService->startAppointment(
    appointmentId: 1,
    doctorId: 2
);

// After 30 minutes, doctor ends
$appointmentService->endAppointment(
    appointmentId: 1,
    doctorId: 2,
    notes: 'Konsultasi selesai'
);

// Patient can now rate doctor
// (Rating system already implemented)
```

## Performance Considerations

### Indexes
- `(doctor_id, scheduled_at)` - Unique index prevents double booking
- `(patient_id)` - Fast patient lookup
- `(status)` - Filter by status
- `(scheduled_at)` - Date range queries
- `(created_at)` - Recent appointments

### Query Optimization
- Relationships loaded with `with(['doctor', 'patient'])`
- Pagination limited to 15 per page by default
- Scopes for common filters (upcoming, past, forDoctor, forPatient)

## Future Enhancements

1. **Doctor Availability Patterns**
   - Weekly recurring availability
   - Blackout dates for vacation
   - Timezone support

2. **Appointment Reminders**
   - SMS/Email 24 hours before
   - In-app notification
   - Auto-cancel no-shows

3. **Video Integration**
   - Jitsi meet link generation
   - Google Meet integration
   - Zoom integration

4. **Appointment Slots**
   - Separate table for pre-defined slots
   - Bulk appointment creation
   - Pricing per slot

5. **Rescheduling Logic**
   - Multiple reschedule options suggestion
   - Auto-find available time
   - Patient preference learning

6. **Payment Integration**
   - Payment before appointment
   - Refund on cancellation
   - Invoice generation

## Commit History

- **d19563d**: Implement appointment/booking system with full workflow
  - Added Appointment model
  - Added AppointmentService (15+ methods)
  - Added AppointmentController (12 endpoints)
  - Added comprehensive tests
  - Added 12 API routes

## Files Changed

```
✅ app/Models/Appointment.php (172 lines)
✅ app/Services/AppointmentService.php (448 lines)
✅ app/Http/Controllers/Api/AppointmentController.php (347 lines)
✅ database/migrations/2025_12_15_create_appointments_table.php (68 lines)
✅ routes/api.php (+30 lines for appointment routes)
✅ test_appointments.php (415 lines)
```

**Total Additions:** 1,480 lines of code

---

**Documentation Generated:** 2025-01-15
**System Status:** ✅ Production Ready
**Test Coverage:** Full workflow covered
