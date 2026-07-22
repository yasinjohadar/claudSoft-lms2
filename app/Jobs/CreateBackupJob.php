<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * عدد المحاولات
     */
    public $tries = 1;

    /**
     * Timeout بالثواني — يجب مواءمة queue.retry_after و worker --timeout (انظر config/backup.php).
     */
    public $timeout = 3600;

    /**
     * اعتبار المهمة فاشلة عند انتهاء المهلة
     */
    public $failOnTimeout = true;

    public function __construct(
        public Backup $backup,
        public array $options = []
    ) {
        $this->timeout = (int) config('backup.job_timeout', 3600);
    }

    /**
     * Execute the job.
     */
    public function handle(BackupService $backupService): void
    {
        try {
            $this->backup->refresh();

            $backupService->createBackup(array_merge($this->options, [
                'backup_id' => $this->backup->id,
            ]));
        } catch (\Exception $e) {
            Log::error('Error creating backup in job: ' . $e->getMessage(), [
                'backup_id' => $this->backup->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->backup->refresh();
            $this->backup->update([
                'status' => 'failed',
                'stage' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            try {
                $notificationService = app(\App\Services\Backup\BackupNotificationService::class);
                $notificationService->notifyBackupFailed($this->backup, $e->getMessage());
            } catch (\Exception $notificationException) {
                Log::error('Error sending backup failure notification: ' . $notificationException->getMessage());
            }
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $this->backup->refresh();
        if (in_array($this->backup->status, ['pending', 'running'], true)) {
            $message = $exception?->getMessage() ?? 'Job failed or timed out';
            $this->backup->update([
                'status' => 'failed',
                'stage' => 'failed',
                'completed_at' => now(),
                'error_message' => $message,
            ]);
        }
    }
}
