<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $fileCategory = $this->input('category', 'consultation_file');
        $maxSize = config("file-upload.max_upload_sizes.{$fileCategory}", 10 * 1024 * 1024);
        $allowedExtensions = config('file-upload.allowed_extensions', []);
        $extensionRules = implode(',', $allowedExtensions);

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxSize}", // Laravel expects KB, so convert
                "mimes:{$extensionRules}",
            ],
            'category' => [
                'required',
                'string',
                'in:profile_photo,medical_document,medical_image,prescription,consultation_file,message_attachment',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File harus dipilih',
            'file.file' => 'Yang dipilih harus berupa file',
            'file.max' => 'File terlalu besar. Maksimal ukuran: ' . $this->getMaxFileSize(),
            'file.mimes' => 'Tipe file tidak didukung. Format yang diizinkan: ' . implode(', ', config('file-upload.allowed_extensions', [])),
            'category.required' => 'Kategori file harus ditentukan',
            'category.in' => 'Kategori file tidak valid',
        ];
    }

    private function getMaxFileSize(): string
    {
        $category = $this->input('category', 'consultation_file');
        $bytes = config("file-upload.max_upload_sizes.{$category}", 10 * 1024 * 1024);
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
