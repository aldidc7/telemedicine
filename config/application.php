<?php

/**
 * Application-wide Configuration Constants
 * 
 * Centralized constants untuk reusable values
 * Updated: Session 5 - Code Refactoring Phase
 */

return [
    // ============ PAGINATION ============
    
    'PAGINATION' => [
        'DEFAULT_PER_PAGE' => 15,
        'MAX_PER_PAGE' => 100,
    ],
    
    // ============ AUTHENTICATION ============
    
    // Email verification token expiry (dalam jam)
    'EMAIL_VERIFICATION_EXPIRES_HOURS' => 24,
    
    // Password reset token expiry (dalam menit)
    'PASSWORD_RESET_EXPIRES_MINUTES' => 15,
    
    // Max login attempts before lockout
    'MAX_LOGIN_ATTEMPTS' => 5,
    
    // Lockout duration (dalam menit)
    'LOCKOUT_DURATION_MINUTES' => 15,
    
    // ============ USER ROLES ============
    
    'ROLES' => [
        'ADMIN' => 'admin',
        'DOCTOR' => 'dokter',
        'PATIENT' => 'pasien',
    ],
    
    // ============ DATE & TIME ============
    
    // Default age untuk new user registrations (dalam tahun)
    'DEFAULT_AGE_FOR_REGISTRATION' => 25,
    
    // Lansia threshold (dalam tahun)
    'LANSIA_AGE_THRESHOLD' => 60,
    
    // ============ SEARCH & FILTERING ============
    
    // Max search result limit
    'MAX_SEARCH_RESULTS' => 100,
    
    // String preview length (untuk truncation)
    'STRING_PREVIEW_LENGTH' => 50,
    
    // ============ MESSAGE CONTENT ============
    
    // Max length untuk message content preview
    'MESSAGE_PREVIEW_LENGTH' => 50,
    
    // Message content max length
    'MESSAGE_MAX_LENGTH' => 5000,
    
    // ============ API RESPONSE CODES ============
    
    'RESPONSE_CODES' => [
        'SUCCESS' => 200,
        'CREATED' => 201,
        'BAD_REQUEST' => 400,
        'UNAUTHORIZED' => 401,
        'FORBIDDEN' => 403,
        'NOT_FOUND' => 404,
        'CONFLICT' => 409,
        'VALIDATION_ERROR' => 422,
        'INTERNAL_ERROR' => 500,
    ],
    
    // ============ DATABASE ============
    
    // Soft delete grace period (dalam hari)
    'SOFT_DELETE_GRACE_PERIOD_DAYS' => 30,
    
    // ============ MONITORING ============
    
    // Log retention (dalam hari)
    'LOG_RETENTION_DAYS' => 30,
    
    // Debug mode untuk development
    'DEBUG_MODE' => env('APP_DEBUG', false),
];
