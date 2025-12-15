<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Traits\SanitizeInput;

/**
 * Register Request Validation
 * 
 * Validate user registration dengan strong password requirements
 * Sanitize input untuk prevent XSS attacks
 */
class RegisterRequest extends FormRequest
{
    use SanitizeInput;

    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'min:8',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'type' => ['required', 'in:pasien,dokter'],
            'phone' => ['nullable', 'string', 'min:10', 'max:15'],
            'nik' => ['nullable', 'string', 'size:16', 'unique:users,nik'],
            'bio' => ['nullable', 'string', 'max:500'],
            'specialization' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'name.min' => 'Nama minimal 3 karakter',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'type.required' => 'Tipe user harus dipilih',
            'type.in' => 'Tipe user harus pasien atau dokter',
            'nik.size' => 'NIK harus 16 digit',
            'nik.unique' => 'NIK sudah terdaftar',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->sanitizeInput($this->email, 'email')),
            'type' => strtolower($this->type),
            'name' => $this->sanitizeInput($this->name, 'text'),
            'bio' => $this->sanitizeInput($this->bio ?? '', 'text'),
            'specialization' => $this->sanitizeInput($this->specialization ?? '', 'text'),
            'phone' => $this->sanitizeInput($this->phone ?? '', 'text'),
            'nik' => $this->sanitizeInput($this->nik ?? '', 'number'),
        ]);
    }
}
