<?php

return [
    /*
     * Token untuk SIMRS API authentication
     * Gunakan env variable untuk production
     */
    'api_token' => env('SIMRS_API_TOKEN', 'token_simrs_dummy_123'),

    /*
     * Base URL SIMRS API (jika ada integrasi real)
     */
    'base_url' => env('SIMRS_BASE_URL', 'http://localhost:8000/simrs'),

    /*
     * Timeout untuk koneksi ke SIMRS (dalam detik)
     */
    'timeout' => env('SIMRS_TIMEOUT', 30),
];