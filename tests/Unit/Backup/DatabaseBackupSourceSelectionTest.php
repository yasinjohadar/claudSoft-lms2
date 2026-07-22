<?php

namespace Tests\Unit\Backup;

use App\Services\Backup\BackupEngine;
use App\Services\Backup\Sources\DatabaseBackupSource;
use App\Services\Backup\Sources\PhpDatabaseBackupSource;
use App\Services\Backup\StorageDrivers\LocalStorageDriver;
use App\Services\Backup\StorageManager;
use App\Services\Backup\BackupNotificationService;
use Mockery;
use Tests\TestCase;

class DatabaseBackupSourceSelectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_engine_prefers_mysqldump_when_available(): void
    {
        $native = Mockery::mock(DatabaseBackupSource::class);
        $native->shouldReceive('isAvailable')->once()->andReturn(true);

        $php = Mockery::mock(PhpDatabaseBackupSource::class);
        $php->shouldReceive('isAvailable')->never();

        $engine = new BackupEngine(
            Mockery::mock(StorageManager::class),
            Mockery::mock(BackupNotificationService::class),
            $native,
            $php,
        );

        $this->assertSame($native, $engine->resolveDatabaseSource());
    }

    public function test_engine_falls_back_to_php_when_mysqldump_missing(): void
    {
        $native = Mockery::mock(DatabaseBackupSource::class);
        $native->shouldReceive('isAvailable')->once()->andReturn(false);

        $php = Mockery::mock(PhpDatabaseBackupSource::class);
        $php->shouldReceive('isAvailable')->once()->andReturn(true);

        $engine = new BackupEngine(
            Mockery::mock(StorageManager::class),
            Mockery::mock(BackupNotificationService::class),
            $native,
            $php,
        );

        $this->assertSame($php, $engine->resolveDatabaseSource());
    }

    public function test_local_store_from_path_copies_file(): void
    {
        $driver = new LocalStorageDriver(['path' => 'backups_test_' . uniqid()]);
        $tmp = storage_path('app/backup_unit_' . uniqid() . '.bin');
        file_put_contents($tmp, 'jetbackup-test-payload');

        $remote = 'unit/' . basename($tmp);
        $this->assertTrue($driver->storeFromPath($remote, $tmp));
        $this->assertTrue($driver->exists($remote));
        $this->assertSame('jetbackup-test-payload', $driver->retrieve($remote));

        $driver->delete($remote);
        @unlink($tmp);
    }
}
