<?php

/**
 * Jitsi Video Conference Configuration
 * 
 * Konfigurasi untuk Jitsi Meet sebagai provider video conferencing
 */

return [
    /**
     * Jitsi Server URL
     * Default menggunakan public Jitsi instance, bisa dikustomisasi ke self-hosted
     */
    'server_url' => env('JITSI_SERVER_URL', 'https://meet.jit.si'),

    /**
     * App ID (untuk JWT token generation)
     * Gunakan domain unik untuk aplikasi Anda
     */
    'app_id' => env('JITSI_APP_ID', env('APP_NAME', 'telemedicine')),

    /**
     * Secret Key untuk JWT signing
     * WAJIB dikonfigurasi di environment untuk production
     */
    'secret_key' => env('JITSI_SECRET_KEY', 'your-secret-key-change-in-production'),

    /**
     * JWT Token Expiration (dalam detik)
     * Default: 24 jam
     */
    'token_ttl' => env('JITSI_TOKEN_TTL', 86400),

    /**
     * Jitsi API URL (jika menggunakan Jitsi API)
     */
    'api_url' => env('JITSI_API_URL', 'https://api.jitsi.net'),

    /**
     * Recording Configuration
     */
    'recording' => [
        'enabled' => env('JITSI_RECORDING_ENABLED', false),
        'service_url' => env('JITSI_RECORDING_SERVICE_URL', null),
    ],

    /**
     * Feature Flags
     */
    'features' => [
        'recording' => env('JITSI_FEATURE_RECORDING', false),
        'screen_sharing' => env('JITSI_FEATURE_SCREEN_SHARING', true),
        'chat' => env('JITSI_FEATURE_CHAT', true),
        'raise_hand' => env('JITSI_FEATURE_RAISE_HAND', true),
        'tile_view' => env('JITSI_FEATURE_TILE_VIEW', true),
        'stats' => env('JITSI_FEATURE_STATS', true),
        'virtual_background' => env('JITSI_FEATURE_VIRTUAL_BACKGROUND', false),
        'dial_out' => env('JITSI_FEATURE_DIAL_OUT', false),
        'invite' => env('JITSI_FEATURE_INVITE', true),
        'display_name' => env('JITSI_FEATURE_DISPLAY_NAME', true),
    ],

    /**
     * Interface Configuration
     */
    'interface' => [
        'lang' => env('JITSI_LANG', 'id'),
        'theme' => env('JITSI_THEME', 'light'),
    ],

    /**
     * Advanced Configuration
     */
    'advanced' => [
        // Bandwidth limit (kbps)
        'bandwidth' => [
            'audio' => env('JITSI_BANDWIDTH_AUDIO', 128),
            'video' => env('JITSI_BANDWIDTH_VIDEO', 2500),
            'screenshare' => env('JITSI_BANDWIDTH_SCREENSHARE', 4500),
        ],
        
        // Connection settings
        'connection' => [
            'transport' => env('JITSI_TRANSPORT', 'websocket'),
            'auto_connect' => env('JITSI_AUTO_CONNECT', true),
            'cloud_flare' => env('JITSI_CLOUDFLARE', false),
        ],

        // Log level
        'log_level' => env('JITSI_LOG_LEVEL', 'info'),
    ],

    /**
     * UI Configuration
     */
    'ui' => [
        'disable_audio_levels' => env('JITSI_DISABLE_AUDIO_LEVELS', false),
        'disable_invite' => env('JITSI_DISABLE_INVITE', false),
        'disable_profile' => env('JITSI_DISABLE_PROFILE', false),
        'hide_watermark' => env('JITSI_HIDE_WATERMARK', true),
    ],
];
