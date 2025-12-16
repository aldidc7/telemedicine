<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DokterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // User data
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            
            // Dokter data
            'specialization' => ['sometimes', 'nullable', 'string', 'max:100'],
            'license_number' => ['sometimes', 'string', 'max:50'],
            'phone_number' => ['sometimes', 'string', 'max:15'],
            'address' => ['sometimes', 'string', 'max:500'],
            'gender' => ['sometimes', 'in:male,female,other'],
            'birthplace_city' => ['sometimes', 'string', 'max:100'],
            'place_of_birth' => ['sometimes', 'date'],
            'blood_type' => ['sometimes', 'in:A,B,AB,O'],
            'marital_status' => ['sometimes', 'in:single,married,divorced,widowed'],
            'ethnicity' => ['sometimes', 'string', 'max:100'],
            'max_concurrent_consultations' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'is_available' => ['sometimes', 'boolean'],
            'tersedia' => ['sometimes', 'boolean'],
            
            // File upload
            'profile_photo' => ['sometimes', 'file', 'image', 'max:5120'], // max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'specialization.max' => 'Spesialisasi maksimal 100 karakter',
            'license_number.max' => 'Nomor lisensi maksimal 50 karakter',
            'phone_number.max' => 'Nomor telepon maksimal 15 digit',
            'address.max' => 'Alamat maksimal 500 karakter',
            'gender.in' => 'Jenis kelamin tidak valid',
            'birthplace_city.max' => 'Kota tempat lahir maksimal 100 karakter',
            'place_of_birth.date' => 'Format tanggal lahir tidak valid',
            'blood_type.in' => 'Golongan darah harus A, B, AB, atau O',
            'marital_status.in' => 'Status pernikahan tidak valid',
            'ethnicity.max' => 'Suku/Etnis maksimal 100 karakter',
            'max_concurrent_consultations.integer' => 'Jumlah konsultasi harus berupa angka',
            'max_concurrent_consultations.min' => 'Jumlah konsultasi minimal 1',
            'max_concurrent_consultations.max' => 'Jumlah konsultasi maksimal 50',
            'is_available.boolean' => 'Status ketersediaan harus true atau false',
            'tersedia.boolean' => 'Status tersedia harus true atau false',
            'profile_photo.image' => 'File harus berupa gambar',
            'profile_photo.max' => 'Ukuran gambar maksimal 5MB',
        ];
    }
}
