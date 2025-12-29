# Setup Twilio WhatsApp - Step by Step

## 🎯 Objective
Setelah setup ini, WhatsApp OTP akan **benar-benar terkirim** ke nomor WhatsApp Anda dalam hitungan **1-2 detik**.

---

## 📋 Step 1: Create Free Twilio Account

1. Go to: https://www.twilio.com/try-twilio
2. Sign up dengan email Anda
3. Verify email (buka link di email)
4. Complete account setup
5. You'll get:
   - **Account SID** (dimulai dengan "AC...")
   - **Auth Token** (string panjang)

⏱️ Time: 5 minutes

---

## 🔧 Step 2: Setup WhatsApp Sandbox

### Option A: Using Twilio Console (Recommended)

1. Login ke https://console.twilio.com
2. Left menu → "Messaging" → "Try it out" → "Send a WhatsApp message"
3. Click "Start with a Sandbox"
4. You'll get a **sandbox WhatsApp number** (e.g., +14155552671)
5. You'll also get instructions untuk **join sandbox** via WhatsApp

### Option B: Manual Setup

1. Go to: https://console.twilio.com/messaging/whatsapp/sandbox
2. Copy the **WhatsApp Sandbox Number**
3. Open WhatsApp on your phone
4. Send message: `join [two random words]` ke sandbox number
5. Twilio akan reply dengan confirmation

⏱️ Time: 2 minutes

---

## 💻 Step 3: Update .env File

Open file: `d:\Aplications\telemedicine\.env`

Find section:
```env
# ============================================
# TWILIO WHATSAPP CONFIGURATION
# ============================================
TWILIO_ACCOUNT_SID=your_account_sid_here
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_NUMBER=+14155552671
```

Replace dengan:
```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_WHATSAPP_NUMBER=+14155552671
```

**Darimana dapat credentials?**

1. Account SID: https://console.twilio.com/account/auth-tokens
   - Copy "Account SID"
   
2. Auth Token: https://console.twilio.com/account/auth-tokens
   - Copy "Auth Token"
   
3. WhatsApp Number: https://console.twilio.com/messaging/whatsapp/sandbox
   - Copy "Sandbox WhatsApp Number"

✅ **Save file**

⏱️ Time: 2 minutes

---

## 🧪 Step 4: Test Setup

### Test 1: Check Twilio Configuration
```bash
# Open terminal di D:\Aplications\telemedicine
php artisan tinker

# Run this:
> $service = app(\App\Services\WhatsAppService::class);
> $service->getStatus();

# Expected output:
# [
#   "configured" => true,
#   "account_sid" => "AC...",
#   "whatsapp_number" => "+14155552671",
#   "timestamp" => ...
# ]
```

Jika `configured` = **true**, maka OK!

### Test 2: Send Test OTP
```bash
# Still in tinker:
> $service->sendOtp('+62888XXXXXXXX', 'TEST01');

# Expected output: true
# Check Laravel logs untuk confirmation:
# storage/logs/laravel.log
```

### Test 3: Via UI
1. Open browser: http://localhost:8000/forgot-password
2. Select: WhatsApp
3. Enter: Nomor WhatsApp Anda (dalam format 08xxx atau +628xxx)
4. Click: "Kirim Kode Reset"
5. **Check WhatsApp Anda** - OTP harus masuk dalam 1-2 detik! 🎉

⏱️ Time: 3 minutes

---

## 🚨 Troubleshooting

### Issue: "Configured = false"
**Reason:** Credentials di .env tidak valid atau belum di-set

**Solution:**
```bash
# Check .env file
cat .env | grep TWILIO

# Verify:
# - No spaces around =
# - No quotes (unless value contains spaces)
# - Correct format: KEY=VALUE
```

### Issue: Message tidak masuk
**Reason:** Nomor WhatsApp belum join sandbox

**Solution:**
1. Open WhatsApp
2. Send ke sandbox number (dari .env): `join [two random words]`
   - Contoh: `join happy moon`
3. Wait untuk Twilio reply
4. Coba test lagi

### Issue: "Invalid phone number format"
**Reason:** Nomor WhatsApp format salah

**Solution:**
- Gunakan format: `+62888XXXXXXXX` (dengan +)
- Atau: `08888XXXXXXXX` (tanpa +)
- Pastikan 10-13 digits total

### Issue: "Twilio API error"
**Check:**
```bash
# View logs:
tail -f storage/logs/laravel.log

# Search untuk "Twilio" atau "WhatsApp"
# Lihat error message
```

---

## ✅ Checklist

- [ ] Twilio account created
- [ ] WhatsApp Sandbox setup
- [ ] Joined WhatsApp Sandbox (sent join message)
- [ ] .env file updated dengan credentials
- [ ] Cache cleared: `php artisan cache:clear`
- [ ] Config cleared: `php artisan config:clear`
- [ ] Test via tinker successful
- [ ] Test via UI successful
- [ ] OTP received in WhatsApp! 🎉

---

## 📚 Reference

**Twilio Console Links:**
- Account Dashboard: https://console.twilio.com
- Auth Tokens: https://console.twilio.com/account/auth-tokens
- WhatsApp Sandbox: https://console.twilio.com/messaging/whatsapp/sandbox

**Dokumentasi:**
- Twilio WhatsApp: https://www.twilio.com/docs/whatsapp
- Twilio SDK: https://www.twilio.com/docs/libraries/php

---

## ⏱️ Total Time: ~15 minutes

1. Create Twilio account: 5 min
2. Setup WhatsApp Sandbox: 2 min
3. Update .env: 2 min
4. Test: 3 min
5. Buffer: 3 min

**After setup, WhatsApp OTP akan instant masuk! ✅**

---

## Next Steps

After successful test:
1. Update registered phone number di database
2. Test full flow (forgot password → verify OTP → reset password)
3. Done! WhatsApp password reset sekarang fully functional

---

Need help? Lihat error di `storage/logs/laravel.log` - usually ada clue disana!
