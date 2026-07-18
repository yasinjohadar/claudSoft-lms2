<?php

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (StorageDiskMapping::where('disk_name', 'blog_images')->exists()) {
            return;
        }

        $localStorage = AppStorageConfig::query()
            ->where('driver', 'local')
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->first();

        if (! $localStorage) {
            return;
        }

        $cloudStorage = AppStorageConfig::query()
            ->where('is_active', true)
            ->whereIn('driver', config('storage_inventory.cloud_drivers', ['s3']))
            ->orderByDesc('priority')
            ->first();

        StorageDiskMapping::create([
            'disk_name' => 'blog_images',
            'label' => 'Blog Images Storage',
            'primary_storage_id' => ($cloudStorage ?? $localStorage)->id,
            'fallback_storage_ids' => $cloudStorage && $localStorage
                ? [$localStorage->id]
                : null,
            'file_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        StorageDiskMapping::where('disk_name', 'blog_images')
            ->where('label', 'Blog Images Storage')
            ->delete();
    }
};
