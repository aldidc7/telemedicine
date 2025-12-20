# Phase 5D: WebSocket Integration Setup Guide

## Overview

Phase 5D implements real-time WebSocket notifications using Laravel Echo and Pusher. This enables:
- ✅ Real-time notification delivery
- ✅ Live appointment updates
- ✅ Instant consultation status changes
- ✅ Real-time message delivery
- ✅ User presence tracking (who's online)

---

## Architecture

### Backend Components
1. **Broadcast Events** - Define what events trigger WebSocket broadcasting
   - `NotificationBroadcast` - User notifications
   - `ConsultationStatusBroadcast` - Consultation updates
   - `AppointmentUpdateBroadcast` - Appointment changes
   - `MessageBroadcast` - Chat messages

2. **Broadcast Channels**
   - Private: `private-notifications.{userId}` - User-specific notifications
   - Private: `private-consultations.{consultationId}` - Consultation updates
   - Private: `private-appointments.{userId}` - Appointment updates
   - Private: `private-conversation.{conversationId}` - Messages

3. **Broadcasting Service** - `BroadcastService`
   - Handles bulk broadcasting
   - Channel subscription management
   - Presence tracking

### Frontend Components
1. **useWebSocket** Composable
   - Initializes Laravel Echo
   - Manages connections
   - Handles reconnections
   - Event listener registration

2. **useNotifications** Composable
   - Notification state management
   - Real-time updates from WebSocket
   - API integration

3. **NotificationListener** Component
   - Background WebSocket listener
   - Auto-sync on reconnect
   - Browser notification support

4. **NotificationCenter & NotificationBell** Components
   - Display notifications
   - Handle user interactions
   - Real-time badge updates

---

## Setup Instructions

### Step 1: Environment Configuration

Add these variables to your `.env` file:

```env
# Pusher Configuration (Use one of the options below)

# Option 1: Use Pusher (Recommended for Production)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
VITE_PUSHER_APP_KEY=your_app_key
VITE_PUSHER_APP_CLUSTER=mt1

# Option 2: Use Redis (Recommended for Development)
BROADCAST_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Option 3: Development without WebSocket (Logs only)
BROADCAST_DRIVER=log
```

### Step 2: Install Pusher (if using Pusher)

```bash
npm install pusher-js laravel-echo
```

Or with composer for PHP:

```bash
composer require pusher/pusher-php-server
```

### Step 3: Enable Broadcasting

Ensure your `config/broadcasting.php` has proper configuration:

```php
'default' => env('BROADCAST_DRIVER', 'pusher'),

'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true,
        ],
    ],
    // ... other configs
]
```

### Step 4: Configure Event Broadcasting

Ensure your broadcast events implement `ShouldBroadcast`:

```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MyEvent implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
```

### Step 5: Frontend Setup

The frontend is already configured in `bootstrap-websocket.js`. Components using WebSocket:

1. **In App.vue** (Root component):
```vue
<NotificationListener 
  v-if="authStore.isAuthenticated"
  :user-id="authStore.user.id"
  @notification-received="handleNewNotification"
/>
```

2. **In any component**:
```vue
<script setup>
import { useNotifications } from '@/composables/useNotifications'

const { notifications, unreadCount, markAsRead } = useNotifications()
</script>
```

---

## Broadcast Channels Reference

### Private Channels (Authenticated Only)

#### 1. User Notifications Channel
```
Channel: private-notifications.{userId}
Events:
  - general-notification (NotificationBroadcast)
Payload:
  {
    id: number,
    user_id: number,
    type: string,
    title: string,
    message: string,
    action_url: string,
    created_at: ISO8601
  }
```

#### 2. Consultation Channel
```
Channel: private-consultations.{consultationId}
Events:
  - consultation-status-changed (ConsultationStatusBroadcast)
Payload:
  {
    id: number,
    status: string,        // 'pending', 'active', 'completed', 'cancelled'
    doctor_id: number,
    patient_id: number,
    started_at: ISO8601,
    ended_at: ISO8601,
    timestamp: ISO8601
  }
```

#### 3. Appointment Channel
```
Channel: private-appointments.{userId}
         private-appointments.doctor.{doctorId}
Events:
  - appointment-updated (AppointmentUpdateBroadcast)
Payload:
  {
    id: number,
    action: string,        // 'confirmed', 'cancelled', 'reminder'
    status: string,
    appointment_date: ISO8601,
    details: object,
    timestamp: ISO8601
  }
```

#### 4. Message Channel
```
Channel: private-conversation.{conversationId}
Events:
  - message-sent (MessageBroadcast)
  - message-read (MessageBroadcast)
Payload:
  {
    conversation_id: number,
    type: string,          // 'sent', 'read', 'typing'
    data: object,
    timestamp: ISO8601
  }
```

### Presence Channels (Track Online Users)

```
Channel: presence-clinic.{clinicId}
Methods:
  - here() - Get list of online users
  - joining() - User came online
  - leaving() - User went offline
```

---

## API Integration

### Broadcasting from Backend

**Example 1: Send notification and broadcast**
```php
// In your service or controller
$notification = Notification::create([
    'user_id' => 123,
    'type' => 'appointment_update',
    'title' => 'Appointment Confirmed',
    'message' => 'Your appointment is confirmed',
]);

// Broadcast notification
broadcast(new NotificationBroadcast($notification));
```

**Example 2: Broadcast appointment update**
```php
$appointment = Appointment::findOrFail($id);
broadcast(new AppointmentUpdateBroadcast($appointment, 'confirmed'));
```

**Example 3: Using NotificationService**
```php
$this->notificationService->broadcastAppointmentUpdate($appointment, 'confirmed');
$this->notificationService->broadcastMessage($conversationId, 'sent', ['message' => '...']);
```

---

## Frontend Usage

### Using useWebSocket Composable

```vue
<script setup>
import { useWebSocket } from '@/composables/useWebSocket'

const { onNotification, onAppointmentUpdate, isConnected } = useWebSocket()

// Listen for notifications
onNotification((notification) => {
  console.log('New notification:', notification)
})

// Listen for appointment updates
onAppointmentUpdate((data) => {
  console.log('Appointment updated:', data)
})
</script>
```

### Using useNotifications Composable

```vue
<script setup>
import { useNotifications } from '@/composables/useNotifications'

const {
  notifications,
  unreadCount,
  markAsRead,
  deleteNotification,
  hasUnread
} = useNotifications()

// Automatically syncs with WebSocket
</script>

<template>
  <div>
    <p>{{ unreadCount }} unread notifications</p>
    <div v-for="notif in notifications" :key="notif.id">
      {{ notif.title }}: {{ notif.message }}
      <button @click="markAsRead(notif.id)">Read</button>
    </div>
  </div>
</template>
```

---

## Testing WebSocket

### 1. Check Pusher Connection (Browser Console)

```javascript
// Check if Echo is initialized
import { useWebSocket } from '@/composables/useWebSocket'
const { isConnected } = useWebSocket()
console.log('Connected:', isConnected.value)
```

### 2. Monitor Pusher Channel (Pusher Dashboard)

1. Go to Pusher Dashboard
2. Select your app
3. Go to "Debug console"
4. Select the channel to monitor
5. Trigger events and watch them appear in real-time

### 3. Test Broadcasting (Artisan Tinker)

```bash
php artisan tinker

# Create test notification and broadcast
$user = User::find(1);
$notification = Notification::create([
    'user_id' => $user->id,
    'type' => 'test',
    'title' => 'Test Notification',
    'message' => 'This is a test broadcast'
]);
broadcast(new \App\Events\NotificationBroadcast($notification));
```

---

## Troubleshooting

### WebSocket Not Connecting

**Symptom:** Console shows "WebSocket disabled" or connection never established

**Solutions:**
1. Check `.env` has `VITE_PUSHER_APP_KEY` set
2. Verify Pusher app credentials are correct
3. Check browser console for specific errors
4. Try using `BROADCAST_DRIVER=log` for development

### Notifications Not Appearing

**Symptom:** Notification created but doesn't appear in real-time

**Solutions:**
1. Verify `NotificationListener` component is mounted
2. Check browser console for WebSocket errors
3. Verify user authentication (broadcast channels are private)
4. Check Pusher Dashboard for broadcast events
5. Verify event class implements `ShouldBroadcast`

### High Latency

**Symptom:** Notifications appear after noticeable delay

**Solutions:**
1. Switch from Pusher (cloud) to Redis (local) for development
2. Reduce `auto-refresh` interval in NotificationListener
3. Check network latency to Pusher cluster
4. Verify no rate limiting is applied

### Redis Queue Issues (if using Redis)

```bash
# Start Redis
redis-server

# Monitor Redis
redis-cli monitor

# Start queue listener
php artisan queue:listen
```

---

## Security Considerations

### Private Channels

All notification channels are **private** - only authenticated users can subscribe:

```php
// Users can only listen to their own channel
Channel::private('private-notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### Presence Channels

Presence channels track who's online without exposing data:

```php
Channel::presence('presence-clinic.{clinicId}', function ($user, $clinicId) {
    // Only clinic members can see presence
    return $user->clinic_id == $clinicId;
});
```

---

## Production Deployment

### 1. Use Pusher (Recommended)

```bash
# Install
composer require pusher/pusher-php-server
npm install pusher-js laravel-echo

# Configure .env
BROADCAST_DRIVER=pusher
PUSHER_APP_KEY=...
PUSHER_APP_SECRET=...
PUSHER_APP_ID=...
PUSHER_APP_CLUSTER=us2  # or your region
```

### 2. Use Redis (Self-hosted)

```bash
# Install
composer require predis/predis

# Configure .env
BROADCAST_DRIVER=redis
REDIS_HOST=...
REDIS_PASSWORD=...
REDIS_PORT=6379

# Start queue listener (in separate process)
php artisan queue:listen redis
```

### 3. Enable Queued Broadcasting (For Large Deployments)

```php
// config/broadcasting.php
'connections' => [
    'pusher' => [
        // ...
        'queue' => true,  // Queue broadcast events
    ]
]
```

### 4. Monitor & Scale

- Set up background worker processes for queue listener
- Monitor WebSocket connections (Pusher metrics)
- Use load balancer for multiple servers
- Enable Redis persistence for fault tolerance

---

## Files Created/Modified

### Files Created (Phase 5D)
- `resources/js/composables/useWebSocket.js` - Enhanced
- `resources/js/composables/useNotifications.js` - New
- `resources/js/components/Notification/NotificationListener.vue` - New
- `app/Events/NotificationBroadcast.php` - New
- `app/Events/ConsultationStatusBroadcast.php` - New
- `app/Events/AppointmentUpdateBroadcast.php` - New
- `app/Events/MessageBroadcast.php` - New

### Files Modified
- `app/Services/NotificationService.php` - Added broadcast methods
- `resources/js/App.vue` - Includes NotificationListener
- `bootstrap-websocket.js` - Pusher configuration

---

## Next Steps

1. **Configure Pusher/Redis** in `.env`
2. **Test locally** with `BROADCAST_DRIVER=log`
3. **Deploy to staging** with Pusher or Redis
4. **Load test** with 100+ concurrent users
5. **Monitor** Pusher Dashboard or Redis metrics
6. **Deploy to production** with proper monitoring

---

## Support & Documentation

- [Pusher Documentation](https://pusher.com/docs)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Echo](https://laravel.com/docs/echo)
- [Redis Configuration](https://redis.io/documentation)

---

**Status:** ✅ Phase 5D Implementation Complete
**Last Updated:** December 20, 2025
**System Ready:** For Beta Testing with WebSocket
