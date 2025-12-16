<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateFileUpload
{
    /**
     * File upload validation rules
     */
    private const ALLOWED_MIMES = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const DANGEROUS_EXTENSIONS = ['exe', 'bat', 'cmd', 'sh', 'php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'js', 'jar'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debug: Log request details BEFORE attempting to access request body
        // Note: We intentionally avoid accessing $request->all() or $request->files
        // before the request is fully handled to prevent stream consumption
        
        \Log::info('ValidateFileUpload middleware - request incoming', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
        ]);
        
        // Check jika request mengandung file
        // This is safe - hasFile() just checks headers, doesn't consume body
        $hasAnyFile = $request->hasFile('photo') || 
                      $request->hasFile('file') || 
                      $request->hasFile('document') || 
                      $request->hasFile('profile_photo');
        
        if ($hasAnyFile) {
            \Log::info('ValidateFileUpload - file input detected, validating...');
            $this->validateFiles($request);
        } else {
            \Log::info('ValidateFileUpload - no file inputs detected');
        }

        return $next($request);
    }

    /**
     * Validate uploaded files
     */
    private function validateFiles(Request $request): void
    {
        $fileInputs = ['photo', 'file', 'document', 'profile_photo'];

        foreach ($fileInputs as $inputName) {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);

                // Validate file exists
                if (!$file || !$file->isValid()) {
                    throw new \Illuminate\Validation\ValidationException(new \Illuminate\Validation\Validator(
                        app('translator'),
                        [],
                        [$inputName => 'required|file'],
                        [$inputName => 'Upload harus berupa file yang valid']
                    ));
                }

                // Validate file size
                if ($file->getSize() > self::MAX_FILE_SIZE) {
                    throw new \Illuminate\Validation\ValidationException(new \Illuminate\Validation\Validator(
                        app('translator'),
                        [],
                        [$inputName => 'max:5120'],
                        [$inputName => 'File tidak boleh lebih dari 5MB']
                    ));
                }

                // Validate MIME type
                $mimeType = $file->getMimeType();
                if (!$this->isAllowedMimeType($mimeType)) {
                    throw new \Illuminate\Validation\ValidationException(new \Illuminate\Validation\Validator(
                        app('translator'),
                        [],
                        [$inputName => 'mimes'],
                        [$inputName => 'Tipe file tidak diperbolehkan. Gunakan: jpg, png, pdf, doc, docx']
                    ));
                }

                // Validate extension
                $extension = strtolower($file->getClientOriginalExtension());
                if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
                    throw new \Illuminate\Validation\ValidationException(new \Illuminate\Validation\Validator(
                        app('translator'),
                        [],
                        [$inputName => 'required'],
                        [$inputName => 'Ekstensi file tidak diperbolehkan untuk keamanan']
                    ));
                }

                // Validate real file content (magic number check)
                if (!$this->isRealFile($file)) {
                    throw new \Illuminate\Validation\ValidationException(new \Illuminate\Validation\Validator(
                        app('translator'),
                        [],
                        [$inputName => 'required'],
                        [$inputName => 'File tidak valid atau mungkin telah dimanipulasi']
                    ));
                }

                // Log upload attempt
                $userId = Auth::check() ? Auth::id() : 'guest';
                \Log::info('File upload validated', [
                    'user_id' => $userId,
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size' => $file->getSize(),
                    'timestamp' => now(),
                ]);
            }
        }
    }

    /**
     * Check if MIME type is allowed
     */
    private function isAllowedMimeType(string $mimeType): bool
    {
        return isset(self::ALLOWED_MIMES[$mimeType]);
    }

    /**
     * Check if file is real (magic number verification)
     * This prevents uploading executable files disguised as images
     */
    private function isRealFile($file): bool
    {
        $mimeType = $file->getMimeType();
        $realPath = $file->getRealPath();

        // Get file content (first 512 bytes for magic number)
        $handle = fopen($realPath, 'r');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 512);
        fclose($handle);

        // Check magic numbers based on MIME type
        switch ($mimeType) {
            case 'image/jpeg':
                // JPEG starts with FF D8 FF
                return substr($header, 0, 3) === "\xFF\xD8\xFF";

            case 'image/png':
                // PNG starts with 89 50 4E 47
                return substr($header, 0, 4) === "\x89PNG";

            case 'image/gif':
                // GIF starts with GIF87a or GIF89a
                return substr($header, 0, 3) === 'GIF' && in_array(substr($header, 3, 2), ['87', '89']);

            case 'application/pdf':
                // PDF starts with %PDF
                return substr($header, 0, 4) === '%PDF';

            default:
                return true; // Allow other types
        }
    }
}
