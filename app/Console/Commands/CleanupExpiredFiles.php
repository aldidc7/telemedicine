<?php

namespace App\Console\Commands;

use App\Services\FileUploadService;
use Illuminate\Console\Command;

class CleanupExpiredFiles extends Command
{
    protected $signature = 'files:cleanup {--dry-run : Hanya tampilkan file yang akan dihapus tanpa benar-benar delete}';
    protected $description = 'Cleanup file yang sudah melewati retention period (default 30 hari di trash)';

    public function __construct(private FileUploadService $uploadService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[CLEANUP] Starting cleanup of expired files...');

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('[WARNING] DRY-RUN MODE: Files will not be deleted, only displayed');
        }

        try {
            $deletedCount = $this->uploadService->cleanupExpiredFiles();

            if ($dryRun) {
                $this->line("📊 Total file yang akan dihapus: <info>{$deletedCount}</info>");
            } else {
                $this->info("✅ Cleanup selesai. {$deletedCount} file dihapus");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Cleanup gagal: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
