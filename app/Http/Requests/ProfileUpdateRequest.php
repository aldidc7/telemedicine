<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

/**
 * Profile Update Request Validation
 * 
 * Validasi untuk update profil user
 */
class ProfileUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        $userId = Auth::id();
        
        return [
            'name' => 'sometimes|string|min:3|max:255',
            'email' => "sometimes|email|unique:users,email,{$userId}|max:255",
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date|before:today',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'spesialisasi' => 'nullable|string|max:255', // untuk dokter
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'phone' => 'Nomor Telepon',
            'alamat' => 'Alamat',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'spesialisasi' => 'Spesialisasi',
        ];
    }
}
