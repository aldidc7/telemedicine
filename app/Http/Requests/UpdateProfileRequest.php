<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\SanitizeInput;

/**
 * Profile Update Request Validation
 * Sanitize profile fields to prevent XSS
 */
class UpdateProfileRequest extends FormRequest
{
    use SanitizeInput;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', "unique:users,email,{$userId}"],
            'phone' => ['sometimes', 'string', 'min:10', 'max:15'],
            'bio' => ['sometimes', 'string', 'max:500'],
            'profile_photo' => ['sometimes', 'url'],
            'specialization' => ['sometimes', 'string', 'max:100'],
            'experience_years' => ['sometimes', 'integer', 'min:0', 'max:70'],
            'address' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:100'],
            'province' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Nama minimal 3 karakter',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'phone.min' => 'No. telepon minimal 10 digit',
            'bio.max' => 'Bio maksimal 500 karakter',
            'profile_photo.url' => 'Format URL foto tidak valid',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('email')) {
            $data['email'] = strtolower($this->sanitizeInput($this->email, 'email'));
        }

        if ($this->has('name')) {
            $data['name'] = $this->sanitizeInput($this->name, 'text');
        }

        if ($this->has('bio')) {
            $data['bio'] = $this->sanitizeInput($this->bio, 'text');
        }

        if ($this->has('phone')) {
            $data['phone'] = $this->sanitizeInput($this->phone, 'text');
        }

        if ($this->has('specialization')) {
            $data['specialization'] = $this->sanitizeInput($this->specialization, 'text');
        }

        if ($this->has('address')) {
            $data['address'] = $this->sanitizeInput($this->address, 'text');
        }

        if ($this->has('city')) {
            $data['city'] = $this->sanitizeInput($this->city, 'text');
        }

        if ($this->has('province')) {
            $data['province'] = $this->sanitizeInput($this->province, 'text');
        }

        if ($this->has('profile_photo')) {
            $data['profile_photo'] = $this->sanitizeInput($this->profile_photo, 'url');
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
