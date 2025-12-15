<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\SanitizeInput;

/**
 * Message Request Validation
 * Sanitize message content to prevent XSS
 */
class MessageRequest extends FormRequest
{
    use SanitizeInput;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'exists:conversations,id'],
            'message' => ['required', 'string', 'min:1', 'max:2000'],
            'attachment_url' => ['nullable', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'conversation_id.required' => 'Conversation ID harus diisi',
            'conversation_id.exists' => 'Conversation tidak ditemukan',
            'message.required' => 'Pesan harus diisi',
            'message.max' => 'Pesan maksimal 2000 karakter',
            'attachment_url.url' => 'Format URL tidak valid',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'message' => $this->sanitizeInput($this->message, 'html'),
            'attachment_url' => $this->sanitizeInput($this->attachment_url ?? '', 'url'),
        ]);
    }
}
