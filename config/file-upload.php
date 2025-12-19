<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    | Konfigurasi untuk membatasi ukuran upload file agar tidak memenuhi
    | storage terlalu cepat dan menjaga performa sistem.
    |
    */

    'max_upload_sizes' => [
        // Foto profil dokter/pasien
        'profile_photo' => 5 * 1024 * 1024,         // 5 MB
        
        // File dokumen medis (PDF, Word)
        'medical_document' => 10 * 1024 * 1024,     // 10 MB
        
        // Foto hasil lab/radiologi
        'medical_image' => 15 * 1024 * 1024,        // 15 MB
        
        // Resep digital
        'prescription' => 5 * 1024 * 1024,          // 5 MB
        
        // File umum dalam chat konsultasi
        'consultation_file' => 8 * 1024 * 1024,     // 8 MB
        
        // Attachment dalam pesan
        'message_attachment' => 10 * 1024 * 1024,   // 10 MB
    ],

    'max_total_storage' => [
        // Total storage maksimal per user
        'patient' => 500 * 1024 * 1024,             // 500 MB per pasien
        'doctor' => 1024 * 1024 * 1024,             // 1 GB per dokter
        'hospital' => 10 * 1024 * 1024 * 1024,      // 10 GB per rumah sakit
    ],

    'allowed_mime_types' => [
        // Foto/Gambar
        'image' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/x-icon',
        ],
        
        // Dokumen
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ],
        
        // Audio/Video
        'media' => [
            'audio/mpeg',
            'audio/wav',
            'video/mp4',
            'video/quicktime',
        ],
        
        // Arsip
        'archive' => [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
        ],
    ],

    'allowed_extensions' => [
        // Foto/Gambar
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'ico',
        
        // Dokumen
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv',
        
        // Audio/Video
        'mp3', 'wav', 'mp4', 'mov',
        
        // Arsip
        'zip', 'rar', '7z',
    ],

    'blocked_extensions' => [
        // Executable files
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr',
        
        // Script files
        'php', 'php3', 'php4', 'php5', 'phtml', 'phps', 'pht', 'jsp', 'py', 'pl',
        
        // Library files
        'dll', 'so', 'dylib', 'sh', 'bash',
        
        // Macro files
        'msi', 'app', 'deb', 'rpm',
    ],

    'virus_scanning' => [
        'enabled' => env('VIRUS_SCAN_ENABLED', false),
        'engine' => env('VIRUS_SCAN_ENGINE', 'clamav'),  // clamav atau virustotal
        'quarantine_path' => storage_path('quarantine'),
    ],

    'storage_path' => [
        'profile_photo' => 'uploads/profiles',
        'medical_document' => 'uploads/documents',
        'medical_image' => 'uploads/medical-images',
        'prescription' => 'uploads/prescriptions',
        'consultation_file' => 'uploads/consultations',
        'message_attachment' => 'uploads/messages',
    ],

    'retention_policies' => [
        // Berapa lama file disimpan sebelum dihapus otomatis
        'temporary_files' => 7,      // 7 hari untuk file temp
        'deleted_files' => 30,       // 30 hari di trash sebelum permanent delete
        'archived_files' => 365,     // 1 tahun untuk file arsip
    ],

    'cleanup_schedule' => [
        // Otomatis cleanup file yang sudah expired
        'enabled' => true,
        'frequency' => 'daily',          // daily, weekly, monthly
        'time' => '02:00',               // jam 2 pagi
    ],

];
