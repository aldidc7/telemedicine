<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\SanitizeInput;

/**
 * Rating Request Validation
 * Sanitize comments to prevent XSS
 */
class RatingRequest extends FormRequest
{
    use SanitizeInput;

    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === 'pasien';
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'exists:appointments,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_id.required' => 'Appointment ID harus diisi',
            'appointment_id.exists' => 'Appointment tidak ditemukan',
            'rating.required' => 'Rating harus diisi',
            'rating.integer' => 'Rating harus angka',
            'rating.min' => 'Rating minimal 1',
            'rating.max' => 'Rating maksimal 5',
            'comment.max' => 'Komentar maksimal 500 karakter',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comment' => $this->sanitizeInput($this->comment ?? '', 'text'),
        ]);
    }
}
