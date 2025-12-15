<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\SanitizeInput;

/**
 * Prescription Request Validation
 * Sanitize medicine details to prevent XSS
 */
class PrescriptionRequest extends FormRequest
{
    use SanitizeInput;

    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === 'dokter';
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'exists:appointments,id'],
            'medicines' => ['required', 'array', 'min:1'],
            'medicines.*.medicine_name' => ['required', 'string', 'max:255'],
            'medicines.*.dosage' => ['required', 'string', 'max:100'],
            'medicines.*.frequency' => ['required', 'string', 'max:100'],
            'medicines.*.duration' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_id.required' => 'Appointment ID harus diisi',
            'appointment_id.exists' => 'Appointment tidak ditemukan',
            'medicines.required' => 'Minimal 1 obat harus ditambahkan',
            'medicines.*.medicine_name.required' => 'Nama obat harus diisi',
            'medicines.*.dosage.required' => 'Dosis harus diisi',
            'medicines.*.frequency.required' => 'Frekuensi harus diisi',
            'medicines.*.duration.required' => 'Durasi harus diisi',
        ];
    }

    protected function prepareForValidation(): void
    {
        $medicines = $this->medicines ?? [];
        $sanitizedMedicines = [];

        foreach ($medicines as $medicine) {
            $sanitizedMedicines[] = [
                'medicine_name' => $this->sanitizeInput($medicine['medicine_name'] ?? '', 'text'),
                'dosage' => $this->sanitizeInput($medicine['dosage'] ?? '', 'text'),
                'frequency' => $this->sanitizeInput($medicine['frequency'] ?? '', 'text'),
                'duration' => $this->sanitizeInput($medicine['duration'] ?? '', 'text'),
            ];
        }

        $this->merge([
            'medicines' => $sanitizedMedicines,
            'notes' => $this->sanitizeInput($this->notes ?? '', 'text'),
        ]);
    }
}
