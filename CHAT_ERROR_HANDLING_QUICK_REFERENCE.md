# Chat Error Handling - Quick Reference

## What Was Changed?

### New Files Created
- **`resources/js/stores/chatMessageStore.js`** - Pinia store for message state management
- **`CHAT_ERROR_HANDLING_GUIDE.md`** - Comprehensive documentation

### Files Updated
- **`resources/js/components/ConsultationChat.vue`** - Enhanced with error handling UI

## Quick Start

### 1. Import the Store in Your Component

```javascript
import { useChatMessageStore } from '@/stores/chatMessageStore'

const chatStore = useChatMessageStore()
```

### 2. Send a Message

```javascript
// The store handles everything:
// - Tracking status (pending → sent → failed)
// - Automatic retry (1s, 2s, 4s delays)
// - localStorage persistence
// - Error extraction and display

await chatStore.sendMessage({
  consultationId: consultation.id,
  message: 'Hello doctor',
  recipientId: doctor.id,
})
```

### 3. Handle Failures

```javascript
// Manual retry
await chatStore.retryMessage(messageId)

// Retry all failed
await chatStore.retryAllMessages()

// Check failed count
console.log(chatStore.failedCount) // Number of failed messages
```

## Message Status Indicators

| Status | Icon | Color | Meaning |
|--------|------|-------|---------|
| pending | ⏳ | gray | Waiting to send |
| sent | ✓ | blue | Server received |
| delivered | ✓✓ | green | Confirmed |
| read | ✓✓ | green | Recipient read |
| failed | ✗ | red | Send error |

## UI Components

### Error Display (Auto Shows on Failure)

```
⚠️ Connection timeout
   Percobaan 1/3
┌─────────────────┐
│ 🔄 Coba Lagi    │
└─────────────────┘
```

### Offline Warning (Auto Shows When Offline)

```
🔴 Anda sedang offline. Pesan akan dikirim saat koneksi kembali.
```

### Failed Messages Banner (Auto Shows Multiple Failures)

```
⚠️ 2 pesan gagal dikirim    │ 🔄 Kirim Semua │
```

## Configuration

### Change Retry Behavior

Edit `resources/js/stores/chatMessageStore.js`:

```javascript
const RETRY_CONFIG = {
  MAX_RETRIES: 3,              // ← Change maximum retries
  INITIAL_DELAY: 1000,         // ← Change first delay (ms)
  BACKOFF_MULTIPLIER: 2,       // ← Change backoff multiplier
}
```

Examples:
- **Aggressive retry**: MAX_RETRIES: 5, INITIAL_DELAY: 500
- **Gentle retry**: MAX_RETRIES: 2, INITIAL_DELAY: 2000
- **No backoff**: BACKOFF_MULTIPLIER: 1

## Common Tasks

### Check if Online

```javascript
const store = useChatMessageStore()
if (store.isOnline) {
  console.log('User is online')
} else {
  console.log('User is offline - messages will retry')
}
```

### Get Failed Messages

```javascript
const store = useChatMessageStore()
const failed = store.failedMessages
failed.forEach(msg => {
  console.log(`Message "${msg.message}" failed: ${msg.error}`)
})
```

### Enable/Disable Auto-Retry

```javascript
const store = useChatMessageStore()
store.autoRetryEnabled = false // Disable auto-retry
store.autoRetryEnabled = true  // Re-enable
```

### Clear Failed Messages

```javascript
const store = useChatMessageStore()
store.clearPersistentStorage() // Clear localStorage
```

### Force Send Now

```javascript
const store = useChatMessageStore()
// Manually retry without waiting for backoff
await store.retryMessage(messageId)
```

## Testing in Browser Console

```javascript
// Get store
const store = useChatMessageStore()

// Simulate offline
window.navigator.onLine = false

// Send message (should queue)
await store.sendMessage({...})

// Check status
console.log(store.messages[0].status) // pending

// Come back online
window.navigator.onLine = true
window.dispatchEvent(new Event('online'))

// Message should auto-retry
setTimeout(() => {
  console.log(store.messages[0].status) // should be 'sent'
}, 5000)
```

## Debugging with Vue DevTools

1. Open Vue DevTools
2. Go to Pinia tab
3. Select `chatMessageStore`
4. View state:
   - `messages` - all messages with status
   - `failedMessages` - only failures
   - `isOnline` - network status
   - `autoRetryEnabled` - retry toggle

## Error Messages Users Will See

| Error | Cause | Solution |
|-------|-------|----------|
| "Connection timeout" | Network too slow | Auto-retries, click "Coba Lagi" |
| "Network error" | Connection failed | App will retry when online |
| "Server error" | 500 error | Will retry, or contact support |
| "Invalid message" | Missing content | Edit message and resend |

## Architecture Overview

```
ConsultationChat.vue (UI)
        ↓
   useChatMessageStore (State)
        ↓
    pesan.js (API)
        ↓
   Laravel Backend
```

**Flow:**
1. User types and clicks send
2. Component calls `store.sendMessage()`
3. Store validates and queues message
4. Store calls API via `pesan.js`
5. If fails → extracts error → auto-retry with backoff
6. If succeeds → WebSocket syncs with other clients
7. User sees status: ⏳ → ✓ → ✓✓

## Files Reference

| File | Purpose | Modified |
|------|---------|----------|
| `chatMessageStore.js` | Message state & logic | ✨ NEW |
| `ConsultationChat.vue` | Chat UI component | ✅ Enhanced |
| `useChatWebSocket.js` | WebSocket composable | (unchanged) |
| `pesan.js` | Message API client | (unchanged) |

## Key Features

✅ **Offline Detection** - Automatic online/offline detection
✅ **Auto Retry** - Exponential backoff (1s → 2s → 4s)
✅ **Message Persistence** - Failed messages saved to localStorage
✅ **Error Display** - User-friendly error messages
✅ **Manual Retry** - "🔄 Coba Lagi" button
✅ **Batch Retry** - "🔄 Kirim Semua" for multiple failures
✅ **Status Indicators** - Visual status icons (⏳ ✓ ✗)
✅ **Network Warning** - Banner when offline

## Integration Checklist

- [ ] Add import: `import { useChatMessageStore } from '@/stores/chatMessageStore'`
- [ ] Call `store.setupNetworkListeners()` in component mount
- [ ] Update send handler to use `store.sendMessage()`
- [ ] Test with network offline (DevTools Network tab)
- [ ] Test retry button click
- [ ] Test "Kirim Semua" with multiple failures
- [ ] Check localStorage after page reload
- [ ] Deploy to staging for testing

## Performance Notes

- Store uses Pinia composition API (lightweight)
- localStorage limit: ~50KB (stores ~100 messages)
- Network checks: HTML5 API (zero overhead)
- Retry delays: Exponential backoff (configurable)
- WebSocket fallback: Polling every 3 seconds

## Support

For detailed documentation, see: **CHAT_ERROR_HANDLING_GUIDE.md**

Quick help:
```javascript
// In browser console, check state
const s = pinia.state.value.chatMessageStore
console.log('Failed:', s.failedMessages.length)
console.log('Online:', s.isOnline)
```
