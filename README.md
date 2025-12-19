# 🏥 Telemedicine Application

**Status:** ✅ 92% Production Ready | 🎓 Thesis Complete

A modern telemedicine web application enabling patients to consult with doctors via real-time chat, manage medical records, and rate healthcare providers.

---

## ✨ Key Features

### 👥 For Patients
- ✅ Register & manage profile with medical history
- ✅ Search & filter doctors by specialization
- ✅ Book consultations with doctors
- ✅ Real-time chat messaging with assigned doctor
- ✅ Upload & manage medical documents
- ✅ View consultation history
- ✅ Rate & review doctors
- ✅ Responsive mobile-friendly interface

### 👨‍⚕️ For Doctors
- ✅ Verify professional credentials
- ✅ Set availability & specialization
- ✅ Accept/reject consultation requests
- ✅ Real-time messaging with patients
- ✅ Create & manage prescriptions
- ✅ View patient medical records
- ✅ Track consultation history

### 🔐 Admin Dashboard
- ✅ User management (patients, doctors, admins)
- ✅ Doctor verification & approval
- ✅ System analytics & statistics
- ✅ Activity logging & audit trail
- ✅ Consultation tracking

---

## 🛠 Tech Stack

**Backend:**
- Laravel 11+
- PHP 8.2+
- MySQL/PostgreSQL
- Sanctum (Authentication)
- Pusher (Real-time Broadcasting)
- Redis (Caching)

**Frontend:**
- Vue.js 3
- Tailwind CSS
- Axios (HTTP Client)
- Responsive Design

**Infrastructure:**
- Docker-ready
- CI/CD compatible
- RESTful API (35+ endpoints)

---

## 📊 Project Statistics

| Category | Count |
|----------|-------|
| **API Endpoints** | 35+ |
| **Database Tables** | 20+ |
| **Vue Components** | 25+ |
| **Frontend Pages** | 12 |
| **Test Cases** | 26+ |
| **Lines of Code** | 50,000+ |

---

## 🚀 Quick Start

### Prerequisites
```bash
- PHP 8.2+
- Composer
- Node.js 16+
- MySQL/PostgreSQL
- Redis
- Pusher account (for real-time features)
```

### Installation

1. **Clone repository**
```bash
git clone https://github.com/aldidc7/telemedicine.git
cd telemedicine
```

2. **Setup backend**
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
```

3. **Setup frontend**
```bash
npm install
npm run dev
```

4. **Start servers**
```bash
php artisan serve
npm run build  # for production
```

---

## 📚 API Documentation

Full API documentation available in `Telemedicine_API_Collection.postman_collection.json`

### Main Endpoints

**Authentication:**
- `POST /api/v1/auth/register` - Register user
- `POST /api/v1/auth/login` - Login user
- `POST /api/v1/auth/logout` - Logout user

**Consultations:**
- `GET /api/v1/konsultasi` - List consultations
- `POST /api/v1/konsultasi` - Create consultation
- `PUT /api/v1/konsultasi/{id}/accept` - Accept consultation
- `PUT /api/v1/konsultasi/{id}/close` - Close consultation

**Messaging:**
- `GET /api/v1/pesan/{konsultasiId}` - Get messages
- `POST /api/v1/pesan` - Send message
- `DELETE /api/v1/pesan/{id}` - Delete message

**Medical Records:**
- `GET /api/v1/rekam-medis` - List medical records
- `POST /api/v1/rekam-medis` - Create record
- `GET /api/v1/rekam-medis/{id}` - Get record details

**File Upload:**
- `POST /api/files/upload` - Upload file
- `GET /api/files/storage-info` - Get storage info
- `DELETE /api/files/{path}` - Delete file

---

## 🧪 Testing

Run tests:
```bash
php artisan test
```

Test coverage:
```bash
php artisan test --coverage
```

---

## 📁 Project Structure

```
telemedicine/
├── app/
│   ├── Http/Controllers/    # API controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic
│   ├── Policies/            # Authorization policies
│   └── Mail/                # Notification classes
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── js/views/            # Vue.js pages
│   ├── js/components/       # Vue components
│   └── css/                 # Tailwind stylesheets
├── routes/
│   ├── api.php              # API routes
│   └── web.php              # Web routes
├── tests/
│   ├── Feature/             # Feature tests
│   ├── Unit/                # Unit tests
│   └── Integration/         # Integration tests
├── storage/                 # File storage
├── public/                  # Public assets
└── config/                  # Configuration files
```

---

## 🔐 Security Features

- ✅ Token-based authentication (Sanctum)
- ✅ Password hashing (bcrypt)
- ✅ Authorization policies
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Rate limiting
- ✅ Encrypted sensitive data
- ✅ Activity logging
- ✅ Audit trail

---

## 📱 Responsive Design

Fully responsive across:
- 📱 Mobile phones (320px+)
- 📱 Tablets (768px+)
- 💻 Desktops (1024px+)

---

## 🚀 Deployment

### Local Development
```bash
php artisan serve
npm run dev
```

### Production
```bash
# Build frontend
npm run build

# Setup environment
cp .env.example .env.production
# Update .env with production values

# Run migrations
php artisan migrate --force

# Start application
php artisan config:cache
php artisan route:cache
```

---

## 📝 License

This project is licensed under the MIT License.

---

## 👨‍💻 Author

Developed for thesis project - Telemedicine Application  
**GitHub:** https://github.com/aldidc7/telemedicine

---

## 🙏 Support

For issues or questions, please create an issue on GitHub.

---

**Last Updated:** December 19, 2025  
**Version:** 1.0.0  
**Status:** Production Ready ✅
