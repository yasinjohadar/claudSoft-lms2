<?php

namespace Tests\Unit\Storage;

use App\Models\AppStorageConfig;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageLocationResolver;
use Mockery;
use Tests\TestCase;

class StorageCleanupTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cleanup_refuses_when_file_not_on_cloud(): void
    {
        $local = new AppStorageConfig(['name' => 'Local', 'driver' => 'local']);
        $local->id = 1;

        $manager = Mockery::mock(AppStorageManager::class);
        $resolver = Mockery::mock(StorageLocationResolver::class);
        $resolver->shouldReceive('resolve')->once()->andReturn([
            'found' => true,
            'status' => StorageLocationResolver::STATUS_LOCAL_ONLY,
            'path' => 'payments/receipts/only-local.jpg',
            'locations' => [
                [
                    'storage_config_id' => 1,
                    'storage_name' => 'Local',
                    'driver' => 'local',
                    'is_cloud' => false,
                    'size' => 10,
                ],
            ],
            'size' => 10,
        ]);
        $manager->shouldReceive('deleteFromConfig')->never();
        $manager->shouldReceive('deleteLegacyPublic')->never();

        $service = new StorageBulkMigrationService($manager, $resolver);
        $result = $service->cleanupLocalIfVerified('payment_receipts', 'payments/receipts/only-local.jpg');

        $this->assertFalse($result['success']);
        $this->assertSame('skipped', $result['action']);
        $this->assertStringContainsString('requires both', $result['message']);
    }

    public function test_cleanup_deletes_local_when_both_confirmed(): void
    {
        $local = new AppStorageConfig(['name' => 'Local', 'driver' => 'local']);
        $local->id = 5;

        $manager = Mockery::mock(AppStorageManager::class);
        $resolver = Mockery::mock(StorageLocationResolver::class);

        $both = [
            'found' => true,
            'status' => StorageLocationResolver::STATUS_BOTH,
            'path' => 'payments/receipts/dup.jpg',
            'locations' => [
                [
                    'storage_config_id' => 9,
                    'storage_name' => 'S3',
                    'driver' => 's3',
                    'is_cloud' => true,
                    'size' => 20,
                ],
                [
                    'storage_config_id' => 5,
                    'storage_name' => 'Local',
                    'driver' => 'local',
                    'is_cloud' => false,
                    'size' => 20,
                ],
            ],
            'size' => 20,
        ];

        $after = [
            'found' => true,
            'status' => StorageLocationResolver::STATUS_CLOUD_ONLY,
            'path' => 'payments/receipts/dup.jpg',
            'locations' => [
                [
                    'storage_config_id' => 9,
                    'storage_name' => 'S3',
                    'driver' => 's3',
                    'is_cloud' => true,
                    'size' => 20,
                ],
            ],
            'size' => 20,
        ];

        $resolver->shouldReceive('resolve')->twice()->andReturn($both, $after);
        $manager->shouldReceive('resolveLocalStorages')->once()->andReturn(collect([$local]));
        $manager->shouldReceive('existsOnConfig')->with($local, 'payments/receipts/dup.jpg')->andReturn(true);
        $manager->shouldReceive('deleteFromConfig')->with($local, 'payments/receipts/dup.jpg')->once()->andReturn(true);
        $manager->shouldReceive('legacyPublicExists')->with('payments/receipts/dup.jpg')->andReturn(false);

        $service = new StorageBulkMigrationService($manager, $resolver);
        $result = $service->cleanupLocalIfVerified('payment_receipts', 'payments/receipts/dup.jpg');

        $this->assertTrue($result['success']);
        $this->assertSame('cleaned', $result['action']);
    }
}
