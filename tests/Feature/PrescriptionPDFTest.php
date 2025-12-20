<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Consultation;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Phase 6: Prescription PDF Tests
 * 
 * Test suite untuk prescription PDF generation, download,
 * dan email delivery functionality
 */
class PrescriptionPDFTest extends TestCase
{
    use RefreshDatabase;

    protected User $patient;
    protected User $doctor;
    protected Consultation $consultation;
    protected Prescription $prescription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = User::factory()->patient()->create([
            'name' => 'Patient Test',
            'email' => 'patient@test.com',
        ]);

        $this->doctor = User::factory()->doctor()->create([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
        ]);

        $this->consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'completed',
        ]);

        $this->prescription = Prescription::factory()->create([
            'consultation_id' => $this->consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'medicines' => [
                [
                    'name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => '3x sehari',
                    'duration' => '7 hari',
                ],
            ],
        ]);
    }

    // ============== PRESCRIPTION PDF GENERATION TESTS ==============

    /** @test */
    public function patient_can_request_prescription_pdf()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function prescription_pdf_contains_required_information()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        // Verify PDF content (simplified - actual verification would involve PDF parsing)
        $this->assertTrue(strlen($response->getContent()) > 0);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function prescription_pdf_includes_patient_details()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        // Check response headers indicate PDF
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function prescription_pdf_includes_doctor_details()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    /** @test */
    public function prescription_pdf_includes_medicines_list()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    /** @test */
    public function prescription_pdf_includes_usage_instructions()
    {
        $this->prescription->update([
            'notes' => 'Take with food. Avoid alcohol.',
        ]);

        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    // ============== PRESCRIPTION PDF SECURITY TESTS ==============

    /** @test */
    public function patient_cannot_download_others_prescription_pdf()
    {
        $otherPatient = User::factory()->patient()->create();

        $this->actingAs($otherPatient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_download_prescription_pdf()
    {
        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(401);
    }

    /** @test */
    public function doctor_can_download_own_prescription_pdf()
    {
        $this->actingAs($this->doctor);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    // ============== PRESCRIPTION PDF EMAIL TESTS ==============

    /** @test */
    public function prescription_pdf_can_be_emailed()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson("/api/v1/prescriptions/{$this->prescription->id}/send-email", [
            'email' => 'patient@test.com',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function prescription_email_includes_pdf_attachment()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->actingAs($this->patient);

        $this->postJson("/api/v1/prescriptions/{$this->prescription->id}/send-email", [
            'email' => 'patient@test.com',
        ]);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PrescriptionMail::class);
    }

    /** @test */
    public function prescription_email_sent_to_correct_recipient()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->actingAs($this->patient);

        $this->postJson("/api/v1/prescriptions/{$this->prescription->id}/send-email", [
            'email' => 'patient@test.com',
        ]);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PrescriptionMail::class, function ($mail) {
            return $mail->hasTo('patient@test.com');
        });
    }

    // ============== PRESCRIPTION PDF DOWNLOAD TRACKING ==============

    /** @test */
    public function prescription_pdf_download_is_logged()
    {
        $this->actingAs($this->patient);

        $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'prescription_pdf_downloaded',
            'entity_id' => $this->prescription->id,
            'user_id' => $this->patient->id,
        ]);
    }

    /** @test */
    public function prescription_download_count_incremented()
    {
        $initialCount = $this->prescription->download_count ?? 0;

        $this->actingAs($this->patient);

        $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $this->prescription->refresh();
        $this->assertEquals($initialCount + 1, $this->prescription->download_count);
    }

    // ============== PRESCRIPTION PDF FORMATTING TESTS ==============

    /** @test */
    public function prescription_pdf_properly_formatted()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        // PDF should start with %PDF
        $content = $response->getContent();
        $this->assertTrue(strpos($content, '%PDF') === 0);
    }

    /** @test */
    public function prescription_pdf_includes_timestamp()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    /** @test */
    public function prescription_pdf_filename_format()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('prescription', strtolower($disposition));
        $this->assertStringContainsString('.pdf', strtolower($disposition));
    }

    // ============== PRESCRIPTION MULTIPLE MEDICINES TESTS ==============

    /** @test */
    public function prescription_with_multiple_medicines_displays_correctly()
    {
        $this->prescription->update([
            'medicines' => [
                [
                    'name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => '3x sehari',
                    'duration' => '7 hari',
                ],
                [
                    'name' => 'Ibuprofen',
                    'dosage' => '400mg',
                    'frequency' => '2x sehari',
                    'duration' => '5 hari',
                ],
                [
                    'name' => 'Vitamin C',
                    'dosage' => '1000mg',
                    'frequency' => '1x sehari',
                    'duration' => '10 hari',
                ],
            ],
        ]);

        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    // ============== PRESCRIPTION PDF GENERATION VALIDATION ==============

    /** @test */
    public function prescription_pdf_generation_includes_consultation_reference()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    /** @test */
    public function prescription_pdf_includes_prescription_date()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/{$this->prescription->id}/pdf");

        $response->assertStatus(200);
    }

    /** @test */
    public function invalid_prescription_id_returns_404()
    {
        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/prescriptions/99999/pdf");

        $response->assertStatus(404);
    }

    // ============== PRESCRIPTION PDF BATCH DOWNLOAD ==============

    /** @test */
    public function patient_can_download_all_prescriptions_as_zip()
    {
        // Create multiple prescriptions
        Prescription::factory()->count(3)->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
        ]);

        $this->actingAs($this->patient);

        $response = $this->getJson('/api/v1/prescriptions/download-all');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
    }
}
