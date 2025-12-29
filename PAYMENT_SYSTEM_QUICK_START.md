# Quick Start - Payment System with Double Payment Prevention

## 🚀 For Developers

### 1. Backend Usage

```php
use App\Services\PaymentService;

class PaymentController {
    public function __construct(private PaymentService $paymentService) {}
    
    public function processPayment(Request $request) {
        // Generate idempotency key (or use request header)
        $idempotencyKey = $request->header('X-Idempotency-Key');
        
        $result = $this->paymentService->processPayment(
            userId: Auth::id(),
            consultationId: $request->input('consultation_id'),
            amount: $request->input('amount'),
            paymentMethod: $request->input('payment_method'),
            idempotencyKey: $idempotencyKey
        );
        
        // Handle duplicate
        if ($result['type'] === 'existing') {
            return response()->json([
                'message' => 'Duplicate payment detected',
                'payment_id' => $result['payment_id'],
                'status' => $result['status']
            ], 409); // Conflict
        }
        
        // Success
        return response()->json($result, 201);
    }
}
```

### 2. Frontend Usage

```javascript
import paymentService from '@/services/paymentService'

// Create payment (idempotency key auto-generated)
const result = await paymentService.createPayment({
    consultationId: 5,
    amount: 5000,
    paymentMethod: 'stripe'
})

// Check for duplicate
if (result.status === 409) {
    console.log('Duplicate payment:', result.data.paymentId)
    // Show user that payment was already created
}

// New payment created
if (result.success && result.type === 'new') {
    console.log('Payment created:', result.data.payment_id)
    // Proceed to payment gateway
}
```

### 3. API Endpoints

#### Create Payment
```bash
POST /api/v1/payments
Headers:
  - Authorization: Bearer {token}
  - X-Idempotency-Key: payment-5-uuid-timestamp (optional, auto-generated)

Body:
{
    "consultation_id": 5,
    "amount": 5000,
    "payment_method": "stripe",
    "idempotency_key": "payment-5-uuid-timestamp"
}

# Response 201 (New)
{
    "data": {
        "type": "new",
        "payment_id": 100,
        "payment": {...},
        "invoice": {...}
    }
}

# Response 409 (Duplicate)
{
    "data": {
        "type": "existing",
        "payment_id": 100,
        "status": "pending"
    }
}
```

#### Confirm Payment
```bash
POST /api/v1/payments/{id}/confirm
Headers:
  - Authorization: Bearer {token}

Body:
{
    "transaction_id": "stripe_pi_123456",
    "receipt_url": "https://stripe.com/receipt"
}

# Response 200
{
    "data": {
        "payment": {...},
        "status": "completed"
    }
}
```

#### Refund Payment
```bash
POST /api/v1/payments/{id}/refund
Headers:
  - Authorization: Bearer {token}

Body:
{
    "amount": 2500 (optional),
    "reason": "Customer requested refund"
}

# Response 200
{
    "data": {
        "payment": {...},
        "refund_amount": 2500
    }
}
```

---

## 🧪 Testing

### Run Tests
```bash
php artisan test tests/Feature/DoublePaymentPreventionTest.php

# Specific test
php artisan test tests/Feature/DoublePaymentPreventionTest.php --filter=testIdempotencyKeyPreventsDoublePayment
```

### Manual Testing
```bash
# 1. Get token
TOKEN=$(curl -X POST http://localhost:8000/api/login \
  -d "email=patient@test.com&password=password" | jq -r '.token')

# 2. First request
curl -X POST http://localhost:8000/api/v1/payments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Idempotency-Key: test-1" \
  -d '{
    "consultation_id": 5,
    "amount": 5000,
    "payment_method": "stripe",
    "idempotency_key": "test-1"
  }'

# Expected Response 201:
# { "data": { "type": "new", "payment_id": 100 } }

# 3. Duplicate request (same idempotency key)
curl -X POST http://localhost:8000/api/v1/payments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Idempotency-Key: test-1" \
  -d '{
    "consultation_id": 5,
    "amount": 5000,
    "payment_method": "stripe",
    "idempotency_key": "test-1"
  }'

# Expected Response 409:
# { "data": { "type": "existing", "payment_id": 100 } }
```

---

## 🔧 Configuration

### Environment Variables
```env
# Redis connection for distributed locks
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_CACHE_DB=1

# Cache configuration
CACHE_DRIVER=redis
CACHE_REDIS_CLIENT=default
```

### PaymentService Settings
```php
// app/Services/PaymentService.php
const LOCK_TIMEOUT = 5;              // seconds
const CACHE_TTL = 300;               // 5 minutes
const MAX_LOCK_ATTEMPTS = 50;        // 5 seconds total
const LOCK_RETRY_DELAY = 100000;     // 100ms in microseconds
```

---

## 📊 Monitoring

### Check Logs
```bash
# Payment-related logs
tail -f storage/logs/laravel.log | grep "Payment:"

# Lock-related logs
tail -f storage/logs/laravel.log | grep "Lock"

# Error logs
tail -f storage/logs/laravel.log | grep "ERROR"
```

### Database Queries
```sql
-- View recent payments
SELECT id, user_id, consultation_id, status, created_at 
FROM payments 
ORDER BY created_at DESC 
LIMIT 10;

-- Check duplicate payments (should be 0)
SELECT consultation_id, COUNT(*) as count 
FROM payments 
WHERE status IN ('pending', 'completed') 
GROUP BY consultation_id 
HAVING count > 1;
```

---

## ⚠️ Common Issues & Solutions

### Issue: "Could not acquire payment lock"
**Solution**: Increase lock timeout
```php
const LOCK_TIMEOUT = 10; // from 5
const MAX_LOCK_ATTEMPTS = 100; // from 50
```

### Issue: "Redis PING failed"
**Solution**: Verify Redis is running
```bash
redis-cli ping
# Should return: PONG
```

### Issue: Payment not created but 409 returned
**Solution**: Check cache
```bash
php artisan tinker
> Cache::flush()
```

### Issue: "Unauthorized" on confirm
**Solution**: Verify user owns payment
```sql
SELECT payment.user_id, payment.id 
FROM payments 
WHERE payment.id = 100;
```

---

## 🔒 Security Best Practices

1. **Always Generate Unique Idempotency Keys**
   ```javascript
   // Good
   const key = `payment-${Date.now()}-${uuid()}`
   
   // Bad (not unique per request)
   const key = 'payment-key'
   ```

2. **Validate Amount Server-Side**
   ```php
   $validated = $request->validate([
       'amount' => 'required|numeric|min:1000|max:10000000',
   ]);
   ```

3. **Check Consultation Ownership**
   ```php
   if ($consultation->pasien_id !== Auth::id()) {
       return response()->json(['message' => 'Unauthorized'], 403);
   }
   ```

4. **Use HTTPS Only**
   ```
   All payment requests must use HTTPS
   Disable HTTP in production
   ```

5. **Rate Limiting**
   ```php
   // app/Http/Middleware/RateLimitPayments.php
   Route::post('/payments', [PaymentController::class, 'create'])
       ->middleware('throttle:10,1'); // 10 requests per minute
   ```

---

## 📈 Performance Tips

1. **Cache Payment Methods**
   ```php
   $methods = Cache::remember('payment_methods', 3600, function() {
       return PaymentMethod::all();
   });
   ```

2. **Use Pagination for History**
   ```php
   $payments = Payment::where('user_id', $userId)
       ->paginate(15); // Don't load all at once
   ```

3. **Index Database Columns**
   ```sql
   CREATE INDEX idx_payments_consultation_user 
   ON payments(consultation_id, user_id);
   ```

4. **Monitor Lock Contention**
   ```bash
   # Check Redis memory
   redis-cli info memory
   
   # Check active locks
   redis-cli keys "payment:lock:*"
   ```

---

## 📚 Additional Resources

- [PaymentService.php](../app/Services/PaymentService.php)
- [PaymentController.php](../app/Http/Controllers/Api/PaymentController.php)
- [DoublePaymentPreventionTest.php](../tests/Feature/DoublePaymentPreventionTest.php)
- [Payment Model](../app/Models/Payment.php)
- [Full Implementation Guide](./DOUBLE_PAYMENT_PREVENTION_IMPLEMENTATION.md)

---

## ✅ Verification Checklist

Before going to production:

- [ ] Redis connection is working
- [ ] All tests pass: `php artisan test`
- [ ] Payment creation works: `POST /api/v1/payments` returns 201
- [ ] Duplicate detection works: Second request returns 409
- [ ] Payment confirmation works: `POST /api/v1/payments/{id}/confirm`
- [ ] Error handling works: Invalid requests return correct status codes
- [ ] Logging works: Check `storage/logs/laravel.log`
- [ ] Frontend idempotency keys work: Check browser console
- [ ] Rate limiting works: Rapid requests are throttled

---

**Last Updated**: 2024-01-15  
**Version**: 1.0.0  
**Status**: ✅ Production Ready
