<?php

/**
 * Redis Cache Strategy Configuration
 * 
 * Defines caching strategy for all application components
 * Key naming, TTLs, tags, and invalidation patterns
 */

return [
    'enabled' => true,

    'driver' => env('CACHE_DRIVER', 'redis'),

    /**
     * Cache key naming strategy
     * Pattern: component:entity:operation:identifier
     * Example: appointments:slots:available:doctor:1:date:2024-12-20
     */
    'naming' => [
        'separator' => ':',
        'prefix' => env('CACHE_PREFIX', 'telemedicine'),
        'include_version' => true,
    ],

    /**
     * Cache tags for selective invalidation
     */
    'tags' => [
        'appointments' => 'appointment related data',
        'consultations' => 'consultation related data',
        'prescriptions' => 'prescription related data',
        'ratings' => 'doctor ratings',
        'users' => 'user profile data',
        'dashboard' => 'doctor dashboard statistics',
        'doctors' => 'doctor availability and info',
        'patient_data' => 'patient specific data',
    ],

    /**
     * Specific cache configurations
     */
    'caches' => [
        'available_slots' => [
            'key_pattern' => 'appointments:slots:available:{doctorId}:{date}',
            'ttl' => 900, // 15 minutes
            'tags' => ['appointments'],
            'description' => 'Doctor available appointment slots for a specific date',
            'invalidation_triggers' => [
                'appointment_booked',
                'appointment_cancelled',
                'doctor_schedule_changed',
            ],
        ],

        'doctor_availability' => [
            'key_pattern' => 'doctors:availability:{doctorId}',
            'ttl' => 1800, // 30 minutes
            'tags' => ['doctors'],
            'description' => 'Doctor overall availability and working hours',
            'invalidation_triggers' => [
                'doctor_profile_updated',
                'doctor_hours_changed',
            ],
        ],

        'patient_appointments' => [
            'key_pattern' => 'patients:appointments:{patientId}',
            'ttl' => 300, // 5 minutes (short because status changes frequently)
            'tags' => ['patient_data'],
            'description' => 'Patient list of appointments with pagination',
            'invalidation_triggers' => [
                'appointment_booked',
                'appointment_confirmed',
                'appointment_cancelled',
                'appointment_completed',
            ],
        ],

        'doctor_statistics' => [
            'key_pattern' => 'dashboard:doctor:{doctorId}:stats',
            'ttl' => 3600, // 1 hour
            'tags' => ['dashboard'],
            'description' => 'Doctor dashboard statistics and metrics',
            'invalidation_triggers' => [
                'appointment_completed',
                'consultation_ended',
                'rating_added',
            ],
        ],

        'consultation_list' => [
            'key_pattern' => 'consultations:list:{type}:{userId}',
            'ttl' => 300, // 5 minutes
            'tags' => ['consultations'],
            'description' => 'Doctor/Patient consultation list with pagination',
            'invalidation_triggers' => [
                'consultation_started',
                'consultation_ended',
                'consultation_cancelled',
            ],
        ],

        'prescription_list' => [
            'key_pattern' => 'prescriptions:list:{consultationId}',
            'ttl' => 600, // 10 minutes
            'tags' => ['prescriptions'],
            'description' => 'Prescriptions for a specific consultation',
            'invalidation_triggers' => [
                'prescription_added',
                'prescription_updated',
                'prescription_deleted',
            ],
        ],

        'doctor_rating_average' => [
            'key_pattern' => 'ratings:average:{doctorId}',
            'ttl' => 7200, // 2 hours
            'tags' => ['ratings'],
            'description' => 'Doctor average rating score',
            'invalidation_triggers' => [
                'rating_added',
                'rating_updated',
            ],
        ],

        'user_profile' => [
            'key_pattern' => 'users:profile:{userId}',
            'ttl' => 1800, // 30 minutes
            'tags' => ['users'],
            'description' => 'User profile information',
            'invalidation_triggers' => [
                'user_profile_updated',
                'user_status_changed',
            ],
        ],
    ],

    /**
     * Cache warming strategy
     * Automatically warm frequently accessed data
     */
    'warming' => [
        'enabled' => true,
        'schedule' => '0 * * * *', // Every hour
        'strategies' => [
            'available_slots' => [
                'enabled' => true,
                'days_ahead' => 7,
                'description' => 'Pre-generate slots for all doctors for next 7 days',
            ],
            'doctor_availability' => [
                'enabled' => true,
                'description' => 'Pre-cache all doctor availability',
            ],
            'user_profiles' => [
                'enabled' => true,
                'limit' => 100, // Top 100 active users
                'description' => 'Pre-cache profiles of active users',
            ],
        ],
    ],

    /**
     * Cache hit rate monitoring
     * Track performance metrics
     */
    'monitoring' => [
        'enabled' => true,
        'track_hits' => true,
        'track_misses' => true,
        'track_evictions' => true,
        'metrics_retention' => 2592000, // 30 days
    ],

    /**
     * Invalidation strategies
     */
    'invalidation' => [
        'strategy' => 'tag-based', // tag-based or key-based
        'cascade' => true, // Invalidate related caches
        'log_all' => true, // Log all invalidation events
    ],

    /**
     * Redis-specific configuration
     */
    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
        'prefix' => env('REDIS_CACHE_PREFIX', 'cache_'),
        'serializer' => 'json', // json or php
        'compression' => true,
        'options' => [
            'tcp_keepalives' => true,
            'tcp_keepalive_interval' => 300,
        ],
    ],

    /**
     * Fallback cache for when Redis is down
     */
    'fallback' => [
        'enabled' => true,
        'driver' => 'file', // Use file cache as fallback
        'degraded_ttl' => 300, // 5 minutes when degraded
    ],

    /**
     * Cache size management
     */
    'size_management' => [
        'max_keys' => 10000,
        'eviction_policy' => 'allkeys-lru', // LRU eviction
        'monitoring_interval' => 3600, // Check every hour
    ],
];
