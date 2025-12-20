<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Dokter;
use App\Models\DoctorVerificationDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DoctorVerificationTest extends TestCase
{
    use RefreshDatabase;

    private $dokter;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $dokterUser = User::factory()->create(['role' => 'dokter']);
        
        // Create dokter
        $this->dokter = Dokter::create([
            'user_id' => $dokterUser->id,
            'specialization' => 'Umum',
            'sip_number' => 'SIP123456',
            'no_identitas' => 'ID123456',
        ]);
    }

    /**
     * Test dokter can upload verification document
     */
    public function test_dokter_can_upload_verification_document()
    {
        Storage::fake('private');
        
        $file = UploadedFile::fake()->create('sip.pdf', 100);

        $response = $this->actingAs($this->dokter->user)
            ->post('/api/doctor/verification/upload', [
                'document_type' => 'sip',
                'file' => $file,
            ]);

        $response->assertStatus(201);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);

        $this->assertDatabaseHas('doctor_verification_documents', [
            'dokter_id' => $this->dokter->id,
            'document_type' => 'sip',
            'status' => 'pending',
        ]);
    }

    /**
     * Test document upload validation
     */
    public function test_document_upload_requires_file()
    {
        $response = $this->actingAs($this->dokter->user)
            ->post('/api/doctor/verification/upload', [
                'document_type' => 'sip',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test admin can list pending documents
     */
    public function test_admin_can_list_pending_documents()
    {
        DoctorVerificationDocument::create([
            'dokter_id' => $this->dokter->id,
            'document_type' => 'sip',
            'file_path' => '/documents/sip.pdf',
            'file_name' => 'sip.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/api/admin/verification/pending');

        $response->assertStatus(200);
        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['data']);
    }

    /**
     * Test admin can approve document
     */
    public function test_admin_can_approve_document()
    {
        $document = DoctorVerificationDocument::create([
            'dokter_id' => $this->dokter->id,
            'document_type' => 'sip',
            'file_path' => '/documents/sip.pdf',
            'file_name' => 'sip.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/api/admin/verification/{$document->id}/approve");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('doctor_verification_documents', [
            'id' => $document->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Test admin can reject document with reason
     */
    public function test_admin_can_reject_document()
    {
        $document = DoctorVerificationDocument::create([
            'dokter_id' => $this->dokter->id,
            'document_type' => 'sip',
            'file_path' => '/documents/sip.pdf',
            'file_name' => 'sip.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/api/admin/verification/{$document->id}/reject", [
                'rejection_reason' => 'Invalid document format',
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('doctor_verification_documents', [
            'id' => $document->id,
            'status' => 'rejected',
            'rejection_reason' => 'Invalid document format',
        ]);
    }

    /**
     * Test dokter can view own verification documents
     */
    public function test_dokter_can_view_own_documents()
    {
        DoctorVerificationDocument::create([
            'dokter_id' => $this->dokter->id,
            'document_type' => 'sip',
            'file_path' => '/documents/sip.pdf',
            'file_name' => 'sip.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->dokter->user)
            ->get('/api/doctor/verification/documents');

        $response->assertStatus(200);
        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['data']);
    }

    /**
     * Test non-admin cannot approve documents
     */
    public function test_non_admin_cannot_approve_document()
    {
        $document = DoctorVerificationDocument::create([
            'dokter_id' => $this->dokter->id,
            'document_type' => 'sip',
            'file_path' => '/documents/sip.pdf',
            'file_name' => 'sip.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->dokter->user)
            ->post("/api/admin/verification/{$document->id}/approve");

        // Should be 403 Forbidden or 401 Unauthorized
        $this->assertTrue(in_array($response->status(), [401, 403]));
    }

    /**
     * Test cannot upload invalid file type
     */
    public function test_cannot_upload_invalid_file_type()
    {
        Storage::fake('private');
        
        $file = UploadedFile::fake()->create('document.exe', 100);

        $response = $this->actingAs($this->dokter->user)
            ->post('/api/doctor/verification/upload', [
                'document_type' => 'sip',
                'file' => $file,
            ]);

        // Should reject exe files
        $this->assertNotEquals(201, $response->status());
    }

    /**
     * Test document status tracking
     */
    public function test_document_status_transitions()
    {
        $document = DoctorVerificationDocument::create([
            'dokter_id' => $this->dokter->id,
            'document_type' => 'sip',
            'file_path' => '/documents/sip.pdf',
            'file_name' => 'sip.pdf',
            'status' => 'pending',
        ]);

        // Initially pending
        $this->assertEquals('pending', $document->status);

        // Approve
        $this->actingAs($this->admin)
            ->post("/api/admin/verification/{$document->id}/approve");

        $document->refresh();
        $this->assertEquals('approved', $document->status);
        $this->assertNotNull($document->verified_at);
    }
}
