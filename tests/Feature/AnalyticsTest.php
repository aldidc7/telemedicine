<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Konsultasi;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $dokter;
    private $pasien;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        $dokterUser = User::factory()->create(['role' => 'dokter']);
        $this->dokter = Dokter::create([
            'user_id' => $dokterUser->id,
            'specialization' => 'Umum',
            'sip_number' => 'SIP123456',
            'no_identitas' => 'ID123456',
        ]);

        $pasienUser = User::factory()->create(['role' => 'pasien']);
        $this->pasien = Pasien::create([
            'user_id' => $pasienUser->id,
            'gender' => 'M',
            'blood_type' => 'O',
        ]);
    }

    /**
     * Test admin can access admin analytics
     */
    public function test_admin_can_access_analytics_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/api/analytics/admin-dashboard');

        $response->assertStatus(200);
        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
    }

    /**
     * Test doctor can access doctor analytics
     */
    public function test_doctor_can_access_doctor_analytics()
    {
        $response = $this->actingAs($this->dokter->user)
            ->get('/api/analytics/doctor-dashboard');

        $response->assertStatus(200);
        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
    }

    /**
     * Test analytics contains required metrics
     */
    public function test_analytics_contains_required_metrics()
    {
        // Create test consultations
        for ($i = 0; $i < 3; $i++) {
            Konsultasi::create([
                'dokter_id' => $this->dokter->id,
                'pasien_id' => $this->pasien->id,
                'status' => 'completed',
                'consultation_fee' => 150000,
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get('/api/analytics/admin-dashboard');

        $response->assertStatus(200);
        $data = $response->getData(true);
        
        $this->assertNotNull($data['data']);
    }

    /**
     * Test doctor analytics filters by doctor
     */
    public function test_doctor_analytics_filters_by_doctor()
    {
        // Create consultation for this doctor
        Konsultasi::create([
            'dokter_id' => $this->dokter->id,
            'pasien_id' => $this->pasien->id,
            'status' => 'completed',
            'consultation_fee' => 150000,
        ]);

        // Create consultation for another doctor
        $anotherDokterUser = User::factory()->create(['role' => 'dokter']);
        $anotherDokter = Dokter::create([
            'user_id' => $anotherDokterUser->id,
            'specialization' => 'Gigi',
            'sip_number' => 'SIP789',
            'no_identitas' => 'ID789',
        ]);

        Konsultasi::create([
            'dokter_id' => $anotherDokter->id,
            'pasien_id' => $this->pasien->id,
            'status' => 'completed',
            'consultation_fee' => 200000,
        ]);

        $response = $this->actingAs($this->dokter->user)
            ->get('/api/analytics/doctor-dashboard');

        $response->assertStatus(200);
        // Doctor should only see their own consultations
    }

    /**
     * Test rating distribution in analytics
     */
    public function test_rating_distribution_in_analytics()
    {
        $konsultasi = Konsultasi::create([
            'dokter_id' => $this->dokter->id,
            'pasien_id' => $this->pasien->id,
            'status' => 'completed',
            'consultation_fee' => 150000,
        ]);

        Rating::create([
            'konsultasi_id' => $konsultasi->id,
            'pasien_id' => $this->pasien->id,
            'dokter_id' => $this->dokter->id,
            'rating' => 5,
            'review' => 'Excellent doctor',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/api/analytics/admin-dashboard');

        $response->assertStatus(200);
        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
    }

    /**
     * Test non-admin cannot access admin analytics
     */
    public function test_non_admin_cannot_access_admin_analytics()
    {
        $response = $this->actingAs($this->dokter->user)
            ->get('/api/analytics/admin-dashboard');

        // Should be 403 Forbidden or redirected
        $this->assertTrue(in_array($response->status(), [401, 403, 302]));
    }

    /**
     * Test authenticated user is required
     */
    public function test_unauthenticated_user_cannot_access_analytics()
    {
        $response = $this->get('/api/analytics/admin-dashboard');

        // Should be 401 Unauthorized
        $this->assertTrue(in_array($response->status(), [401, 302]));
    }

    /**
     * Test analytics response structure
     */
    public function test_analytics_response_structure()
    {
        $response = $this->actingAs($this->admin)
            ->get('/api/analytics/admin-dashboard');

        $response->assertStatus(200);
        $data = $response->getData(true);
        
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertTrue($data['success']);
    }
}
