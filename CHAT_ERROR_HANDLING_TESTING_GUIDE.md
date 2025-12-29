# Chat Error Handling - Testing Guide

## Test Scenarios

### Scenario 1: Successful Message Send (Happy Path)

**Precondition**: User is online, internet is stable

**Steps**:
1. Open chat with doctor
2. Type message: "Halo dokter"
3. Click send
4. Observe: Message appears with ⏳ pending icon
5. Wait 1-2 seconds
6. Observe: Status changes to ✓ sent icon
7. Observe: WebSocket event received (✓✓ delivered)

**Expected Result**: ✅ PASS
- Message shows as delivered
- No error message
- No retry button
- Status sequence: ⏳ → ✓ → ✓✓

**Duration**: 2-3 seconds

---

### Scenario 2: Send While Offline

**Precondition**: Network available

**Steps**:
1. Open browser DevTools (F12)
2. Go to Network tab → set Throttling to "Offline"
3. Type message: "Pesan saat offline"
4. Click send
5. Observe: Offline warning banner appears
6. Observe: Message shows ⏳ pending icon
7. Set throttling back to "Online"
8. Observe: Offline banner disappears
9. Wait 2-3 seconds
10. Observe: Message auto-retries and changes to ✓ sent

**Expected Result**: ✅ PASS
- Offline warning shows immediately
- Message queued (⏳)
- Auto-retries on reconnection
- Eventually shows ✓ sent

**Duration**: 5-10 seconds

---

### Scenario 3: Manual Retry After Send Failure

**Precondition**: Network available, but API server is slow/unreliable

**Steps**:
1. Simulate slow network: DevTools → Network → set to "2G"
2. Type message: "Pesan dengan jaringan lambat"
3. Click send quickly twice (rapid clicks)
4. Observe: Status ⏳ pending
5. Wait for timeout (should happen with 2G throttle)
6. Observe: Message status changes to ✗ failed
7. Observe: Error message shows (e.g., "Connection timeout")
8. Observe: "🔄 Coba Lagi" button appears
9. Click "🔄 Coba Lagi" button
10. Observe: Button shows "⏳ Mengirim ulang..."
11. Set throttling back to "No throttling"
12. Wait
13. Observe: Message status changes to ✓ sent

**Expected Result**: ✅ PASS
- Error detected correctly
- Retry button clickable
- Manual retry works
- Message eventually succeeds

**Duration**: 10-15 seconds

---

### Scenario 4: Automatic Retry with Exponential Backoff

**Precondition**: Network available but unreliable

**Setup**:
Edit `chatMessageStore.js` temporarily:
```javascript
const RETRY_CONFIG = {
  MAX_RETRIES: 3,
  INITIAL_DELAY: 500,      // Reduced from 1000 for faster testing
  BACKOFF_MULTIPLIER: 2,
}
```

**Steps**:
1. Open browser console (F12)
2. Paste this to watch retry timing:
```javascript
const store = pinia.state.value.chatMessageStore
setInterval(() => {
  const failed = store.failedMessages
  if (failed.length > 0) {
    console.log(`⏳ Retry count: ${failed[0].retryCount}`)
  }
}, 500)
```
3. Simulate network issue: DevTools → Network → "Offline"
4. Send message: "Test auto-retry"
5. Observe: Message ⏳ pending
6. After 2 seconds, turn network online: Throttling → "No throttling"
7. Watch console logs for retry count incrementing

**Expected Result**: ✅ PASS
- Retry count increases: 0 → 1 → 2 → 3
- Delays approximately: 500ms → 1000ms → 2000ms
- After retry 3, stops and shows as failed
- User can click "Coba Lagi" for manual retry

**Duration**: 5-10 seconds

---

### Scenario 5: Multiple Failed Messages - Batch Retry

**Precondition**: Network available, but API is down

**Setup**:
1. Create a mock API that returns 500 error
   OR simulate in DevTools by blocking POST to /api/pesan

**Steps**:
1. Turn network to offline
2. Send 3 messages:
   - "Pesan pertama"
   - "Pesan kedua"
   - "Pesan ketiga"
3. All show ⏳ pending
4. Turn network online
5. Wait 5 seconds
6. Observe: "⚠️ 3 pesan gagal dikirim" banner appears
7. Observe: "🔄 Kirim Semua" button on right
8. Click "🔄 Kirim Semua"
9. Observe: Button shows "⏳ Mengirim ulang..."
10. Wait
11. Observe: All messages change to ✓ sent

**Expected Result**: ✅ PASS
- Failed messages count displays correctly
- "Kirim Semua" button available
- Batch retry sends all messages
- All messages eventually succeed

**Duration**: 8-12 seconds

---

### Scenario 6: Message Persistence Across Page Reload

**Precondition**: Network available initially

**Steps**:
1. Turn network to offline
2. Send message: "Pesan yang tidak terkirim"
3. Observe: Message ⏳ pending, offline warning shows
4. Press F5 to reload page
5. Wait for page to fully load
6. Observe: Failed message still appears in chat!
7. Observe: Offline warning still shows
8. Turn network online
9. Observe: Offline warning disappears
10. Wait 2 seconds
11. Observe: Message auto-retries and succeeds

**Expected Result**: ✅ PASS
- Failed message persists in localStorage
- Appears after page reload
- Auto-retries on network reconnection
- localStorage is cleaned after success

**Duration**: 5-8 seconds

---

### Scenario 7: Error Message Variations

**Precondition**: Network available

**Setup**: 
Configure backend to return specific errors OR simulate with fetch interception

**Steps**:

#### 7a: Network Timeout
```javascript
// Should show: "Connection timeout"
// or "Request timeout"
```

#### 7b: Server Error (500)
```javascript
// Should show: "Server error"
// or "Internal server error"
```

#### 7c: Not Found (404)
```javascript
// Should show: "Message not found"
// or "Resource not available"
```

#### 7d: Validation Error (422)
```javascript
// Should show: "Invalid message"
// or validation details
```

#### 7e: No Internet
```javascript
// Should show: "Network error"
// and offline warning
```

**Expected Result**: ✅ PASS
- Error messages are specific
- Help user understand what happened
- All are actionable (retry or resend)

---

### Scenario 8: Network Connection Lost During Send

**Precondition**: Network stable, message sending

**Steps**:
1. Type a long message
2. Start sending
3. Immediately turn network offline (Throttling → "Offline")
4. Observe: Message status ⏳ pending
5. Observe: Offline warning appears
6. Turn network online again
7. Observe: Offline warning disappears
8. Observe: Message auto-retries
9. Observe: Message succeeds

**Expected Result**: ✅ PASS
- Message gracefully handles connection loss
- Shows offline state immediately
- Auto-recovers when online
- No error shown (app handles it)

**Duration**: 5-10 seconds

---

### Scenario 9: Rapid Message Sends

**Precondition**: Network available

**Steps**:
1. Send 5 messages rapidly (within 2 seconds)
2. Observe: All show ⏳ pending
3. Watch status change to ✓ sent in sequence
4. Verify no messages are lost
5. Verify order is preserved

**Expected Result**: ✅ PASS
- All messages queued properly
- No duplicates
- Order preserved
- All eventually succeed

**Duration**: 5-8 seconds

---

### Scenario 10: Disable Auto-Retry

**Precondition**: Component mounted, store available

**Steps**:
In browser console:
```javascript
const store = pinia.state.value.chatMessageStore
store.autoRetryEnabled = false
```

1. Simulate send failure (make network offline)
2. Send message: "Test manual retry only"
3. Observe: Message shows ⏳ pending
4. Turn network online
5. Wait 5 seconds
6. Observe: Message does NOT auto-retry
7. Observe: Status still ✗ failed
8. Click "🔄 Coba Lagi" button
9. Observe: Message retries on click
10. Re-enable auto-retry:
```javascript
store.autoRetryEnabled = true
```

**Expected Result**: ✅ PASS
- Auto-retry can be disabled
- Manual retry still works
- Can be re-enabled

---

## Browser DevTools Setup

### Simulate Offline

1. Open DevTools: F12
2. Go to Network tab
3. At top, find "Throttling" dropdown
4. Select "Offline"
5. Try to send message
6. Change back to "No throttling" to go online

### Simulate Slow Connection

1. Network tab → Throttling
2. Select "2G" or "3G"
3. Send message - watch it take longer
4. Auto-retry with these delays

### Monitor Network Requests

1. Network tab
2. Filter by XHR
3. Send message
4. See POST /api/pesan request
5. Check response (success or error)

### Check localStorage

1. Go to Application tab
2. Left panel → Storage → Local Storage
3. Select your domain
4. Look for key: "consultationChat_failedMessages"
5. See JSON array of failed messages

---

## Verification Checklist

Use this checklist for final validation:

### Functional Tests
- [ ] ✅ Happy path: Send succeeds, no errors
- [ ] ✅ Offline mode: Warning shows, message queues
- [ ] ✅ Retry button: Visible and functional when failed
- [ ] ✅ Auto-retry: Works on network reconnection
- [ ] ✅ Batch retry: "Kirim Semua" button works
- [ ] ✅ Persistence: Messages persist after reload
- [ ] ✅ Multiple failures: Shows count correctly
- [ ] ✅ Error messages: Clear and specific

### UI Tests
- [ ] ✅ Status icons: ⏳ pending, ✓ sent, ✗ failed display correctly
- [ ] ✅ Offline banner: Shows/hides properly
- [ ] ✅ Failed banner: Shows/hides with correct count
- [ ] ✅ Error box: Displays with proper styling
- [ ] ✅ Retry button: Clickable, shows loading state
- [ ] ✅ Buttons disabled: During retry operation
- [ ] ✅ Colors: Failed = red, pending = gray, success = green
- [ ] ✅ Animations: Smooth transitions

### Performance Tests
- [ ] ✅ No memory leaks: DevTools → Memory → snapshot after 10 operations
- [ ] ✅ Responsive: UI remains responsive during sends
- [ ] ✅ No lag: No freezing on retry operations
- [ ] ✅ Network efficient: Only necessary API calls made

### Edge Cases
- [ ] ✅ Empty message: Rejected properly
- [ ] ✅ Very long message: Handles correctly
- [ ] ✅ Special characters: No encoding issues
- [ ] ✅ Rapid sends: All queued properly
- [ ] ✅ Network flapping: Handles on/off/on cycles
- [ ] ✅ Max retries: Shows 3/3 correctly
- [ ] ✅ localStorage full: Graceful degradation

---

## Debugging Commands

### Check Failed Messages
```javascript
const store = pinia.state.value.chatMessageStore
console.table(store.failedMessages)
```

### Monitor All Messages
```javascript
const store = pinia.state.value.chatMessageStore
watch(
  () => store.messages,
  (messages) => {
    console.clear()
    console.table(messages.map(m => ({
      id: m.id,
      status: m.status,
      message: m.message.substring(0, 20) + '...',
      error: m.error || 'none',
    })))
  },
  { deep: true }
)
```

### Simulate Retry Timing
```javascript
const store = pinia.state.value.chatMessageStore
// Make first message fail
store.messages[0].status = 'failed'
store.messages[0].error = 'Network error'
// Watch auto-retry logs
```

### Force Network Status
```javascript
// Go offline
window.navigator.onLine = false
window.dispatchEvent(new Event('offline'))

// Go online
window.navigator.onLine = true
window.dispatchEvent(new Event('online'))
```

---

## Success Criteria

All tests pass when:

✅ **Reliability**
- 100% of messages eventually deliver (auto-retry or manual)
- No messages lost, even on refresh
- Error recovery is automatic and transparent

✅ **User Experience**
- Status is always visible (icons and text)
- User knows when offline or failed
- One-click retry available
- Auto-recovery happens transparently

✅ **Performance**
- No lag or freezing
- Response time < 2 seconds for most operations
- Memory stable over time
- Network requests efficient

✅ **Quality**
- Error messages are helpful
- UI is professional and polished
- Animations are smooth
- No console errors
