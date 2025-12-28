@component('mail::message')
# [APPOINTMENT] Appointment Reminder

Hello {{ $user->name }},

Kami ingin mengingatkan bahwa Anda memiliki appointment dengan dokter kami.

## Appointment Details

| Detail | Informasi |
|--------|-----------|
| **Dokter** | Dr. {{ $doctor->user->name }} |
| **Spesialisasi** | {{ $doctor->spesialization }} |
| **Tanggal & Jam** | {{ $appointmentTime }} |
| **Tipe Konsultasi** | Telemedicine (Video Call) |

## Yang Perlu Anda Persiapkan

✓ Pastikan Anda memiliki koneksi internet yang stabil  
✓ Pastikan perangkat (HP, Tablet, atau Laptop) sudah terisi baterai  
✓ Cari tempat yang tenang untuk konsultasi  
✓ Siapkan gejala atau pertanyaan medis yang ingin Anda tanyakan  

## Sebelum Appointment

Silakan masuk ke aplikasi telemedicine Anda **5 menit sebelum waktu appointment** untuk memastikan koneksi dan video berfungsi dengan baik.

## Tautan Quick Link

Aplikasi kami akan mengirimkan tautan chat otomatis pada waktu yang ditentukan.

---

Jika Anda perlu membatalkan atau menangguhkan appointment, silakan hubungi dokter atau tim support kami.

Terimakasih,

**Tim Telemedicine**

---

@component('mail::subcopy')
Jika Anda mengalami kesulitan, silakan hubungi customer support kami di support@telemedicine.com atau hubungi nomor hotline kami.
@endcomponent

@endcomponent
