<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Profile Update Request Validation
 */
class UpdateProfileRequest extends FormRequest
{
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
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower($this->email),
            ]);
        }
    }
}
