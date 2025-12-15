<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test - Get dashboard dengan semua statistik
     * GET /api/v1/admin/dashboard
     */
    public function test_get_dashboard_statistics()
    {
        Pasien::factory()->count(5)->create();
        Dokter::factory()->count(3)->create();
        Konsultasi::factory()->count(10)->create();
        ActivityLog::factory()->count(15)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'user_stats',
                'consultation_stats',
                'monthly_stats',
                'doctor_stats',
                'consultation_by_specialty',
                'system_health',
                'recent_consultations',
                'recent_activities',
            ],
        ]);
    }

    /**
     * Test - Non-admin tidak bisa akses dashboard
     */
    public function test_non_admin_cannot_access_dashboard()
    {
        /** @var User $pasienUser */
        $pasienUser = User::factory()->create(['role' => 'pasien']);

        $response = $this->actingAs($pasienUser, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test - Dashboard tanpa autentikasi
     */
    public function test_dashboard_unauthenticated()
    {
        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(401);
    }

    /**
     * Test - Get semua users
     * GET /api/v1/admin/users
     */
    public function test_get_all_users()
    {
        User::factory()->count(10)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at',
                ],
            ],
        ]);
    }

    /**
     * Test - Filter users berdasarkan role
     */
    public function test_get_users_by_role()
    {
        User::factory()->count(5)->create(['role' => 'pasien']);
        User::factory()->count(3)->create(['role' => 'dokter']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/users?role=pasien');

        $response->assertStatus(200);
    }

    /**
     * Test - Get detail user
     * GET /api/v1/admin/users/{id}
     */
    public function test_get_user_detail()
    {
        $user = User::factory()->create(['role' => 'pasien']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'id',
                'name',
                'email',
                'role',
            ],
        ]);
    }

    /**
     * Test - Update user role
     * PUT /api/v1/admin/users/{id}/role
     */
    public function test_update_user_role()
    {
        $user = User::factory()->create(['role' => 'pasien']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}/role", [
                'role' => 'dokter',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Role user berhasil diubah',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'dokter',
        ]);
    }

    /**
     * Test - Delete user
     * DELETE /api/v1/admin/users/{id}
     */
    public function test_delete_user()
    {
        $user = User::factory()->create(['role' => 'pasien']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'User berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test - Get activity logs
     * GET /api/v1/admin/activity-logs
     */
    public function test_get_activity_logs()
    {
        ActivityLog::factory()->count(10)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'action',
                    'description',
                    'created_at',
                ],
            ],
        ]);
    }

    /**
     * Test - Filter activity logs berdasarkan action
     */
    public function test_get_activity_logs_by_action()
    {
        ActivityLog::factory()->create(['action' => 'login']);
        ActivityLog::factory()->create(['action' => 'logout']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/activity-logs?action=login');

        $response->assertStatus(200);
    }

    /**
     * Test - Get activity logs dengan date filter
     */
    public function test_get_activity_logs_by_date()
    {
        ActivityLog::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/activity-logs?from_date=2024-01-01&to_date=2024-12-31');

        $response->assertStatus(200);
    }

    /**
     * Test - Get statistik konsultasi
     * GET /api/v1/admin/statistics/consultations
     */
    public function test_get_consultation_statistics()
    {
        Konsultasi::factory()->create(['status' => 'pending']);
        Konsultasi::factory()->create(['status' => 'active']);
        Konsultasi::factory()->create(['status' => 'closed']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/statistics/consultations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data',
        ]);
    }

    /**
     * Test - Get statistik dokter
     * GET /api/v1/admin/statistics/doctors
     */
    public function test_get_doctor_statistics()
    {
        Dokter::factory()->count(5)->create(['is_tersedia' => true]);
        Dokter::factory()->count(2)->create(['is_tersedia' => false]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/statistics/doctors');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data',
        ]);
    }

    /**
     * Test - Get statistik user
     * GET /api/v1/admin/statistics/users
     */
    public function test_get_user_statistics()
    {
        User::factory()->count(5)->create(['role' => 'pasien']);
        User::factory()->count(3)->create(['role' => 'dokter']);
        User::factory()->count(1)->create(['role' => 'admin']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/statistics/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data',
        ]);
    }

    /**
     * Test - Export laporan konsultasi
     * GET /api/v1/admin/reports/consultations
     */
    public function test_export_consultation_report()
    {
        Konsultasi::factory()->count(5)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/reports/consultations');

        $response->assertStatus(200);
    }

    /**
     * Test - Admin tidak bisa dihapus
     */
    public function test_cannot_delete_admin()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/v1/admin/users/{$this->adminUser->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
        ]);
    }

    /**
     * Test - Update user dengan email yang sudah terdaftar
     */
    public function test_update_user_duplicate_email()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user1->id}", [
                'email' => 'user2@example.com',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test - Get user yang tidak ada
     */
    public function test_get_user_not_found()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/users/99999');

        $response->assertStatus(404);
    }
}
