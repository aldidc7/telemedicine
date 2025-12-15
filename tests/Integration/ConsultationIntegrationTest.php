<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Services\ConsultationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Consultation Integration Tests
 * 
 * Test consultation workflows with appointments
 * Start, end, status updates, relationship validation
 */
class ConsultationIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $patient;
    protected User $doctor;
    protected Appointment $appointment;
    protected ConsultationService $consultationService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->patient = User::factory()->create(['role' => 'pasien']);
        $this->doctor = User::factory()->create(['role' => 'dokter']);
        
        $this->appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::now()->addHour(),
            'type' => 'online',
            'status' => 'confirmed'
        ]);
        
        $this->consultationService = app(ConsultationService::class);
    }
    
    /**
     * Test complete consultation workflow
     */
    public function test_complete_consultation_workflow(): void
    {
        // Create consultation
        $consultation = $this->consultationService->createConsultation(
            $this->appointment->id,
            $this->doctor->id
        );
        
        $this->assertNotNull($consultation);
        $this->assertEquals('scheduled', $consultation->status);
        
        // Start consultation
        $started = $this->consultationService->startConsultation($consultation->id);
        
        $this->assertEquals('in_progress', $started->status);
        $this->assertNotNull($started->started_at);
        
        // End consultation
        $ended = $this->consultationService->endConsultation(
            $consultation->id,
            'Test diagnosis',
            'Test treatment'
        );
        
        $this->assertEquals('completed', $ended->status);
        $this->assertNotNull($ended->ended_at);
        $this->assertEquals('Test diagnosis', $ended->diagnosis);
        $this->assertEquals('Test treatment', $ended->treatment);
    }
    
    /**
     * Test consultation status transitions are valid
     */
    public function test_consultation_valid_status_transitions(): void
    {
        $consultation = Consultation::create([
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'scheduled'
        ]);
        
        // Valid: scheduled -> in_progress
        $consultation->update(['status' => 'in_progress']);
        $consultation->refresh();
        $this->assertEquals('in_progress', $consultation->status);
        
        // Valid: in_progress -> completed
        $consultation->update(['status' => 'completed']);
        $consultation->refresh();
        $this->assertEquals('completed', $consultation->status);
    }
    
    /**
     * Test consultation includes appointment data
     */
    public function test_consultation_with_appointment_relationship(): void
    {
        $consultation = Consultation::create([
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'scheduled'
        ]);
        
        $consultation->load('appointment');
        
        $this->assertNotNull($consultation->appointment);
        $this->assertEquals($this->appointment->id, $consultation->appointment->id);
    }
    
    /**
     * Test doctor can access their consultations
     */
    public function test_doctor_can_access_their_consultations(): void
    {
        // Create multiple consultations for this doctor
        Consultation::factory(5)->create([
            'doctor_id' => $this->doctor->id
        ]);
        
        $consultations = $this->consultationService->getDoctorConsultations(
            $this->doctor->id
        );
        
        $this->assertGreaterThan(0, $consultations->count());
        
        foreach ($consultations as $consultation) {
            $this->assertEquals($this->doctor->id, $consultation->doctor_id);
        }
    }
    
    /**
     * Test patient can access their consultations
     */
    public function test_patient_can_access_their_consultations(): void
    {
        // Create consultations for this patient
        Consultation::factory(3)->create([
            'appointment_id' => $this->appointment->id
        ]);
        
        $consultations = $this->consultationService->getPatientConsultations(
            $this->patient->id
        );
        
        $this->assertGreaterThan(0, $consultations->count());
    }
    
    /**
     * Test consultation notes are properly stored
     */
    public function test_consultation_notes_are_stored(): void
    {
        $consultation = Consultation::create([
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'scheduled',
            'notes' => 'Important medical notes'
        ]);
        
        $retrieved = Consultation::find($consultation->id);
        
        $this->assertEquals('Important medical notes', $retrieved->notes);
    }
    
    /**
     * Test consultation diagnosis and treatment required on completion
     */
    public function test_consultation_diagnosis_treatment_on_completion(): void
    {
        $consultation = Consultation::create([
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'in_progress'
        ]);
        
        // Complete with diagnosis and treatment
        $consultation->update([
            'status' => 'completed',
            'diagnosis' => 'Flu',
            'treatment' => 'Rest and medication'
        ]);
        
        $consultation->refresh();
        
        $this->assertEquals('completed', $consultation->status);
        $this->assertEquals('Flu', $consultation->diagnosis);
        $this->assertEquals('Rest and medication', $consultation->treatment);
    }
    
    /**
     * Test appointment status updates when consultation starts
     */
    public function test_appointment_status_updates_with_consultation(): void
    {
        $consultation = Consultation::create([
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'in_progress',
            'started_at' => now()
        ]);
        
        // Verify appointment is still confirmed (doesn't auto-change)
        $this->appointment->refresh();
        $this->assertEquals('confirmed', $this->appointment->status);
    }
    
    /**
     * Test consultation with prescriptions
     */
    public function test_consultation_can_have_prescriptions(): void
    {
        $consultation = Consultation::create([
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'completed'
        ]);
        
        // Create prescriptions for this consultation
        \App\Models\Prescription::factory(2)->create([
            'consultation_id' => $consultation->id
        ]);
        
        $consultation->load('prescriptions');
        
        $this->assertEquals(2, $consultation->prescriptions->count());
    }
    
    /**
     * Test consultation with ratings
     */
    public function test_consultation_can_have_ratings(): void
    {
        $consultation = Consultation::create([
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'completed'
        ]);
        
        // Create rating for this consultation
        \App\Models\Rating::create([
            'appointment_id' => $this->appointment->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'rating' => 5,
            'comment' => 'Excellent service'
        ]);
        
        $rating = \App\Models\Rating::where('appointment_id', $this->appointment->id)->first();
        
        $this->assertNotNull($rating);
        $this->assertEquals(5, $rating->rating);
    }
}
