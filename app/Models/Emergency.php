<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ============================================
 * EMERGENCY MODEL
 * ============================================
 * 
 * Track emergency cases dan escalation procedures
 * 
 * Emergency levels:
 * - critical: Life-threatening (ambulance, police, hospital)
 * - severe: Serious condition (urgent hospital referral)
 * - moderate: Needs immediate professional evaluation
 * 
 * @property int $id
 * @property int $consultation_id - Consultation yang trigger emergency
 * @property int $created_by_id - User yang trigger (doctor atau patient)
 * @property string $level - critical, severe, moderate
 * @property string $reason - Alasan emergency (symptoms, condition)
 * @property string $status - open, escalated, resolved, referred
 * @property int|null $hospital_id - Hospital yang direferensikan
 * @property string|null $hospital_name - Nama hospital
 * @property string|null $hospital_address - Alamat hospital
 * @property string|null $ambulance_called_at - Kapan ambulance dipanggil
 * @property string|null $ambulance_eta - ETA ambulance
 * @property string|null $escalated_at - Kapan di-escalate
 * @property string|null $referral_letter - Referral letter content
 * @property string|null $notes - Additional notes
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property timestamp $deleted_at
 */
class Emergency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultation_id',
        'created_by_id',
        'level',
        'reason',
        'status',
        'hospital_id',
        'hospital_name',
        'hospital_address',
        'ambulance_called_at',
        'ambulance_eta',
        'escalated_at',
        'referral_letter',
        'notes',
    ];

    protected $casts = [
        'ambulance_called_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    // ============= RELATIONSHIPS =============

    /**
     * Consultation yang trigger emergency
     */
    public function consultation()
    {
        return $this->belongsTo(Konsultasi::class);
    }

    /**
     * User yang trigger emergency (doctor atau patient)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Hospital referral
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Emergency contacts yang dihubungi
     */
    public function contacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Escalation logs
     */
    public function escalationLogs()
    {
        return $this->hasMany(EmergencyEscalationLog::class);
    }

    // ============= SCOPES =============

    /**
     * Get active emergencies
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'escalated']);
    }

    /**
     * Get critical emergencies
     */
    public function scopeCritical($query)
    {
        return $query->where('level', 'critical');
    }

    /**
     * Get unresolved emergencies
     */
    public function scopeUnresolved($query)
    {
        return $query->where('status', '!=', 'resolved');
    }

    // ============= METHODS =============

    /**
     * Escalate emergency ke hospital
     */
    public function escalateToHospital($hospitalData)
    {
        $this->hospital_name = $hospitalData['name'];
        $this->hospital_address = $hospitalData['address'];
        $this->status = 'escalated';
        $this->escalated_at = now();
        $this->save();

        // Log escalation
        $this->escalationLogs()->create([
            'action' => 'hospital_escalation',
            'details' => "Escalated to {$hospitalData['name']}",
            'timestamp' => now(),
        ]);

        return $this;
    }

    /**
     * Call ambulance
     */
    public function callAmbulance($eta = null)
    {
        $this->ambulance_called_at = now();
        if ($eta) {
            $this->ambulance_eta = $eta;
        }
        $this->save();

        // Log ambulance call
        $this->escalationLogs()->create([
            'action' => 'ambulance_called',
            'details' => "Ambulance called, ETA: {$eta}",
            'timestamp' => now(),
        ]);

        return $this;
    }

    /**
     * Generate referral letter
     */
    public function generateReferralLetter()
    {
        $content = $this->buildReferralLetterContent();
        $this->referral_letter = $content;
        $this->save();

        return $this;
    }

    /**
     * Build referral letter content
     */
    private function buildReferralLetterContent()
    {
        $consultation = $this->consultation;
        $patient = $consultation->patient;
        $doctor = $consultation->doctor;
        $escalatedDate = $this->escalated_at?->format('d-m-Y H:i') ?? 'N/A';
        $escalatedDateFull = $this->escalated_at?->format('d F Y') ?? date('d F Y');

        return <<<HTML
TELEMEDICINE EMERGENCY REFERRAL LETTER

Date: {$escalatedDate}
Emergency Level: {$this->level}
Status: {$this->status}

PATIENT INFORMATION:
Name: {$patient->nama}
ID: {$patient->id}
Phone: {$patient->nomor_telepon}

DOCTOR INFORMATION:
Name: {$doctor->nama}
License: {$doctor->nomor_sip}

EMERGENCY REASON:
{$this->reason}

CLINICAL NOTES:
{$this->notes}

REFERRED TO:
Hospital: {$this->hospital_name}
Address: {$this->hospital_address}

This referral letter serves as documentation of medical emergency escalation from telemedicine consultation and authorizes immediate medical treatment at the referred hospital.

Signed: Telemedicine System
{$escalatedDateFull}
HTML;
    }

    /**
     * Mark as resolved
     */
    public function markResolved($resolutionNotes = null)
    {
        $this->status = 'resolved';
        if ($resolutionNotes) {
            $this->notes .= "\n\nResolution: {$resolutionNotes}";
        }
        $this->save();

        return $this;
    }

    /**
     * Check if emergency is critical
     */
    public function isCritical()
    {
        return $this->level === 'critical';
    }

    /**
     * Check if ambulance has been called
     */
    public function ambulanceCalled()
    {
        return !is_null($this->ambulance_called_at);
    }
}
