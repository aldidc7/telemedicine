# QUICK DEVELOPER REFERENCE GUIDE

**Telemedicine API - Development Handbook**

---

## TABLE OF CONTENTS
1. [Quick Setup](#quick-setup)
2. [API Response Format](#api-response-format)
3. [Authentication](#authentication)
4. [Error Handling](#error-handling)
5. [Database](#database)
6. [Testing](#testing)
7. [Common Tasks](#common-tasks)
8. [Useful Commands](#useful-commands)

---

## QUICK SETUP

### Environment Setup
```bash
# Clone & install
git clone https://github.com/aldidc7/telemedicine.git
cd telemedicine
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate
php artisan migrate

# Start development
php artisan serve
npm run dev
```

### Database Seeding
```bash
# Seed with test data
php artisan db:seed

# Or seed specific seeder
php artisan db:seed --class=PasienSeeder
```

---

## API RESPONSE FORMAT

### Success Response
```json
{
  "success": true,
  "pesan": "Data berhasil diambil",
  "data": { /* ... */ },
  "error_code": null,
  "status_code": 200
}
```

### Error Response
```json
{
  "success": false,
  "pesan": "Email tidak ditemukan",
  "data": null,
  "error_code": "USER_NOT_FOUND",
  "status_code": 404
}
```

### Using ApiResponse Helper
```php
// Success
return ApiResponse::success($data, 'Data berhasil', 200);

// Error
return ApiResponse::error('Not found', 404, null, 'NOT_FOUND');

// Validation error
return ApiResponse::validationFailed($errors);

// Unauthorized
return ApiResponse::unauthorized('Invalid credentials');

// Forbidden
return ApiResponse::forbidden('You do not have access');

// Not found
return ApiResponse::notFound('Resource not found');

// Conflict
return ApiResponse::conflict('Email already registered');

// Server error
return ApiResponse::serverError('Internal server error');
```

---

## AUTHENTICATION

### Token-Based (Sanctum)

#### Login
```php
// POST /api/v1/auth/login
{
  "email": "dokter@example.com",
  "password": "password123"
}

// Response
{
  "success": true,
  "pesan": "Login berhasil",
  "data": {
    "user": { /* user data */ },
    "token": "1|abcdefg..."
  },
  "status_code": 200
}
```

#### Usage in Frontend
```javascript
// Set token in header
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Or per request
axios.get('/api/v1/auth/me', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

#### API Key Authentication
```php
// Model
use App\Models\ApiKey;

// Generate key
$key = ApiKey::generateNew('Integration Name', 'simrs', $user->id);
// Returns: { key: 'sk_xxxx...', secret: '...' }

// Validate in middleware
$apiKey = ApiKey::validate($request->header('X-API-Key'), $secret);
```

---

## ERROR HANDLING

### Custom Exceptions
```php
// Validation error
throw new \App\Exceptions\ValidationFailedException(['email' => 'Invalid email']);

// Authentication error
throw new \App\Exceptions\InvalidCredentialsException();

// Authorization error
throw new \App\Exceptions\UnauthorizedException();

// Not found error
throw new \App\Exceptions\ResourceNotFoundException('User', $id);
```

### Global Error Handler
Located: `app/Exceptions/Handler.php`
- Automatically catches all exceptions
- Returns standardized JSON response
- Logs errors to `storage/logs/laravel.log`

### HTTP Status Codes
```
200 - OK (success)
201 - Created (resource created)
204 - No Content (success, no data)
400 - Bad Request (validation error)
401 - Unauthorized (need login)
403 - Forbidden (no permission)
404 - Not Found (resource not found)
409 - Conflict (constraint violation)
422 - Unprocessable Entity (validation failed)
429 - Too Many Requests (rate limited)
500 - Internal Server Error
```

---

## DATABASE

### Tables & Models
```
patients         → App\Models\Pasien
doctors          → App\Models\Dokter
consultations    → App\Models\Konsultasi
appointments     → App\Models\Appointment
ratings          → App\Models\Rating
messages         → App\Models\Message
api_keys         → App\Models\ApiKey
users            → App\Models\User
```

### Useful Queries

#### Get doctor with patients
```php
$doctor = Dokter::with('users')->find($id);
```

#### Get patient consultations
```php
$consultations = Konsultasi::where('pasien_id', $id)
    ->with('dokter')
    ->latest()
    ->paginate(10);
```

#### Get doctor earnings
```php
$earnings = Konsultasi::where('dokter_id', $id)
    ->whereBetween('created_at', [$start, $end])
    ->where('status', 'completed')
    ->sum('tarif_konsultasi');
```

#### Get pending appointments
```php
$pending = Appointment::where('status', 'pending')
    ->where('tanggal_appointment', '>=', now())
    ->orderBy('tanggal_appointment')
    ->get();
```

### Migrations
```bash
# Create migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Refresh all
php artisan migrate:refresh

# Seed after migrate
php artisan migrate --seed
```

---

## TESTING

### Run Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Unit/PasienModelTest.php

# Specific test method
php artisan test --filter test_create_patient

# With coverage
php artisan test --coverage

# Verbose output
php artisan test -v
```

### Write Unit Test
```php
// tests/Unit/MyModelTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\MyModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyModelTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_model_creation()
    {
        $model = MyModel::create(['name' => 'Test']);
        
        $this->assertDatabaseHas('models', [
            'id' => $model->id,
            'name' => 'Test'
        ]);
    }
}
```

### Write Feature Test
```php
// tests/Feature/MyControllerTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_get_endpoint()
    {
        $response = $this->getJson('/api/v1/endpoint');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 'pesan', 'data', 'status_code'
            ]);
    }
    
    public function test_authenticated_endpoint()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/protected');
        
        $response->assertStatus(200);
    }
}
```

---

## COMMON TASKS

### Add New API Endpoint

#### 1. Create Controller
```bash
php artisan make:controller Api/MyController
```

#### 2. Add Route
```php
// routes/api.php
Route::post('/my-endpoint', [MyController::class, 'store']);
```

#### 3. Implement Method
```php
public function store(MyFormRequest $request)
{
    // Validation happens in MyFormRequest
    $data = MyModel::create($request->validated());
    
    return ApiResponse::created($data, 'Data berhasil dibuat');
}
```

### Add Form Request Validation
```bash
php artisan make:request MyFormRequest
```

```php
// app/Http/Requests/MyFormRequest.php
public function rules()
{
    return [
        'email' => 'required|email|unique:users',
        'name' => 'required|string|max:255',
    ];
}

public function messages()
{
    return [
        'email.unique' => 'Email sudah terdaftar',
    ];
}
```

### Add New Vue Page
```
1. Create component: resources/js/views/MyPage.vue
2. Add route: resources/js/router/index.js
3. Link in navigation: resources/js/components/Navigation.vue
```

### Query with Pagination
```php
$items = MyModel::paginate(15); // 15 per page

// In response
return ApiResponse::paginated($items, 'Data berhasil diambil');
```

---

## USEFUL COMMANDS

### Laravel Commands
```bash
# Development
php artisan serve              # Start server
php artisan tinker             # Interactive shell
php artisan db:seed            # Seed database
php artisan migrate:fresh      # Reset & seed

# Code generation
php artisan make:model MyModel
php artisan make:controller Api/MyController
php artisan make:request MyFormRequest
php artisan make:migration create_table_name
php artisan make:test Feature/MyTest
php artisan make:seeder MySeeder

# Maintenance
php artisan cache:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache
php artisan storage:link

# Testing
php artisan test
php artisan test --coverage
```

### Npm Commands
```bash
# Development
npm run dev             # Start Vite dev server
npm run build           # Build for production
npm run preview         # Preview build
npm install             # Install dependencies
npm install package-name
npm update
```

### Git Commands
```bash
# Standard flow
git status
git add .
git commit -m "Your message"
git push origin main
git pull origin main

# View history
git log --oneline
git show commit_hash
```

### Database
```bash
# MySQL/SQLite
sqlite3 database/database.sqlite  # SQLite CLI

# Laravel
php artisan tinker
>>> DB::table('users')->get()
>>> MyModel::all()
```

---

## COMMON ERRORS & SOLUTIONS

### "Class not found"
```
Cause: Namespace mismatch or missing import
Solution: Check namespace in file matches folder structure
          Run: composer dump-autoload
```

### "Table does not exist"
```
Cause: Migration not run or table name mismatch
Solution: php artisan migrate
          Check table name in query matches migration
```

### "Undefined variable"
```
Cause: Variable not defined in scope
Solution: Check variable passed from controller to view
          Use dd($variable) to debug
```

### "500 Internal Server Error"
```
Cause: Unhandled exception
Solution: Check storage/logs/laravel.log for details
          Run php artisan tinker to test code
```

### "CORS error"
```
Cause: Frontend and backend on different origins
Solution: Check config/cors.php
          Add frontend origin to allowed_origins
          Restart server
```

---

## LOGGING & DEBUGGING

### Log to File
```php
use Illuminate\Support\Facades\Log;

Log::info('User login', ['user_id' => 123]);
Log::error('Error occurred', ['exception' => $e]);
Log::debug('Debug info', ['data' => $data]);

// View logs
tail -f storage/logs/laravel.log
```

### Debug with dd()
```php
dd($variable);  // Dump and die
dump($variable); // Just dump

// In tests
$this->dump(); // Dump test context
```

### API Testing Tools
- Postman: Import `Telemedicine_API_Collection.postman_collection.json`
- Insomnia: Alternative to Postman
- cURL: Command line
- Browser DevTools: Network tab

---

## PERFORMANCE TIPS

### Query Optimization
```php
// ❌ Bad: N+1 problem
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->bio; // Query executed for each user!
}

// ✅ Good: Eager load
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->bio; // Data already loaded
}
```

### Caching
```php
// Cache key
$data = cache()->remember('doctor-list', 3600, function () {
    return Dokter::all();
});

// Clear cache
cache()->forget('doctor-list');
```

### Pagination
```php
// ✅ Good: Use pagination
$items = MyModel::paginate(20);

// ❌ Bad: Load all
$items = MyModel::all();
```

---

## SECURITY CHECKLIST

When writing code:
- ✅ Validate all inputs via FormRequest
- ✅ Authorize actions with policies/gates
- ✅ Use parameterized queries (Eloquent)
- ✅ Hash passwords (bcrypt)
- ✅ Validate API tokens (Sanctum)
- ✅ Use HTTPS in production
- ✅ Set CORS properly
- ✅ Log suspicious activity
- ✅ Never commit secrets to Git
- ✅ Use environment variables for config

---

## USEFUL RESOURCES

- **Laravel Docs**: https://laravel.com/docs
- **Vue 3 Docs**: https://vuejs.org
- **API Docs**: See `storage/api-docs/openapi.json`
- **GitHub**: https://github.com/aldidc7/telemedicine
- **Local Swagger UI**: Install locally or use https://editor.swagger.io

---

## Getting Help

### Issues?
1. Check logs: `tail -f storage/logs/laravel.log`
2. Check tests: `php artisan test --filter test_name`
3. Use Tinker: `php artisan tinker`
4. Debug with dd(): `dd($variable);`
5. Check GitHub issues or ask team

### Before Pushing:
1. Run tests: `php artisan test`
2. Check coding standards
3. No debugging code (dd, var_dump)
4. Write commit message
5. Pull latest before push

---

**Version**: 2.1.0
**Last Updated**: 19 Desember 2025
**Status**: Production Ready for Phase 2
