<?php

namespace App\Services\Security;

use Illuminate\Http\UploadedFile;

/**
 * ============================================
 * SERVICE: FILE UPLOAD VALIDATION
 * ============================================
 * 
 * Secure file upload handling:
 * - Virus/malware scanning
 * - File type validation
 * - Size limits
 * - Safe storage
 * - Quarantine for suspicious files
 */
class FileUploadValidationService
{
    const ALLOWED_TYPES = [
        'image' => ['jpeg', 'jpg', 'png', 'gif'],
        'document' => ['pdf', 'doc', 'docx', 'txt'],
        'prescription' => ['pdf'],
    ];

    const MAX_FILE_SIZES = [
        'image' => 5 * 1024 * 1024, // 5MB
        'document' => 10 * 1024 * 1024, // 10MB
        'prescription' => 5 * 1024 * 1024, // 5MB
    ];

    const MIME_TYPES = [
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain',
    ];

    /**
     * Validate uploaded file
     * 
     * @param UploadedFile $file
     * @param string $type - 'image', 'document', 'prescription'
     * @return array ['valid' => bool, 'message' => string, 'errors' => array]
     */
    public static function validate(UploadedFile $file, string $type = 'document'): array
    {
        $errors = [];

        // Check if type exists
        if (!isset(self::ALLOWED_TYPES[$type])) {
            return [
                'valid' => false,
                'message' => 'Invalid file type category',
                'errors' => ['Unknown type: ' . $type],
            ];
        }

        // Check file size
        $maxSize = self::MAX_FILE_SIZES[$type];
        if ($file->getSize() > $maxSize) {
            $errors[] = "File size exceeds limit of " . ($maxSize / 1024 / 1024) . "MB";
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_TYPES[$type])) {
            $errors[] = "File type '.$extension' not allowed for {$type}";
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!self::isValidMimeType($extension, $mimeType)) {
            $errors[] = "File MIME type doesn't match extension";
        }

        // Check file content (magic bytes)
        if (!self::isValidFileContent($file, $extension)) {
            $errors[] = "File content validation failed - possible malicious file";
        }

        // Scan for suspicious patterns
        if (self::containsSuspiciousPatterns($file, $extension)) {
            $errors[] = "File contains potentially malicious content";
        }

        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'File validation passed' : 'File validation failed',
            'errors' => $errors,
        ];
    }

    /**
     * Check if MIME type matches extension
     */
    private static function isValidMimeType(string $extension, string $mimeType): bool
    {
        if (!isset(self::MIME_TYPES[$extension])) {
            return false;
        }

        return $mimeType === self::MIME_TYPES[$extension];
    }

    /**
     * Check file content (magic bytes)
     */
    private static function isValidFileContent(UploadedFile $file, string $extension): bool
    {
        $path = $file->getRealPath();
        $fp = fopen($path, 'r');
        $magicBytes = fread($fp, 12);
        fclose($fp);

        // Image magic bytes
        if ($extension === 'jpeg' || $extension === 'jpg') {
            return strpos($magicBytes, "\xFF\xD8\xFF") === 0;
        }
        if ($extension === 'png') {
            return strpos($magicBytes, "\x89PNG\r\n\x1a\n") === 0;
        }
        if ($extension === 'gif') {
            return strpos($magicBytes, 'GIF8') === 0;
        }

        // PDF magic bytes
        if ($extension === 'pdf') {
            return strpos($magicBytes, '%PDF') === 0;
        }

        return true;
    }

    /**
     * Scan for suspicious patterns
     */
    private static function containsSuspiciousPatterns(UploadedFile $file, string $extension): bool
    {
        // Only check text-based files
        if (!in_array($extension, ['txt', 'pdf'])) {
            return false;
        }

        $content = file_get_contents($file->getRealPath(), false, null, 0, 1024);

        // Check for script tags atau executable patterns
        $suspiciousPatterns = [
            '<?php',
            '<?=',
            '<script',
            'javascript:',
            'onerror=',
            'onclick=',
            'onload=',
            'exec(',
            'system(',
            'shell_exec',
            'passthru',
            'eval(',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get safe filename (prevent directory traversal)
     */
    public static function getSafeFilename(UploadedFile $file): string
    {
        $originalName = $file->getClientOriginalName();

        // Remove path components
        $filename = basename($originalName);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Prevent double extensions
        $filename = preg_replace('/\.php\./i', '.', $filename);

        // Add timestamp to prevent collisions
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $timestamp = time();

        return "{$name}_{$timestamp}.{$extension}";
    }

    /**
     * Get safe storage path (prevent directory traversal)
     */
    public static function getSafeStoragePath(string $baseDir, int $userId): string
    {
        // Use user ID and timestamp to create isolated directory
        return "{$baseDir}/{$userId}/" . date('Y/m/d');
    }

    /**
     * Check if file should be quarantined
     */
    public static function shouldQuarantine(string $filename): bool
    {
        $suspiciousExtensions = [
            'exe',
            'bat',
            'cmd',
            'com',
            'pif',
            'scr',
            'vbs',
            'js',
            'jar',
            'zip',
            'rar',
            '7z',
        ];

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, $suspiciousExtensions);
    }

    /**
     * Scan file with ClamAV (if available)
     * 
     * @param string $filePath
     * @return bool true if clean, false if infected
     */
    public static function scanWithClamAV(string $filePath): bool
    {
        // Check if ClamAV is installed
        if (!shell_exec('which clamscan')) {
            // ClamAV not available, skip scanning
            return true;
        }

        $output = shell_exec("clamscan --quiet {$filePath}");

        // If output is empty, file is clean
        return empty($output);
    }

    /**
     * Generate file integrity hash
     */
    public static function generateFileHash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    /**
     * Validate file integrity
     */
    public static function validateFileIntegrity(string $filePath, string $expectedHash): bool
    {
        $currentHash = hash_file('sha256', $filePath);
        return $currentHash === $expectedHash;
    }

    /**
     * Get file metadata untuk audit
     */
    public static function getFileMetadata(UploadedFile $file): array
    {
        return [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'hash' => self::generateFileHash($file),
            'uploaded_at' => now(),
        ];
    }
}
