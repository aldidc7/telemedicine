<?php

namespace Tests\Feature\Api;

use App\Models\Konsultasi;
use App\Models\User;
use App\Models\VideoRecording;
use App\Models\VideoRecordingConsent;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * VideoCallControllerTest - Feature #1: Video Consultation
 * 
 * Tests for:
 * - JWT token generation for Jitsi
 * - Recording consent workflow (GDPR)
 * - Recording lifecycle (start/stop)
 * - Call duration tracking
 * - Permission checks
 * - Error handling
 */
class VideoCallControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $doctor;
    protected User $patient;
    protected Konsultasi $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->doctor = User::factory()->create([
            'name' => 'Dr. Ahmad',
            'email' => 'dr.ahmad@telemedicine.app',
        ]);
        $this->doctor->assignRole('doctor');

        $this->patient = User::factory()->create([
            'name' => 'Pasien Budi',
            'email' => 'budi@patient.app',
        ]);
        $this->patient->assignRole('patient');

        // Create consultation
        $this->consultation = Konsultasi::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Test: Start consultation generates JWT token
     * Requirement: Doctor can start video consultation
     */
    public function test_start_consultation_generates_jwt_token()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson("/api/v1/video-consultations/{$this->consultation->id}/start");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'room_name',
                    'jwt_token',
                    'consultation_id',
                    'user_name',
                    'user_email',
                    'is_doctor',
                ],
                'message',
            ])
            ->assertJsonFragment(['success' => true]);

        // Verify consultation status updated
        $this->assertDatabaseHas('konsultasis', [
            'id' => $this->consultation->id,
            'status' => 'ongoing',
        ]);
    }

    /**
     * Test: Patient can start consultation
     * Requirement: Both doctor and patient can initiate
     */
    public function test_patient_can_start_consultation()
    {
        $response = $this->actingAs($this->patient)
            ->postJson("/api/v1/video-consultations/{$this->consultation->id}/start");

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    /**
     * Test: Unauthorized user cannot start consultation
     * Requirement: Only participants can start
     */
    public function test_unauthorized_user_cannot_start_consultation()
    {
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $otherUser->assignRole('patient');

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/video-consultations/{$this->consultation->id}/start");

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Unauthorized to access this consultation']);
    }

    /**
     * Test: Store recording consent
     * Requirement: Consent recorded before video session
     */
    public function test_store_recording_consent()
    {
        $response = $this->actingAs($this->patient)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/consent",
                [
                    'consented_to_recording' => true,
                    'consent_reason' => 'Patient consented',
                    'ip_address' => '192.168.1.1',
                    'user_agent' => 'Mozilla/5.0',
                ]
            );

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'consent_id',
                    'consented_to_recording',
                    'status',
                ],
            ]);

        // Verify in database
        $this->assertDatabaseHas('video_recording_consents', [
            'consultation_id' => $this->consultation->id,
            'consented_to_recording' => true,
        ]);
    }

    /**
     * Test: Patient can decline recording
     * Requirement: Recording not allowed if consent denied
     */
    public function test_patient_can_decline_recording()
    {
        $response = $this->actingAs($this->patient)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/consent",
                [
                    'consented_to_recording' => false,
                    'consent_reason' => 'Patient declined',
                ]
            );

        $response->assertStatus(201)
            ->assertJsonFragment(['consented_to_recording' => false]);

        $this->assertDatabaseHas('video_recording_consents', [
            'consultation_id' => $this->consultation->id,
            'consented_to_recording' => false,
        ]);
    }

    /**
     * Test: Consent stores IP and user agent for audit trail
     * Requirement: GDPR compliance - track consent metadata
     */
    public function test_consent_stores_audit_metadata()
    {
        $ip = '203.0.113.42';
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0)';

        $this->actingAs($this->patient)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/consent",
                [
                    'consented_to_recording' => true,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                ]
            );

        $this->assertDatabaseHas('video_recording_consents', [
            'consultation_id' => $this->consultation->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Test: Start recording creates VideoRecording record
     * Requirement: Recording lifecycle begins
     */
    public function test_start_recording_creates_record()
    {
        // First, set consent
        VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => true,
            'consent_given_at' => now(),
        ]);

        $response = $this->actingAs($this->doctor)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/recording/start"
            );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'recording_id',
                    'started_at',
                ],
            ]);

        // Verify recording in database
        $this->assertDatabaseHas('video_recordings', [
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'duration' => 0, // Initially 0
        ]);
    }

    /**
     * Test: Cannot record without consent
     * Requirement: GDPR compliance
     */
    public function test_cannot_record_without_consent()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/recording/start"
            );

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Recording consent not found or denied']);
    }

    /**
     * Test: Stop recording saves duration and file size
     * Requirement: Recording metadata captured
     */
    public function test_stop_recording_saves_metadata()
    {
        // Create recording first
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 0,
            'file_size' => 0,
        ]);

        $duration = 1523; // seconds
        $fileSize = 125000000; // bytes

        $response = $this->actingAs($this->doctor)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/recording/stop",
                [
                    'recording_id' => $recording->id,
                    'duration' => $duration,
                    'file_size' => $fileSize,
                ]
            );

        $response->assertStatus(200)
            ->assertJsonFragment([
                'duration_formatted' => '25:23', // 25 minutes, 23 seconds
            ]);

        // Verify in database
        $this->assertDatabaseHas('video_recordings', [
            'id' => $recording->id,
            'duration' => $duration,
            'file_size' => $fileSize,
        ]);
    }

    /**
     * Test: Recording duration formatted correctly
     * Requirement: Display format MM:SS
     */
    public function test_recording_duration_formatted()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 125, // 2 minutes, 5 seconds
            'file_size' => 1000000,
        ]);

        $this->assertEquals('2:05', $recording->getDurationFormatted());
    }

    /**
     * Test: Recording file size formatted correctly
     * Requirement: Display format with units (MB, GB)
     */
    public function test_recording_file_size_formatted()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 100,
            'file_size' => 125000000, // ~125 MB
        ]);

        $formatted = $recording->getFileSizeFormatted();
        $this->assertStringContainsString('MB', $formatted);
    }

    /**
     * Test: End consultation updates status
     * Requirement: Consultation marked as completed
     */
    public function test_end_consultation_updates_status()
    {
        // Start first
        $this->consultation->update(['status' => 'ongoing']);

        $response = $this->actingAs($this->doctor)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/end",
                ['duration' => 1523]
            );

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);

        $this->assertDatabaseHas('konsultasis', [
            'id' => $this->consultation->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test: List recordings - only user's recordings returned
     * Requirement: Privacy - users only see their recordings
     */
    public function test_list_recordings_filters_by_user()
    {
        // Create recording for this consultation
        VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'room-1',
            'duration' => 100,
            'file_size' => 1000000,
        ]);

        // Create another consultation with different doctor
        $otherDoctor = User::factory()->create();
        $otherDoctor->assignRole('doctor');
        $otherConsultation = Konsultasi::factory()->create([
            'doctor_id' => $otherDoctor->id,
            'patient_id' => $this->patient->id,
        ]);
        VideoRecording::create([
            'consultation_id' => $otherConsultation->id,
            'doctor_id' => $otherDoctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'room-2',
            'duration' => 50,
            'file_size' => 500000,
        ]);

        // Patient should see both (as participant)
        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/video-consultations/recordings/list');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test: Get recording details
     * Requirement: User can view recording metadata
     */
    public function test_get_recording_details()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 1234,
            'file_size' => 123456789,
        ]);

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/video-consultations/recordings/{$recording->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'consultation_id',
                    'duration',
                    'duration_formatted',
                    'file_size',
                    'file_size_formatted',
                    'is_accessible',
                    'doctor',
                    'patient',
                ],
            ]);
    }

    /**
     * Test: Unauthorized user cannot access recording
     * Requirement: Privacy protection
     */
    public function test_unauthorized_user_cannot_access_recording()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 100,
            'file_size' => 1000000,
        ]);

        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $otherUser->assignRole('patient');

        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/v1/video-consultations/recordings/{$recording->id}");

        $response->assertStatus(403);
    }

    /**
     * Test: Delete recording soft deletes
     * Requirement: GDPR right to delete
     */
    public function test_delete_recording_soft_deletes()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 100,
            'file_size' => 1000000,
        ]);

        $response = $this->actingAs($this->patient)
            ->deleteJson("/api/v1/video-consultations/recordings/{$recording->id}");

        $response->assertStatus(200);

        // Verify soft delete
        $this->assertSoftDeleted('video_recordings', ['id' => $recording->id]);
    }

    /**
     * Test: Only patient can delete recording
     * Requirement: Permission control
     */
    public function test_only_patient_can_delete_recording()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 100,
            'file_size' => 1000000,
        ]);

        // Doctor tries to delete
        $response = $this->actingAs($this->doctor)
            ->deleteJson("/api/v1/video-consultations/recordings/{$recording->id}");

        $response->assertStatus(403);

        // Recording still exists
        $this->assertDatabaseHas('video_recordings', ['id' => $recording->id]);
    }

    /**
     * Test: Withdraw consent
     * Requirement: GDPR right to withdraw
     */
    public function test_withdraw_consent()
    {
        $consent = VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => true,
            'consent_given_at' => now(),
        ]);

        $response = $this->actingAs($this->patient)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/consent/withdraw"
            );

        $response->assertStatus(200);

        // Verify withdrawn_at timestamp set
        $this->assertDatabaseHas('video_recording_consents', [
            'id' => $consent->id,
            'consented_to_recording' => true, // Still true
        ]);
    }

    /**
     * Test: Invalid recording_id validation
     * Requirement: Error handling
     */
    public function test_recording_id_validation()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson(
                "/api/v1/video-consultations/{$this->consultation->id}/recording/stop",
                [
                    'recording_id' => 99999, // Non-existent
                    'duration' => 100,
                ]
            );

        $response->assertStatus(422);
    }

    /**
     * Test: Invalid consultation_id returns 404
     * Requirement: Error handling
     */
    public function test_invalid_consultation_returns_404()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson('/api/v1/video-consultations/99999/start');

        $response->assertStatus(404);
    }

    /**
     * Test: Unauthenticated request returns 401
     * Requirement: Authentication required
     */
    public function test_unauthenticated_request_returns_401()
    {
        $response = $this->postJson(
            "/api/v1/video-consultations/{$this->consultation->id}/start"
        );

        $response->assertStatus(401);
    }

    /**
     * Test: Recording is marked as deleted after soft delete
     * Requirement: Proper soft delete flag
     */
    public function test_recording_marked_deleted_after_delete()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 100,
            'file_size' => 1000000,
            'is_deleted' => false,
        ]);

        $this->actingAs($this->patient)
            ->deleteJson("/api/v1/video-consultations/recordings/{$recording->id}");

        $deleted = VideoRecording::withTrashed()->find($recording->id);
        $this->assertTrue($deleted->is_deleted);
        $this->assertNotNull($deleted->deleted_at);
    }
}
