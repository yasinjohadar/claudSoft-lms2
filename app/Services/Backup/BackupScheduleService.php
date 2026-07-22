<?php

namespace App\Services\Backup;

use App\Models\BackupSchedule;
use App\Models\Backup;
use App\Jobs\CreateBackupJob;
use App\Services\Backup\BackupService;
use Carbon\Carbon;

class BackupScheduleService
{
    public function __construct(
        private BackupService $backupService
    ) {}

    /**
     * إنشاء جدولة
     */
    public function createSchedule(array $data): BackupSchedule
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $schedule = BackupSchedule::create($data);
        $schedule->update(['next_run_at' => $schedule->calculateNextRun()]);

        return $schedule->fresh();
    }

    /**
     * تحديث جدولة
     */
    public function updateSchedule(BackupSchedule $schedule, array $data): BackupSchedule
    {
        $schedule->update($data);
        $schedule->update(['next_run_at' => $schedule->calculateNextRun()]);

        return $schedule->fresh();
    }

    /**
     * حذف جدولة
     */
    public function deleteSchedule(BackupSchedule $schedule): bool
    {
        return $schedule->delete();
    }

    /**
     * تنفيذ جدولة
     */
    public function executeSchedule(BackupSchedule $schedule): Backup
    {
        $storageDrivers = $schedule->storage_drivers ?? ['local'];
        $compressionTypes = $schedule->compression_types ?? ['zip'];
        $backups = collect();

        foreach ($storageDrivers as $driver) {
            $storageConfig = \App\Models\AppStorageConfig::where('driver', $driver)
                ->where('is_active', true)
                ->orderByDesc('priority')
                ->first();

            if (!$storageConfig) {
                \Log::warning("Storage config not found for driver: {$driver} in schedule {$schedule->id}");
                continue;
            }

            foreach ($compressionTypes as $compression) {
                $backup = Backup::create([
                    'name' => $schedule->name . '_' . now()->format('Y-m-d_H-i-s'),
                    'type' => 'scheduled',
                    'backup_type' => $schedule->backup_type,
                    'storage_driver' => $driver,
                    'storage_config_id' => $storageConfig->id,
                    'storage_path' => null,
                    'file_path' => null,
                    'compression_type' => $compression,
                    'status' => 'pending',
                    'retention_days' => $schedule->retention_days,
                    'created_by' => null,
                    'schedule_id' => $schedule->id,
                ]);

                $backup->update([
                    'expires_at' => $backup->calculateExpiresAt(),
                ]);

                $jobOptions = [
                    'name' => $backup->name,
                    'type' => 'scheduled',
                    'backup_type' => $schedule->backup_type,
                    'storage_config_id' => $storageConfig->id,
                    'storage_driver' => $driver,
                    'compression_type' => $compression,
                    'retention_days' => $schedule->retention_days,
                    'schedule_id' => $schedule->id,
                ];

                $useSync = config('backup.schedule_dispatch_sync', false)
                    && $schedule->backup_type !== 'database'
                    && $schedule->backup_type !== 'full';

                if ($useSync) {
                    CreateBackupJob::dispatchSync($backup, $jobOptions);
                } else {
                    CreateBackupJob::dispatch($backup, $jobOptions);
                }

                $backups->push($backup);
            }
        }

        if ($backups->isEmpty()) {
            throw new \RuntimeException(
                'تعذر إنشاء نسخة مجدولة: لا يوجد مكان تخزين نشط يطابق السائقين المحددين ('
                . implode(', ', $storageDrivers) . ')'
            );
        }

        $schedule->update([
            'last_run_at' => now(),
            'next_run_at' => $schedule->calculateNextRun(),
        ]);

        return $backups->first();
    }

    /**
     * تشغيل النسخ المجدولة
     */
    public function runScheduledBackups(): int
    {
        $schedules = BackupSchedule::where('is_active', true)
                                  ->where('next_run_at', '<=', now())
                                  ->get();

        $count = 0;
        foreach ($schedules as $schedule) {
            if ($schedule->shouldRun()) {
                try {
                    $this->executeSchedule($schedule);
                    $count++;
                } catch (\Exception $e) {
                    \Log::error('Error executing backup schedule: ' . $e->getMessage(), [
                        'schedule_id' => $schedule->id,
                    ]);
                }
            }
        }

        return $count;
    }

    /**
     * حساب وقت التشغيل التالي
     */
    public function calculateNextRun(BackupSchedule $schedule): Carbon
    {
        return $schedule->calculateNextRun();
    }

    /**
     * التحقق من وجوب التشغيل
     */
    public function shouldRun(BackupSchedule $schedule): bool
    {
        return $schedule->shouldRun();
    }
}

