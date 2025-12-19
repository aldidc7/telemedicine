<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileUploadService
{
    /**
     * Upload file dengan validasi ukuran ketat
     */
    public function uploadFile(
        UploadedFile $file,
        string $category,
        User $user,
        ?string $customPath = null
    ): array {
        // 1. Validasi ekstension & MIME type
        $this->validateFileType($file, $category);

        // 2. Validasi ukuran file individual
        $maxSize = config("file-upload.max_upload_sizes.{$category}");
        if ($file->getSize() > $maxSize) {
            throw new FileUploadException(
                "File terlalu besar. Maksimal: " . $this->formatBytes($maxSize)
            );
        }

        // 3. Validasi total storage pengguna
        $this->validateUserStorageQuota($user, $file->getSize());

        // 4. Simpan file dengan nama unik
        $path = $this->storeFile($file, $category, $user, $customPath);

        // 5. Log upload
        $this->logFileUpload($user, $file, $category, $path);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Validasi tipe file berdasarkan MIME type & extension
     */
    private function validateFileType(UploadedFile $file, string $category): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Cek extension yang diblok
        $blockedExtensions = config('file-upload.blocked_extensions', []);
        if (in_array($extension, $blockedExtensions)) {
            throw new FileUploadException("Tipe file {$extension} tidak diizinkan");
        }

        // Cek extension yang diizinkan
        $allowedExtensions = config('file-upload.allowed_extensions', []);
        if (!in_array($extension, $allowedExtensions)) {
            throw new FileUploadException("Tipe file {$extension} tidak didukung");
        }

        // Validasi MIME type berdasarkan kategori
        $allowedMimes = config("file-upload.allowed_mime_types.{$category}", []);
        if (!empty($allowedMimes) && !in_array($mimeType, $allowedMimes)) {
            throw new FileUploadException("MIME type {$mimeType} tidak diizinkan untuk kategori {$category}");
        }
    }

    /**
     * Validasi storage quota pengguna
     */
    private function validateUserStorageQuota(User $user, int $fileSize): void
    {
        $userRole = $user->role ?? 'patient';
        $maxStorage = config("file-upload.max_total_storage.{$userRole}");

        if (!$maxStorage) {
            $maxStorage = config('file-upload.max_total_storage.patient');
        }

        // Hitung total size file pengguna saat ini
        $currentSize = $this->getUserStorageSize($user);

        if ($currentSize + $fileSize > $maxStorage) {
            $remaining = $maxStorage - $currentSize;
            throw new FileUploadException(
                "Kuota storage Anda penuh. Tersisa: " . $this->formatBytes($remaining)
            );
        }
    }

    /**
     * Hitung total ukuran file pengguna
     */
    private function getUserStorageSize(User $user): int
    {
        $paths = [
            storage_path('app/public/uploads/profiles/' . $user->id),
            storage_path('app/public/uploads/documents/' . $user->id),
            storage_path('app/public/uploads/medical-images/' . $user->id),
            storage_path('app/public/uploads/consultations/' . $user->id),
            storage_path('app/public/uploads/messages/' . $user->id),
        ];

        $totalSize = 0;

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $totalSize += $this->getDirectorySize($path);
            }
        }

        return $totalSize;
    }

    /**
     * Hitung ukuran direktori rekursif
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Simpan file ke storage dengan path terstruktur
     */
    private function storeFile(
        UploadedFile $file,
        string $category,
        User $user,
        ?string $customPath
    ): string {
        $storagePath = $customPath ?? config("file-upload.storage_path.{$category}");
        $filename = $this->generateUniqueFilename($file);

        // Path: uploads/category/user_id/date/filename
        $path = sprintf(
            '%s/%d/%s/%s',
            $storagePath,
            $user->id,
            now()->format('Y/m/d'),
            $filename
        );

        $file->storeAs(
            dirname($path),
            basename($path),
            'public'
        );

        return $path;
    }

    /**
     * Generate nama file unik dengan timestamp
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = Str::slug($name);
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->getTimestamp();
        $random = Str::random(8);

        return "{$name}-{$timestamp}-{$random}.{$extension}";
    }

    /**
     * Log file upload untuk audit trail
     */
    private function logFileUpload(
        User $user,
        UploadedFile $file,
        string $category,
        string $path
    ): void {
        $logData = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'filename' => $file->getClientOriginalName(),
            'category' => $category,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        \Log::info('File uploaded', $logData);
    }

    /**
     * Format bytes ke format human-readable
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Delete file dengan soft delete (pindah ke trash)
     */
    public function softDeleteFile(User $user, string $path): void
    {
        $trashPath = 'trash/' . now()->format('Y/m/d') . '/' . basename($path);
        Storage::disk('public')->move($path, $trashPath);

        \Log::info('File soft deleted', [
            'user_id' => $user->id,
            'original_path' => $path,
            'trash_path' => $trashPath,
        ]);
    }

    /**
     * Permanently delete file (hanya setelah retention period)
     */
    public function permanentlyDeleteFile(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    /**
     * Cleanup file yang sudah expired
     */
    public function cleanupExpiredFiles(): int
    {
        $deletedCount = 0;
        $trashPath = 'trash';
        $files = Storage::disk('public')->files($trashPath);

        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            $fileAge = now()->timestamp - $lastModified;
            $retentionDays = config('file-upload.retention_policies.deleted_files', 30);
            $retentionSeconds = $retentionDays * 24 * 60 * 60;

            if ($fileAge > $retentionSeconds) {
                if ($this->permanentlyDeleteFile($file)) {
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Get storage usage info untuk user
     */
    public function getUserStorageInfo(User $user): array
    {
        $userRole = $user->role ?? 'patient';
        $maxStorage = config("file-upload.max_total_storage.{$userRole}");
        $currentSize = $this->getUserStorageSize($user);
        $remainingSize = $maxStorage - $currentSize;
        $usagePercent = round(($currentSize / $maxStorage) * 100, 2);

        return [
            'max_storage' => $maxStorage,
            'current_usage' => $currentSize,
            'remaining_storage' => $remainingSize,
            'usage_percent' => $usagePercent,
            'max_storage_formatted' => $this->formatBytes($maxStorage),
            'current_usage_formatted' => $this->formatBytes($currentSize),
            'remaining_storage_formatted' => $this->formatBytes($remainingSize),
        ];
    }
}
