# PHASE 3B - QUICK ACTION GUIDE: PAYMENT INTEGRATION

**Priority:** 🔴 CRITICAL  
**Estimated Duration:** 3-4 hours  
**Impact:** Revenue model enablement, compliance with payment regulations  
**Status:** Planning → Ready to Start

---

## 🎯 Objective

Implement comprehensive payment system untuk:
- ✅ Doctor consultation fees
- ✅ Emergency procedures charges
- ✅ Multiple payment methods (Stripe, GCash, Bank Transfer)
- ✅ Invoice generation & tax calculation
- ✅ Payment audit trail & reconciliation

---

## 📋 Implementation Breakdown

### Phase 3B.1: Backend Payment Infrastructure (90 min)

#### 1. Models (30 min)
```php
// app/Models/Payment.php - Main payment record
- user_id, consultation_id, amount, currency
- payment_method (stripe, gcash, bank_transfer)
- status (pending, processing, completed, failed, refunded)
- transaction_id, receipt_url
- timestamps, soft-delete
- Methods: isPending(), isCompleted(), refund()

// app/Models/Invoice.php - Invoice generation
- payment_id, user_id, date, due_date
- items (consultation, emergency fee, taxes)
- subtotal, tax_amount, total_amount
- status (draft, sent, paid, overdue)
- Methods: generate(), send(), mark_paid()

// app/Models/TaxRecord.php - Tax compliance (PPh, PPN)
- payment_id, tax_type (pph, ppn, other)
- tax_amount, tax_rate
- calculated_at, reported_at
- Methods: calculate(), report()
```

#### 2. Migration (20 min)
```php
// database/migrations/2025_12_21_create_payment_tables.php
- payments table (15 columns, soft-delete)
- invoices table (12 columns)
- payment_items table (line items for invoices)
- tax_records table (compliance tracking)
- Indexes untuk search & filtering
```

#### 3. Controller (40 min)
```php
// app/Http/Controllers/Api/PaymentController.php
- create() - Initiate payment
- show() - Get payment details
- confirm() - Webhook confirmation
- refund() - Refund request
- getInvoice() - Get invoice
- getHistory() - Payment history

// app/Http/Controllers/Api/InvoiceController.php
- generate() - Generate invoice from payment
- getPaymentInvoices() - List invoices
- download() - Download as PDF
```

#### 4. Service Classes (30 min)
```php
// app/Services/PaymentService.php
- processPayment($data)
- verifyTransaction($transactionId)
- calculateTax($amount, $type)

// app/Services/StripeService.php
- createPaymentIntent()
- handleWebhook()
- refundTransaction()

// app/Services/GCashService.php
- initiatePayment()
- handleCallback()
```

#### 5. Routes (20 min)
```php
// routes/api.php
POST /api/v1/payments - Create payment
GET /api/v1/payments/{id} - Get payment status
POST /api/v1/payments/{id}/refund - Refund
POST /api/v1/webhooks/payment - Payment webhook
GET /api/v1/invoices/{id} - Get invoice
GET /api/v1/invoices/{id}/download - Download PDF
GET /api/v1/payments/history - Payment history
```

### Phase 3B.2: Payment Provider Integration (60 min)

#### Stripe Integration (30 min)
```php
// config/services.php
'stripe' => [
    'public_key' => env('STRIPE_PUBLIC_KEY'),
    'secret_key' => env('STRIPE_SECRET_KEY'),
]

// Steps:
1. Install stripe/stripe-php
2. Create StripeService with payment intent flow
3. Setup webhook endpoint untuk payment confirmation
4. Handle 3D Secure & PCI compliance
5. Test dengan Stripe test cards
```

#### GCash Integration (20 min)
```php
// GCash API endpoint: https://api.gcash.com.ph/
// Steps:
1. Register app dengan GCash
2. Create GCashService class
3. Implement payment flow
4. Handle callback notifications
5. Test dengan sandbox
```

#### Bank Transfer (10 min)
```php
// Manual bank transfer flow
1. Generate unique transfer reference number
2. Display bank account details
3. Allow user upload bukti transfer
4. Manual verification by admin
5. Mark as completed when verified
```

### Phase 3B.3: Frontend Payment Components (90 min)

#### 1. Payment Form Component (40 min)
```vue
// resources/js/Components/Payment/PaymentForm.vue
- Amount input dengan currency selection
- Payment method selector (Stripe/GCash/Bank)
- Stripe card element integration
- Form validation
- Loading state during processing
- Error handling & display
```

#### 2. Invoice Component (30 min)
```vue
// resources/js/Components/Payment/InvoiceView.vue
- Invoice details display
- Itemized breakdown (consultation fee, tax, etc)
- Download PDF button
- Send via email button
- Shareable link
```

#### 3. Payment History Component (20 min)
```vue
// resources/js/Components/Payment/PaymentHistory.vue
- Tabel payment history
- Status badge (pending, completed, failed)
- Filter by status, date range
- Action buttons (view invoice, refund)
```

#### 4. Payment Success Page (40 min)
```vue
// resources/js/Pages/Payment/PaymentSuccessPage.vue
- Success message
- Receipt display
- Invoice download link
- Next steps guidance
```

### Phase 3B.4: Tax Calculation & Compliance (30 min)

#### Tax Types (Indonesia)
```php
// PPh (Personal Income Tax)
- Applied to: Consultation fees
- Rate: 15% for professionals
- Threshold: No tax below certain amount

// PPN (Value Added Tax)
- Rate: 11% (standard Indonesia rate)
- Applied to: Services
- Input/output tracking

// Implementation
1. Create TaxService untuk calculate taxes
2. Store tax records untuk compliance reporting
3. Include taxes di invoice
4. Generate tax reports untuk accounting
```

---

## 🗂️ File Structure After Phase 3B

```
app/
├── Models/
│   ├── Payment.php (new)
│   ├── Invoice.php (new)
│   ├── PaymentItem.php (new)
│   └── TaxRecord.php (new)
├── Http/Controllers/Api/
│   ├── PaymentController.php (new)
│   └── InvoiceController.php (new)
├── Services/
│   ├── PaymentService.php (new)
│   ├── StripeService.php (new)
│   ├── GCashService.php (new)
│   └── TaxService.php (new)
└── Observers/
    └── PaymentObserver.php (new)

database/migrations/
└── 2025_12_21_create_payment_tables.php (new)

resources/js/
├── Components/Payment/
│   ├── PaymentForm.vue (new)
│   ├── InvoiceView.vue (new)
│   └── PaymentHistory.vue (new)
└── Pages/Payment/
    ├── PaymentPage.vue (new)
    └── PaymentSuccessPage.vue (new)

routes/
└── api.php (modified - add payment routes)
```

---

## 🔄 Integration Points

### Dengan Emergency Procedures (Phase 3A)
```php
// When emergency is escalated
Emergency.escalate() → Create Payment for emergency fee
// Example: Emergency handling fee = Rp 500,000
```

### Dengan Consultation (Existing)
```php
// After consultation completed
Consultation.complete() → Create Payment for doctor fee
// Example: Doctor fee = Rp 150,000 per 30 min
```

### Dengan Invoice System
```php
// Invoice line items
- Consultation fee: Rp 150,000
- Emergency charge: Rp 500,000 (if applicable)
- Subtotal: Rp 650,000
- PPN (11%): Rp 71,500
- PPh (15%): Rp 97,500
- Total: Rp 624,000
```

---

## 💡 Key Implementation Details

### Payment Status Flow
```
PENDING (awaiting payment)
  ↓
PROCESSING (payment processing)
  ↓
COMPLETED (payment confirmed) → Generate Invoice
  ↓
REFUNDED (if refund requested)
```

### Webhook Security
```php
// Stripe webhook signature verification
$signature = request()->header('Stripe-Signature');
verify_signature($signature) || throw UnauthorizedException
```

### PCI Compliance
- ✅ Never store full credit card numbers
- ✅ Use Stripe tokenization
- ✅ HTTPS only for payment data
- ✅ Rate limiting on payment endpoints
- ✅ Audit logs for all transactions

### Error Handling
```php
// Specific error handling for:
- Card declined
- Insufficient funds
- Expired card
- Network timeout
- Webhook timeout
```

---

## 🧪 Testing Strategy

### Unit Tests
```bash
# PaymentService tests
php artisan test --filter=PaymentServiceTest

# TaxService tests
php artisan test --filter=TaxServiceTest
```

### Integration Tests
```bash
# Full payment flow
POST /api/v1/payments (with test stripe token)
GET /api/v1/payments/{id} (verify status)
POST /api/v1/webhooks/payment (simulate webhook)
```

### Manual Testing
```bash
# Stripe test cards
4242 4242 4242 4242 - Success
4000 0000 0000 0002 - Declined
4000 2500 0000 3155 - 3D Secure required
```

---

## 📦 Dependencies to Install

```bash
# Payment processing
composer require stripe/stripe-php

# PDF generation (for invoices)
composer require barryvdh/laravel-dompdf

# Notifications (for invoice emails)
composer require laravel/framework  # Already have

# Frontend payment elements
npm install @stripe/react-stripe-js
npm install @stripe/stripe-js
```

---

## 🎯 Success Criteria

### Functional
- ✅ Create payment untuk consultation
- ✅ Create payment untuk emergency
- ✅ Process payment dengan Stripe
- ✅ Handle payment webhook
- ✅ Generate invoice dengan taxes
- ✅ Refund payment
- ✅ Download invoice as PDF

### Security
- ✅ PCI compliance checks
- ✅ Webhook signature verification
- ✅ Rate limiting on endpoints
- ✅ No card data storage
- ✅ Immutable audit trail

### Compliance
- ✅ Tax calculation (PPh, PPN)
- ✅ Invoice format sesuai regulasi
- ✅ Payment record retention
- ✅ Accounting reconciliation

### Performance
- ✅ Payment creation < 500ms
- ✅ Webhook handling < 1s
- ✅ Invoice PDF generation < 3s
- ✅ Database queries indexed

---

## 🚀 Execution Timeline

| Task | Duration | Dependencies |
|------|----------|--------------|
| Models & Migration | 30 min | None |
| Controllers & Routes | 40 min | Models |
| Stripe Integration | 30 min | Controllers |
| GCash Integration | 20 min | Controllers |
| Frontend Components | 90 min | Controllers |
| Tax Service | 20 min | Models |
| Testing & Debugging | 30 min | All |
| **TOTAL** | **3.5 hours** | Sequential |

---

## 📝 Checklist Before Starting

- [ ] Stripe account created & API keys obtained
- [ ] GCash sandbox account (if using GCash)
- [ ] pdf library (dompdf) ready for invoices
- [ ] Email configuration tested
- [ ] Database backup before migration
- [ ] Review payment regulations (Indonesia Tax Law)

---

## 🔐 Security Checklist

- [ ] Never log credit card numbers
- [ ] HTTPS enforced on payment endpoints
- [ ] Webhook signature verification implemented
- [ ] Rate limiting on payment endpoints
- [ ] User authorization checks (own payments only)
- [ ] PCI compliance assessment done
- [ ] Payment data encrypted at rest
- [ ] Audit trail for all transactions

---

## 📞 Post-Implementation Tasks

### Day 1 (After Implementation)
- [ ] Test with real Stripe account
- [ ] Verify webhook delivery
- [ ] Check invoice PDF generation
- [ ] Test refund flow
- [ ] Verify tax calculation

### Day 2 (Before Production)
- [ ] Load test payment endpoints
- [ ] Security audit
- [ ] Compliance check
- [ ] Documentation update
- [ ] Team training

### Week 1 (Monitoring)
- [ ] Monitor failed payments
- [ ] Check webhook logs
- [ ] Verify tax records
- [ ] Customer support setup
- [ ] Payment reconciliation

---

## 🎓 Expected Outcomes

### Technical
- ✅ Full payment processing system
- ✅ Invoice management
- ✅ Tax compliance tracking
- ✅ Payment history & analytics
- ✅ Webhook handling & retry logic

### Business
- ✅ Revenue model activated
- ✅ Automatic invoicing
- ✅ Tax reporting ready
- ✅ Payment reconciliation
- ✅ Compliance documentation

### Compliance Score
- Before: 84.5% (after Emergency)
- After: 89% (estimated)
- Gap Closed: Payment Integration ✅

---

## 🏁 Next Phase (3C) Preview

After Phase 3B Complete → Phase 3C: Video Consultation (4-5 hours)
- WebRTC integration (Jitsi/Agora)
- Screen sharing
- Recording with consent
- Call quality monitoring

