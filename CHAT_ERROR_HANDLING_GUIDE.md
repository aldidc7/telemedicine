# Chat Error Handling & Message Persistence Guide

## Overview

This guide explains the comprehensive error handling system implemented for the telemedicine chat functionality. The system handles network failures, unstable connections, and provides users with clear feedback and retry options.

## Architecture

### Core Components

1. **`chatMessageStore.js`** - Pinia Store
   - Central message state management
   - Error tracking and status management
   - Automatic retry logic with exponential backoff
   - localStorage persistence
   - Network state detection

2. **`ConsultationChat.vue`** - Main Chat Component
   - User interface for chat
   - Error message display
   - Retry button controls
   - Network status indicators
   - Offline warning banner

3. **`useChatWebSocket.js`** - WebSocket Composable
   - Real-time message sync via Laravel Echo/Pusher
   - Fallback to polling when WebSocket unavailable
   - Basic retry mechanism

4. **`pesan.js`** - Message API Client
   - REST API calls for message operations
   - Backend communication

## Message Lifecycle

```
User sends message
         ↓
STATUS: 'pending' (⏳ pending icon)
Message queued in store
         ↓
Try send to server via API
         ↓
    ├─ SUCCESS ──→ STATUS: 'sent' (✓ sent icon)
    │                    ↓
    │            Sync from WebSocket
    │                    ↓
    │            STATUS: 'delivered' (✓✓ delivered icon)
    │                    ↓
    │            If recipient reads
    │                    ↓
    │            STATUS: 'read' (✓✓ read icon)
    │
    └─ FAILURE ──→ STATUS: 'failed' (✗ failed icon)
                        ↓
                 Error message stored
                        ↓
                Auto-retry starts (1s, 2s, 4s delays)
                        ↓
                 Retry button available to user
                        ↓
                User clicks "🔄 Coba Lagi" or auto-retry succeeds
```

## Message Status Types

```typescript
MESSAGE_STATUS = {
  PENDING: 'pending',       // ⏳ Waiting to send
  SENT: 'sent',            // ✓ Server acknowledged
  FAILED: 'failed',        // ✗ Send error occurred
  DELIVERED: 'delivered',  // ✓✓ Server confirmed receipt
  READ: 'read',           // ✓✓ Recipient read
}
```

## Error Handling Flow

### 1. Online Scenario (Stable Connection)

```
User clicks send
    ↓
Message status = 'pending' (⏳)
    ↓
Send to API endpoint POST /api/pesan
    ↓
Server returns {id, created_at, ...}
    ↓
Message status = 'sent' (✓)
    ↓
WebSocket event: 'new-message' received
    ↓
Message status = 'delivered' (✓✓)
```

### 2. Offline Scenario (No Internet)

```
User clicks send while offline
    ↓
navigator.onLine = false
    ↓
Message status = 'pending' (⏳)
    ↓
Offline warning banner shows:
"🔴 Anda sedang offline..."
    ↓
Message queued in localStorage
    ↓
When network reconnects
    ↓
Auto-retry with exponential backoff
    ↓
Message status = 'sent' (✓)
    ↓
Auto banner disappears
```

### 3. Network Failure Scenario (Send Failed)

```
User clicks send, network is flaky
    ↓
Message status = 'pending' (⏳)
    ↓
API request fails (timeout, 500, etc.)
    ↓
Error extracted: "Connection timeout"
    ↓
Message status = 'failed' (✗)
    ↓
Error block shows:
"⚠️ Connection timeout"
"Percobaan 1/3"
"🔄 Coba Lagi" button
    ↓
Auto-retry starts:
  - Delay 1s, retry 1
  - Delay 2s, retry 2
  - Delay 4s, retry 3
    ↓
If all retries fail:
"Percobaan 3/3"
    ↓
User clicks "🔄 Coba Lagi" manually
    ↓
Or network reconnects (auto-retry)
    ↓
Success → status = 'sent' (✓)
```

## Configuration

### Retry Settings

Edit `resources/js/stores/chatMessageStore.js`:

```javascript
const RETRY_CONFIG = {
  MAX_RETRIES: 3,        // Maximum retry attempts
  INITIAL_DELAY: 1000,   // First retry delay in ms
  BACKOFF_MULTIPLIER: 2, // Exponential backoff (1s → 2s → 4s)
}
```

### Auto-Retry Behavior

```javascript
// In chatMessageStore:
const autoRetryMessage = async (message) => {
  for (let attempt = 0; attempt < RETRY_CONFIG.MAX_RETRIES; attempt++) {
    const delay = RETRY_CONFIG.INITIAL_DELAY * 
                  Math.pow(RETRY_CONFIG.BACKOFF_MULTIPLIER, attempt)
    
    await new Promise(resolve => setTimeout(resolve, delay))
    
    try {
      // Send message...
      return // success
    } catch (error) {
      message.retryCount = attempt + 1
      // Continue to next retry
    }
  }
  // All retries exhausted, keep as failed
}
```

## User Interface

### Error Display

When a message fails to send:

```
┌─────────────────────────────────────────┐
│ Anda                               12:34 │ ✗ Failed
│                                         │
│ Halo dokter, bagaimana kelanjutannya?   │
│                                         │
│ ┌───────────────────────────────────┐   │
│ │ ⚠️ Connection timeout             │   │
│ │    Percobaan 1/3                  │   │
│ │ ┌─────────────────────────────────┤   │
│ │ │ 🔄 Coba Lagi                    │   │
│ │ └─────────────────────────────────┤   │
│ └───────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

### Offline Warning Banner

When offline:

```
┌──────────────────────────────────────────────────────┐
│ 🔴 Anda sedang offline. Pesan akan dikirim saat      │ 🔴 Offline
│    koneksi kembali.                                  │
└──────────────────────────────────────────────────────┘
```

### Failed Messages Summary

When multiple messages fail:

```
┌──────────────────────────────────────────────────────┐
│ ⚠️ 2 pesan gagal dikirim         │ 🔄 Kirim Semua    │
└──────────────────────────────────────────────────────┘
```

## Store API Reference

### `useChatMessageStore()`

#### State

```typescript
state: {
  messages: [],           // All messages with status
  failedMessages: [],    // Subset of failed
  messageQueue: [],      // Offline queue
  isOnline: true,        // Network status
  autoRetryEnabled: true // Auto-retry toggle
}
```

#### Main Methods

```javascript
const store = useChatMessageStore()

// Send a message
await store.sendMessage({
  consultationId: '123',
  message: 'Hello doctor',
  recipientId: '456',
})

// Retry a failed message
await store.retryMessage(messageId)

// Retry all failed messages
await store.retryAllMessages()

// Load messages from API
await store.loadMessages(consultationId)

// Mark message as read
await store.markMessageAsRead(messageId)

// Setup network detection
store.setupNetworkListeners()

// Clear localStorage
store.clearPersistentStorage()
```

#### Computed Properties

```javascript
store.failedMessages  // Array of failed messages
store.failedCount     // Number of failed messages
store.isOnline        // Current network status
store.hasUnsent       // Any pending/failed messages
```

## Using the Chat Component

### Basic Usage

```vue
<template>
  <ConsultationChat :consultation="consultation" />
</template>

<script setup>
import ConsultationChat from '@/components/ConsultationChat.vue'
import { ref } from 'vue'

const consultation = ref({
  id: '123',
  doctor_id: '456',
  patient_id: '789',
})
</script>
```

### Handling Message Events

```vue
<script setup>
import { useChatMessageStore } from '@/stores/chatMessageStore'
import { watch } from 'vue'

const chatStore = useChatMessageStore()

// Watch for message delivery
watch(
  () => chatStore.messages,
  (messages) => {
    const failedCount = messages.filter(m => m.status === 'failed').length
    if (failedCount > 0) {
      console.warn(`${failedCount} messages failed to send`)
    }
  },
  { deep: true }
)

// Listen for specific message
watch(
  () => chatStore.failedCount,
  (newCount) => {
    if (newCount > 0) {
      // Show notification to user
      showNotification(`${newCount} pesan gagal dikirim`)
    }
  }
)
</script>
```

## Testing Error Scenarios

### Test 1: Simulate Offline Mode

```javascript
// In browser console
window.navigator.onLine = false
// Send a message - it should show offline warning
window.navigator.onLine = true
// Message should auto-retry
```

### Test 2: Simulate Network Failure

Use browser DevTools:
1. Open Chrome DevTools → Network tab
2. Set throttling to "Offline"
3. Try to send message
4. Message should show error with retry button
5. Change to online
6. Message should auto-retry and succeed

### Test 3: Multiple Failed Messages

1. Set network to offline
2. Send 3 messages
3. All show pending status
4. Come back online
5. "🔄 Kirim Semua" button appears
6. Click it to retry all at once

### Test 4: Message Persistence

1. Send message and make it fail
2. Refresh page with F5
3. Failed message should still appear
4. Retry button should work
5. On success, message removed from persistence

### Test 5: Auto-Retry Backoff

```javascript
// In store, reduce delays for testing:
RETRY_CONFIG.INITIAL_DELAY = 100  // 100ms instead of 1s
// Watch console logs to see retry timing
```

## localStorage Format

Failed messages are stored in localStorage as:

```json
{
  "consultationChat_failedMessages": [
    {
      "id": "temp-1234",
      "consultationId": "123",
      "message": "Hello",
      "status": "failed",
      "error": "Network error",
      "retryCount": 2,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

## Debugging

### Enable Console Logging

In `chatMessageStore.js`, uncomment debug statements:

```javascript
const sendMessage = async (payload) => {
  console.log('📤 Sending message:', payload)
  try {
    const response = await api.pesan.create(payload)
    console.log('✅ Message sent:', response)
    return response
  } catch (error) {
    console.error('❌ Send failed:', error)
    throw error
  }
}
```

### Check Network Status

```javascript
const store = useChatMessageStore()
console.log('Online?', store.isOnline)
console.log('Failed messages:', store.failedMessages)
console.log('Failed count:', store.failedCount)
```

### Clear Persistent Storage

```javascript
const store = useChatMessageStore()
store.clearPersistentStorage()
localStorage.clear()
location.reload()
```

## Best Practices

1. **Always cleanup on unmount**: ConsultationChat.vue does this in `onUnmounted()`
2. **Don't disable auto-retry**: Users can manually retry if they want control
3. **Show status indicators**: Users need to know message state
4. **Provide clear error messages**: Explain WHY it failed
5. **Test with slow connections**: Use DevTools throttling

## Common Issues & Solutions

### Issue: Messages show as pending forever

**Cause**: WebSocket not connected, no polling happening

**Solution**: 
- Check network tab for WebSocket errors
- Verify Pusher credentials in config
- Check browser console for errors
- Restart application

### Issue: Offline warning never disappears

**Cause**: Network event listeners not properly attached

**Solution**:
```javascript
// In browser console
window.dispatchEvent(new Event('online'))
// Or refresh page
```

### Issue: Failed messages not persisting

**Cause**: localStorage quota exceeded

**Solution**:
```javascript
// Clear old data
localStorage.clear()
// Or use storage quota API
navigator.storage.estimate()
```

### Issue: Auto-retry not working

**Cause**: autoRetryEnabled = false or network listeners not set up

**Solution**:
```javascript
const store = useChatMessageStore()
store.autoRetryEnabled = true
store.setupNetworkListeners()
```

## Migration from Old Chat System

If upgrading from previous chat implementation:

1. **Backup existing messages**: Export localStorage
2. **Install new store**: chatMessageStore.js
3. **Update component imports**: Use new ConsultationChat.vue
4. **Test thoroughly**: Run all test scenarios above
5. **Deploy to staging**: Before production

## Performance Considerations

- **Message storage**: Limits to ~100 messages in localStorage (50KB limit)
- **Retry delays**: Default 1s, 2s, 4s - adjust RETRY_CONFIG if needed
- **Network listeners**: Cleaned up on component unmount
- **WebSocket**: Falls back to polling if unavailable
- **API calls**: Rate-limited by server, not client

## Future Enhancements

- [ ] Message encryption for sensitive data
- [ ] Offline message sync queue (more reliable than localStorage)
- [ ] File upload error handling
- [ ] Message read receipts
- [ ] Typing indicators
- [ ] Message search/history
- [ ] Export chat history
- [ ] Multi-device sync

## Support & Troubleshooting

For issues, check:
1. Browser console for errors
2. Network tab for API failures
3. Application tab for localStorage content
4. Store state in Vue DevTools
5. Server logs for backend errors

Contact: Development Team
