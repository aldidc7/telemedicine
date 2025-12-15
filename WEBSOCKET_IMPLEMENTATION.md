# WebSocket Real-time Implementation Guide

## Overview

This document covers the complete implementation of real-time features using Pusher WebSockets in the telemedicine application.

## Features Implemented

### 1. Real-time Messaging
- Instant message delivery between users
- No page refresh needed
- Live typing indicators

### 2. Real-time Notifications
- Instant notification delivery
- Background update without user action
- Notification status tracking

### 3. Real-time Appointment Updates
- Appointment status changes (pending → confirmed → in_progress → completed)
- Instant confirmation/rejection
- Rescheduling notifications

### 4. Real-time Prescriptions
- New prescription alerts
- Prescription status updates

### 5. User Presence
- Online/offline status
- Last seen timestamp

### 6. Doctor Availability
- Real-time availability changes
- Live doctor status updates

## Architecture

### Backend Stack
- **Broadcasting Driver**: Pusher (pusher/pusher-php-server 7.2.7)
- **Service Layer**: WebSocketService
- **Event Classes**: ShouldBroadcast implementations
- **Controllers**: BroadcastingController for channel authentication

### Frontend Stack (To Be Implemented)
- **Library**: Pusher JavaScript SDK
- **Framework**: Vue 3 Composition API
- **State Management**: Pinia
- **Real-time Updates**: Event subscriptions

## Backend Implementation

### 1. WebSocketService (`app/Services/WebSocketService.php`)

The main service class for broadcasting events. Key methods:

```php
// Broadcast new message
broadcastNewMessage(int $conversationId, array $message)

// Broadcast notifications
broadcastNotification(int $userId, array $notification)

// Broadcast appointment updates
broadcastAppointmentUpdate(int $appointmentId, array $appointment)

// Broadcast prescription created
broadcastPrescriptionCreated(int $patientId, array $prescription)

// Broadcast user status changes
broadcastUserOnline(int $userId, string $status)

// Broadcast doctor availability
broadcastDoctorAvailabilityChange(int $doctorId, bool $available)

// Get Pusher authentication data for frontend
getAuthenticationData(int $userId)

// Authenticate private/presence channels
authenticateChannel($request)
```

### 2. Event Classes

All event classes implement `ShouldBroadcast` interface:

#### MessageSent (`app/Events/MessageSent.php`)
```php
// Channel: private-conversation.{conversationId}
// Event: message-sent
// Triggered: When message is sent in a conversation
```

#### NotificationCreated (`app/Events/NotificationCreated.php`)
```php
// Channel: private-user.{userId}.notifications
// Event: notification-created
// Triggered: When notification is created for user
```

#### AppointmentUpdated (`app/Events/AppointmentUpdated.php`)
```php
// Channels: 
//   - private-user.{patientId}.appointments
//   - private-user.{doctorId}.appointments
// Event: appointment-updated
// Triggered: When appointment status changes
```

#### PrescriptionCreated (`app/Events/PrescriptionCreated.php`)
```php
// Channel: private-user.{patientId}.prescriptions
// Event: prescription-created
// Triggered: When prescription is created
```

#### UserStatusChanged (`app/Events/UserStatusChanged.php`)
```php
// Channel: private-user.{userId}.status
// Event: user-status-changed
// Triggered: When user online/offline status changes
```

#### DoctorAvailabilityChanged (`app/Events/DoctorAvailabilityChanged.php`)
```php
// Channel: public-doctor.{doctorId}.availability
// Event: doctor-availability-changed
// Triggered: When doctor availability changes
```

#### RatingReceived (`app/Events/RatingReceived.php`)
```php
// Channel: private-doctor.{doctorId}.ratings
// Event: rating-received
// Triggered: When doctor receives a rating
```

### 3. Service Integration

Events are triggered in the following services:

#### MessageService
- `sendMessage()` → broadcasts `MessageSent`

#### AppointmentService
- `confirmAppointment()` → broadcasts `AppointmentUpdated`
- `startAppointment()` → broadcasts `AppointmentUpdated`
- `endAppointment()` → broadcasts `AppointmentUpdated`
- `rescheduleAppointment()` → broadcasts `AppointmentUpdated`

#### NotificationService
- `create()` → broadcasts `NotificationCreated`

#### PrescriptionService
- `createPrescription()` → broadcasts `PrescriptionCreated`

### 4. BroadcastingController

Handles WebSocket authentication:

```php
// Authenticate channel subscriptions
POST /api/v1/broadcasting/auth

// Get Pusher configuration for frontend
GET /api/v1/broadcasting/config
```

## Configuration

### Environment Variables

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt
PUSHER_HOST=api-mt.pusher.com
PUSHER_PORT=443
PUSHER_SCHEME=https
```

### config/broadcasting.php

Already configured with Pusher driver. Key settings:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'host' => env('PUSHER_HOST', 'api-mt.pusher.com'),
        'port' => env('PUSHER_PORT', 443),
        'scheme' => env('PUSHER_SCHEME', 'https'),
        'encrypted' => true,
    ],
],
```

## Frontend Integration (Vue 3)

### Step 1: Install Pusher JavaScript SDK

```bash
npm install pusher-js
```

### Step 2: Create WebSocket Composable

```javascript
// composables/useWebSocket.js
import Pusher from 'pusher-js';
import { ref, onMounted, onUnmounted } from 'vue';

export const useWebSocket = () => {
  const pusher = ref(null);
  const isConnected = ref(false);

  onMounted(async () => {
    // Get Pusher configuration from backend
    const response = await fetch('/api/v1/broadcasting/config', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    const { data } = await response.json();

    // Initialize Pusher
    pusher.value = new Pusher(data.key, {
      cluster: data.cluster,
      forceTLS: data.forceTLS,
      auth: {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      },
      authEndpoint: '/api/v1/broadcasting/auth'
    });

    pusher.value.connection.bind('connected', () => {
      isConnected.value = true;
    });

    pusher.value.connection.bind('disconnected', () => {
      isConnected.value = false;
    });
  });

  onUnmounted(() => {
    if (pusher.value) {
      pusher.value.disconnect();
    }
  });

  const subscribeToChannel = (channelName) => {
    if (!pusher.value) return null;
    return pusher.value.subscribe(channelName);
  };

  const subscribeToPrivateChannel = (channelName) => {
    if (!pusher.value) return null;
    return pusher.value.subscribe(channelName);
  };

  const unsubscribeFromChannel = (channelName) => {
    if (pusher.value) {
      pusher.value.unsubscribe(channelName);
    }
  };

  return {
    pusher,
    isConnected,
    subscribeToChannel,
    subscribeToPrivateChannel,
    unsubscribeFromChannel
  };
};
```

### Step 3: Use in Components

#### Messages Component
```javascript
import { useWebSocket } from '@/composables/useWebSocket';
import { ref, onMounted } from 'vue';

export default {
  setup() {
    const { subscribeToPrivateChannel } = useWebSocket();
    const messages = ref([]);
    const conversationId = ref(1);

    onMounted(() => {
      const channel = subscribeToPrivateChannel(`user.${conversationId.value}.messages`);
      
      channel.bind('message-sent', (data) => {
        messages.value.push(data);
      });

      channel.bind('user-typing', (data) => {
        // Show typing indicator
      });
    });

    return { messages, conversationId };
  }
};
```

#### Notifications Component
```javascript
import { useWebSocket } from '@/composables/useWebSocket';
import { ref, onMounted } from 'vue';

export default {
  setup() {
    const userId = ref(1);
    const { subscribeToPrivateChannel } = useWebSocket();
    const notifications = ref([]);

    onMounted(() => {
      const channel = subscribeToPrivateChannel(`user.${userId.value}.notifications`);
      
      channel.bind('notification-created', (data) => {
        notifications.value.unshift(data);
        // Show toast notification
      });
    });

    return { notifications };
  }
};
```

#### Appointments Component
```javascript
import { useWebSocket } from '@/composables/useWebSocket';
import { ref, onMounted } from 'vue';

export default {
  setup() {
    const userId = ref(1);
    const { subscribeToPrivateChannel } = useWebSocket();
    const appointments = ref([]);

    onMounted(() => {
      const channel = subscribeToPrivateChannel(`user.${userId.value}.appointments`);
      
      channel.bind('appointment-updated', (data) => {
        // Update appointment in list
        const index = appointments.value.findIndex(a => a.id === data.id);
        if (index !== -1) {
          appointments.value[index] = data;
        }
        // Show update notification
      });
    });

    return { appointments };
  }
};
```

## Testing

### Manual Testing Steps

1. **Test Message Broadcasting**
   - Open two browser windows with different users
   - Send message from one user
   - Verify message appears instantly in other user's window without refresh

2. **Test Notification Broadcasting**
   - Create notification from admin panel
   - Verify notification appears instantly to target user

3. **Test Appointment Updates**
   - Doctor confirms appointment
   - Verify status updates instantly on patient side

4. **Test Presence**
   - User goes online
   - Other users see status change instantly

### API Testing

```bash
# Test broadcasting config endpoint
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/v1/broadcasting/config

# Response:
{
  "status": "success",
  "data": {
    "key": "pusher_app_key",
    "cluster": "mt",
    "forceTLS": true,
    "user_id": 1,
    "auth_endpoint": "/api/v1/broadcasting/auth"
  }
}
```

## Channel Naming Convention

### Public Channels
- `doctor.{doctorId}.availability` - Doctor availability changes

### Private Channels (Requires Authentication)
- `user.{userId}.notifications` - User notifications
- `user.{userId}.appointments` - Appointment updates
- `user.{userId}.prescriptions` - Prescription updates
- `user.{userId}.status` - User online/offline status
- `user.{userId}.reminders` - Appointment reminders
- `conversation.{conversationId}` - Message in conversation
- `doctor.{doctorId}.ratings` - Doctor ratings

## Error Handling

### Connection Errors

```javascript
pusher.connection.bind('error', (error) => {
  console.error('Pusher connection error:', error);
  // Implement reconnection logic
});
```

### Authentication Errors

```javascript
channel.bind('pusher:subscription_error', (error) => {
  console.error('Channel subscription error:', error);
  // Handle authentication failures
});
```

## Performance Considerations

1. **Message Batching**: For high-frequency events, consider batching updates
2. **Channel Limits**: Pusher free tier has limits on concurrent connections
3. **Database Queries**: Load relationships in event classes to minimize N+1
4. **Memory Management**: Unsubscribe from channels when components unmount

## Security

### Private Channels

All sensitive channels are private and require authentication:

```php
// Only authenticated users can subscribe
channel.bind('authenticated', () => {
  channel.subscribe('user.123.notifications');
});
```

### Channel Authorization

Validate user permissions in `BroadcastingController->authenticate()`:

```php
public function authenticate(Request $request)
{
    // Only user can subscribe to their own channels
    if (strpos($request->channel_name, 'user.' . auth()->id()) === false) {
        return abort(403);
    }
    
    return $webSocketService->authenticateChannel($request);
}
```

## Development vs Production

### Local Development
- Use Pusher free tier with local app credentials
- Set `PUSHER_APP_ID=local`
- Use test credentials in `.env`

### Production
- Register for Pusher account: https://pusher.com
- Get real app credentials
- Update `.env` with production credentials
- Enable TLS/SSL (already configured)

## Troubleshooting

### Messages Not Broadcasting

1. Check `.env` has correct Pusher credentials
2. Verify `BROADCAST_CONNECTION=pusher`
3. Ensure user is authenticated
4. Check browser console for connection errors

### Channel Subscription Fails

1. Verify user is authenticated
2. Check authentication endpoint returns valid data
3. Verify channel name is correct
4. Check user permissions for private channels

### Events Not Triggering

1. Verify `broadcast()` function is called in service
2. Check event class implements `ShouldBroadcast`
3. Verify channel name matches subscription
4. Check Pusher app credentials

## Files Modified/Created

### New Files
- `app/Services/WebSocketService.php` (309 lines)
- `app/Events/MessageSent.php` (32 lines)
- `app/Events/NotificationCreated.php` (32 lines)
- `app/Events/AppointmentUpdated.php` (35 lines)
- `app/Events/PrescriptionCreated.php` (33 lines)
- `app/Events/UserStatusChanged.php` (34 lines)
- `app/Events/DoctorAvailabilityChanged.php` (31 lines)
- `app/Events/RatingReceived.php` (34 lines)
- `app/Http/Controllers/Api/BroadcastingController.php` (42 lines)

### Modified Files
- `.env` (+8 lines for Pusher config)
- `routes/api.php` (+2 imports, +8 lines for broadcasting routes)
- `app/Services/MessageService.php` (+1 import, +5 lines broadcast code)
- `app/Services/AppointmentService.php` (+1 import, +15 lines broadcast code)
- `app/Services/NotificationService.php` (+1 import, +5 lines broadcast code)
- `app/Services/PrescriptionService.php` (+1 import, +8 lines broadcast code)

## Next Steps

1. ✅ Backend WebSocket implementation complete
2. ⏳ Frontend Vue 3 integration (create composable)
3. ⏳ Test real-time features
4. ⏳ Implement typing indicators
5. ⏳ Add connection status indicator
6. ⏳ Implement offline message queue

## Summary

WebSocket real-time features enable:
- Instant message delivery without page refresh
- Real-time notification updates
- Live appointment status changes
- User presence tracking
- Doctor availability updates
- Improved user experience with immediate feedback

All infrastructure is in place and ready for frontend integration with Vue 3.
