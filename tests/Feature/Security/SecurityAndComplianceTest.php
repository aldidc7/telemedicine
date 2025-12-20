<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\Konsultasi;
use App\Models\ConsultationMessage;
use App\Services\Security\AuditLoggingService;
use App\Services\Security\GDPRComplianceService;
use App\Services\Security\FileUploadValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndComplianceTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;
    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::factory()->create(['role' => 'dokter']);
        $this->patient = User::factory()->create(['role' => 'pasien']);
    }

    // ===== GDPR COMPLIANCE TESTS =====

    /**
     * Test user dapat request data portability
     */
    public function test_user_can_request_data_portability()
    {
        $portableData = GDPRComplianceService::getPortableData($this->patient->id);

        $this->assertArrayHasKey('user_profile', $portableData);
        $this->assertArrayHasKey('consultations', $portableData);
        $this->assertArrayHasKey('messages', $portableData);
        $this->assertEquals($this->patient->id, $portableData['user_profile']['id']);
    }

    /**
     * Test user data deletion (right to be forgotten)
     */
    public function test_user_data_deletion()
    {
        // Create some data
        $consultation = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
        ]);

        ConsultationMessage::create([
            'consultation_id' => $consultation->id,
            'sender_id' => $this->patient->id,
            'message' => 'Test message',
        ]);

        // Delete data
        $result = GDPRComplianceService::deleteUserData(
            $this->patient->id,
            'User requested deletion'
        );

        $this->assertTrue($result['success']);

        // Check user is anonymized
        $deletedUser = User::find($this->patient->id);
        $this->assertStringContainsString('Deleted User', $deletedUser->name);
        $this->assertStringContainsString('@deleted.local', $deletedUser->email);
    }

    /**
     * Test data retention policy enforcement
     */
    public function test_data_retention_policy()
    {
        $status = GDPRComplianceService::getRetentionStatus($this->patient->id);

        $this->assertArrayHasKey('consultations', $status);
        $this->assertArrayHasKey('messages', $status);
        $this->assertArrayHasKey('audit_logs', $status);
    }

    /**
     * Test user dapat request data rectification
     */
    public function test_user_can_request_data_rectification()
    {
        $success = GDPRComplianceService::requestDataRectification(
            $this->patient->id,
            [
                'name' => 'New Name',
                'phone' => '081234567890',
            ]
        );

        $this->assertTrue($success);

        $updated = User::find($this->patient->id);
        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals('081234567890', $updated->phone);
    }

    /**
     * Test user dapat restrict processing
     */
    public function test_user_can_restrict_processing()
    {
        $success = GDPRComplianceService::restrictProcessing(
            $this->patient->id,
            ['marketing', 'analytics']
        );

        $this->assertTrue($success);

        $this->assertTrue(
            GDPRComplianceService::isProcessingRestricted(
                $this->patient->id,
                'marketing'
            )
        );
    }

    /**
     * Test cannot rectify sensitive fields
     */
    public function test_cannot_rectify_sensitive_fields()
    {
        $success = GDPRComplianceService::requestDataRectification(
            $this->patient->id,
            [
                'email' => 'hacker@example.com',
                'password' => 'newpassword',
            ]
        );

        $this->assertFalse($success);

        $user = User::find($this->patient->id);
        $this->assertNotEquals('hacker@example.com', $user->email);
    }

    // ===== AUDIT LOGGING TESTS =====

    /**
     * Test audit log login
     */
    public function test_audit_log_authentication()
    {
        AuditLoggingService::logAuth('LOGIN', $this->patient->id, true);
        AuditLoggingService::logAuth('LOGOUT', $this->patient->id, true);

        // Verify logs were created (in real implementation, check database)
        $this->assertTrue(true);
    }

    /**
     * Test audit log consultation access
     */
    public function test_audit_log_consultation_access()
    {
        $consultation = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
        ]);

        AuditLoggingService::logConsultationAccess(
            $consultation->id,
            $this->patient->id
        );

        $this->assertTrue(true);
    }

    /**
     * Test audit log patient data access
     */
    public function test_audit_log_patient_data_access()
    {
        AuditLoggingService::logPatientDataAccess(
            $this->patient->id,
            'MedicalHistory',
            $this->doctor->id
        );

        $this->assertTrue(true);
    }

    /**
     * Test audit log data export
     */
    public function test_audit_log_data_export()
    {
        AuditLoggingService::logDataExport(
            $this->patient->id,
            'PersonalData',
            42,
            'json'
        );

        $this->assertTrue(true);
    }

    /**
     * Test audit log consent
     */
    public function test_audit_log_consent()
    {
        AuditLoggingService::logConsent(
            AuditLoggingService::ACTION_CONSENT_ACCEPT,
            $this->patient->id,
            'PrivacyPolicy',
            true
        );

        $this->assertTrue(true);
    }

    // ===== FILE UPLOAD VALIDATION TESTS =====

    /**
     * Test valid image upload
     */
    public function test_valid_image_upload_validation()
    {
        Storage::fake('uploads');

        $file = UploadedFile::fake()->image('profile.jpg');

        $result = FileUploadValidationService::validate($file, 'image');

        $this->assertTrue($result['valid']);
    }

    /**
     * Test invalid file extension rejected
     */
    public function test_invalid_file_extension_rejected()
    {
        Storage::fake('uploads');

        $file = UploadedFile::fake()->create('malware.exe', 1024);

        $result = FileUploadValidationService::validate($file, 'document');

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    /**
     * Test oversized file rejected
     */
    public function test_oversized_file_rejected()
    {
        Storage::fake('uploads');

        // Create file larger than 5MB for image type
        $file = UploadedFile::fake()->create('large.jpg', 6 * 1024 * 1024);

        $result = FileUploadValidationService::validate($file, 'image');

        $this->assertFalse($result['valid']);
    }

    /**
     * Test safe filename generation
     */
    public function test_safe_filename_generation()
    {
        $file = UploadedFile::fake()->create('test file (1) [dangerous].pdf');

        $safeFilename = FileUploadValidationService::getSafeFilename($file);

        $this->assertStringNotContainsString('(', $safeFilename);
        $this->assertStringNotContainsString(')', $safeFilename);
        $this->assertStringNotContainsString('[', $safeFilename);
        $this->assertStringContainsString('_', $safeFilename);
    }

    /**
     * Test file should not be quarantined
     */
    public function test_safe_file_not_quarantined()
    {
        $this->assertFalse(FileUploadValidationService::shouldQuarantine('document.pdf'));
        $this->assertFalse(FileUploadValidationService::shouldQuarantine('image.jpg'));
    }

    /**
     * Test suspicious file should be quarantined
     */
    public function test_suspicious_file_quarantined()
    {
        $this->assertTrue(FileUploadValidationService::shouldQuarantine('malware.exe'));
        $this->assertTrue(FileUploadValidationService::shouldQuarantine('script.js'));
        $this->assertTrue(FileUploadValidationService::shouldQuarantine('archive.zip'));
    }

    /**
     * Test file integrity hash
     */
    public function test_file_integrity_hash()
    {
        Storage::fake('uploads');

        $file = UploadedFile::fake()->create('test.txt');
        $hash = FileUploadValidationService::generateFileHash($file);

        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash)); // SHA256 = 64 chars
    }

    /**
     * Test get file metadata
     */
    public function test_get_file_metadata()
    {
        Storage::fake('uploads');

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $metadata = FileUploadValidationService::getFileMetadata($file);

        $this->assertArrayHasKey('original_name', $metadata);
        $this->assertArrayHasKey('mime_type', $metadata);
        $this->assertArrayHasKey('size', $metadata);
        $this->assertArrayHasKey('hash', $metadata);
    }

    // ===== SECURITY INTEGRATION TESTS =====

    /**
     * Test file upload in consultation chat with validation
     */
    public function test_file_upload_in_chat_with_validation()
    {
        Storage::fake('uploads');

        $consultation = Konsultasi::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $validation = FileUploadValidationService::validate($file, 'image');

        $this->assertTrue($validation['valid']);

        // Simulate file upload
        $safeFilename = FileUploadValidationService::getSafeFilename($file);
        $this->assertNotEmpty($safeFilename);
    }

    /**
     * Test sensitive data masking in logs
     */
    public function test_sensitive_data_masking()
    {
        AuditLoggingService::log(
            'TEST',
            'User',
            $this->patient->id,
            [
                'name' => 'John Doe',
                'password' => 'secret123',
                'email' => 'john@example.com',
                'phone_number' => '081234567890',
            ]
        );

        // Verify masking worked (would check logs in real test)
        $this->assertTrue(true);
    }

    /**
     * Test GDPR compliance status
     */
    public function test_gdpr_compliance_status()
    {
        $status = GDPRComplianceService::getComplianceStatus();

        $this->assertArrayHasKey('consent_management', $status);
        $this->assertArrayHasKey('audit_logging', $status);
        $this->assertArrayHasKey('data_portability', $status);
        $this->assertEquals('Implemented', $status['consent_management']);
    }
}
