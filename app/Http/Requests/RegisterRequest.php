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
            'password' => 'required|string|min:8|max:255|confirmed',
            'password_confirmation' => 'required|string|min:8|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:pasien,dokter',
        ];

        // Validasi khusus untuk pasien
        if ($this->input('role') === 'pasien') {
            $rules['nik'] = 'required|string|size:16|regex:/^\d{16}$/';
        }

        // Validasi khusus untuk dokter
        if ($this->input('role') === 'dokter') {
            $rules['sip'] = 'required|string|max:255|unique:doctors,license_number';
            $rules['specialization'] = 'required|string|in:Umum,Anak,Kandungan,Jantung,Mata,THT';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi Password',
            'phone' => 'Nomor Telepon',
            'role' => 'Tipe Akun',
            'nik' => 'NIK',
            'sip' => 'Nomor SIP',
            'specialization' => 'Spesialisasi',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus 16 digit',
            'nik.regex' => 'NIK harus berupa angka',
            'sip.required' => 'Nomor SIP wajib diisi',
            'sip.unique' => 'Nomor SIP sudah terdaftar',
            'specialization.required' => 'Spesialisasi wajib dipilih',
            'specialization.in' => 'Spesialisasi tidak valid',
        ];
    }
}

