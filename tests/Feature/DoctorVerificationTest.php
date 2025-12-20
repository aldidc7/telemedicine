<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DoctorVerification;
use App\Models\DoctorVerificationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Phase 6: Doctor Verification Tests
 * 
 * Test suite untuk doctor verification workflow, document handling,
 * dan approval/rejection logic
 */
class DoctorVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');

        $this->doctor = User::factory()->doctor()->create([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
        ]);

        $this->admin = User::factory()->admin()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
        ]);
    }

    // ============== VERIFICATION SUBMISSION TESTS ==============

    /** @test */
    public function doctor_can_submit_verification()
    {
        $this->actingAs($this->doctor);

        $response = $this->postJson('/api/v1/doctor-verification/submit', [
            'medical_license' => '12345/DKK/2020',
            'specialization' => 'Cardiologist',
            'institution' => 'Hospital ABC',
            'years_of_experience' => 5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('doctor_verifications', [
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function doctor_verification_requires_license()
    {
        $this->actingAs($this->doctor);

        $response = $this->postJson('/api/v1/doctor-verification/submit', [
            'specialization' => 'Cardiologist',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function non_doctor_cannot_submit_verification()
    {
        $patient = User::factory()->patient()->create();
        $this->actingAs($patient);

        $response = $this->postJson('/api/v1/doctor-verification/submit', [
            'medical_license' => '12345/DKK/2020',
            'specialization' => 'Cardiologist',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function doctor_cannot_submit_twice_simultaneously()
    {
        DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $response = $this->postJson('/api/v1/doctor-verification/submit', [
            'medical_license' => '12345/DKK/2020',
            'specialization' => 'Cardiologist',
        ]);

        $response->assertStatus(409);
    }

    // ============== DOCUMENT UPLOAD TESTS ==============

    /** @test */
    public function doctor_can_upload_verification_document()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $file = UploadedFile::fake()->image('ktp.jpg');

        $response = $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/documents",
            [
                'document_type' => 'ktp',
                'document' => $file,
            ]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('doctor_verification_documents', [
            'verification_id' => $verification->id,
            'document_type' => 'ktp',
        ]);
    }

    /** @test */
    public function document_upload_validates_file_type()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $file = UploadedFile::fake()->create('malicious.exe', 100);

        $response = $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/documents",
            [
                'document_type' => 'ktp',
                'document' => $file,
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function document_upload_validates_file_size()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        // Create a 6MB file
        $file = UploadedFile::fake()->create('large.pdf', 6000);

        $response = $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/documents",
            [
                'document_type' => 'ktp',
                'document' => $file,
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function doctor_cannot_upload_for_others_verification()
    {
        $otherDoctor = User::factory()->doctor()->create();
        $verification = DoctorVerification::create([
            'doctor_id' => $otherDoctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $file = UploadedFile::fake()->image('ktp.jpg');

        $response = $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/documents",
            [
                'document_type' => 'ktp',
                'document' => $file,
            ]
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function required_documents_include_ktp_skp_sertifikat()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $requiredDocs = ['ktp', 'skp', 'sertifikat'];

        foreach ($requiredDocs as $docType) {
            $file = UploadedFile::fake()->image("{$docType}.jpg");

            $this->postJson(
                "/api/v1/doctor-verification/{$verification->id}/documents",
                [
                    'document_type' => $docType,
                    'document' => $file,
                ]
            );
        }

        $this->assertEquals(3, DoctorVerificationDocument::where('verification_id', $verification->id)->count());
    }

    // ============== VERIFICATION APPROVAL TESTS ==============

    /** @test */
    public function admin_can_approve_verification()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        // Add required documents
        DoctorVerificationDocument::create([
            'verification_id' => $verification->id,
            'document_type' => 'ktp',
        ]);

        $this->actingAs($this->admin);

        $response = $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/approve",
            ['notes' => 'Verified successfully']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('doctor_verifications', [
            'id' => $verification->id,
            'status' => 'verified',
        ]);
    }

    /** @test */
    public function only_admin_can_approve_verification()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $response = $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/approve",
            ['notes' => 'Verified']
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_reject_verification()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin);

        $response = $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/reject",
            ['reason' => 'Documents incomplete']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('doctor_verifications', [
            'id' => $verification->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function doctor_receives_notification_on_approval()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin);

        $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/approve",
            ['notes' => 'Verified']
        );

        // Check notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->doctor->id,
            'type' => 'verification_approved',
        ]);
    }

    /** @test */
    public function doctor_receives_notification_on_rejection()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin);

        $this->postJson(
            "/api/v1/doctor-verification/{$verification->id}/reject",
            ['reason' => 'Documents incomplete']
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->doctor->id,
            'type' => 'verification_rejected',
        ]);
    }

    // ============== VERIFICATION STATUS TESTS ==============

    /** @test */
    public function doctor_can_view_own_verification_status()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $response = $this->getJson('/api/v1/doctor-verification/status');

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'pending');
    }

    /** @test */
    public function verified_doctor_can_consult()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'verified',
        ]);

        $this->doctor->update(['verified_at' => now()]);

        $this->actingAs($this->doctor);

        $response = $this->getJson('/api/v1/doctor/profile');

        $response->assertStatus(200);
        $response->assertJsonPath('data.is_verified', true);
    }

    /** @test */
    public function unverified_doctor_cannot_access_consultation_endpoints()
    {
        $verification = DoctorVerification::create([
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->doctor);

        $response = $this->getJson('/api/v1/consultations');

        $response->assertStatus(403);
    }
}
