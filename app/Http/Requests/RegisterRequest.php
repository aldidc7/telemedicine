<?php

namespace App\Http\Requests;

/**
 * Register Request Validation
 * 
 * Validasi untuk registrasi user baru (pasien atau dokter)
 */
class RegisterRequest extends ApiRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|max:255|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            'password_confirmation' => 'required|string|min:8|max:255',
            'no_telepon' => 'required|string|max:20',
            'role' => 'required|in:pasien,dokter',
        ];

        // Validasi khusus untuk pasien
        if ($this->input('role') === 'pasien') {
            $rules['nik'] = 'required|string|size:16|regex:/^\d{16}$/';
        }

        // Untuk dokter, hanya perlu nama, email, password, no telepon
        // Spesialisasi dan SIP diisi saat profile completion

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi Password',
            'no_telepon' => 'Nomor Telepon',
            'role' => 'Tipe Akun',
            'nik' => 'NIK',
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password harus minimal 8 karakter, terdiri dari huruf besar, huruf kecil, angka, dan simbol (@$!%*?&)',
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus 16 digit',
            'nik.regex' => 'NIK harus berupa angka',
        ];
    }
}

