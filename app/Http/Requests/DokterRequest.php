<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DokterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
    
    protected function prepareForValidation()
    {
        // Log incoming request data
        \Log::info('DokterRequest - raw input', [
            'all' => $this->all(),
            'files' => $this->files->all(),
            'has_profile_photo_file' => $this->hasFile('profile_photo'),
        ]);
        
        // If place_of_birth is a string date, keep it as is (Laravel will validate)
        // date validation will accept string dates
    }

    public function rules(): array
    {
        return [
            // User data
            'name' => ['sometimes', 'string', 'max:255', 'regex:/^[a-zA-Z\s.]+$/'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . ($this->user()?->id ?? 'NULL')],
            
            // Dokter data - Required for registration
            'specialization' => ['sometimes', 'required_without:update', 'nullable', 'string', 'max:100'],
            'license_number' => ['sometimes', 'required_without:update', 'string', 'max:50', 'unique:doctors,license_number'],
            'phone_number' => ['sometimes', 'required_without:update', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['sometimes', 'string', 'max:500'],
            'gender' => ['sometimes', 'in:male,female,other'],
            'birthplace_city' => ['sometimes', 'string', 'max:100'],
            'place_of_birth' => ['sometimes', 'nullable', 'date', 'before_or_equal:' . now()->subYears(20)->format('Y-m-d')],
            'blood_type' => ['sometimes', 'in:A,B,AB,O'],
            'marital_status' => ['sometimes', 'in:single,married,divorced,widowed'],
            'ethnicity' => ['sometimes', 'string', 'max:100'],
            'max_concurrent_consultations' => ['sometimes', 'integer', 'min:1', 'max:50', 'required_without:update'],
            'is_available' => ['sometimes', 'boolean'],
            'tersedia' => ['sometimes', 'boolean'],
            
            // File upload - only validate if actually a file is being sent
            'profile_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // max 5MB
            
            // License/credential documents
            'credentials' => ['nullable', 'array'],
            'credentials.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, dan titik',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'email.unique' => 'Email sudah terdaftar',
            'specialization.max' => 'Spesialisasi maksimal 100 karakter',
            'specialization.required_without' => 'Spesialisasi wajib diisi saat registrasi',
            'license_number.max' => 'Nomor lisensi maksimal 50 karakter',
            'license_number.required_without' => 'Nomor lisensi wajib diisi saat registrasi',
            'license_number.unique' => 'Nomor lisensi sudah terdaftar',
            'phone_number.max' => 'Nomor telepon maksimal 15 digit',
            'phone_number.regex' => 'Format nomor telepon tidak valid',
            'phone_number.required_without' => 'Nomor telepon wajib diisi saat registrasi',
            'address.max' => 'Alamat maksimal 500 karakter',
            'gender.in' => 'Jenis kelamin tidak valid',
            'birthplace_city.max' => 'Kota tempat lahir maksimal 100 karakter',
            'place_of_birth.date' => 'Format tanggal lahir tidak valid',
            'place_of_birth.before_or_equal' => 'Dokter minimal berusia 20 tahun',
            'blood_type.in' => 'Golongan darah harus A, B, AB, atau O',
            'marital_status.in' => 'Status pernikahan tidak valid',
            'ethnicity.max' => 'Suku/Etnis maksimal 100 karakter',
            'max_concurrent_consultations.integer' => 'Jumlah konsultasi harus berupa angka',
            'max_concurrent_consultations.min' => 'Jumlah konsultasi minimal 1',
            'max_concurrent_consultations.max' => 'Jumlah konsultasi maksimal 50',
            'max_concurrent_consultations.required_without' => 'Batas konsultasi wajib diisi saat registrasi',
            'is_available.boolean' => 'Status ketersediaan harus true atau false',
            'tersedia.boolean' => 'Status tersedia harus true atau false',
            'profile_photo.image' => 'File harus berupa gambar',
            'profile_photo.mimes' => 'Format gambar harus JPEG atau PNG',
            'profile_photo.max' => 'Ukuran gambar maksimal 5MB',
            'credentials.array' => 'Credentials harus berupa array',
            'credentials.*.file' => 'Setiap credential harus berupa file',
            'credentials.*.mimes' => 'Format credential harus PDF, JPG, atau PNG',
            'credentials.*.max' => 'Ukuran setiap credential maksimal 5MB',
        ];
    }
}
