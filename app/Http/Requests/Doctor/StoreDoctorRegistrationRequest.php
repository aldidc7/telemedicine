<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreDoctorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Stage 1: Basic Info
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:50'],

            // Stage 2: Documents (optional at registration)
            'sip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'sip_number' => ['nullable', 'string', 'max:50'],
            'str' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'str_number' => ['nullable', 'string', 'max:50'],
            'ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Stage 3: Profile
            'facility_name' => ['nullable', 'string', 'max:255'],
            'is_available' => ['nullable', 'boolean'],
            'max_concurrent_consultations' => ['nullable', 'integer', 'min:1', 'max:50'],

            // Stage 4: Compliance
            'accepted_terms' => ['nullable', 'boolean'],
            'accepted_privacy' => ['nullable', 'boolean'],
            'accepted_informed_consent' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal 12 karakter.',
            'specialization.max' => 'Spesialisasi maksimal 100 karakter.',
            'sip.max' => 'File SIP maksimal 5MB.',
            'sip.mimes' => 'File SIP harus PDF, JPG, atau PNG.',
            'str.max' => 'File STR maksimal 5MB.',
            'str.mimes' => 'File STR harus PDF, JPG, atau PNG.',
            'ktp.max' => 'File KTP maksimal 5MB.',
            'ktp.mimes' => 'File KTP harus PDF, JPG, atau PNG.',
            'ijazah.max' => 'File Ijazah maksimal 5MB.',
            'ijazah.mimes' => 'File Ijazah harus PDF, JPG, atau PNG.',
        ];
    }
}
