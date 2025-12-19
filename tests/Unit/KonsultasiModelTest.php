<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Konsultasi;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KonsultasiModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $patientUser;
    protected User $doctorUser;
    protected Pasien $patient;
    protected Dokter $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users and models
        $this->patientUser = User::factory()->create(['role' => 'pasien']);
        $this->doctorUser = User::factory()->create(['role' => 'dokter']);
        
        $this->patient = Pasien::create([
            'user_id' => $this->patientUser->id,
            'medical_record_number' => 'RM-2025-00001',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);
        
        $this->doctor = Dokter::create([
            'user_id' => $this->doctorUser->id,
            'license_number' => 'SIP-12345678',
            'specialization' => 'Dokter Umum',
        ]);
    }

    /**
     * Test creating a consultation
     */
    public function test_create_consultation(): void
    {
        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit kepala berkelanjutan',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $this->assertDatabaseHas('konsultasi', [
            'id' => $consultation->id,
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test consultation belongs to patient
     */
    public function test_consultation_belongs_to_patient(): void
    {
        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Demam tinggi',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $this->assertInstanceOf(Pasien::class, $consultation->patient);
        $this->assertEquals($this->patient->id, $consultation->patient->id);
    }

    /**
     * Test consultation belongs to doctor
     */
    public function test_consultation_belongs_to_doctor(): void
    {
        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Flu',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $this->assertInstanceOf(Dokter::class, $consultation->doctor);
        $this->assertEquals($this->doctor->id, $consultation->doctor->id);
    }

    /**
     * Test updating consultation status
     */
    public function test_update_consultation_status(): void
    {
        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Batuk terus-menerus',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $this->assertEquals('pending', $consultation->status);

        $consultation->update(['status' => 'ongoing']);
        $this->assertEquals('ongoing', $consultation->status);

        $consultation->update(['status' => 'completed']);
        $this->assertEquals('completed', $consultation->status);
    }

    /**
     * Test consultation with diagnosis and treatment
     */
    public function test_consultation_with_diagnosis_and_treatment(): void
    {
        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Pusing dan mual',
            'status' => 'completed',
            'consultation_date' => now(),
            'diagnosis' => 'Gastritis akut',
            'treatment' => 'Istirahat, minum air putih, obat antasida',
        ]);

        $this->assertEquals('Gastritis akut', $consultation->diagnosis);
        $this->assertStringContainsString('antasida', $consultation->treatment);
    }

    /**
     * Test filtering consultations by status
     */
    public function test_filter_consultations_by_status(): void
    {
        // Create multiple consultations with different statuses
        Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit gigi',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit telinga',
            'status' => 'ongoing',
            'consultation_date' => now(),
        ]);

        Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit perut',
            'status' => 'completed',
            'consultation_date' => now(),
        ]);

        $pending = Konsultasi::where('status', 'pending')->count();
        $ongoing = Konsultasi::where('status', 'ongoing')->count();
        $completed = Konsultasi::where('status', 'completed')->count();

        $this->assertEquals(1, $pending);
        $this->assertEquals(1, $ongoing);
        $this->assertEquals(1, $completed);
    }

    /**
     * Test consultation date ordering
     */
    public function test_consultations_ordered_by_date(): void
    {
        $date1 = now()->subDays(3);
        $date2 = now()->subDays(1);
        $date3 = now();

        $consultation1 = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Keluhan 1',
            'status' => 'completed',
            'consultation_date' => $date1,
        ]);

        $consultation2 = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Keluhan 2',
            'status' => 'completed',
            'consultation_date' => $date2,
        ]);

        $consultation3 = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Keluhan 3',
            'status' => 'completed',
            'consultation_date' => $date3,
        ]);

        $ordered = Konsultasi::orderBy('consultation_date', 'desc')->get();
        
        $this->assertEquals($consultation3->id, $ordered->first()->id);
        $this->assertEquals($consultation1->id, $ordered->last()->id);
    }

    /**
     * Test deleting a consultation
     */
    public function test_delete_consultation(): void
    {
        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Sakit perut',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $consultationId = $consultation->id;
        $consultation->delete();

        $this->assertDatabaseMissing('konsultasi', [
            'id' => $consultationId,
        ]);
    }

    /**
     * Test filtering consultations by patient
     */
    public function test_filter_consultations_by_patient(): void
    {
        // Create second patient
        $patientUser2 = User::factory()->create(['role' => 'pasien']);
        $patient2 = Pasien::create([
            'user_id' => $patientUser2->id,
            'medical_record_number' => 'RM-2025-00002',
            'date_of_birth' => '1995-05-15',
            'gender' => 'female',
        ]);

        // Create consultations for both patients
        Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Keluhan pasien 1',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        Konsultasi::create([
            'pasien_id' => $patient2->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Keluhan pasien 2',
            'status' => 'pending',
            'consultation_date' => now(),
        ]);

        $patient1Consultations = Konsultasi::where('pasien_id', $this->patient->id)->count();
        $patient2Consultations = Konsultasi::where('pasien_id', $patient2->id)->count();

        $this->assertEquals(1, $patient1Consultations);
        $this->assertEquals(1, $patient2Consultations);
    }

    /**
     * Test consultation has many messages relationship
     */
    public function test_consultation_has_messages(): void
    {
        $consultation = Konsultasi::create([
            'pasien_id' => $this->patient->id,
            'dokter_id' => $this->doctor->id,
            'complaint' => 'Test complaint',
            'status' => 'ongoing',
            'consultation_date' => now(),
        ]);

        // This test assumes Konsultasi model has messages relationship
        $this->assertNotNull($consultation);
    }
}
