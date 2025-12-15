# Real-time Analytics - Quick Setup Guide

## 🚀 Getting Started (5 minutes)

### Step 1: Verify Files Are in Place

Backend files:
```
✅ app/Services/AnalyticsService.php (Enhanced)
✅ app/Services/RealtimeAnalyticsBroadcaster.php (New)
✅ app/Http/Controllers/Api/AnalyticsController.php (Enhanced)
✅ routes/api.php (Updated)
```

Frontend files:
```
✅ resources/js/views/admin/AnalyticsPage.vue (Enhanced)
✅ resources/js/services/AnalyticsWebSocket.js (New)
✅ resources/js/composables/useRealtimeAnalytics.js (New)
✅ resources/js/utils/AnalyticsCacheManager.js (New)
✅ resources/js/router/index.js (Updated)
✅ resources/js/views/admin/DashboardPage.vue (Updated)
```

### Step 2: Clear Cache (If running in production)

```bash
php artisan cache:clear
php artisan config:clear
npm run build
```

### Step 3: Warm Cache (Optional)

```bash
php artisan tinker
>>> app(App\Services\AnalyticsService::class)->warmCache()
>>> exit
```

### Step 4: Test the Feature

1. Navigate to Admin Dashboard
2. Click "Advanced Analytics" card
3. Verify data loads
4. Test auto-refresh toggle
5. Change refresh interval
6. Check "Last updated" timestamp

---

## 🎮 Using Auto-Refresh

### Enable Auto-Refresh
1. Click the refresh icon in the analytics header
2. Select interval from dropdown (15s, 30s, 1m, 5m)
3. Watch data update automatically

### Features
- **Toggle Button:** Click icon to enable/disable
- **Interval Select:** Choose update frequency
- **Status Indicator:** Shows if live updates are active
- **Last Updated:** Shows how fresh the data is

### Recommended Intervals
- **15 seconds:** Real-time monitoring (high network usage)
- **30 seconds:** Default (balanced)
- **1 minute:** Moderate updates
- **5 minutes:** Low bandwidth

---

## 🔌 Using WebSocket (Optional)

### Prerequisites
- WebSocket server configured (Laravel WebSockets or similar)
- Valid authentication token

### Enable WebSocket
```javascript
import { useRealtimeAnalytics } from '@/composables/useRealtimeAnalytics'

const { initializeWebSocket } = useRealtimeAnalytics()

// Connect with auth token
await initializeWebSocket(authToken)
```

### Available Events
```javascript
analyticsWebSocket.onConsultationUpdate((data) => {
  console.log('Consultations updated:', data)
})

analyticsWebSocket.onDoctorPerformanceUpdate((data) => {
  console.log('Doctor performance updated:', data)
})

analyticsWebSocket.onRevenueUpdate((data) => {
  console.log('Revenue updated:', data)
})

analyticsWebSocket.onHealthTrendsUpdate((data) => {
  console.log('Health trends updated:', data)
})
```

---

## 💾 Cache Management

### Check Cache Status
```bash
# View cache statistics
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/v1/analytics/cache/status
```

### Warm Cache
```bash
# Pre-load all metrics
curl -X POST -H "Authorization: Bearer TOKEN" \
  http://localhost/api/v1/analytics/cache/warm
```

### Get Fresh Data
```bash
# Bypass cache, get real-time metrics
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/v1/analytics/realtime
```

### Clear Cache
```bash
# Force refresh (clears cache)
curl -X POST -H "Authorization: Bearer TOKEN" \
  http://localhost/api/v1/analytics/refresh
```

---

## 📊 API Quick Reference

### All Endpoints

```
GET    /api/v1/analytics/overview          # Main dashboard
GET    /api/v1/analytics/consultations     # Consultation metrics
GET    /api/v1/analytics/doctors           # Doctor performance
GET    /api/v1/analytics/health-trends     # Health trends
GET    /api/v1/analytics/revenue           # Revenue analytics
GET    /api/v1/analytics/range             # Date range analytics
POST   /api/v1/analytics/refresh           # Refresh cache
GET    /api/v1/analytics/cache/status      # Cache stats
POST   /api/v1/analytics/cache/warm        # Warm cache
GET    /api/v1/analytics/realtime          # Real-time (no cache)
```

---

## 🔍 Testing

### Test Auto-Refresh
```javascript
// Open browser console
setInterval(() => {
  console.log('Last updated:', new Date())
}, 5000)
```

### Test WebSocket
```javascript
// Open browser console
import analyticsWebSocket from '@/services/AnalyticsWebSocket'
await analyticsWebSocket.connect(token)
analyticsWebSocket.requestMetrics(['consultations'])
```

### Test API Endpoints
```bash
# Test cache status
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/v1/analytics/cache/status | jq

# Test real-time
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/v1/analytics/realtime | jq
```

---

## ⚙️ Configuration

### Change Default Intervals

In `AnalyticsPage.vue`:
```javascript
const autoRefreshInterval = ref(30) // Change to desired seconds
```

### Adjust Cache TTLs

In `AnalyticsService.php`:
```php
Cache::remember($cacheKey, 300, function () {
    // 300 = 5 minutes, adjust as needed
})
```

### WebSocket Settings

In `AnalyticsWebSocket.js`:
```javascript
this.maxReconnectAttempts = 5        // Retry count
this.reconnectDelay = 3000           // Wait time (ms)
```

---

## 🐛 Common Issues

### Issue: "Analytics page blank"
**Solution:** Check browser console for errors, verify API token

### Issue: "Data not updating"
**Solution:** 
- Check auto-refresh is enabled
- Verify network tab for API calls
- Check cache with `/cache/status` endpoint

### Issue: "WebSocket won't connect"
**Solution:**
- Check WebSocket server is running
- Verify auth token is valid
- Check browser console for connection errors

### Issue: "High memory usage"
**Solution:**
- Increase refresh interval to 5m
- Disable WebSocket if not needed
- Monitor with browser dev tools

---

## 📈 Performance Tuning

### For Real-time Monitoring
```javascript
// Use 15s or 30s intervals
// Enable WebSocket for best performance
// Keep cache warm
```

### For Occasional Checks
```javascript
// Use 5m interval
// Disable WebSocket
// Manual refresh as needed
```

### For High Traffic
```javascript
// Increase cache TTLs
// Use 5m+ intervals
// Warm cache on schedule
// Monitor `/cache/status` regularly
```

---

## ✅ Verification Checklist

- [ ] Admin can see Analytics card in dashboard
- [ ] Analytics page loads all metrics
- [ ] Auto-refresh toggle works
- [ ] Intervals can be changed
- [ ] Last updated timestamp is current
- [ ] Status indicator shows live
- [ ] API endpoints respond correctly
- [ ] Cache status shows statistics
- [ ] Real-time endpoint bypasses cache
- [ ] No console errors

---

## 📚 Full Documentation

For comprehensive documentation, see:
- `REALTIME_ANALYTICS_GUIDE.md` - Complete guide
- `REALTIME_ANALYTICS_SUMMARY.md` - Feature summary

---

## 🆘 Need Help?

1. Check the documentation files
2. Review browser console for errors
3. Test API endpoints with curl/Postman
4. Check server logs
5. Monitor performance metrics

---

## 🎉 You're All Set!

Real-time analytics is ready to use. Start monitoring your telemedicine platform with:
- 📊 Live consultation metrics
- 👨‍⚕️ Doctor performance tracking
- 💰 Revenue monitoring
- 🏥 Health trends analysis
- ⚡ Auto-refresh with configurable intervals
- 🔌 Optional WebSocket real-time updates

Happy monitoring! 🚀
