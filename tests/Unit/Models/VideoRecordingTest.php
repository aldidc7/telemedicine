<?php

namespace Tests\Unit\Models;

use App\Models\Konsultasi;
use App\Models\User;
use App\Models\VideoRecording;
use App\Models\VideoRecordingConsent;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * VideoRecordingTest - Unit tests for VideoRecording model
 * 
 * Tests model methods, relationships, and calculations
 */
class VideoRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected User $patient;
    protected Konsultasi $consultation;
    protected VideoRecording $recording;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::factory()->create();
        $this->doctor->assignRole('doctor');

        $this->patient = User::factory()->create();
        $this->patient->assignRole('patient');

        $this->consultation = Konsultasi::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
        ]);

        $this->recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 125, // 2:05
            'file_size' => 125000000,
        ]);
    }

    /**
     * Test: Recording has konsultasi relationship
     */
    public function test_recording_has_konsultasi_relationship()
    {
        $this->assertInstanceOf(Konsultasi::class, $this->recording->konsultasi);
        $this->assertEquals($this->consultation->id, $this->recording->konsultasi->id);
    }

    /**
     * Test: Recording has doctor relationship
     */
    public function test_recording_has_doctor_relationship()
    {
        $this->assertInstanceOf(User::class, $this->recording->doctor);
        $this->assertEquals($this->doctor->id, $this->recording->doctor->id);
    }

    /**
     * Test: Recording has patient relationship
     */
    public function test_recording_has_patient_relationship()
    {
        $this->assertInstanceOf(User::class, $this->recording->patient);
        $this->assertEquals($this->patient->id, $this->recording->patient->id);
    }

    /**
     * Test: getDurationFormatted returns MM:SS format
     */
    public function test_get_duration_formatted_mm_ss()
    {
        $this->assertEquals('2:05', $this->recording->getDurationFormatted());
    }

    /**
     * Test: getDurationFormatted handles seconds < 60
     */
    public function test_get_duration_formatted_seconds_only()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 45,
            'file_size' => 0,
        ]);

        $this->assertEquals('0:45', $recording->getDurationFormatted());
    }

    /**
     * Test: getDurationFormatted handles zero duration
     */
    public function test_get_duration_formatted_zero()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 0,
            'file_size' => 0,
        ]);

        $this->assertEquals('0:00', $recording->getDurationFormatted());
    }

    /**
     * Test: getDurationFormatted handles large durations
     */
    public function test_get_duration_formatted_large()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 3661, // 1:01:01
            'file_size' => 0,
        ]);

        // Should show as 61:01 (minutes:seconds)
        $this->assertEquals('61:01', $recording->getDurationFormatted());
    }

    /**
     * Test: getFileSizeFormatted returns bytes
     */
    public function test_get_file_size_formatted_bytes()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 10,
            'file_size' => 512,
        ]);

        $this->assertStringContainsString('512', $recording->getFileSizeFormatted());
    }

    /**
     * Test: getFileSizeFormatted returns MB
     */
    public function test_get_file_size_formatted_mb()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 10,
            'file_size' => 125000000, // ~125 MB
        ]);

        $formatted = $recording->getFileSizeFormatted();
        $this->assertStringContainsString('MB', $formatted);
        $this->assertStringContainsString('125', $formatted);
    }

    /**
     * Test: getFileSizeFormatted returns GB
     */
    public function test_get_file_size_formatted_gb()
    {
        $recording = VideoRecording::create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'jitsi_room_name' => 'test-room',
            'duration' => 10,
            'file_size' => 2500000000, // ~2.5 GB
        ]);

        $formatted = $recording->getFileSizeFormatted();
        $this->assertStringContainsString('GB', $formatted);
    }

    /**
     * Test: isAccessible returns true for active recording
     */
    public function test_is_accessible_true_for_active()
    {
        $this->assertTrue($this->recording->isAccessible());
    }

    /**
     * Test: isAccessible returns false for deleted recording
     */
    public function test_is_accessible_false_for_deleted()
    {
        $this->recording->markAsDeleted();

        $this->assertFalse($this->recording->isAccessible());
    }

    /**
     * Test: markAsDeleted soft deletes and marks is_deleted flag
     */
    public function test_mark_as_deleted()
    {
        $this->recording->markAsDeleted();

        $this->assertSoftDeleted('video_recordings', ['id' => $this->recording->id]);
        $this->assertTrue($this->recording->is_deleted);
    }

    /**
     * Test: Recording has casting for created_at timestamp
     */
    public function test_recording_has_timestamp_casting()
    {
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->recording->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->recording->updated_at);
    }

    /**
     * Test: Recording cascade deletes when consultation deleted
     */
    public function test_recording_cascade_delete_with_consultation()
    {
        $recordingId = $this->recording->id;

        $this->consultation->delete();

        $this->assertDatabaseMissing('video_recordings', ['id' => $recordingId]);
    }
}

/**
 * VideoRecordingConsentTest - Unit tests for VideoRecordingConsent model
 * 
 * Tests consent workflow and GDPR compliance
 */
class VideoRecordingConsentTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected User $patient;
    protected Konsultasi $consultation;
    protected VideoRecordingConsent $consent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::factory()->create();
        $this->doctor->assignRole('doctor');

        $this->patient = User::factory()->create();
        $this->patient->assignRole('patient');

        $this->consultation = Konsultasi::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
        ]);

        $this->consent = VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => true,
            'consent_given_at' => now(),
        ]);
    }

    /**
     * Test: Consent has consultation relationship
     */
    public function test_consent_has_consultation_relationship()
    {
        $this->assertInstanceOf(Konsultasi::class, $this->consent->konsultasi);
    }

    /**
     * Test: Consent has patient relationship
     */
    public function test_consent_has_patient_relationship()
    {
        $this->assertInstanceOf(User::class, $this->consent->patient);
        $this->assertEquals($this->patient->id, $this->consent->patient->id);
    }

    /**
     * Test: Consent has doctor relationship
     */
    public function test_consent_has_doctor_relationship()
    {
        $this->assertInstanceOf(User::class, $this->consent->doctor);
        $this->assertEquals($this->doctor->id, $this->consent->doctor->id);
    }

    /**
     * Test: isActive returns true for non-withdrawn consent
     */
    public function test_is_active_true_when_not_withdrawn()
    {
        $this->assertTrue($this->consent->isActive());
    }

    /**
     * Test: isActive returns false after withdrawal
     */
    public function test_is_active_false_when_withdrawn()
    {
        $this->consent->withdraw();

        $this->assertFalse($this->consent->isActive());
    }

    /**
     * Test: withdraw() sets withdrawn_at timestamp
     */
    public function test_withdraw_sets_timestamp()
    {
        $this->assertNull($this->consent->withdrawn_at);

        $this->consent->withdraw();

        $this->assertNotNull($this->consent->withdrawn_at);
    }

    /**
     * Test: getStatusText returns Indonesian text
     */
    public function test_get_status_text_consented()
    {
        $consent = VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => true,
            'consent_given_at' => now(),
        ]);

        $status = $consent->getStatusText();
        $this->assertStringContainsString('Setuju', $status); // Indonesian for "Agree"
    }

    /**
     * Test: getStatusText returns declined text
     */
    public function test_get_status_text_declined()
    {
        $consent = VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => false,
            'consent_given_at' => now(),
        ]);

        $status = $consent->getStatusText();
        $this->assertStringContainsString('Tidak Setuju', $status); // Indonesian for "Disagree"
    }

    /**
     * Test: Consent has unique constraint on (consultation_id, patient_id)
     */
    public function test_consent_unique_constraint()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to create duplicate
        VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => false,
            'consent_given_at' => now(),
        ]);
    }

    /**
     * Test: Consent stores and retrieves IP address
     */
    public function test_consent_stores_ip_address()
    {
        $ip = '203.0.113.42';
        $consent = VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => true,
            'ip_address' => $ip,
            'consent_given_at' => now(),
        ]);

        $this->assertEquals($ip, $consent->ip_address);
    }

    /**
     * Test: Consent stores and retrieves user agent
     */
    public function test_consent_stores_user_agent()
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0)';
        $consent = VideoRecordingConsent::create([
            'consultation_id' => $this->consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consented_to_recording' => true,
            'user_agent' => $userAgent,
            'consent_given_at' => now(),
        ]);

        $this->assertEquals($userAgent, $consent->user_agent);
    }

    /**
     * Test: Consent cascade deletes when consultation deleted
     */
    public function test_consent_cascade_delete_with_consultation()
    {
        $consentId = $this->consent->id;

        $this->consultation->delete();

        $this->assertDatabaseMissing('video_recording_consents', ['id' => $consentId]);
    }

    /**
     * Test: Consent has timestamp casting
     */
    public function test_consent_has_timestamp_casting()
    {
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->consent->consent_given_at);
    }
}
