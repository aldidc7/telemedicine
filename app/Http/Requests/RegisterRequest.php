<?php

namespace App\Http\Requests;

/**
 * Register Request Validation
 * 
 * Validasi untuk registrasi user baru (pasien)
 */
class RegisterRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|max:255|confirmed',
            'password_confirmation' => 'required|string|min:8|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:pasien',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi Password',
            'phone' => 'Nomor Telepon',
            'role' => 'Role',
        ];
    }
}
