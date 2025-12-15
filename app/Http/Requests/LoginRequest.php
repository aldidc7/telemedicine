<?php

namespace App\Http\Requests;

/**
 * Login Request Validation
 * 
 * Validasi untuk login pasien, dokter, dan admin
 * Support login dengan email atau NIK (untuk pasien)
 */
class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:255',  // Email atau NIK
            'email' => 'nullable|email|max:255',        // Alternative: email only
            'password' => 'required|string|min:6|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'identifier' => 'Email/NIK',
            'email' => 'Email',
            'password' => 'Password',
        ];
    }

    /**
     * Prepare data for validation
     * Support 'email' sebagai alternative field untuk 'identifier'
     */
    protected function prepareForValidation()
    {
        if ($this->has('email') && !$this->has('identifier')) {
            $this->merge(['identifier' => $this->input('email')]);
        }
    }
}

