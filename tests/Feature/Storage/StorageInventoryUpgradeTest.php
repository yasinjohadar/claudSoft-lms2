<?php

namespace Tests\Feature\Storage;

use App\Models\AppStorageConfig;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageFileCatalogService;
use App\Services\Storage\StorageInventoryService;
use App\Services\Storage\StorageLocationResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class InventoryCatalogProbeModel extends Model
{
    protected $table = 'inventory_catalog_probe';

    public $timestamps = false;

    protected $guarded = [];
}

class StorageInventoryUpgradeTest extends TestCase
{
    protected function tearDown(): void
    {
        Schema::dropIfExists('inventory_catalog_probe');
        Mockery::close();
        parent::tearDown();
    }

    public function test_config_includes_payments_and_receipts_phase(): void
    {
        $keys = collect(config('storage_inventory.sources'))->pluck('key')->all();

        $this->assertContains('payments', $keys);
        $this->assertContains('group_registrations', $keys);
        $this->assertContains('resources', $keys);
        $this->assertContains('videos', $keys);
        $this->assertContains('certificates_pdf', $keys);
        $this->assertArrayHasKey('receipts', config('storage_inventory.phases'));
        $this->assertContains('payments', config('storage_inventory.phases.receipts'));
    }

    public function test_payments_like_source_appears_in_catalog_with_disk_column(): void
    {
        Schema::dropIfExists('inventory_catalog_probe');
        Schema::create('inventory_catalog_probe', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_disk')->nullable();
        });

        $row = InventoryCatalogProbeModel::query()->create([
            'receipt_path' => 'payments/receipts/inventory-test.jpg',
            'receipt_disk' => 'payment_receipts',
        ]);

        config([
            'storage_inventory.sources' => [
                [
                    'key' => 'payments',
                    'label' => 'إيصالات الدفع',
                    'model' => InventoryCatalogProbeModel::class,
                    'column' => 'receipt_path',
                    'disk' => 'public',
                    'disk_column' => 'receipt_disk',
                    'path_prefix' => 'payments/receipts/',
                    'route_name' => null,
                    'route_param' => null,
                ],
            ],
        ]);

        $catalog = app(StorageFileCatalogService::class);
        $refs = $catalog->collectReferences('payments');

        $this->assertCount(1, $refs);
        $this->assertSame($row->id, $refs[0]['entity_id']);
        $this->assertSame('payment_receipts', $refs[0]['disk']);
        $this->assertSame('payments/receipts/inventory-test.jpg', $refs[0]['path']);
    }

    public function test_inventory_summarize_groups_by_disk_and_source(): void
    {
        $service = app(StorageInventoryService::class);

        $summary = $service->summarize([
            [
                'status' => StorageLocationResolver::STATUS_LOCAL_ONLY,
                'size' => 100,
                'disk' => 'payment_receipts',
                'source_key' => 'payments',
                'source_label' => 'إيصالات الدفع',
            ],
            [
                'status' => StorageLocationResolver::STATUS_BOTH,
                'size' => 50,
                'disk' => 'payment_receipts',
                'source_key' => 'payments',
                'source_label' => 'إيصالات الدفع',
            ],
            [
                'status' => StorageLocationResolver::STATUS_CLOUD_ONLY,
                'size' => 20,
                'disk' => 'blog_images',
                'source_key' => 'blog_posts',
                'source_label' => 'المدونة',
            ],
        ]);

        $this->assertSame(2, $summary['by_disk']['payment_receipts']['count']);
        $this->assertSame(150, $summary['by_disk']['payment_receipts']['bytes']);
        $this->assertSame(2, $summary['by_source']['payments']['count']);
        $this->assertSame('إيصالات الدفع', $summary['by_source']['payments']['label']);
    }

    public function test_dry_run_counts_only_eligible_local_and_both(): void
    {
        $this->mock(StorageLocationResolver::class, function ($mock) {
            $mock->shouldReceive('resolve')
                ->andReturnUsing(function (string $disk, string $path) {
                    $map = [
                        'a.jpg' => StorageLocationResolver::STATUS_LOCAL_ONLY,
                        'b.jpg' => StorageLocationResolver::STATUS_BOTH,
                        'c.jpg' => StorageLocationResolver::STATUS_CLOUD_ONLY,
                        'd.jpg' => StorageLocationResolver::STATUS_MISSING,
                    ];

                    return [
                        'found' => true,
                        'status' => $map[$path],
                        'path' => $path,
                        'locations' => [],
                        'size' => 100,
                    ];
                });
        });

        $manager = Mockery::mock(AppStorageManager::class);
        $cloud = new AppStorageConfig(['name' => 'S3', 'driver' => 's3']);
        $cloud->id = 1;
        $manager->shouldReceive('resolveCloudPrimaryStorages')->andReturn(collect([$cloud]));

        $this->app->instance(AppStorageManager::class, $manager);

        $migration = app(StorageBulkMigrationService::class);

        $preview = $migration->dryRun([
            ['disk' => 'public', 'path' => 'a.jpg'],
            ['disk' => 'public', 'path' => 'b.jpg'],
            ['disk' => 'public', 'path' => 'c.jpg'],
            ['disk' => 'public', 'path' => 'd.jpg'],
        ]);

        $this->assertSame(2, $preview['eligible_count']);
        $this->assertSame(1, $preview['skipped_count']);
        $this->assertSame(1, $preview['missing_count']);
        $this->assertSame(200, $preview['total_bytes']);
    }

    public function test_enrich_item_adds_arabic_status_and_location_labels(): void
    {
        $service = app(StorageInventoryService::class);
        $item = $service->enrichItem([
            'status' => StorageLocationResolver::STATUS_BOTH,
            'size' => 2048,
            'locations' => [
                [
                    'storage_name' => 'S3',
                    'driver' => 's3',
                    'is_cloud' => true,
                    'size' => 2048,
                ],
                [
                    'storage_name' => 'Local',
                    'driver' => 'local',
                    'is_cloud' => false,
                    'size' => 2048,
                ],
            ],
        ]);

        $this->assertSame('نسختان (محلي + سحابة)', $item['status_label']);
        $this->assertSame('سحابة', $item['locations'][0]['kind_label']);
        $this->assertSame('محلي', $item['locations'][1]['kind_label']);
        $this->assertNotEmpty($item['size_human']);
    }
}
