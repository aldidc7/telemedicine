<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base API Request
 * 
 * Base class untuk semua API request classes
 * Provides centralized validation dan authorization
 */
class ApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi',
            'email' => ':attribute harus berupa email yang valid',
            'min' => ':attribute minimal :min karakter',
            'max' => ':attribute maksimal :max karakter',
            'unique' => ':attribute sudah terdaftar',
            'confirmed' => ':attribute tidak cocok',
            'numeric' => ':attribute harus berupa angka',
            'date' => ':attribute harus berupa tanggal yang valid',
            'exists' => ':attribute tidak ditemukan',
            'in' => ':attribute tidak valid',
            'regex' => ':attribute format tidak valid',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }
}
