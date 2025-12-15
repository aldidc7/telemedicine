<?php

/**
 * Configuration untuk Appointment/Consultation Management
 * 
 * Centralized constants untuk menghindari magic numbers dan duplikasi
 * Updated: Session 5 - Code Refactoring Phase
 */

return [
    // ============ APPOINTMENT CONFIGURATION ============
    
    // Durasi slot appointment default (dalam menit)
    'SLOT_DURATION_MINUTES' => 30,
    
    // Working hours default (dalam format 24-jam)
    'WORKING_HOURS' => [
        'START' => 9,    // 9 AM
        'END' => 17,     // 5 PM
    ],
    
    // Cache TTL untuk available slots (dalam detik)
    'SLOT_CACHE_TTL' => 900, // 15 minutes
    
    // Pagination defaults
    'PER_PAGE' => 15,
    'PER_PAGE_DEFAULT' => 15,
    'PER_PAGE_MAX' => 100,
    
    // ============ STATUS & TRANSITIONS ============
    
    // Valid appointment statuses
    'VALID_STATUSES' => [
        'pending',      // Menunggu konfirmasi dokter
        'confirmed',    // Sudah dikonfirmasi dokter
        'rejected',     // Ditolak dokter
        'completed',    // Selesai
        'cancelled',    // Dibatalkan
        'no-show',      // Pasien tidak datang
    ],
    
    // Valid appointment status transitions
    // Format: FROM_STATUS => [TO_STATUS_1, TO_STATUS_2, ...]
    'STATUS_TRANSITIONS' => [
        'pending' => ['confirmed', 'rejected', 'cancelled'],
        'confirmed' => ['completed', 'cancelled', 'no-show'],
        'rejected' => [],
        'completed' => [],
        'cancelled' => [],
        'no-show' => [],
    ],
    
    // ============ CONSULTATION CONFIGURATION ============
    
    // Valid consultation statuses
    'CONSULTATION_STATUSES' => [
        'pending',      // Menunggu dimulai
        'aktif',        // Sedang berlangsung
        'selesai',      // Selesai
        'dibatalkan',   // Dibatalkan
    ],
    
    // Valid consultation status transitions
    'CONSULTATION_TRANSITIONS' => [
        'pending' => ['aktif', 'dibatalkan'],
        'aktif' => ['selesai', 'dibatalkan'],
        'selesai' => [],
        'dibatalkan' => [],
    ],
    
    // ============ PRESCRIPTION CONFIGURATION ============
    
    // Valid prescription statuses
    'PRESCRIPTION_STATUSES' => [
        'active',       // Masih aktif
        'expired',      // Expired
        'completed',    // Selesai/diambil
        'archived',     // Diarsipkan
    ],
    
    // Valid prescription status transitions
    'PRESCRIPTION_TRANSITIONS' => [
        'active' => ['expired', 'completed', 'archived'],
        'expired' => ['archived'],
        'completed' => ['archived'],
        'archived' => [],
    ],
    
    // ============ RATING CONFIGURATION ============
    
    // Valid rating statuses
    'RATING_STATUSES' => [
        'active',       // Aktif
        'archived',     // Diarsipkan
    ],
    
    // Valid rating status transitions
    'RATING_TRANSITIONS' => [
        'active' => ['archived'],
        'archived' => ['active'],
    ],
    
    // ============ CONCURRENT ACCESS CONFIGURATION ============
    
    // Max retry untuk deadlock scenario
    'DEADLOCK_MAX_RETRIES' => 3,
    
    // Backoff delay untuk retry (dalam microseconds)
    'DEADLOCK_BACKOFF_MIN' => 100000,  // 100ms
    'DEADLOCK_BACKOFF_MAX' => 500000,  // 500ms
    
    // Lock timeout (dalam detik)
    'LOCK_TIMEOUT' => 30,
    
    // ============ RATE LIMITING ============
    
    // Default rate limit (requests per minute)
    'RATE_LIMIT_PER_MINUTE' => 60,
    'RATE_LIMIT_DECAY' => 60,
    
    // Role multipliers untuk rate limiting
    'RATE_LIMIT_MULTIPLIERS' => [
        'admin' => 2.0,      // Admin dapat 2x limit
        'dokter' => 1.5,     // Dokter dapat 1.5x limit
        'pasien' => 1.0,     // Pasien dapat 1x limit
        'guest' => 0.5,      // Guest dapat 0.5x limit
    ],
    
    // ============ CACHE CONFIGURATION ============
    
    // Cache TTL values (dalam detik)
    'CACHE_TTL' => [
        'SHORT' => 300,      // 5 minutes untuk real-time data
        'MEDIUM' => 900,     // 15 minutes untuk stats
        'LONG' => 3600,      // 1 hour untuk trends
        'VERY_LONG' => 86400, // 24 hours untuk master data
    ],
    
    // Cache key prefixes
    'CACHE_KEYS' => [
        'APPOINTMENT_SLOTS' => 'appointment:slots:{doctorId}:{date}',
        'DOCTOR_AVAILABILITY' => 'doctor:availability:{doctorId}:{date}',
        'CONSULTATION_STATUS' => 'consultation:status:{consultationId}',
        'PRESCRIPTION_ACTIVE' => 'prescription:active:{patientId}',
        'RATING_AVERAGE' => 'rating:average:{doctorId}',
    ],
    
    // ============ VALIDATION RULES ============
    
    // Max concurrent consultations per doctor
    'MAX_CONCURRENT_CONSULTATIONS' => 5,
    
    // Min time before appointment can be booked (dalam jam)
    'MIN_HOURS_BEFORE_BOOKING' => 0,
    
    // Max time in advance appointment can be booked (dalam hari)
    'MAX_DAYS_ADVANCE_BOOKING' => 30,
    
    // ============ FILE UPLOAD CONFIGURATION ============
    
    // Max file size untuk uploads (dalam bytes)
    'MAX_FILE_SIZE' => 5 * 1024 * 1024, // 5 MB
    
    // Allowed MIME types untuk prescription files
    'ALLOWED_MIME_TYPES' => [
        'image/jpeg',
        'image/png',
        'application/pdf',
    ],
    
    // Allowed file extensions
    'ALLOWED_EXTENSIONS' => [
        'jpg',
        'jpeg',
        'png',
        'pdf',
    ],
];
