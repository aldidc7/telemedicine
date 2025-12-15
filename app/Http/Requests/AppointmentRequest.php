<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Appointment Request Validation
 */
class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'exists:users,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Dokter harus dipilih',
            'doctor_id.exists' => 'Dokter tidak ditemukan',
            'appointment_date.required' => 'Tanggal appointment harus diisi',
            'appointment_date.date' => 'Format tanggal tidak valid',
            'appointment_date.after_or_equal' => 'Tanggal tidak boleh di masa lalu',
            'appointment_time.required' => 'Waktu appointment harus diisi',
            'reason.required' => 'Alasan appointment harus diisi',
            'reason.min' => 'Alasan minimal 10 karakter',
        ];
    }
}
