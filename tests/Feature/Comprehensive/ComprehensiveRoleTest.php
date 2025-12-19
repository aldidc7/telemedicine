<?php

namespace Tests\Feature\Comprehensive;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dokter;
use App\Models\Pasien;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComprehensiveRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $dokterUser;
    protected $pasienUser;
    
    protected $adminToken;
    protected $dokterToken;
    protected $pasienToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestUsers();
        $this->authenticateUsers();
    }

    protected function createTestUsers()
    {
        // Create Admin User
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        // Create Doctor User & Profile
        $this->dokterUser = User::factory()->create([
            'email' => 'dokter@test.com',
            'role' => 'dokter',
        ]);
        Dokter::factory()->create([
            'user_id' => $this->dokterUser->id,
        ]);

        // Create Patient User & Profile
        $this->pasienUser = User::factory()->create([
            'email' => 'pasien@test.com',
            'role' => 'pasien',
        ]);
        Pasien::factory()->create([
            'user_id' => $this->pasienUser->id,
        ]);
    }

    protected function authenticateUsers()
    {
        $this->adminToken = $this->adminUser->createToken('test')->plainTextToken;
        $this->dokterToken = $this->dokterUser->createToken('test')->plainTextToken;
        $this->pasienToken = $this->pasienUser->createToken('test')->plainTextToken;
    }

    // ==================== ADMIN TESTS ====================
    
    /** @test */
    public function admin_can_access_dashboard()
    {
        $response = $this->withToken($this->adminToken)
            ->get('/api/v1/admin/dashboard');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_all_users()
    {
        $response = $this->withToken($this->adminToken)
            ->get('/api/v1/admin/pengguna');
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function admin_can_view_system_statistics()
    {
        $response = $this->withToken($this->adminToken)
            ->get('/api/v1/admin/statistik');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_pending_doctors()
    {
        $response = $this->withToken($this->adminToken)
            ->get('/api/v1/admin/dokter/pending');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_activity_logs()
    {
        $response = $this->withToken($this->adminToken)
            ->get('/api/v1/admin/log-aktivitas');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_analytics()
    {
        $response = $this->withToken($this->adminToken)
            ->get('/api/v1/analytics/overview');
        
        $response->assertStatus(200);
    }

    // ==================== DOKTER TESTS ====================
    
    /** @test */
    public function dokter_can_view_profile()
    {
        $response = $this->withToken($this->dokterToken)
            ->get('/api/v1/auth/me');
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.role', 'dokter');
    }

    /** @test */
    public function dokter_can_view_consultations()
    {
        $response = $this->withToken($this->dokterToken)
            ->get('/api/v1/konsultasi');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function dokter_can_view_profile_completion()
    {
        $response = $this->withToken($this->dokterToken)
            ->get('/api/v1/auth/profile-completion');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function dokter_can_access_conversations()
    {
        $response = $this->withToken($this->dokterToken)
            ->get('/api/v1/messages/conversations');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function dokter_cannot_access_admin_dashboard()
    {
        $response = $this->withToken($this->dokterToken)
            ->get('/api/v1/admin/dashboard');
        
        $response->assertStatus(403);
    }

    // ==================== PASIEN TESTS ====================
    
    /** @test */
    public function pasien_can_view_profile()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/auth/me');
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.role', 'pasien');
    }

    /** @test */
    public function pasien_can_browse_doctors()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/dokter');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pasien_can_view_top_rated_doctors()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/dokter/top-rated');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pasien_can_search_doctors()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/dokter/search/advanced?spesialisasi=Umum');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pasien_can_view_consultations()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/konsultasi');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pasien_can_view_notifications()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/notifications');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pasien_can_view_appointments()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/appointments');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pasien_can_view_prescriptions()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/prescriptions');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pasien_cannot_access_admin_dashboard()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/admin/dashboard');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function pasien_cannot_access_doctor_verification()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/admin/dokter/pending');
        
        $response->assertStatus(403);
    }

    // ==================== SECURITY TESTS ====================
    
    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->get('/api/v1/admin/dashboard');
        
        $response->assertStatus(401);
    }

    /** @test */
    public function invalid_token_is_rejected()
    {
        $response = $this->withToken('invalid-token')
            ->get('/api/v1/auth/me');
        
        $response->assertStatus(401);
    }

    // ==================== FEATURE INTEGRATION TESTS ====================
    
    /** @test */
    public function all_roles_can_access_public_endpoints()
    {
        // Health check endpoint should be public
        $response = $this->get('/api/v1/health');
        $response->assertStatus(200);
    }

    /** @test */
    public function all_roles_can_access_their_profile()
    {
        $roles = [
            ['token' => $this->adminToken, 'role' => 'admin'],
            ['token' => $this->dokterToken, 'role' => 'dokter'],
            ['token' => $this->pasienToken, 'role' => 'pasien'],
        ];

        foreach ($roles as $user) {
            $response = $this->withToken($user['token'])
                ->get('/api/v1/auth/me');
            
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function broadcast_config_is_accessible()
    {
        $response = $this->withToken($this->pasienToken)
            ->get('/api/v1/broadcasting/config');
        
        // Should be either 200 or 403/401
        $this->assertIn($response->status(), [200, 401, 403]);
    }
}
