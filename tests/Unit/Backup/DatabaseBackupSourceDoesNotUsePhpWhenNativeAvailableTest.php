<?php

namespace Tests\Unit\Backup;

use App\Models\Backup;
use App\Services\Backup\BackupEngine;
use App\Services\Backup\DTO\BackupArtifact;
use App\Services\Backup\Sources\DatabaseBackupSource;
use App\Services\Backup\Sources\PhpDatabaseBackupSource;
use Mockery;
use Tests\TestCase;

class DatabaseBackupSourceDoesNotUsePhpWhenNativeAvailableTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_produce_uses_native_source_not_php_when_available(): void
    {
        $backup = new Backup();
        $backup->id = 999001;

        $artifact = new BackupArtifact(
            path: storage_path('app/fake.sql.gz'),
            size: 10,
            extension: 'sql.gz',
            metadata: ['source' => 'mysqldump'],
        );

        $native = Mockery::mock(DatabaseBackupSource::class);
        $native->shouldReceive('isAvailable')->andReturn(true);
        $native->shouldReceive('produce')
            ->once()
            ->with(Mockery::type(Backup::class), Mockery::type('callable'), Mockery::type('array'))
            ->andReturn($artifact);

        $php = Mockery::mock(PhpDatabaseBackupSource::class);
        $php->shouldReceive('isAvailable')->never();
        $php->shouldReceive('produce')->never();

        $engine = new BackupEngine(
            Mockery::mock(\App\Services\Backup\StorageManager::class),
            Mockery::mock(\App\Services\Backup\BackupNotificationService::class),
            $native,
            $php,
        );

        $result = $engine->produceDatabaseArtifact($backup, ['compress' => true]);
        $this->assertSame('mysqldump', $result->metadata['source'] ?? null);
    }
}
