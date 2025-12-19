## 🔧 QUICK API REFERENCE - CONSULTATION SUMMARY

**For Postman/API Testing - Copy-Paste Ready**

---

## 📌 BASE URL
```
http://localhost:8000/api/v1
```

## 🔑 HEADERS (All Requests)
```json
{
  "Authorization": "Bearer {YOUR_TOKEN}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

---

## 1️⃣ CREATE CONSULTATION SUMMARY

**Who:** Doctor only

**Endpoint:**
```
POST /api/v1/consultations/{id}/summary
```

**Example:**
```bash
curl -X POST http://localhost:8000/api/v1/consultations/123/summary \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "diagnosis": "Demam Berdarah Dengue",
    "clinical_findings": "Ruam petekia di dada, demam 39°C, hepatomegali",
    "examination_results": "Trombosit 90.000, Leukosit 4.500, Hematokrit 45%",
    "treatment_plan": "Istirahat total, minum cairan banyak, monitoring vital signs 2x sehari",
    "follow_up_date": "2025-12-26",
    "follow_up_instructions": "Kembali jika demam tidak turun atau ada perdarahan",
    "medications": [
      {
        "name": "Paracetamol",
        "dose": "500mg",
        "frequency": "3x sehari",
        "duration_days": 5,
        "instructions": "Setelah makan, jangan melebihi 4g per hari",
        "route": "oral"
      },
      {
        "name": "Vitamin C",
        "dose": "1000mg",
        "frequency": "1x sehari",
        "duration_days": 5,
        "route": "oral"
      },
      {
        "name": "Cairan IV",
        "dose": "500ml",
        "frequency": "Sesuai kebutuhan",
        "duration_days": 2,
        "route": "injection"
      }
    ],
    "referrals": [
      "Konsultasi dengan spesialis penyakit dalam jika kondisi memburuk",
      "Laboratorium untuk pengecekan trombosit ulang"
    ],
    "additional_notes": "Pasien disarankan untuk banyak istirahat dan menghindari aktivitas berat"
  }'
```

**Response (201):**
```json
{
  "status": "success",
  "message": "Kesimpulan konsultasi berhasil dibuat",
  "data": {
    "id": 1,
    "consultation_id": 123,
    "doctor_id": 5,
    "diagnosis": "Demam Berdarah Dengue",
    "clinical_findings": "Ruam petekia di dada, demam 39°C, hepatomegali",
    "examination_results": "Trombosit 90.000, Leukosit 4.500, Hematokrit 45%",
    "treatment_plan": "Istirahat total, minum cairan banyak...",
    "follow_up_date": "2025-12-26",
    "follow_up_instructions": "Kembali jika demam tidak turun atau ada perdarahan",
    "medications": [...],
    "patient_acknowledged": false,
    "patient_acknowledged_at": null,
    "created_at": "2025-12-19T14:20:00.000000Z",
    "updated_at": "2025-12-19T14:20:00.000000Z"
  },
  "meta": {
    "medications_count": 3,
    "has_follow_up": true
  }
}
```

---

## 2️⃣ GET CONSULTATION SUMMARY

**Who:** Patient, Doctor, Admin

**Endpoint:**
```
GET /api/v1/consultations/{id}/summary
```

**Example:**
```bash
curl -X GET http://localhost:8000/api/v1/consultations/123/summary \
  -H "Authorization: Bearer your_token_here" \
  -H "Accept: application/json"
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Summary konsultasi berhasil diambil",
  "data": {
    "id": 1,
    "consultation_id": 123,
    "doctor_id": 5,
    "diagnosis": "Demam Berdarah Dengue",
    "clinical_findings": "Ruam petekia di dada, demam 39°C, hepatomegali",
    "examination_results": "Trombosit 90.000",
    "treatment_plan": "Istirahat total, minum cairan banyak...",
    "follow_up_date": "2025-12-26",
    "follow_up_instructions": "Kembali jika demam tidak turun",
    "medications": [
      {
        "id": 1,
        "consultation_id": 123,
        "doctor_id": 5,
        "medication_name": "Paracetamol",
        "dose": "500mg",
        "frequency": "3x sehari",
        "duration_days": 5,
        "instructions": "Setelah makan",
        "route": "oral",
        "is_active": true,
        "status": "prescribed",
        "prescribed_at": "2025-12-19T14:20:00.000000Z",
        "filled_at": null,
        "created_at": "2025-12-19T14:20:00.000000Z"
      },
      {
        "id": 2,
        "medication_name": "Vitamin C",
        "dose": "1000mg",
        ...
      }
    ],
    "dokter": {
      "id": 5,
      "name": "Dr. Budi Santoso",
      "email": "budi@hospital.com",
      "spesialis": "Penyakit Dalam"
    },
    "patient_acknowledged": true,
    "patient_acknowledged_at": "2025-12-20T10:30:00.000000Z",
    "created_at": "2025-12-19T14:20:00.000000Z",
    "updated_at": "2025-12-19T14:20:00.000000Z"
  },
  "meta": {
    "medications_count": 2,
    "has_follow_up": true
  }
}
```

---

## 3️⃣ UPDATE CONSULTATION SUMMARY

**Who:** Doctor (owner), Admin

**Endpoint:**
```
PUT /api/v1/consultations/{id}/summary
```

**Example:**
```bash
curl -X PUT http://localhost:8000/api/v1/consultations/123/summary \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "diagnosis": "Demam Berdarah Dengue - Confirmed",
    "treatment_plan": "Istirahat total, minum cairan banyak, monitoring vital signs 2x sehari, IV fluids jika dehidrasi",
    "follow_up_date": "2025-12-27",
    "follow_up_instructions": "Kembali untuk pengecekan trombosit. Emergency: jika perdarahan, kondisi memburuk, atau demam naik"
  }'
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Summary berhasil diperbarui",
  "data": {
    "id": 1,
    "consultation_id": 123,
    "diagnosis": "Demam Berdarah Dengue - Confirmed",
    "treatment_plan": "Istirahat total, minum cairan banyak, monitoring vital signs 2x sehari, IV fluids jika dehidrasi",
    "follow_up_date": "2025-12-27",
    ...
  }
}
```

---

## 4️⃣ PATIENT ACKNOWLEDGE SUMMARY

**Who:** Patient only

**Endpoint:**
```
PUT /api/v1/consultations/{id}/summary/acknowledge
```

**Example:**
```bash
curl -X PUT http://localhost:8000/api/v1/consultations/123/summary/acknowledge \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json"
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Summary sudah dikonfirmasi dibaca",
  "data": {
    "id": 1,
    "consultation_id": 123,
    "patient_acknowledged": true,
    "patient_acknowledged_at": "2025-12-20T10:30:00.000000Z"
  }
}
```

---

## 5️⃣ LIST PATIENT SUMMARIES

**Who:** Patient (own), Admin (all)

**Endpoint:**
```
GET /api/v1/patient/summaries?per_page=15&acknowledged=true&from_date=2025-12-01&to_date=2025-12-31
```

**Query Parameters:**
```
per_page         : 15 (default)
acknowledged     : true/false (optional)
from_date        : YYYY-MM-DD (optional)
to_date          : YYYY-MM-DD (optional)
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/v1/patient/summaries?per_page=10&acknowledged=true" \
  -H "Authorization: Bearer your_token_here" \
  -H "Accept: application/json"
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Daftar summary pasien",
  "data": [
    {
      "id": 1,
      "consultation_id": 123,
      "diagnosis": "Demam Berdarah",
      "treatment_plan": "Istirahat, minum banyak",
      "follow_up_date": "2025-12-26",
      "medications_count": 2,
      "patient_acknowledged": true,
      "created_at": "2025-12-19T14:20:00.000000Z",
      "dokter": {
        "id": 5,
        "name": "Dr. Budi Santoso",
        "spesialis": "Penyakit Dalam"
      }
    },
    {
      "id": 2,
      "consultation_id": 124,
      "diagnosis": "Bronkitis",
      ...
    }
  ],
  "pagination": {
    "total": 25,
    "per_page": 10,
    "current_page": 1,
    "last_page": 3,
    "from": 1,
    "to": 10
  },
  "meta": {
    "total_summaries": 25
  }
}
```

---

## 6️⃣ LIST DOCTOR SUMMARIES

**Who:** Doctor (own), Admin (all)

**Endpoint:**
```
GET /api/v1/doctor/summaries?per_page=15&acknowledged=false
```

**Query Parameters:**
```
per_page         : 15 (default)
acknowledged     : true/false (optional)
from_date        : YYYY-MM-DD (optional)
to_date          : YYYY-MM-DD (optional)
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/v1/doctor/summaries?acknowledged=false" \
  -H "Authorization: Bearer your_token_here" \
  -H "Accept: application/json"
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Daftar summary dokter",
  "data": [
    {
      "id": 1,
      "consultation_id": 123,
      "patient": {
        "id": 10,
        "name": "Budi Santoso",
        "phone": "+62812345678"
      },
      "diagnosis": "Demam Berdarah",
      "medications_count": 2,
      "follow_up_date": "2025-12-26",
      "patient_acknowledged": true,
      "created_at": "2025-12-19T14:20:00.000000Z"
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4
  },
  "meta": {
    "statistics": {
      "total_summaries": 50,
      "acknowledged": 45,
      "pending_acknowledgement": 5,
      "with_follow_ups": 35
    }
  }
}
```

---

## 🚨 ERROR RESPONSES

### 400 Bad Request
```json
{
  "status": "error",
  "message": "Gagal membuat summary: Validation error",
  "errors": {
    "diagnosis": ["The diagnosis field is required"]
  }
}
```

### 401 Unauthorized
```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "Hanya dokter konsultasi yang bisa membuat summary"
}
```

### 404 Not Found
```json
{
  "status": "error",
  "message": "Konsultasi tidak ditemukan"
}
```

---

## ⚡ POSTMAN COLLECTION IMPORT

**Save as `consultation-summary.json`:**

```json
{
  "info": {
    "name": "Consultation Summary API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Create Summary",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}",
            "type": "text"
          },
          {
            "key": "Content-Type",
            "value": "application/json",
            "type": "text"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"diagnosis\": \"Demam Berdarah Dengue\",\n  \"clinical_findings\": \"Ruam petekia di dada\",\n  \"treatment_plan\": \"Istirahat, minum banyak\",\n  \"follow_up_date\": \"2025-12-26\",\n  \"medications\": [\n    {\n      \"name\": \"Paracetamol\",\n      \"dose\": \"500mg\",\n      \"frequency\": \"3x sehari\",\n      \"duration_days\": 5\n    }\n  ]\n}"
        },
        "url": {
          "raw": "{{base_url}}/api/v1/consultations/{{consultation_id}}/summary",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "consultations", "{{consultation_id}}", "summary"]
        }
      }
    },
    {
      "name": "Get Summary",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}",
            "type": "text"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/consultations/{{consultation_id}}/summary",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "consultations", "{{consultation_id}}", "summary"]
        }
      }
    },
    {
      "name": "Acknowledge Summary",
      "request": {
        "method": "PUT",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}",
            "type": "text"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/consultations/{{consultation_id}}/summary/acknowledge",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "consultations", "{{consultation_id}}", "summary", "acknowledge"]
        }
      }
    },
    {
      "name": "Patient Summaries",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}",
            "type": "text"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/patient/summaries?per_page=15",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "patient", "summaries"],
          "query": [
            {
              "key": "per_page",
              "value": "15"
            }
          ]
        }
      }
    },
    {
      "name": "Doctor Summaries",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}",
            "type": "text"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/doctor/summaries",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "doctor", "summaries"]
        }
      }
    }
  ]
}
```

---

## 🎯 WORKFLOW EXAMPLE

### Step-by-Step Integration:

```bash
# 1. Doctor Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -d "email=doctor@hospital.com&password=password"
# Response: { "token": "abc123..." }

# Set token as environment variable
export TOKEN="abc123..."

# 2. Create Summary After Consultation
curl -X POST http://localhost:8000/api/v1/consultations/123/summary \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"diagnosis": "...", ...}'

# 3. Patient Logs In
curl -X POST http://localhost:8000/api/v1/auth/login \
  -d "email=patient@email.com&password=password"
# Response: { "token": "xyz789..." }

export PATIENT_TOKEN="xyz789..."

# 4. Patient Views Summary
curl -X GET http://localhost:8000/api/v1/consultations/123/summary \
  -H "Authorization: Bearer $PATIENT_TOKEN"

# 5. Patient Acknowledges
curl -X PUT http://localhost:8000/api/v1/consultations/123/summary/acknowledge \
  -H "Authorization: Bearer $PATIENT_TOKEN"

# 6. Doctor Checks Statistics
curl -X GET http://localhost:8000/api/v1/doctor/summaries \
  -H "Authorization: Bearer $TOKEN"
```

---

## ✅ CHECKLIST BEFORE DEPLOYING

- [ ] Run migration: `php artisan migrate`
- [ ] Test all 6 endpoints
- [ ] Verify authorization (doctor/patient/admin)
- [ ] Check database (4 new tables created)
- [ ] Test with Postman
- [ ] Verify response formats
- [ ] Check error handling

---

**Last Updated:** December 19, 2025  
**API Version:** 1.0  
**Status:** ✅ Ready for Testing
