# Video Consultation Architecture

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     FRONTEND (Vue.js)                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  VideoCallPage.vue                VideoCallJitsi.vue            │
│  ├─ Load session                  ├─ Load Jitsi SDK             │
│  ├─ Get session ID                ├─ Get JWT token              │
│  └─ Mount component               ├─ Initialize Jitsi           │
│                                   ├─ Log events                 │
│                                   ├─ Monitor connection          │
│                                   └─ Handle disconnect           │
│                                                                 │
│  Events logged via:                                             │
│  POST /api/v1/video-sessions/{id}/jitsi-event                 │
│                                                                 │
└──────────────────────────────────────────────────────────────────┘
                           ↓ (HTTP/REST)
┌──────────────────────────────────────────────────────────────────┐
│                     API LAYER (Laravel)                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  VideoSessionController                                          │
│  ├─ POST   /video-sessions                                       │
│  ├─ GET    /video-sessions/{id}                                  │
│  ├─ POST   /video-sessions/{id}/start                            │
│  ├─ POST   /video-sessions/{id}/end                              │
│  ├─ GET    /video-sessions/{id}/jitsi-token        ← NEW        │
│  ├─ POST   /video-sessions/{id}/jitsi-event        ← NEW        │
│  └─ GET    /video-sessions/config/jitsi            ← NEW        │
│                           ↓                                       │
│  Middleware: auth:sanctum                                        │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│                   SERVICE LAYER                                  │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  VideoSessionService                JitsiTokenService            │
│  ├─ initializeSession()              ├─ generateToken()          │
│  ├─ startSession()                   ├─ generateModeratorToken() │
│  ├─ endSession()                     ├─ generateParticipantToken│
│  ├─ getJitsiToken()                  ├─ verifyToken()            │
│  ├─ logParticipantEvent()            ├─ decodeToken()            │
│  ├─ getSessionAnalytics()            └─ formatRoomName()         │
│  └─ isSessionActive()                                            │
│                                                                  │
│  ↓ (Uses)                                                        │
│  config/jitsi.php                                               │
│  ├─ JITSI_SERVER_URL                                            │
│  ├─ JITSI_APP_ID                                                │
│  ├─ JITSI_SECRET_KEY                                            │
│  ├─ Features config                                             │
│  └─ Advanced settings                                           │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│                    DATA LAYER (Database)                         │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  VideoSession                  VideoSessionEvent                 │
│  ├─ id                         ├─ id                             │
│  ├─ consultation_id            ├─ video_session_id              │
│  ├─ doctor_id                  ├─ event_type                    │
│  ├─ patient_id                 ├─ message                       │
│  ├─ room_id                    ├─ data (JSON)                   │
│  ├─ status                     ├─ severity                      │
│  ├─ started_at                 └─ timestamp                     │
│  ├─ ended_at                                                    │
│  ├─ duration_seconds           VideoParticipantLog              │
│  ├─ call_quality               ├─ id                            │
│  ├─ recording_url              ├─ video_session_id              │
│  └─ is_recorded                ├─ user_id                       │
│                                ├─ event_type                    │
│                                ├─ data (JSON)                   │
│                                └─ timestamp                     │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│               EXTERNAL SERVICE (Jitsi Meet)                      │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Jitsi Server: https://meet.jit.si (or self-hosted)            │
│  ├─ Authenticates with JWT token                                │
│  ├─ Manages room/conference                                     │
│  ├─ Handles audio/video streams (WebRTC)                        │
│  ├─ Provides chat functionality                                 │
│  ├─ Supports screen sharing                                     │
│  └─ Optional: Recording & streaming                             │
│                                                                  │
│  Client SDK: external_api.js                                    │
│  ├─ Embedded in web page                                        │
│  ├─ Controls Jitsi iframe                                       │
│  ├─ Emits events (participant joined, etc)                      │
│  └─ Provides API to Vue component                               │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## JWT Token Flow

```
┌─ DOCTOR START SESSION ─────────────────────────────────────────┐
│                                                                │
│  1. Doctor initiates video call                                │
│     POST /api/v1/video-sessions/{id}/start                     │
│                                                                │
│  2. Create VideoSession record in DB                           │
│     ├─ consultation_id, doctor_id, patient_id                 │
│     ├─ room_id (UUID)                                         │
│     └─ status = 'active'                                      │
│                                                                │
│  3. Frontend mounts VideoCallJitsi component                   │
│     → sessionId passed via route param                         │
│                                                                │
└────────────────────────────────────────────────────────────────┘

┌─ DOCTOR REQUEST JITSI TOKEN ───────────────────────────────────┐
│                                                                │
│  1. Frontend requests token                                    │
│     GET /api/v1/video-sessions/{id}/jitsi-token               │
│                                                                │
│  2. Backend verifies authorization                             │
│     - Check user is doctor or patient in session              │
│                                                                │
│  3. Generate JWT with claims:                                  │
│     {                                                          │
│       "iss": "telemedicine",                                  │
│       "sub": "https://meet.jit.si",                           │
│       "aud": "jitsi",                                         │
│       "room": "consultation-123",                             │
│       "name": "Dr. Bambang",                                  │
│       "email": "bambang@hospital.com",                        │
│       "context": {                                            │
│         "user": {                                             │
│           "id": "1",                                          │
│           "name": "Dr. Bambang",                              │
│           "role": "moderator"                                 │
│         }                                                     │
│       },                                                      │
│       "iat": 1705320000,                                      │
│       "exp": 1705406400                                       │
│     }                                                         │
│                                                                │
│  4. Sign with JITSI_SECRET_KEY (HS256)                        │
│                                                                │
│  5. Return to frontend                                         │
│     {                                                          │
│       "token": "eyJhbGc...",                                   │
│       "room_name": "consultation-123",                        │
│       "is_doctor": true                                       │
│     }                                                          │
│                                                                │
└────────────────────────────────────────────────────────────────┘

┌─ JITSI AUTHENTICATION ─────────────────────────────────────────┐
│                                                                │
│  1. Frontend passes token to Jitsi SDK                         │
│     JitsiMeetExternalAPI({                                     │
│       jwt: token,                                             │
│       roomName: 'consultation-123'                            │
│     })                                                         │
│                                                                │
│  2. Jitsi SDK loads https://meet.jit.si/external_api.js      │
│                                                                │
│  3. Jitsi client validates JWT                                │
│     - Verify signature with public key                        │
│     - Check expiration                                        │
│     - Check room access                                       │
│                                                                │
│  4. Create room if not exists                                 │
│     Room: "consultation-123"                                  │
│     Moderators: doctor_id                                     │
│     Participants: patient_id                                  │
│                                                                │
│  5. Establish WebRTC connection                               │
│     ├─ Audio stream from user's microphone                    │
│     ├─ Video stream from user's camera                        │
│     └─ Signaling through Jitsi server                         │
│                                                                │
└────────────────────────────────────────────────────────────────┘

┌─ PARTICIPANT JOINS ────────────────────────────────────────────┐
│                                                                │
│  1. Second participant (patient) gets token same way          │
│     GET /api/v1/video-sessions/{id}/jitsi-token              │
│     → Role: "participant" (different claims)                  │
│                                                                │
│  2. Patient loads VideoCallJitsi with same session ID         │
│                                                                │
│  3. Patient's Jitsi SDK:                                       │
│     - Joins existing room "consultation-123"                  │
│     - Establishes WebRTC with doctor                          │
│     - Receives doctor's audio/video streams                   │
│     - Sends patient's audio/video streams                     │
│                                                                │
│  4. Both see each other + all Jitsi features                  │
│     ├─ Audio controls                                         │
│     ├─ Video controls                                         │
│     ├─ Screen sharing                                         │
│     ├─ Chat                                                   │
│     ├─ Participant list                                       │
│     └─ Settings                                               │
│                                                                │
└────────────────────────────────────────────────────────────────┘

┌─ EVENT LOGGING ────────────────────────────────────────────────┐
│                                                                │
│  During call, Jitsi SDK emits events:                         │
│                                                                │
│  jitsiAPI.addEventListener('participantJoined', ...) →        │
│    POST /api/v1/video-sessions/{id}/jitsi-event              │
│    { event_type: 'participant_joined' }                       │
│                                                                │
│  jitsiAPI.addEventListener('videoMuted', ...) →              │
│    POST /api/v1/video-sessions/{id}/jitsi-event              │
│    { event_type: 'video_muted' }                              │
│                                                                │
│  → Backend logs to video_participant_logs & video_events     │
│                                                                │
└────────────────────────────────────────────────────────────────┘

┌─ END CALL ─────────────────────────────────────────────────────┐
│                                                                │
│  1. User clicks End Call button                                │
│                                                                │
│  2. Frontend calls:                                            │
│     POST /api/v1/video-sessions/{id}/end                      │
│     {                                                         │
│       "reason": "user_ended",                                │
│       "quality": "good"                                       │
│     }                                                         │
│                                                                │
│  3. Backend:                                                   │
│     - Update VideoSession status = 'ended'                    │
│     - Calculate duration_seconds                              │
│     - Store call_quality                                      │
│     - Log session end event                                   │
│                                                                │
│  4. Dispose Jitsi SDK                                          │
│     jitsiAPI.dispose() → Clean up WebRTC connections         │
│                                                                │
│  5. Redirect to consultations page                             │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

## Security Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Layer 1: Authentication                                         │
│  ├─ Sanctum token (Laravel auth:sanctum middleware)            │
│  ├─ Every API endpoint requires valid token                    │
│  └─ Verified user identity before any operation                │
│                                                                  │
│  Layer 2: Authorization                                         │
│  ├─ User must be doctor or patient in consultation             │
│  ├─ Doctor can only modify own sessions                        │
│  ├─ Patient can only access own consultations                  │
│  └─ Role-based access control (moderator vs participant)       │
│                                                                  │
│  Layer 3: JWT Signing                                           │
│  ├─ Jitsi tokens signed with JITSI_SECRET_KEY                  │
│  ├─ HS256 (HMAC with SHA-256)                                  │
│  ├─ Jitsi verifies signature before allowing join              │
│  └─ Tampered tokens rejected by Jitsi                          │
│                                                                  │
│  Layer 4: Token Expiration                                      │
│  ├─ JWT tokens expire after 24 hours (configurable)           │
│  ├─ Expired tokens rejected by Jitsi                          │
│  └─ Fresh token required for each session                      │
│                                                                  │
│  Layer 5: Room Access Control                                   │
│  ├─ Room ID verified in token                                  │
│  ├─ Only specified room accessible with token                  │
│  ├─ Doctor and patient get same room ID                        │
│  └─ Other users cannot join room                               │
│                                                                  │
│  Layer 6: Event Logging                                         │
│  ├─ All video events logged to database                        │
│  ├─ Audit trail of all participant actions                     │
│  ├─ Connection quality and issues tracked                      │
│  └─ Compliance and forensics support                           │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│              JITSI SECRET KEY MANAGEMENT                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Environment: JITSI_SECRET_KEY                                  │
│  ├─ Never commit to git                                         │
│  ├─ Store in secure vault (e.g., AWS Secrets Manager)         │
│  ├─ Rotate regularly (e.g., quarterly)                        │
│  ├─ Different key for each environment (dev, staging, prod)   │
│  └─ Strong entropy (use crypto library to generate)           │
│                                                                  │
│  Token Claims Protection:                                       │
│  ├─ iss: App identifier (telemedicine)                        │
│  ├─ sub: Jitsi server URL                                     │
│  ├─ room: Consultation room only                              │
│  ├─ exp: Limited lifetime (24 hours)                          │
│  └─ context: User info + role                                 │
│                                                                  │
│  No sensitive data in token:                                    │
│  ├─ ❌ NOT: passwords, SSNs, medical data                      │
│  ├─ ❌ NOT: database IDs or internal state                     │
│  ├─ ✅ Only: user identification and role                      │
│  └─ ✅ Verified by backend before use                          │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## Data Flow Diagram

```
Video Session Lifecycle:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. CREATE SESSION
   Consultation (exists)
        ↓
   VideoSessionController::create()
        ↓
   VideoSessionService::initializeSession()
        ↓
   VideoSession (created with status='pending')
   VideoSessionEvent (logged: "Session initialized")

2. START SESSION
   VideoSession (status='pending')
        ↓
   VideoSessionController::start()
        ↓
   VideoSessionService::startSession()
        ↓
   VideoSession (status='active', started_at=now)
   VideoParticipantLog (doctor joined)
   VideoSessionEvent (logged: "Session started")

3. GET TOKEN (BOTH DOCTOR & PATIENT)
   VideoSession (status='active')
        ↓
   VideoSessionController::getJitsiToken()
        ↓
   VideoSessionService::getJitsiToken()
        ↓
   JitsiTokenService::generateToken()
        ↓
   JWT Token (signed with secret)
        ↓
   Frontend receives: { token, room_name, is_doctor }

4. JITSI AUTHENTICATION
   JWT Token
        ↓
   Jitsi SDK (external_api.js)
        ↓
   Jitsi Server validation
        ↓
   WebRTC connection established
   Audio/Video streams flowing
   Participants can interact

5. EVENT LOGGING (CONTINUOUS)
   Jitsi events (participant_joined, video_muted, etc)
        ↓
   VideoCallJitsi component detects
        ↓
   POST /api/v1/video-sessions/{id}/jitsi-event
        ↓
   VideoParticipantLog (event recorded)
   VideoSessionEvent (if warning/error)

6. END SESSION
   User clicks End Call
        ↓
   VideoCallJitsi::endCall()
        ↓
   POST /api/v1/video-sessions/{id}/end
        ↓
   VideoSessionController::end()
        ↓
   VideoSessionService::endSession()
        ↓
   VideoSession (status='ended', ended_at=now, duration_seconds=X)
   VideoParticipantLog (participant left)
   VideoSessionEvent (logged: "Session ended")

7. ANALYTICS
   VideoSession (completed)
        ↓
   GET /api/v1/video-sessions/{id}/analytics
        ↓
   VideoSessionService::getSessionAnalytics()
        ↓
   Aggregate data:
   ├─ Duration
   ├─ Participants
   ├─ Events count
   ├─ Quality metrics
   └─ Timeline
        ↓
   Frontend displays analytics
```

---

## Technology Stack

```
FRONTEND:
  ├─ Vue 3 (Composition API)
  ├─ Vue Router
  ├─ Jitsi Meet External API (external_api.js)
  └─ Tailwind CSS

BACKEND:
  ├─ Laravel 10
  ├─ PHP 8.x
  ├─ Firebase JWT
  ├─ Sanctum (authentication)
  └─ MySQL 8.0

EXTERNAL:
  ├─ Jitsi Meet (https://meet.jit.si or self-hosted)
  ├─ WebRTC (for audio/video)
  └─ XMPP (for chat - Jitsi internal)

INFRASTRUCTURE:
  ├─ Redis (caching)
  ├─ MySQL (data storage)
  └─ CDN (Jitsi SDK delivery)
```

---

## Error Handling Strategy

```
Frontend Errors:
  ├─ SDK load failure → Show fallback UI
  ├─ Token request failure → Show error modal
  ├─ Network disconnection → Attempt reconnect
  ├─ Jitsi initialization failure → Log and display error
  └─ User permission denied → Show settings instruction

Backend Errors:
  ├─ Authorization failed → Return 403
  ├─ Session not found → Return 404
  ├─ Invalid token claims → Return 400
  ├─ Database error → Return 500 + log
  └─ Jitsi service down → Return 503

Recovery:
  ├─ Automatic token refresh (new token for reconnect)
  ├─ Event logging for debugging
  ├─ User notification on disconnection
  └─ Graceful fallback to text chat
```

---

**Architecture Version:** 1.0
**Last Updated:** 2024-01-15
**Status:** ✅ Production Ready
