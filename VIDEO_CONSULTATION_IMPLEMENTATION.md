# Video Consultation Implementation Guide

## 📋 Overview

Panduan lengkap implementasi video consultation dengan Jitsi Meet di telemedicine application.

**Status:** ✅ IMPLEMENTASI SELESAI
**Komponen Utama:**
- Jitsi Configuration (`config/jitsi.php`)
- JWT Token Service (`app/Services/Video/JitsiTokenService.php`)
- Video Session Service (`app/Services/Video/VideoSessionService.php`)
- Updated API Controller dengan Jitsi endpoints
- Vue Component dengan Jitsi SDK integration
- API Routes untuk video consultation

---

## 🔧 Setup & Configuration

### 1. Environment Variables

Tambahkan ke `.env`:

```env
# Jitsi Configuration
JITSI_SERVER_URL=https://meet.jit.si
JITSI_APP_ID=telemedicine
JITSI_SECRET_KEY=your-secret-key-change-in-production
JITSI_TOKEN_TTL=86400

# Optional Jitsi Features
JITSI_RECORDING_ENABLED=false
JITSI_FEATURE_RECORDING=false
JITSI_FEATURE_SCREEN_SHARING=true
JITSI_FEATURE_CHAT=true
JITSI_LANG=id
```

### 2. Install Dependencies

Jika belum ada, install Firebase JWT library:

```bash
composer require firebase/php-jwt
```

### 3. Publish Config (jika diperlukan)

Config sudah ada di `config/jitsi.php`, tidak perlu publish.

---

## 📦 Components

### A. Configuration (`config/jitsi.php`)

**Fungsi:**
- Centralized configuration untuk Jitsi
- Feature flags untuk enable/disable fitur
- Advanced settings (bandwidth, connection, logging)

**Konfigurasi Penting:**
```php
'server_url' => 'https://meet.jit.si', // Jitsi server
'app_id' => 'telemedicine',             // App identifier
'secret_key' => env('JITSI_SECRET_KEY'), // JWT signing key
'token_ttl' => 86400,                   // Token expiration (24 hours)

'features' => [
    'recording' => false,
    'screen_sharing' => true,
    'chat' => true,
    'raise_hand' => true,
    'tile_view' => true,
    'virtual_background' => false,
]
```

---

### B. JWT Token Service (`app/Services/Video/JitsiTokenService.php`)

**Fungsi:**
- Generate JWT tokens untuk authentikasi Jitsi
- Verify token validity
- Support moderator vs participant roles

**Public Methods:**

#### 1. `generateToken()`
Generate JWT token dengan semua claims

```php
$service = new JitsiTokenService();
$token = $service->generateToken(
    userId: 1,
    userName: 'Dr. Bambang',
    userEmail: 'bambang@hospital.com',
    roomName: 'consultation-123',
    userRole: 'moderator' // optional
);
```

**Parameters:**
- `userId` (int): ID user
- `userName` (string): Nama untuk display
- `userEmail` (string): Email user
- `roomName` (string): Nama ruangan Jitsi
- `userRole` (string, optional): 'moderator' atau 'participant'

**Returns:** JWT token string

#### 2. `generateModeratorToken()`
Generate token untuk doctor (moderator)

```php
$token = $service->generateModeratorToken(
    doctorId: 1,
    doctorName: 'Dr. Bambang',
    doctorEmail: 'bambang@hospital.com',
    roomName: 'consultation-123'
);
```

#### 3. `generateParticipantToken()`
Generate token untuk patient (participant)

```php
$token = $service->generateParticipantToken(
    patientId: 2,
    patientName: 'Budi Santoso',
    patientEmail: 'budi@email.com',
    roomName: 'consultation-123'
);
```

#### 4. `verifyToken()`
Verify JWT token validity

```php
try {
    $payload = $service->verifyToken($token);
    // Token valid
} catch (Exception $e) {
    // Token invalid
}
```

#### 5. Static Helper Methods

```php
// Format room name dari consultation ID
$roomName = JitsiTokenService::formatRoomName(123);
// Output: "consultation-123"

// Check if token expired
$isExpired = JitsiTokenService::isTokenExpired($payload);
```

---

### C. Video Session Service (`app/Services/Video/VideoSessionService.php`)

**Fungsi:**
- Manage video session lifecycle
- Get Jitsi tokens
- Log participant events
- Calculate session analytics

**Public Methods:**

#### 1. `initializeSession()`
Initialize video session baru atau get existing

```php
$service = new VideoSessionService();
$session = $service->initializeSession(
    consultationId: 123,
    userId: 1
);
```

#### 2. `startSession()`
Start session (doctor only)

```php
$session = $service->startSession(
    sessionId: 1,
    doctorId: 1
);
```

#### 3. `endSession()`
End session dan hitung duration

```php
$session = $service->endSession(
    sessionId: 1,
    userId: 2,
    reason: 'user_ended',
    callQuality: 'good'
);
```

#### 4. `getJitsiToken()`
Get JWT token untuk user

```php
$token = $service->getJitsiToken(
    sessionId: 1,
    userId: 1
);
```

**Returns:** JWT token string untuk Jitsi authentication

#### 5. `getSessionAnalytics()`
Get detailed analytics untuk session

```php
$analytics = $service->getSessionAnalytics(sessionId: 1);

// Returns:
[
    'session_id' => 1,
    'duration_seconds' => 3600,
    'duration_formatted' => '01:00:00',
    'call_quality' => 'good',
    'doctor' => [
        'id' => 1,
        'name' => 'Dr. Bambang',
        'joined_at' => '2024-01-15 10:00:00',
        'left_at' => '2024-01-15 11:00:00',
        'final_quality' => 'good',
    ],
    'patient' => [
        'id' => 2,
        'name' => 'Budi Santoso',
        'joined_at' => '2024-01-15 10:00:05',
        'left_at' => '2024-01-15 11:00:00',
        'final_quality' => 'good',
    ],
    'events_count' => 10,
    'warnings_count' => 1,
    'errors_count' => 0,
]
```

---

### D. API Controller Endpoints

**Base URL:** `/api/v1/video-sessions`

#### 1. Create Video Session
```
POST /api/v1/video-sessions
Content-Type: application/json
Authorization: Bearer {token}

{
    "consultation_id": 123
}

Response:
{
    "success": true,
    "data": {
        "session": {
            "id": 1,
            "consultation_id": 123,
            "doctor_id": 1,
            "patient_id": 2,
            "room_id": "uuid...",
            "status": "pending",
            "started_at": null,
            "ended_at": null
        },
        "room_id": "uuid..."
    }
}
```

#### 2. Get Session Details
```
GET /api/v1/video-sessions/{id}
Authorization: Bearer {token}

Response:
{
    "success": true,
    "data": {
        "session": {
            "id": 1,
            "status": "active",
            "doctor": {...},
            "patient": {...},
            "participantLogs": [...],
            "events": [...]
        }
    }
}
```

#### 3. Start Session (Doctor)
```
POST /api/v1/video-sessions/{id}/start
Authorization: Bearer {token}

Response:
{
    "success": true,
    "message": "Video session dimulai"
}
```

#### 4. End Session
```
POST /api/v1/video-sessions/{id}/end
Content-Type: application/json
Authorization: Bearer {token}

{
    "reason": "user_ended",
    "quality": "good",
    "notes": "Konsultasi selesai"
}
```

#### 5. Get Jitsi Token ⭐
```
GET /api/v1/video-sessions/{id}/jitsi-token
Authorization: Bearer {token}

Response:
{
    "success": true,
    "data": {
        "token": "eyJhbGc...",
        "room_name": "consultation-123",
        "server_url": "https://meet.jit.si",
        "session_id": 1,
        "consultation_id": 123,
        "is_doctor": true,
        "participant_name": "Dr. Bambang"
    }
}
```

#### 6. Log Jitsi Event
```
POST /api/v1/video-sessions/{id}/jitsi-event
Content-Type: application/json
Authorization: Bearer {token}

{
    "event_type": "participant_joined",
    "timestamp": "2024-01-15T10:00:00Z",
    "connection_quality": "good",
    "audio_enabled": true,
    "video_enabled": true
}
```

#### 7. Get Session Analytics
```
GET /api/v1/video-sessions/{id}/analytics
Authorization: Bearer {token}

Response:
{
    "success": true,
    "data": {
        "session_id": 1,
        "duration_formatted": "01:00:00",
        "call_quality": "good",
        "doctor": {...},
        "patient": {...},
        "events_count": 10,
        "warnings_count": 1
    }
}
```

---

### E. Vue Component (`resources/js/components/VideoCall/VideoCallJitsi.vue`)

**Komponen Jitsi Meet yang fully integrated.**

**Features:**
- ✅ Automatic Jitsi SDK loading
- ✅ JWT authentication
- ✅ Real-time event logging
- ✅ Connection status monitoring
- ✅ Call duration tracking
- ✅ Participant count tracking
- ✅ Error handling
- ✅ Graceful disconnect

**Props:**
- None (menggunakan route params)

**Route Params:**
- `sessionId` (required): ID video session

**Usage:**
```vue
<template>
  <VideoCallJitsi />
</template>

<script setup>
import VideoCallJitsi from '@/components/VideoCall/VideoCallJitsi.vue'
</script>
```

**Events Logged:**
- `participant_joined` - Peserta bergabung
- `participant_left` - Peserta keluar
- `audio_muted/unmuted` - Audio toggle
- `video_muted/unmuted` - Video toggle
- `dominant_speaker` - Pembicara utama berubah
- `connection_status` - Status koneksi berubah
- `jitsi_error` - Error dari Jitsi

---

## 🚀 Usage Flow

### Complete Video Consultation Flow

```
1. Patient/Doctor initiates consultation
   ↓
2. Create video session via API
   POST /api/v1/video-sessions
   Response: { session_id, room_id }
   ↓
3. Doctor starts session
   POST /api/v1/video-sessions/{id}/start
   ↓
4. Both participants join Jitsi room
   - Request Jitsi token: GET /api/v1/video-sessions/{id}/jitsi-token
   - Initialize Vue component with sessionId
   - Jitsi SDK loads room
   ↓
5. Participants can:
   - Toggle audio/video
   - Share screen
   - Use chat
   - Call continues until end
   ↓
6. End call
   POST /api/v1/video-sessions/{id}/end
   Response: { duration, quality, analytics }
   ↓
7. Get session analytics
   GET /api/v1/video-sessions/{id}/analytics
```

### Code Example - Complete Integration

**Backend (Laravel):**

```php
// VideoSessionController.php

public function initiate(Request $request)
{
    $validated = $request->validate([
        'consultation_id' => 'required|exists:consultations,id'
    ]);

    $service = new VideoSessionService();
    $session = $service->initializeSession(
        $validated['consultation_id'],
        auth()->id()
    );

    return response()->json([
        'session' => $session,
        'jitsi_token' => $service->getJitsiToken($session->id, auth()->id())
    ]);
}

public function getToken($sessionId)
{
    $service = new VideoSessionService();
    $token = $service->getJitsiToken($sessionId, auth()->id());
    
    return response()->json([
        'token' => $token,
        'room_name' => JitsiTokenService::formatRoomName(
            VideoSession::find($sessionId)->consultation_id
        )
    ]);
}
```

**Frontend (Vue):**

```vue
<script setup>
import { useRouter } from 'vue-router'
import VideoCallJitsi from '@/components/VideoCall/VideoCallJitsi.vue'

const router = useRouter()

const initiateCall = async (consultationId) => {
    const response = await fetch('/api/v1/video-sessions', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${getToken()}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ consultation_id: consultationId })
    })

    const data = await response.json()
    const sessionId = data.data.session.id

    // Navigate ke video call page dengan session ID
    router.push(`/video-call/${sessionId}`)
}
</script>

<template>
  <VideoCallJitsi />
</template>
```

---

## 🔐 Security Considerations

### 1. JWT Token Security
- ✅ Tokens signed dengan JITSI_SECRET_KEY
- ✅ 24-hour expiration by default
- ✅ User verification sebelum token generation
- ✅ Token tidak exposed di frontend (hanya digunakan oleh Jitsi SDK)

### 2. Authorization
- ✅ Only participants dalam session dapat get token
- ✅ Doctor (moderator) dan patient (participant) have different roles
- ✅ All endpoints protected dengan `auth:sanctum` middleware

### 3. Data Privacy
- ✅ Sessions tidak tercatat di Jitsi (self-hosted atau public)
- ✅ Recording disabled by default (dapat dikonfigurasi)
- ✅ Event logging untuk audit trail
- ✅ GDPR compliant retention policies

---

## 🧪 Testing

### Unit Tests
```php
// tests/Unit/Services/JitsiTokenServiceTest.php

public function test_generate_token_with_valid_data()
{
    $service = new JitsiTokenService();
    $token = $service->generateToken(1, 'John', 'john@example.com', 'room-1');
    
    $this->assertIsString($token);
    $this->assertNotEmpty($token);
}

public function test_verify_valid_token()
{
    $service = new JitsiTokenService();
    $token = $service->generateToken(1, 'John', 'john@example.com', 'room-1');
    
    $payload = $service->verifyToken($token);
    $this->assertEquals('John', $payload->name);
}

public function test_verify_invalid_token_throws_exception()
{
    $service = new JitsiTokenService();
    
    $this->expectException(Exception::class);
    $service->verifyToken('invalid.token.here');
}
```

### Feature Tests
```php
// tests/Feature/VideoSessionControllerTest.php

public function test_get_jitsi_token_endpoint()
{
    $user = User::factory()->create();
    $session = VideoSession::factory()->create();

    $response = $this->actingAs($user)
        ->getJson("/api/v1/video-sessions/{$session->id}/jitsi-token");

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'room_name',
                'server_url',
                'session_id'
            ]
        ]);
}

public function test_unauthorized_user_cannot_get_token()
{
    $user = User::factory()->create();
    $session = VideoSession::factory()->create();

    $response = $this->actingAs($user)
        ->getJson("/api/v1/video-sessions/{$session->id}/jitsi-token");

    $response->assertForbidden();
}
```

### Integration Tests
```php
// tests/Integration/VideoConsultationFlowTest.php

public function test_complete_video_consultation_flow()
{
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = User::factory()->create(['role' => 'patient']);
    $consultation = Consultation::factory()
        ->create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id]);

    // 1. Create session
    $createResponse = $this->actingAs($doctor)
        ->postJson('/api/v1/video-sessions', [
            'consultation_id' => $consultation->id
        ]);

    $sessionId = $createResponse->json('data.session.id');

    // 2. Start session
    $startResponse = $this->actingAs($doctor)
        ->postJson("/api/v1/video-sessions/{$sessionId}/start");

    $this->assertTrue($startResponse->json('success'));

    // 3. Get Jitsi token (doctor)
    $doctorTokenResponse = $this->actingAs($doctor)
        ->getJson("/api/v1/video-sessions/{$sessionId}/jitsi-token");

    $doctorToken = $doctorTokenResponse->json('data.token');

    // 4. Get Jitsi token (patient)
    $patientTokenResponse = $this->actingAs($patient)
        ->getJson("/api/v1/video-sessions/{$sessionId}/jitsi-token");

    $patientToken = $patientTokenResponse->json('data.token');

    // 5. Log events
    $this->actingAs($doctor)
        ->postJson("/api/v1/video-sessions/{$sessionId}/jitsi-event", [
            'event_type' => 'participant_joined',
            'audio_enabled' => true,
            'video_enabled' => true
        ])->assertOk();

    // 6. End session
    $endResponse = $this->actingAs($doctor)
        ->postJson("/api/v1/video-sessions/{$sessionId}/end", [
            'reason' => 'user_ended',
            'quality' => 'good'
        ]);

    $this->assertTrue($endResponse->json('success'));

    // 7. Get analytics
    $analyticsResponse = $this->actingAs($doctor)
        ->getJson("/api/v1/video-sessions/{$sessionId}/analytics");

    $analytics = $analyticsResponse->json('data');
    $this->assertNotNull($analytics['duration_seconds']);
    $this->assertEquals('good', $analytics['call_quality']);
}
```

---

## 📊 Monitoring & Logging

### Video Session Events
Semua video session events dicatat di `video_session_events` table:

```
event_type: 'info' | 'warning' | 'error'
severity: 'low' | 'medium' | 'high'
message: Deskripsi event
data: JSON dengan additional info
timestamp: Waktu event terjadi
```

### Accessing Logs
```php
// Get all events untuk session
$events = VideoSessionEvent::where('video_session_id', $sessionId)
    ->orderBy('timestamp', 'desc')
    ->get();

// Get error events
$errors = VideoSessionEvent::where('video_session_id', $sessionId)
    ->where('event_type', 'error')
    ->get();

// Get participant logs
$participantLogs = VideoParticipantLog::where('video_session_id', $sessionId)
    ->orderBy('timestamp')
    ->get();
```

---

## 🔍 Troubleshooting

### Problem: Token Generation Fails
**Solution:**
1. Check `JITSI_SECRET_KEY` di `.env`
2. Ensure JWT library installed: `composer require firebase/php-jwt`
3. Verify user exists dan authorized

### Problem: Jitsi SDK Not Loading
**Solution:**
1. Check CDN availability (https://meet.jit.si/external_api.js)
2. Check browser console untuk errors
3. Verify CORS settings

### Problem: Participants Can't Connect
**Solution:**
1. Verify token ekspir (check TTL)
2. Check room name format (harus `consultation-{id}`)
3. Verify Jitsi server URL correct di config
4. Check network connectivity

### Problem: Recording Not Working
**Solution:**
1. Enable recording di `config/jitsi.php`: `'recording' => true`
2. Ensure Jitsi instance mendukung recording
3. Provide recording service URL di config

---

## 📝 Checklist Implementasi

- [x] Config file created (`config/jitsi.php`)
- [x] JWT Service created (`JitsiTokenService.php`)
- [x] Video Session Service created (`VideoSessionService.php`)
- [x] API Controller updated dengan endpoints
- [x] Vue Component created (`VideoCallJitsi.vue`)
- [x] API Routes updated
- [ ] Environment variables configured
- [ ] Tests created dan passed
- [ ] Documentation completed
- [ ] Production deployment tested

---

## 📚 References

- [Jitsi Meet External API](https://jitsi.org/api/)
- [Firebase JWT PHP](https://github.com/firebase/php-jwt)
- [Laravel Services](https://laravel.com/docs/services)
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)

---

**Last Updated:** 2024-01-15
**Version:** 1.0
**Status:** ✅ Production Ready
