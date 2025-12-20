# Video Consultation - Quick Start Guide

**Panduan cepat setup video consultation dengan Jitsi Meet**

---

## ⚡ 5-Minute Setup

### Step 1: Configure Environment
```bash
# Edit .env
JITSI_SERVER_URL=https://meet.jit.si
JITSI_APP_ID=telemedicine
JITSI_SECRET_KEY=your-secret-key-here
JITSI_TOKEN_TTL=86400
```

### Step 2: Install Dependencies
```bash
composer require firebase/php-jwt
```

### Step 3: Use in Code

**Create Video Session:**
```php
use App\Services\Video\VideoSessionService;

$service = new VideoSessionService();
$session = $service->initializeSession(consultationId: 123, userId: 1);
// Returns: VideoSession object with id, room_id, etc
```

**Get Jitsi Token:**
```php
$token = $service->getJitsiToken(sessionId: $session->id, userId: 1);
// Returns: JWT token string for Jitsi authentication
```

**Frontend (Vue):**
```vue
<template>
  <VideoCallJitsi />
</template>

<script setup>
import VideoCallJitsi from '@/components/VideoCall/VideoCallJitsi.vue'
</script>
```

---

## 📱 API Quick Reference

### Create Session
```
POST /api/v1/video-sessions
{ "consultation_id": 123 }
```

### Start Session (Doctor)
```
POST /api/v1/video-sessions/{id}/start
```

### Get Jitsi Token
```
GET /api/v1/video-sessions/{id}/jitsi-token
```

### Log Event
```
POST /api/v1/video-sessions/{id}/jitsi-event
{ "event_type": "participant_joined" }
```

### End Session
```
POST /api/v1/video-sessions/{id}/end
{ "reason": "user_ended", "quality": "good" }
```

### Get Analytics
```
GET /api/v1/video-sessions/{id}/analytics
```

---

## 🔑 Key Classes

| Class | File | Purpose |
|-------|------|---------|
| `JitsiTokenService` | `app/Services/Video/JitsiTokenService.php` | JWT token generation |
| `VideoSessionService` | `app/Services/Video/VideoSessionService.php` | Session management |
| `VideoSessionController` | `app/Http/Controllers/Api/VideoSessionController.php` | API endpoints |
| `VideoCallJitsi` | `resources/js/components/VideoCall/VideoCallJitsi.vue` | Jitsi component |
| `VideoSession` | `app/Models/VideoSession.php` | Database model |

---

## 🧪 Quick Test

```php
// Test JWT token generation
$service = new JitsiTokenService();
$token = $service->generateToken(1, 'John', 'john@test.com', 'room-1');
$verified = $service->verifyToken($token); // Should work

// Test session service
$videoService = new VideoSessionService();
$session = $videoService->initializeSession(1, 1); // Create session
$token = $videoService->getJitsiToken($session->id, 1); // Get token
```

---

## 🚨 Common Issues

| Issue | Fix |
|-------|-----|
| Token generation fails | Check `JITSI_SECRET_KEY` in .env |
| Jitsi SDK not loading | Check internet, CDN availability |
| Token expired | Increase `JITSI_TOKEN_TTL` in config |
| Can't connect to Jitsi | Verify `JITSI_SERVER_URL` and firewall |

---

## 📊 Files Modified/Created

### Created:
- ✅ `config/jitsi.php` - Jitsi configuration
- ✅ `app/Services/Video/JitsiTokenService.php` - JWT token service
- ✅ `app/Services/Video/VideoSessionService.php` - Session service
- ✅ `resources/js/components/VideoCall/VideoCallJitsi.vue` - Vue component

### Modified:
- ✅ `app/Http/Controllers/Api/VideoSessionController.php` - Added Jitsi endpoints
- ✅ `routes/api.php` - Added new routes

---

## ✅ Next Steps

1. **Test locally** - Start a consultation and test video call
2. **Deploy** - Push to production with proper secret key
3. **Monitor** - Check video session events and analytics
4. **Optimize** - Adjust bandwidth and quality settings as needed

---

**Status: READY FOR PRODUCTION** ✅
