<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Models\BackupLog;
use App\Services\Backup\BackupStorageService;
use App\Services\Backup\BackupCompressionService;
use App\Services\Backup\BackupNotificationService;
use App\Services\Backup\StorageManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupService
{
    public function __construct(
        private BackupStorageService $storageService,
        private BackupCompressionService $compressionService,
        private BackupNotificationService $notificationService,
        private StorageManager $storageManager
    ) {}

    /**
     * إنشاء نسخة احتياطية
     */
    public function createBackup(array $options): Backup
    {
        // إذا تم تمرير backup_id، استخدم النسخة الموجودة
        $backup = null;
        if (isset($options['backup_id'])) {
            $backup = Backup::find($options['backup_id']);
            if (!$backup) {
                throw new \Exception('النسخة الاحتياطية غير موجودة');
            }
        }
        
        // الحصول على إعدادات التخزين من AppStorageConfig إذا تم تمرير storage_config_id
        $storageDriver = $options['storage_driver'] ?? 'local';
        $storageConfigId = $options['storage_config_id'] ?? null;
        
        if ($storageConfigId) {
            $storageConfig = \App\Models\AppStorageConfig::find($storageConfigId);
            if ($storageConfig) {
                $storageDriver = $storageConfig->driver;
            }
        }
        
        // إنشاء نسخة جديدة إذا لم يتم تمرير backup_id
        if (!$backup) {
            $backup = Backup::create([
                'name' => $options['name'] ?? 'backup_' . now()->format('Y-m-d_H-i-s'),
                'type' => $options['type'] ?? 'manual',
                'backup_type' => $options['backup_type'] ?? 'full',
                'storage_driver' => $storageDriver,
                'storage_config_id' => $storageConfigId, // إضافة ربط مع AppStorageConfig
                'storage_path' => null, // سيتم تعيينه بعد الرفع
                'file_path' => null, // سيتم تعيينه بعد الإنشاء
                'compression_type' => $options['compression_type'] ?? 'zip',
                'status' => 'pending',
                'retention_days' => $options['retention_days'] ?? 30,
                'created_by' => $options['created_by'] ?? auth()->id(),
                'schedule_id' => $options['schedule_id'] ?? null,
            ]);
        }

        // تحديث حالة النسخة إلى running
        $backup->update([
            'expires_at' => $backup->calculateExpiresAt(),
            'started_at' => now(),
            'status' => 'running',
        ]);

        // تحميل storageConfig relationship
        $backup->load('storageConfig');

        try {
            $this->log($backup, 'info', 'بدء عملية النسخ الاحتياطي');

            $filePath = match($backup->backup_type) {
                'full' => $this->createFullBackup($backup, $options),
                'database' => $this->createDatabaseBackup($backup, $options),
                'files' => $this->createFilesBackup($backup, $options),
                'config' => $this->createConfigBackup($backup, $options),
                default => throw new \Exception('نوع النسخ غير معروف: ' . $backup->backup_type),
            };

            $this->log($backup, 'info', 'تم إنشاء ملف النسخة بنجاح: ' . $filePath);

            // تحديث file_path قبل الضغط
            $backup->update(['file_path' => $filePath]);

            // ضغط الملف
            $this->log($backup, 'info', 'بدء ضغط ملف النسخة...');
            $compressedPath = $this->compressionService->compress($backup, $backup->compression_type);
            $this->log($backup, 'info', 'تم ضغط ملف النسخة بنجاح: ' . $compressedPath);

            // رفع الملف إلى التخزين مع Auto-failover
            $this->log($backup, 'info', 'بدء رفع ملف النسخة إلى التخزين...');
            $this->storageManager->storeWithFailover($backup, $compressedPath);
            $this->log($backup, 'info', 'تم رفع ملف النسخة إلى التخزين بنجاح');
            
            // تخزين في أماكن متعددة إذا كان مفعلاً
            // التحقق من وجود AppStorageConfig مع redundancy = true قبل الاستدعاء
            $redundancyConfigs = \App\Models\AppStorageConfig::where('is_active', true)
                ->where('redundancy', true)
                ->get();
            
            // تخطي المكان الأساسي من قائمة Redundancy
            if ($backup->storageConfig) {
                $redundancyConfigs = $redundancyConfigs->filter(function ($config) use ($backup) {
                    return $config->id !== $backup->storageConfig->id;
                });
            }
            
            $redundancyResult = [
                'successful' => [],
                'failed' => [],
            ];
            
            // استدعاء storeToMultipleStorages فقط إذا كان هناك configs مع redundancy = true
            if ($redundancyConfigs->isNotEmpty()) {
                $this->log($backup, 'info', 'بدء التخزين في أماكن Redundancy...');
                $redundancyResult = $this->storageManager->storeToMultipleStorages($backup, $compressedPath);
                $this->log($backup, 'info', 'اكتمل التخزين في أماكن Redundancy');
            } else {
                $this->log($backup, 'info', 'لا توجد أماكن تخزين مع تفعيل Redundancy، تم تخطي التخزين المتعدد');
            }
            
            $storagePath = $backup->storage_path;

            $duration = now()->diffInSeconds($backup->started_at);
            
            // الحصول على حجم الملف - استخدام filesize() لأن compressedPath هو مسار كامل
            if (!file_exists($compressedPath)) {
                throw new \Exception('ملف النسخة الاحتياطية غير موجود: ' . $compressedPath);
            }
            
            $fileSize = filesize($compressedPath);
            if ($fileSize === false) {
                throw new \Exception('فشل في الحصول على حجم ملف النسخة الاحتياطية: ' . $compressedPath);
            }

            // إعداد metadata مع معلومات Redundancy
            $metadata = $backup->metadata ?? [];
            if (!empty($redundancyResult['successful'])) {
                $metadata['redundancy_storages'] = $redundancyResult['successful'];
            }
            if (!empty($redundancyResult['failed'])) {
                $metadata['redundancy_failed'] = $redundancyResult['failed'];
            }

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'duration' => $duration,
                'file_path' => $compressedPath,
                'storage_path' => $storagePath,
                'file_size' => $fileSize,
                'metadata' => $metadata,
            ]);

            // حذف الملف المحلي المؤقت بعد الرفع الناجح إلى التخزين السحابي
            // يتم الحذف فقط إذا كان التخزين الأساسي ليس محلياً
            if ($backup->storageConfig && $backup->storageConfig->driver !== 'local') {
                if ($compressedPath && file_exists($compressedPath)) {
                    try {
                        @unlink($compressedPath);
                        $this->log($backup, 'info', 'تم حذف الملف المحلي المؤقت بعد الرفع الناجح');
                        Log::info("Deleted local temporary file after successful upload: {$compressedPath}");
                    } catch (\Exception $e) {
                        $this->log($backup, 'warning', 'فشل حذف الملف المحلي المؤقت: ' . $e->getMessage());
                        Log::warning("Failed to delete local temporary file: {$compressedPath} - {$e->getMessage()}");
                        // لا نعتبر هذا خطأ فادحاً، الملف موجود في التخزين السحابي
                    }
                }
            } else {
                // إذا كان التخزين محلياً، نحتفظ بالملف
                $this->log($backup, 'info', 'التخزين محلي، تم الاحتفاظ بالملف المحلي');
            }

            $this->log($backup, 'info', 'اكتملت عملية النسخ الاحتياطي بنجاح');
            $this->notificationService->notifyBackupCompleted($backup);

            return $backup->fresh();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorDetails = [
                'message' => $errorMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];

            Log::error('Backup creation failed', [
                'backup_id' => $backup->id,
                'backup_name' => $backup->name,
                'backup_type' => $backup->backup_type,
                'error' => $errorDetails,
            ]);

            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $errorMessage,
            ]);

            $this->log($backup, 'error', 'فشلت عملية النسخ الاحتياطي: ' . $errorMessage);
            $this->log($backup, 'error', 'تفاصيل الخطأ: ' . json_encode($errorDetails, JSON_UNESCAPED_UNICODE));
            
            try {
                $this->notificationService->notifyBackupFailed($backup, $errorMessage);
            } catch (\Exception $notificationException) {
                Log::error('Failed to send backup failure notification', [
                    'backup_id' => $backup->id,
                    'error' => $notificationException->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * إنشاء نسخة كاملة
     */
    public function createFullBackup(Backup $backup, array $options): string
    {
        $this->log($backup, 'info', 'بدء نسخ قاعدة البيانات');
        $dbPath = $this->createDatabaseBackup($backup, $options);

        $this->log($backup, 'info', 'بدء نسخ الملفات');
        $filesPath = $this->createFilesBackup($backup, $options);

        $this->log($backup, 'info', 'بدء نسخ الإعدادات');
        $configPath = $this->createConfigBackup($backup, $options);

        // دمج جميع الملفات في مجلد واحد
        $backupDir = storage_path('app/backups/temp/' . $backup->id);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        copy($dbPath, $backupDir . '/database.sql');
        $this->extractToDirectory($filesPath, $backupDir . '/files');
        $this->extractToDirectory($configPath, $backupDir . '/config');

        return $backupDir;
    }

    /**
     * إنشاء نسخة قاعدة البيانات
     */
    public function createDatabaseBackup(Backup $backup, array $options): string
    {
        $filename = 'database_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $backupDir = storage_path('app/backups');
        $path = $backupDir . '/' . $filename;

        // التأكد من وجود المجلد
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        // استخدام Laravel DB facade بدلاً من mysqldump
        try {
            $tables = DB::select('SHOW TABLES');
            $databaseName = $database;
            $tablesKey = 'Tables_in_' . $databaseName;
            
            $sqlContent = "-- Database Backup\n";
            $sqlContent .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $sqlContent .= "-- Database: {$databaseName}\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tablesKey;
                
                // الحصول على CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlContent .= $createTable[0]->{'Create Table'} . ";\n\n";

                // الحصول على البيانات
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sqlContent .= "LOCK TABLES `{$tableName}` WRITE;\n";
                    
                    // الحصول على أسماء الأعمدة من أول صف
                    $firstRow = (array) $rows->first();
                    $columns = array_map(function ($col) {
                        return "`{$col}`";
                    }, array_keys($firstRow));
                    $columnsStr = implode(", ", $columns);
                    
                    $values = [];
                    $chunkSize = 100;
                    $currentChunk = 0;
                    
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        
                        $valArray = array_map(function ($val) {
                            if ($val === null) {
                                return 'NULL';
                            }
                            return DB::getPdo()->quote($val);
                        }, array_values($rowArray));
                        
                        $values[] = "(" . implode(", ", $valArray) . ")";
                        $currentChunk++;
                        
                        // كتابة كل 100 صف
                        if ($currentChunk >= $chunkSize) {
                            $valuesStr = implode(",\n", $values);
                            $sqlContent .= "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n{$valuesStr};\n\n";
                            $values = [];
                            $currentChunk = 0;
                        }
                    }
                    
                    // كتابة الصفوف المتبقية
                    if (!empty($values)) {
                        $valuesStr = implode(",\n", $values);
                        $sqlContent .= "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n{$valuesStr};\n\n";
                    }
                    
                    $sqlContent .= "UNLOCK TABLES;\n\n";
                }
            }

            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            file_put_contents($path, $sqlContent);

            if (!file_exists($path) || filesize($path) === 0) {
                throw new \Exception('فشل في إنشاء ملف النسخة الاحتياطية - الملف فارغ أو غير موجود');
            }

            $this->log($backup, 'info', 'تم نسخ قاعدة البيانات بنجاح');

            return $path;
        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('فشل في نسخ قاعدة البيانات: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء نسخة الملفات
     */
    public function createFilesBackup(Backup $backup, array $options): string
    {
        $filesDir = storage_path('app/public');
        $backupDir = storage_path('app/backups/temp/files_' . $backup->id);

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $this->copyDirectory($filesDir, $backupDir);

        $this->log($backup, 'info', 'تم نسخ الملفات بنجاح');

        return $backupDir;
    }

    /**
     * إنشاء نسخة الإعدادات
     */
    public function createConfigBackup(Backup $backup, array $options): string
    {
        $configFiles = [
            '.env',
            'config/app.php',
            'config/database.php',
            'config/mail.php',
        ];

        $backupDir = storage_path('app/backups/temp/config_' . $backup->id);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        foreach ($configFiles as $file) {
            $sourcePath = base_path($file);
            if (file_exists($sourcePath)) {
                $destPath = $backupDir . '/' . basename($file);
                copy($sourcePath, $destPath);
            }
        }

        $this->log($backup, 'info', 'تم نسخ الإعدادات بنجاح');

        return $backupDir;
    }

    /**
     * حذف نسخة
     */
    public function deleteBackup(Backup $backup): bool
    {
        try {
            // حذف الملف من التخزين الأساسي
            $this->log($backup, 'info', 'بدء حذف النسخة من التخزين الأساسي...');
            $this->storageManager->delete($backup);
            $this->log($backup, 'info', 'تم حذف النسخة من التخزين الأساسي');

            // حذف الملف من أماكن Redundancy
            $metadata = $backup->metadata ?? [];
            if (!empty($metadata['redundancy_storages']) && is_array($metadata['redundancy_storages'])) {
                $this->log($backup, 'info', 'بدء حذف النسخة من أماكن Redundancy...');
                
                foreach ($metadata['redundancy_storages'] as $redundancyStorage) {
                    try {
                        // الحصول على storage config
                        $storageConfigId = $redundancyStorage['storage_config_id'] ?? null;
                        $storagePath = $redundancyStorage['storage_path'] ?? null;
                        
                        if ($storageConfigId && $storagePath) {
                            $storageConfig = \App\Models\AppStorageConfig::find($storageConfigId);
                            
                            if ($storageConfig && $storageConfig->is_active) {
                                $driver = \App\Services\Backup\StorageFactory::create($storageConfig);
                                
                                if ($driver->delete($storagePath)) {
                                    $this->log($backup, 'info', "تم حذف النسخة من: {$storageConfig->name}");
                                } else {
                                    $this->log($backup, 'warning', "فشل حذف النسخة من: {$storageConfig->name}");
                                }
                            } else {
                                $this->log($backup, 'warning', "تخزين Redundancy غير موجود أو غير نشط: {$storageConfigId}");
                            }
                        }
                    } catch (\Exception $e) {
                        $storageName = $redundancyStorage['storage_config_name'] ?? 'Unknown';
                        $this->log($backup, 'error', "خطأ في حذف النسخة من {$storageName}: " . $e->getMessage());
                        Log::warning("Failed to delete backup from redundancy storage: {$storageName}", [
                            'backup_id' => $backup->id,
                            'storage_config_id' => $redundancyStorage['storage_config_id'] ?? null,
                            'error' => $e->getMessage(),
                        ]);
                        // نستمر في حذف باقي الأماكن حتى لو فشل أحدها
                    }
                }
                
                $this->log($backup, 'info', 'اكتمل حذف النسخة من أماكن Redundancy');
            }

            // حذف الملف المحلي - file_path هو مسار كامل (absolute path)
            if ($backup->file_path && file_exists($backup->file_path)) {
                @unlink($backup->file_path);
            }

            $backup->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('فشل في حذف النسخة: ' . $e->getMessage());
        }
    }

    /**
     * تحميل نسخة
     */
    public function downloadBackup(Backup $backup): BinaryFileResponse
    {
        $fileContent = $this->storageManager->retrieve($backup);
        $tempFilePath = storage_path('app/temp/download_' . $backup->id . '_' . time() . '.' . $backup->compression_type);
        
        if (!is_dir(dirname($tempFilePath))) {
            mkdir(dirname($tempFilePath), 0755, true);
        }
        
        file_put_contents($tempFilePath, $fileContent);
        $filePath = $tempFilePath;

        if (!file_exists($filePath)) {
            throw new \Exception('الملف غير موجود');
        }

        return response()->download($filePath, $backup->name . '.' . $backup->compression_type);
    }

    /**
     * استعادة نسخة
     */
    public function restoreBackup(Backup $backup, array $options = []): bool
    {
        $preRestoreBackupPath = null;
        $preRestoreFilesPath = null;
        $preRestoreConfigPath = null;

        try {
            $this->log($backup, 'info', 'بدء عملية الاستعادة');

            // إنشاء backup تلقائي قبل الاستعادة
            $this->log($backup, 'info', 'إنشاء نسخة احتياطية من البيانات الحالية قبل الاستعادة...');
            $preRestoreBackup = $this->createPreRestoreBackup($backup->backup_type);
            if ($preRestoreBackup) {
                $this->log($backup, 'info', 'تم إنشاء نسخة احتياطية من البيانات الحالية بنجاح');
                $preRestoreBackupPath = $preRestoreBackup['database'] ?? null;
                $preRestoreFilesPath = $preRestoreBackup['files'] ?? null;
                $preRestoreConfigPath = $preRestoreBackup['config'] ?? null;
            }

            $fileContent = $this->storageManager->retrieve($backup);
            $tempFilePath = storage_path('app/temp/restore_' . $backup->id . '_' . time() . '.zip');
            
            if (!is_dir(dirname($tempFilePath))) {
                mkdir(dirname($tempFilePath), 0755, true);
            }
            
            file_put_contents($tempFilePath, $fileContent);
            $filePath = $tempFilePath;

            // فك الضغط
            $extractedPath = $this->compressionService->decompress($filePath, storage_path('app/backups/restore_' . $backup->id));

            // استعادة حسب النوع
            match($backup->backup_type) {
                'database' => $this->restoreDatabase($extractedPath),
                'files' => $this->restoreFiles($extractedPath),
                'config' => $this->restoreConfig($extractedPath),
                'full' => $this->restoreFull($extractedPath),
                default => throw new \Exception('نوع النسخ غير معروف'),
            };

            // تنظيف الملفات المؤقتة
            $this->cleanupRestoreTempFiles($tempFilePath, $extractedPath);

            $this->log($backup, 'info', 'اكتملت عملية الاستعادة بنجاح');

            return true;
        } catch (\Exception $e) {
            $this->log($backup, 'error', 'فشلت عملية الاستعادة: ' . $e->getMessage());
            
            // محاولة استعادة من backup التلقائي في حالة الفشل
            if ($preRestoreBackupPath || $preRestoreFilesPath || $preRestoreConfigPath) {
                $this->log($backup, 'warning', 'محاولة استعادة البيانات من النسخة الاحتياطية التلقائية...');
                try {
                    if ($preRestoreBackupPath && file_exists($preRestoreBackupPath)) {
                        $this->restoreDatabase($preRestoreBackupPath);
                        $this->log($backup, 'info', 'تم استعادة قاعدة البيانات من النسخة الاحتياطية التلقائية');
                    }
                    if ($preRestoreFilesPath && is_dir($preRestoreFilesPath)) {
                        $this->restoreFiles($preRestoreFilesPath);
                        $this->log($backup, 'info', 'تم استعادة الملفات من النسخة الاحتياطية التلقائية');
                    }
                    if ($preRestoreConfigPath && is_dir($preRestoreConfigPath)) {
                        $this->restoreConfig($preRestoreConfigPath);
                        $this->log($backup, 'info', 'تم استعادة الإعدادات من النسخة الاحتياطية التلقائية');
                    }
                } catch (\Exception $rollbackException) {
                    $this->log($backup, 'error', 'فشل استعادة البيانات من النسخة الاحتياطية التلقائية: ' . $rollbackException->getMessage());
                    Log::error('Rollback failed: ' . $rollbackException->getMessage());
                }
            }
            
            throw $e;
        }
    }

    /**
     * إنشاء backup تلقائي قبل الاستعادة
     */
    private function createPreRestoreBackup(string $backupType): ?array
    {
        $backups = [];
        $timestamp = date('Y-m-d_H-i-s');

        try {
            // Backup قاعدة البيانات
            if (in_array($backupType, ['database', 'full'])) {
                $dbBackupPath = storage_path('app/temp/pre_restore_db_' . $timestamp . '.sql');
                $this->createQuickDatabaseBackup($dbBackupPath);
                $backups['database'] = $dbBackupPath;
            }

            // Backup الملفات
            if (in_array($backupType, ['files', 'full'])) {
                $filesBackupPath = storage_path('app/temp/pre_restore_files_' . $timestamp);
                $this->createQuickFilesBackup($filesBackupPath);
                $backups['files'] = $filesBackupPath;
            }

            // Backup الإعدادات
            if (in_array($backupType, ['config', 'full'])) {
                $configBackupPath = storage_path('app/temp/pre_restore_config_' . $timestamp);
                $this->createQuickConfigBackup($configBackupPath);
                $backups['config'] = $configBackupPath;
            }

            return $backups;
        } catch (\Exception $e) {
            Log::warning('Failed to create pre-restore backup: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إنشاء backup سريع لقاعدة البيانات
     */
    private function createQuickDatabaseBackup(string $filePath): void
    {
        $connection = config('database.connections.mysql');
        $database = $connection['database'];
        $username = $connection['username'];
        $password = $connection['password'];
        $host = $connection['host'];
        $port = $connection['port'] ?? 3306;

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $mysqldumpCommand = $isWindows ? 'mysqldump.exe' : 'mysqldump';

        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($mysqldumpCommand),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('فشل في إنشاء backup لقاعدة البيانات: ' . implode("\n", $output));
        }
    }

    /**
     * إنشاء backup سريع للملفات
     */
    private function createQuickFilesBackup(string $destPath): void
    {
        $sourcePath = storage_path('app/public');
        
        if (!is_dir($sourcePath)) {
            return;
        }

        if (!is_dir($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $this->copyDirectory($sourcePath, $destPath);
    }

    /**
     * إنشاء backup سريع للإعدادات
     */
    private function createQuickConfigBackup(string $destPath): void
    {
        $sourcePath = base_path('config');
        
        if (!is_dir($sourcePath)) {
            return;
        }

        if (!is_dir($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $this->copyDirectory($sourcePath, $destPath);
    }

    /**
     * تنظيف الملفات المؤقتة بعد الاستعادة
     */
    private function cleanupRestoreTempFiles(string $tempFilePath, string $extractedPath): void
    {
        try {
            // حذف الملف المضغوط المؤقت
            if (file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }

            // حذف المجلد المستخرج
            if (is_dir($extractedPath)) {
                $this->deleteDirectory($extractedPath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup restore temp files: ' . $e->getMessage());
        }
    }

    /**
     * حذف مجلد بشكل متكرر
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    /**
     * تنظيف النسخ المنتهية الصلاحية
     */
    public function cleanupExpiredBackups(): int
    {
        $expiredBackups = Backup::expired()->get();
        $count = 0;

        foreach ($expiredBackups as $backup) {
            try {
                $this->deleteBackup($backup);
                $count++;
            } catch (\Exception $e) {
                \Log::error('Error deleting expired backup: ' . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * الحصول على حجم النسخة
     */
    public function getBackupSize(Backup $backup): int
    {
        return $backup->file_size ?? 0;
    }

    /**
     * الحصول على إجمالي حجم النسخ
     */
    public function getTotalBackupSize(): int
    {
        return Backup::completed()->sum('file_size');
    }

    /**
     * الحصول على إحصائيات النسخ
     */
    public function getBackupStats(): array
    {
        return [
            'total' => Backup::count(),
            'completed' => Backup::completed()->count(),
            'failed' => Backup::failed()->count(),
            'pending' => Backup::where('status', 'pending')->count(),
            'running' => Backup::where('status', 'running')->count(),
            'total_size' => $this->getTotalBackupSize(),
            'expired' => Backup::expired()->count(),
        ];
    }

    /**
     * استعادة قاعدة البيانات
     */
    private function restoreDatabase(string $filePath): void
    {
        $connection = config('database.connections.mysql');
        $database = $connection['database'];
        $username = $connection['username'];
        $password = $connection['password'];
        $host = $connection['host'];
        $port = $connection['port'] ?? 3306;

        // تحديد أمر MySQL حسب نظام التشغيل
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $mysqlCommand = $isWindows ? 'mysql.exe' : 'mysql';

        // التحقق من وجود ملف SQL
        if (!file_exists($filePath)) {
            throw new \Exception("ملف قاعدة البيانات غير موجود: {$filePath}");
        }

        // بناء الأمر
        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s',
            escapeshellarg($mysqlCommand),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database)
        );

        // قراءة محتوى الملف
        $sqlContent = file_get_contents($filePath);
        if ($sqlContent === false) {
            throw new \Exception("فشل في قراءة ملف قاعدة البيانات: {$filePath}");
        }

        // على Windows، نستخدم pipe بدلاً من redirect
        if ($isWindows) {
            $process = proc_open(
                $command,
                [
                    0 => ['pipe', 'r'], // stdin
                    1 => ['pipe', 'w'], // stdout
                    2 => ['pipe', 'w'], // stderr
                ],
                $pipes
            );

            if (!is_resource($process)) {
                throw new \Exception('فشل في بدء عملية MySQL');
            }

            // كتابة محتوى SQL إلى stdin
            fwrite($pipes[0], $sqlContent);
            fclose($pipes[0]);

            // قراءة stderr للأخطاء
            $errorOutput = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnVar = proc_close($process);

            if ($returnVar !== 0) {
                throw new \Exception('فشل في استعادة قاعدة البيانات: ' . ($errorOutput ?: 'خطأ غير معروف'));
            }
        } else {
            // على Linux/Unix، نستخدم shell redirect
            $command .= ' < ' . escapeshellarg($filePath);
            exec($command . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0) {
                $errorMessage = implode("\n", $output);
                throw new \Exception('فشل في استعادة قاعدة البيانات: ' . ($errorMessage ?: 'خطأ غير معروف'));
            }
        }
    }

    /**
     * استعادة الملفات
     */
    private function restoreFiles(string $filePath): void
    {
        $destDir = storage_path('app/public');
        
        // التأكد من وجود المجلد الهدف
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // نسخ الملفات مع الحفاظ على البنية
        $this->copyDirectory($filePath, $destDir);
    }

    /**
     * استعادة الإعدادات
     */
    private function restoreConfig(string $filePath): void
    {
        if (!is_dir($filePath)) {
            throw new \Exception("مسار الإعدادات غير موجود: {$filePath}");
        }

        $files = glob($filePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $destPath = base_path('config/' . basename($file));
                
                // التأكد من وجود المجلد الهدف
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                
                copy($file, $destPath);
            }
        }
    }

    /**
     * استعادة كاملة
     */
    private function restoreFull(string $filePath): void
    {
        $this->restoreDatabase($filePath . '/database.sql');
        $this->restoreFiles($filePath . '/files');
        $this->restoreConfig($filePath . '/config');
    }

    /**
     * نسخ مجلد
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item, $destPath);
            }
        }
    }

    /**
     * استخراج إلى مجلد
     */
    private function extractToDirectory(string $archivePath, string $destDir): void
    {
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($archivePath) === true) {
            $zip->extractTo($destDir);
            $zip->close();
        }
    }

    /**
     * إضافة سجل
     */
    private function log(Backup $backup, string $level, string $message, array $context = []): void
    {
        BackupLog::create([
            'backup_id' => $backup->id,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }
}

