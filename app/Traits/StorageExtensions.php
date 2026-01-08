<?php

namespace App\Traits;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Trait untuk extend Storage Disk methods
 * Menambahkan methods yang tidak ada di semua disk
 */
trait StorageExtensions
{
    /**
     * Get temporary URL (untuk S3-compatible storage)
     */
    public function temporaryUrl(string $path, \DateTimeInterface $expiration): string
    {
        // Untuk local disk, return regular URL
        if ($this->getDriver() === 'local') {
            return url('storage/' . $path);
        }

        // Untuk S3 atau storage lain dengan temp URL support
        if (method_exists($this->disk, 'temporaryUrl')) {
            return $this->disk->temporaryUrl($path, $expiration);
        }

        // Fallback ke regular URL
        return $this->url($path);
    }

    /**
     * Download file
     */
    public function download(string $path, ?string $name = null)
    {
        if (!$this->exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        $name = $name ?? basename($path);
        $contents = $this->get($path);

        return response()->streamDownload(
            function() use ($contents) { return $contents; },
            $name,
            [
                'Content-Type' => $this->mimeType($path) ?? 'application/octet-stream',
                'Content-Length' => strlen($contents),
            ]
        );
    }

    /**
     * Get driver name
     */
    protected function getDriver(): string
    {
        return config('filesystems.default', 'local');
    }
}
