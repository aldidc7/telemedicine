<?php return array (
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => '12',
      'verify' => true,
      'limit' => NULL,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'D:\\Aplications\\telemedicine\\resources\\views',
    ),
    'compiled' => 'D:\\Aplications\\telemedicine\\storage\\framework\\views',
  ),
  'app' => 
  array (
    'name' => 'Laravel',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:bBzVDRJEoCxWNCGmha+PVW0J4y2rkk9vwKDjrBn+96c=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
      'store' => 'database',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'App\\Providers\\AppServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Benchmark' => 'Illuminate\\Support\\Benchmark',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'Uri' => 'Illuminate\\Support\\Uri',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
  ),
  'application' => 
  array (
    'PAGINATION' => 
    array (
      'DEFAULT_PER_PAGE' => 15,
      'MAX_PER_PAGE' => 100,
    ),
    'EMAIL_VERIFICATION_EXPIRES_HOURS' => 24,
    'PASSWORD_RESET_EXPIRES_MINUTES' => 15,
    'MAX_LOGIN_ATTEMPTS' => 5,
    'LOCKOUT_DURATION_MINUTES' => 15,
    'ROLES' => 
    array (
      'ADMIN' => 'admin',
      'DOCTOR' => 'dokter',
      'PATIENT' => 'pasien',
    ),
    'DEFAULT_AGE_FOR_REGISTRATION' => 25,
    'LANSIA_AGE_THRESHOLD' => 60,
    'MAX_SEARCH_RESULTS' => 100,
    'STRING_PREVIEW_LENGTH' => 50,
    'MESSAGE_PREVIEW_LENGTH' => 50,
    'MESSAGE_MAX_LENGTH' => 5000,
    'RESPONSE_CODES' => 
    array (
      'SUCCESS' => 200,
      'CREATED' => 201,
      'BAD_REQUEST' => 400,
      'UNAUTHORIZED' => 401,
      'FORBIDDEN' => 403,
      'NOT_FOUND' => 404,
      'CONFLICT' => 409,
      'VALIDATION_ERROR' => 422,
      'INTERNAL_ERROR' => 500,
    ),
    'SOFT_DELETE_GRACE_PERIOD_DAYS' => 30,
    'LOG_RETENTION_DAYS' => 30,
    'DEBUG_MODE' => true,
  ),
  'appointment' => 
  array (
    'SLOT_DURATION_MINUTES' => 30,
    'WORKING_HOURS' => 
    array (
      'START' => 9,
      'END' => 17,
    ),
    'SLOT_CACHE_TTL' => 900,
    'PER_PAGE' => 15,
    'PER_PAGE_DEFAULT' => 15,
    'PER_PAGE_MAX' => 100,
    'VALID_STATUSES' => 
    array (
      0 => 'pending',
      1 => 'confirmed',
      2 => 'rejected',
      3 => 'completed',
      4 => 'cancelled',
      5 => 'no-show',
    ),
    'STATUS_TRANSITIONS' => 
    array (
      'pending' => 
      array (
        0 => 'confirmed',
        1 => 'rejected',
        2 => 'cancelled',
      ),
      'confirmed' => 
      array (
        0 => 'completed',
        1 => 'cancelled',
        2 => 'no-show',
      ),
      'rejected' => 
      array (
      ),
      'completed' => 
      array (
      ),
      'cancelled' => 
      array (
      ),
      'no-show' => 
      array (
      ),
    ),
    'CONSULTATION_STATUSES' => 
    array (
      0 => 'pending',
      1 => 'aktif',
      2 => 'selesai',
      3 => 'dibatalkan',
    ),
    'CONSULTATION_TRANSITIONS' => 
    array (
      'pending' => 
      array (
        0 => 'aktif',
        1 => 'dibatalkan',
      ),
      'aktif' => 
      array (
        0 => 'selesai',
        1 => 'dibatalkan',
      ),
      'selesai' => 
      array (
      ),
      'dibatalkan' => 
      array (
      ),
    ),
    'PRESCRIPTION_STATUSES' => 
    array (
      0 => 'active',
      1 => 'expired',
      2 => 'completed',
      3 => 'archived',
    ),
    'PRESCRIPTION_TRANSITIONS' => 
    array (
      'active' => 
      array (
        0 => 'expired',
        1 => 'completed',
        2 => 'archived',
      ),
      'expired' => 
      array (
        0 => 'archived',
      ),
      'completed' => 
      array (
        0 => 'archived',
      ),
      'archived' => 
      array (
      ),
    ),
    'RATING_STATUSES' => 
    array (
      0 => 'active',
      1 => 'archived',
    ),
    'RATING_TRANSITIONS' => 
    array (
      'active' => 
      array (
        0 => 'archived',
      ),
      'archived' => 
      array (
        0 => 'active',
      ),
    ),
    'DEADLOCK_MAX_RETRIES' => 3,
    'DEADLOCK_BACKOFF_MIN' => 100000,
    'DEADLOCK_BACKOFF_MAX' => 500000,
    'LOCK_TIMEOUT' => 30,
    'RATE_LIMIT_PER_MINUTE' => 60,
    'RATE_LIMIT_DECAY' => 60,
    'RATE_LIMIT_MULTIPLIERS' => 
    array (
      'admin' => 2.0,
      'dokter' => 1.5,
      'pasien' => 1.0,
      'guest' => 0.5,
    ),
    'CACHE_TTL' => 
    array (
      'SHORT' => 300,
      'MEDIUM' => 900,
      'LONG' => 3600,
      'VERY_LONG' => 86400,
    ),
    'CACHE_KEYS' => 
    array (
      'APPOINTMENT_SLOTS' => 'appointment:slots:{doctorId}:{date}',
      'DOCTOR_AVAILABILITY' => 'doctor:availability:{doctorId}:{date}',
      'CONSULTATION_STATUS' => 'consultation:status:{consultationId}',
      'PRESCRIPTION_ACTIVE' => 'prescription:active:{patientId}',
      'RATING_AVERAGE' => 'rating:average:{doctorId}',
    ),
    'MAX_CONCURRENT_CONSULTATIONS' => 5,
    'MIN_HOURS_BEFORE_BOOKING' => 0,
    'MAX_DAYS_ADVANCE_BOOKING' => 30,
    'MAX_FILE_SIZE' => 5242880,
    'ALLOWED_MIME_TYPES' => 
    array (
      0 => 'image/jpeg',
      1 => 'image/png',
      2 => 'application/pdf',
    ),
    'ALLOWED_EXTENSIONS' => 
    array (
      0 => 'jpg',
      1 => 'jpeg',
      2 => 'png',
      3 => 'pdf',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'broadcasting' => 
  array (
    'default' => 'pusher',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => 'your_pusher_app_key',
        'secret' => 'your_pusher_app_secret',
        'app_id' => 'your_pusher_app_id',
        'options' => 
        array (
          'host' => 'api-mt.pusher.com',
          'port' => 443,
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'database',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'session' => 
      array (
        'driver' => 'session',
        'key' => '_cache',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'cache',
        'lock_connection' => NULL,
        'lock_table' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'D:\\Aplications\\telemedicine\\storage\\framework/cache/data',
        'lock_path' => 'D:\\Aplications\\telemedicine\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
      'failover' => 
      array (
        'driver' => 'failover',
        'stores' => 
        array (
          0 => 'database',
          1 => 'array',
        ),
      ),
    ),
    'prefix' => 'laravel-cache-',
  ),
  'cache-strategy' => 
  array (
    'enabled' => true,
    'driver' => 'redis',
    'naming' => 
    array (
      'separator' => ':',
      'prefix' => 'telemedicine',
      'include_version' => true,
    ),
    'tags' => 
    array (
      'appointments' => 'appointment related data',
      'consultations' => 'consultation related data',
      'prescriptions' => 'prescription related data',
      'ratings' => 'doctor ratings',
      'users' => 'user profile data',
      'dashboard' => 'doctor dashboard statistics',
      'doctors' => 'doctor availability and info',
      'patient_data' => 'patient specific data',
    ),
    'caches' => 
    array (
      'available_slots' => 
      array (
        'key_pattern' => 'appointments:slots:available:{doctorId}:{date}',
        'ttl' => 900,
        'tags' => 
        array (
          0 => 'appointments',
        ),
        'description' => 'Doctor available appointment slots for a specific date',
        'invalidation_triggers' => 
        array (
          0 => 'appointment_booked',
          1 => 'appointment_cancelled',
          2 => 'doctor_schedule_changed',
        ),
      ),
      'doctor_availability' => 
      array (
        'key_pattern' => 'doctors:availability:{doctorId}',
        'ttl' => 1800,
        'tags' => 
        array (
          0 => 'doctors',
        ),
        'description' => 'Doctor overall availability and working hours',
        'invalidation_triggers' => 
        array (
          0 => 'doctor_profile_updated',
          1 => 'doctor_hours_changed',
        ),
      ),
      'patient_appointments' => 
      array (
        'key_pattern' => 'patients:appointments:{patientId}',
        'ttl' => 300,
        'tags' => 
        array (
          0 => 'patient_data',
        ),
        'description' => 'Patient list of appointments with pagination',
        'invalidation_triggers' => 
        array (
          0 => 'appointment_booked',
          1 => 'appointment_confirmed',
          2 => 'appointment_cancelled',
          3 => 'appointment_completed',
        ),
      ),
      'doctor_statistics' => 
      array (
        'key_pattern' => 'dashboard:doctor:{doctorId}:stats',
        'ttl' => 3600,
        'tags' => 
        array (
          0 => 'dashboard',
        ),
        'description' => 'Doctor dashboard statistics and metrics',
        'invalidation_triggers' => 
        array (
          0 => 'appointment_completed',
          1 => 'consultation_ended',
          2 => 'rating_added',
        ),
      ),
      'consultation_list' => 
      array (
        'key_pattern' => 'consultations:list:{type}:{userId}',
        'ttl' => 300,
        'tags' => 
        array (
          0 => 'consultations',
        ),
        'description' => 'Doctor/Patient consultation list with pagination',
        'invalidation_triggers' => 
        array (
          0 => 'consultation_started',
          1 => 'consultation_ended',
          2 => 'consultation_cancelled',
        ),
      ),
      'prescription_list' => 
      array (
        'key_pattern' => 'prescriptions:list:{consultationId}',
        'ttl' => 600,
        'tags' => 
        array (
          0 => 'prescriptions',
        ),
        'description' => 'Prescriptions for a specific consultation',
        'invalidation_triggers' => 
        array (
          0 => 'prescription_added',
          1 => 'prescription_updated',
          2 => 'prescription_deleted',
        ),
      ),
      'doctor_rating_average' => 
      array (
        'key_pattern' => 'ratings:average:{doctorId}',
        'ttl' => 7200,
        'tags' => 
        array (
          0 => 'ratings',
        ),
        'description' => 'Doctor average rating score',
        'invalidation_triggers' => 
        array (
          0 => 'rating_added',
          1 => 'rating_updated',
        ),
      ),
      'user_profile' => 
      array (
        'key_pattern' => 'users:profile:{userId}',
        'ttl' => 1800,
        'tags' => 
        array (
          0 => 'users',
        ),
        'description' => 'User profile information',
        'invalidation_triggers' => 
        array (
          0 => 'user_profile_updated',
          1 => 'user_status_changed',
        ),
      ),
    ),
    'warming' => 
    array (
      'enabled' => true,
      'schedule' => '0 * * * *',
      'strategies' => 
      array (
        'available_slots' => 
        array (
          'enabled' => true,
          'days_ahead' => 7,
          'description' => 'Pre-generate slots for all doctors for next 7 days',
        ),
        'doctor_availability' => 
        array (
          'enabled' => true,
          'description' => 'Pre-cache all doctor availability',
        ),
        'user_profiles' => 
        array (
          'enabled' => true,
          'limit' => 100,
          'description' => 'Pre-cache profiles of active users',
        ),
      ),
    ),
    'monitoring' => 
    array (
      'enabled' => true,
      'track_hits' => true,
      'track_misses' => true,
      'track_evictions' => true,
      'metrics_retention' => 2592000,
    ),
    'invalidation' => 
    array (
      'strategy' => 'tag-based',
      'cascade' => true,
      'log_all' => true,
    ),
    'redis' => 
    array (
      'host' => '127.0.0.1',
      'password' => NULL,
      'port' => '6379',
      'database' => 1,
      'prefix' => 'cache_',
      'serializer' => 'json',
      'compression' => true,
      'options' => 
      array (
        'tcp_keepalives' => true,
        'tcp_keepalive_interval' => 300,
      ),
    ),
    'fallback' => 
    array (
      'enabled' => true,
      'driver' => 'file',
      'degraded_ttl' => 300,
    ),
    'size_management' => 
    array (
      'max_keys' => 10000,
      'eviction_policy' => 'allkeys-lru',
      'monitoring_interval' => 3600,
    ),
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => 'http://localhost:3000',
      1 => 'http://localhost:5173',
      2 => 'http://localhost:8000',
      3 => 'http://127.0.0.1:3000',
      4 => 'http://127.0.0.1:5173',
      5 => 'http://127.0.0.1:8000',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
      0 => 'Authorization',
      1 => 'Content-Type',
      2 => 'X-Total-Count',
      3 => 'X-Per-Page',
      4 => 'X-Current-Page',
      5 => 'X-Total-Pages',
      6 => 'X-RateLimit-Limit',
      7 => 'X-RateLimit-Remaining',
      8 => 'X-RateLimit-Reset',
    ),
    'max_age' => 0,
    'supports_credentials' => true,
  ),
  'database' => 
  array (
    'default' => 'sqlite',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'database/database.sqlite',
        'prefix' => '',
        'foreign_key_constraints' => true,
        'busy_timeout' => NULL,
        'journal_mode' => NULL,
        'synchronous' => NULL,
        'transaction_mode' => 'DEFERRED',
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'database/database.sqlite',
        'username' => 'root',
        'password' => 'root',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => 'InnoDB',
        'modes' => 
        array (
          0 => 'STRICT_TRANS_TABLES',
          1 => 'ERROR_FOR_DIVISION_BY_ZERO',
          2 => 'NO_ENGINE_SUBSTITUTION',
        ),
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'database/database.sqlite',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '5432',
        'database' => 'database/database.sqlite',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '1433',
        'database' => 'database/database.sqlite',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 
    array (
      'table' => 'migrations',
      'update_date_on_publish' => true,
    ),
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'laravel-database-',
        'persistent' => false,
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
        'max_retries' => 3,
        'backoff_algorithm' => 'decorrelated_jitter',
        'backoff_base' => 100,
        'backoff_cap' => 1000,
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
        'max_retries' => 3,
        'backoff_algorithm' => 'decorrelated_jitter',
        'backoff_base' => 100,
        'backoff_cap' => 1000,
      ),
    ),
  ),
  'file-upload' => 
  array (
    'max_upload_sizes' => 
    array (
      'profile_photo' => 5242880,
      'medical_document' => 10485760,
      'medical_image' => 15728640,
      'prescription' => 5242880,
      'consultation_file' => 8388608,
      'message_attachment' => 10485760,
    ),
    'max_total_storage' => 
    array (
      'patient' => 524288000,
      'doctor' => 1073741824,
      'hospital' => 10737418240,
    ),
    'allowed_mime_types' => 
    array (
      'image' => 
      array (
        0 => 'image/jpeg',
        1 => 'image/png',
        2 => 'image/gif',
        3 => 'image/webp',
        4 => 'image/x-icon',
      ),
      'document' => 
      array (
        0 => 'application/pdf',
        1 => 'application/msword',
        2 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        3 => 'application/vnd.ms-excel',
        4 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        5 => 'text/plain',
        6 => 'text/csv',
      ),
      'media' => 
      array (
        0 => 'audio/mpeg',
        1 => 'audio/wav',
        2 => 'video/mp4',
        3 => 'video/quicktime',
      ),
      'archive' => 
      array (
        0 => 'application/zip',
        1 => 'application/x-rar-compressed',
        2 => 'application/x-7z-compressed',
      ),
    ),
    'allowed_extensions' => 
    array (
      0 => 'jpg',
      1 => 'jpeg',
      2 => 'png',
      3 => 'gif',
      4 => 'webp',
      5 => 'ico',
      6 => 'pdf',
      7 => 'doc',
      8 => 'docx',
      9 => 'xls',
      10 => 'xlsx',
      11 => 'txt',
      12 => 'csv',
      13 => 'mp3',
      14 => 'wav',
      15 => 'mp4',
      16 => 'mov',
      17 => 'zip',
      18 => 'rar',
      19 => '7z',
    ),
    'blocked_extensions' => 
    array (
      0 => 'exe',
      1 => 'bat',
      2 => 'cmd',
      3 => 'com',
      4 => 'pif',
      5 => 'scr',
      6 => 'php',
      7 => 'php3',
      8 => 'php4',
      9 => 'php5',
      10 => 'phtml',
      11 => 'phps',
      12 => 'pht',
      13 => 'jsp',
      14 => 'py',
      15 => 'pl',
      16 => 'dll',
      17 => 'so',
      18 => 'dylib',
      19 => 'sh',
      20 => 'bash',
      21 => 'msi',
      22 => 'app',
      23 => 'deb',
      24 => 'rpm',
    ),
    'virus_scanning' => 
    array (
      'enabled' => false,
      'engine' => 'clamav',
      'quarantine_path' => 'D:\\Aplications\\telemedicine\\storage\\quarantine',
    ),
    'storage_path' => 
    array (
      'profile_photo' => 'uploads/profiles',
      'medical_document' => 'uploads/documents',
      'medical_image' => 'uploads/medical-images',
      'prescription' => 'uploads/prescriptions',
      'consultation_file' => 'uploads/consultations',
      'message_attachment' => 'uploads/messages',
    ),
    'retention_policies' => 
    array (
      'temporary_files' => 7,
      'deleted_files' => 30,
      'archived_files' => 365,
    ),
    'cleanup_schedule' => 
    array (
      'enabled' => true,
      'frequency' => 'daily',
      'time' => '02:00',
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'D:\\Aplications\\telemedicine\\storage\\app/private',
        'serve' => true,
        'throw' => false,
        'report' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'D:\\Aplications\\telemedicine\\storage\\app/public',
        'url' => 'http://localhost/storage',
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
        'report' => false,
      ),
    ),
    'links' => 
    array (
      'D:\\Aplications\\telemedicine\\public\\storage' => 'D:\\Aplications\\telemedicine\\storage\\app/public',
    ),
  ),
  'jitsi' => 
  array (
    'server_url' => 'https://meet.jit.si',
    'app_id' => 'Laravel',
    'secret_key' => 'your-secret-key-change-in-production',
    'token_ttl' => 86400,
    'api_url' => 'https://api.jitsi.net',
    'recording' => 
    array (
      'enabled' => false,
      'service_url' => NULL,
    ),
    'features' => 
    array (
      'recording' => false,
      'screen_sharing' => true,
      'chat' => true,
      'raise_hand' => true,
      'tile_view' => true,
      'stats' => true,
      'virtual_background' => false,
      'dial_out' => false,
      'invite' => true,
      'display_name' => true,
    ),
    'interface' => 
    array (
      'lang' => 'id',
      'theme' => 'light',
    ),
    'advanced' => 
    array (
      'bandwidth' => 
      array (
        'audio' => 128,
        'video' => 2500,
        'screenshare' => 4500,
      ),
      'connection' => 
      array (
        'transport' => 'websocket',
        'auto_connect' => true,
        'cloud_flare' => false,
      ),
      'log_level' => 'info',
    ),
    'ui' => 
    array (
      'disable_audio_levels' => false,
      'disable_invite' => false,
      'disable_profile' => false,
      'hide_watermark' => true,
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'D:\\Aplications\\telemedicine\\storage\\logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'D:\\Aplications\\telemedicine\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'handler_with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'formatter' => NULL,
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'D:\\Aplications\\telemedicine\\storage\\logs/laravel.log',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'log',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'scheme' => NULL,
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '2525',
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => 'localhost',
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
        'retry_after' => 60,
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
        'retry_after' => 60,
      ),
    ),
    'from' => 
    array (
      'address' => 'noreply@telemedicine.local',
      'name' => 'Telemedicine RSUD',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'D:\\Aplications\\telemedicine\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'queue' => 
  array (
    'default' => 'database',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
      'deferred' => 
      array (
        'driver' => 'deferred',
      ),
      'failover' => 
      array (
        'driver' => 'failover',
        'connections' => 
        array (
          0 => 'database',
          1 => 'deferred',
        ),
      ),
      'background' => 
      array (
        'driver' => 'background',
      ),
    ),
    'batching' => 
    array (
      'database' => 'sqlite',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'sqlite',
      'table' => 'failed_jobs',
    ),
  ),
  'ratelimit' => 
  array (
    'limits' => 
    array (
      'auth' => 
      array (
        'limit' => 5,
        'decay' => 60,
        'description' => 'Login/Register attempts per minute (brute force protection)',
      ),
      'upload' => 
      array (
        'limit' => 10,
        'decay' => 60,
        'description' => 'File uploads per minute (storage protection)',
      ),
      'konsultasi' => 
      array (
        'limit' => 20,
        'decay' => 60,
        'description' => 'Appointment requests per minute (DB intensive operation)',
      ),
      'search' => 
      array (
        'limit' => 30,
        'decay' => 60,
        'description' => 'Search/Filter requests per minute (query intensive)',
      ),
      'admin' => 
      array (
        'limit' => 100,
        'decay' => 60,
        'description' => 'Admin panel requests per minute',
      ),
      'general' => 
      array (
        'limit' => 60,
        'decay' => 60,
        'description' => 'General API requests per minute',
      ),
    ),
    'excluded_endpoints' => 
    array (
      0 => 'v1/health',
      1 => 'up',
    ),
    'cache_driver' => 'database',
    'enabled' => true,
    'response_format' => 
    array (
      'status_code' => 429,
      'include_reset_time' => true,
      'include_retry_after' => true,
      'include_limit_headers' => true,
    ),
    'whitelist_ips' => 
    array (
      0 => '',
    ),
    'user_multipliers' => 
    array (
      'admin' => 2.0,
      'dokter' => 1.5,
      'pasien' => 1.0,
    ),
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => 'localhost',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
      'validate_csrf_token' => 'Illuminate\\Foundation\\Http\\Middleware\\ValidateCsrfToken',
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'key' => NULL,
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
  ),
  'session' => 
  array (
    'driver' => 'database',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'D:\\Aplications\\telemedicine\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'laravel-session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'simrs' => 
  array (
    'api_token' => 'token_simrs_dummy_123',
    'base_url' => 'http://localhost:8000/simrs',
    'timeout' => 30,
  ),
  'ide-helper' => 
  array (
    'filename' => '_ide_helper.php',
    'models_filename' => '_ide_helper_models.php',
    'meta_filename' => '.phpstorm.meta.php',
    'include_fluent' => false,
    'include_factory_builders' => false,
    'write_model_magic_where' => true,
    'write_model_external_builder_methods' => true,
    'write_model_relation_count_properties' => true,
    'write_model_relation_exists_properties' => false,
    'write_eloquent_model_mixins' => false,
    'include_helpers' => false,
    'helper_files' => 
    array (
      0 => 'D:\\Aplications\\telemedicine/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
      1 => 'D:\\Aplications\\telemedicine/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php',
    ),
    'model_locations' => 
    array (
      0 => 'app',
    ),
    'ignored_models' => 
    array (
    ),
    'model_hooks' => 
    array (
    ),
    'extra' => 
    array (
      'Eloquent' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'Illuminate\\Database\\Query\\Builder',
      ),
      'Session' => 
      array (
        0 => 'Illuminate\\Session\\Store',
      ),
    ),
    'magic' => 
    array (
    ),
    'interfaces' => 
    array (
    ),
    'model_camel_case_properties' => false,
    'type_overrides' => 
    array (
      'integer' => 'int',
      'boolean' => 'bool',
    ),
    'include_class_docblocks' => false,
    'force_fqn' => false,
    'use_generics_annotations' => true,
    'macro_default_return_types' => 
    array (
      'Illuminate\\Http\\Client\\Factory' => 'Illuminate\\Http\\Client\\PendingRequest',
    ),
    'additional_relation_types' => 
    array (
    ),
    'additional_relation_return_types' => 
    array (
    ),
    'enforce_nullable_relationships' => true,
    'post_migrate' => 
    array (
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
