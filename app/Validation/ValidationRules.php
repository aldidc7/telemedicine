<?php

namespace App\Validation;

/**
 * ============================================
 * CUSTOM VALIDATION RULES
 * ============================================
 * 
 * Validation rules khusus untuk aplikasi
 */
class ValidationRules
{
    /**
     * Rules untuk registrasi user
     * Password: Min 8 karakter, simple tapi secure
     * User-friendly: Tidak perlu uppercase, angka, atau special chars
     */
    public static function registrationRules(): array
    {
        return [
            'name' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|max:255',
            'password_confirmation' => 'required|same:password',
            'no_telepon' => 'nullable|regex:/^(\+62|0)[0-9]{9,12}$/',
            'role' => 'required|in:dokter,pasien',
        ];
    }

    /**
     * Rules untuk profile update
     */
    public static function profileUpdateRules(): array
    {
        return [
            'name' => 'nullable|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'email' => 'nullable|email|unique:users,email',
            'no_telepon' => 'nullable|regex:/^(\+62|0)[0-9]{9,12}$/',
            'address' => 'nullable|string|max:500',
            'photo_url' => 'nullable|url',
        ];
    }

    /**
     * Rules untuk dokter
     */
    public static function dokterRules(): array
    {
        return [
            'specialization' => 'required|string|max:100',
            'license_number' => 'required|string|unique:dokteres,license_number|max:50',
            'bio' => 'nullable|string|max:1000',
            'is_available' => 'nullable|boolean',
        ];
    }

    /**
     * Rules untuk pasien
     */
    public static function pasienRules(): array
    {
        return [
            'nik' => 'required|numeric|digits:16|unique:pasiens,nik',
            'tgl_lahir' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'no_telepon' => 'nullable|regex:/^(\+62|0)[0-9]{9,12}$/',
        ];
    }

    /**
     * Rules untuk konsultasi
     */
    public static function konsultasiRules(): array
    {
        return [
            'dokter_id' => 'required|exists:dokteres,id',
            'keluhan' => 'required|string|min:10|max:1000',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'tanggal_mulai' => 'nullable|date|after_or_equal:today',
        ];
    }

    /**
     * Rules untuk pesan chat
     */
    public static function pesanRules(): array
    {
        return [
            'konsultasi_id' => 'required|exists:konsultasis,id',
            'pesan' => 'required|string|min:1|max:5000',
            'tipe_pesan' => 'required|in:text,image,file,audio',
            'url_file' => 'nullable|url',
        ];
    }

    /**
     * Rules untuk rating
     */
    public static function ratingRules(): array
    {
        return [
            'konsultasi_id' => 'required|exists:konsultasis,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom messages untuk errors
     */
    public static function customMessages(): array
    {
        return [
            'name.regex' => 'Nama hanya boleh mengandung huruf dan spasi.',
            'password.regex' => 'Password harus mengandung minimal 1 huruf besar dan 1 angka.',
            'no_telepon.regex' => 'Format nomor telepon tidak valid.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'pesan.min' => 'Pesan tidak boleh kosong.',
        ];
    }
}
