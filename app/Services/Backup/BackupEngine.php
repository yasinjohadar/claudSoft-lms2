<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Models\BackupLog;
use App\Services\Backup\Sources\DatabaseBackupSource;
use App\Services\Backup\Sources\PhpDatabaseBackupSource;
use App\Services\Backup\DTO\BackupArtifact;
use Illuminate\Support\Facades\Log;

class BackupEngine
{
    public function __construct(
        private StorageManager $storageManager,
        private BackupNotificationService $notificationService,
        private DatabaseBackupSource $databaseSource,
        private PhpDatabaseBackupSource $phpDatabaseSource,
    ) {}

    /**
     * Run a full database backup: dump → upload → verify → complete.
     */
    public function runDatabaseBackup(Backup $backup, array $options = []): Backup
    {
        $backup->load('storageConfig');
        $tmpDir = storage_path('app/backups/tmp/' . $backup->id);
        $artifact = null;

        try {
            $this->setStage($backup, 'preparing', 5);
            $this->log($backup, 'info', 'بدء محرك النسخ الاحتياطي لقاعدة البيانات');

            $source = $this->resolveDatabaseSource();
            $sourceName = $source instanceof DatabaseBackupSource ? 'mysqldump' : 'php_fallback';
            $this->log($backup, 'info', "مصدر النسخ: {$sourceName}");

            $artifact = $source->produce(
                $backup,
                function (string $stage, int $progress, ?int $bytesProcessed, ?int $bytesTotal) use ($backup) {
                    $this->setStage($backup, $stage, $progress, $bytesProcessed, $bytesTotal);
                },
                ['compress' => true]
            );

            $backup->update([
                'file_path' => $artifact->path,
                'file_size' => $artifact->size,
                'compression_type' => 'gzip',
                'metadata' => array_merge($backup->metadata ?? [], [
                    'artifact' => $artifact->metadata,
                    'extension' => $artifact->extension,
                ]),
            ]);

            $this->setStage($backup, 'uploading', 80, 0, $artifact->size);
            $this->log($backup, 'info', 'بدء رفع الملف إلى التخزين...');
            $this->storageManager->storeWithFailover($backup, $artifact->path);
            $backup->refresh();
            $backup->load('storageConfig');

            $redundancyConfigs = \App\Models\AppStorageConfig::where('is_active', true)
                ->where('redundancy', true)
                ->get();
            if ($backup->storageConfig) {
                $redundancyConfigs = $redundancyConfigs->filter(
                    fn ($config) => $config->id !== $backup->storageConfig->id
                );
            }

            $redundancyResult = ['successful' => [], 'failed' => []];
            if ($redundancyConfigs->isNotEmpty()) {
                $this->log($backup, 'info', 'بدء التخزين في أماكن Redundancy...');
                $redundancyResult = $this->storageManager->storeToMultipleStorages($backup, $artifact->path);
            }

            $this->setStage($backup, 'verifying', 95, $artifact->size, $artifact->size);
            if (!$backup->storage_path) {
                throw new \RuntimeException('storage_path missing after upload');
            }

            $metadata = $backup->metadata ?? [];
            if (!empty($redundancyResult['successful'])) {
                $metadata['redundancy_storages'] = $redundancyResult['successful'];
            }
            if (!empty($redundancyResult['failed'])) {
                $metadata['redundancy_failed'] = $redundancyResult['failed'];
            }

            $duration = $backup->started_at
                ? (int) abs($backup->started_at->diffInSeconds(now()))
                : 0;

            $backup->update([
                'status' => 'completed',
                'stage' => 'completed',
                'progress' => 100,
                'bytes_processed' => $artifact->size,
                'bytes_total' => $artifact->size,
                'completed_at' => now(),
                'duration' => $duration,
                'file_size' => $artifact->size,
                'metadata' => $metadata,
            ]);

            $this->log($backup, 'info', 'اكتملت نسخة قاعدة البيانات بنجاح');

            $this->cleanupLocalArtifact($backup, $artifact);

            try {
                $this->notificationService->notifyBackupCompleted($backup);
            } catch (\Exception $e) {
                Log::warning('Backup completion notification failed: ' . $e->getMessage());
            }

            return $backup->fresh();
        } catch (\Throwable $e) {
            Log::error('BackupEngine database backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            $backup->update([
                'status' => 'failed',
                'stage' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
            $this->log($backup, 'error', 'فشلت عملية النسخ: ' . $e->getMessage());

            if ($artifact && is_file($artifact->path)) {
                @unlink($artifact->path);
            }
            $this->cleanupTmpDir($tmpDir);

            try {
                $this->notificationService->notifyBackupFailed($backup, $e->getMessage());
            } catch (\Exception $notificationException) {
                Log::error('Failed to send backup failure notification: ' . $notificationException->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Produce a local DB dump artifact (for full backups). Prefer mysqldump.
     *
     * @param  callable(string, int, ?int, ?int): void|null  $onProgress
     */
    public function produceDatabaseArtifact(Backup $backup, array $options = [], ?callable $onProgress = null): BackupArtifact
    {
        $onProgress ??= function () {};
        $source = $this->resolveDatabaseSource();

        return $source->produce($backup, $onProgress, $options);
    }

    public function resolveDatabaseSource(): DatabaseBackupSource|PhpDatabaseBackupSource
    {
        if ($this->databaseSource->isAvailable()) {
            return $this->databaseSource;
        }

        $configured = config('backup.mysqldump_path');
        Log::warning('mysqldump unavailable; evaluating PHP fallback', [
            'configured_path' => $configured,
            'configured_exists' => is_string($configured) && $configured !== '' && is_file($configured),
        ]);

        if ($this->phpDatabaseSource->isAvailable()) {
            return $this->phpDatabaseSource;
        }

        throw new \RuntimeException(
            'mysqldump غير متاح. عيّن BACKUP_MYSQLDUMP_PATH في .env ثم أعد تشغيل queue:work. '
            . '(المسار الحالي: ' . (is_string($configured) && $configured !== '' ? $configured : 'غير مضبوط') . ')'
        );
    }

    protected function setStage(
        Backup $backup,
        string $stage,
        int $progress,
        ?int $bytesProcessed = null,
        ?int $bytesTotal = null
    ): void {
        $previousStage = $backup->stage;
        $previousProgress = (int) ($backup->progress ?? 0);
        $progress = max(0, min(100, $progress));

        $data = [
            'stage' => $stage,
            'progress' => $progress,
        ];
        if ($bytesProcessed !== null) {
            $data['bytes_processed'] = $bytesProcessed;
        }
        if ($bytesTotal !== null) {
            $data['bytes_total'] = $bytesTotal;
        }
        $backup->update($data);

        $stageLabels = [
            'preparing' => 'تجهيز النسخة',
            'dumping' => 'تصدير قاعدة البيانات',
            'compressing' => 'ضغط الملف',
            'uploading' => 'رفع إلى التخزين',
            'verifying' => 'التحقق من الرفع',
            'completed' => 'اكتمل',
            'failed' => 'فشل',
        ];

        if ($previousStage !== $stage) {
            $label = $stageLabels[$stage] ?? $stage;
            $this->log($backup, 'info', "المرحلة: {$label} ({$progress}%)");
        } elseif ($progress >= $previousProgress + 10) {
            $label = $stageLabels[$stage] ?? $stage;
            $extra = '';
            if ($bytesProcessed !== null) {
                $extra = ' — ' . number_format($bytesProcessed) . ' بايت';
            }
            $this->log($backup, 'info', "{$label}: {$progress}%{$extra}");
        }
    }

    protected function log(Backup $backup, string $level, string $message, array $context = []): void
    {
        BackupLog::create([
            'backup_id' => $backup->id,
            'level' => $level,
            'message' => $message,
            'context' => $context ?: null,
        ]);
    }

    protected function cleanupLocalArtifact(Backup $backup, BackupArtifact $artifact): void
    {
        $storageDriver = $backup->storage_driver;
        if ($storageDriver === 'local' || empty($storageDriver)) {
            $this->log($backup, 'info', 'التخزين محلي، تم الاحتفاظ بالملف');

            return;
        }

        if (is_file($artifact->path)) {
            @unlink($artifact->path);
        }
        $this->cleanupTmpDir(storage_path('app/backups/tmp/' . $backup->id));
        $this->log($backup, 'info', 'تم حذف الملف المحلي المؤقت بعد الرفع');
    }

    protected function cleanupTmpDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }
}
