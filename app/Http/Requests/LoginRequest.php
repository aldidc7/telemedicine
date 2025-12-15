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
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
        ];
    }
}

