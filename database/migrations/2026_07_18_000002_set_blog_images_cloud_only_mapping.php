<?php

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = StorageDiskMapping::where('disk_name', 'blog_images')->first();

        if (! $mapping) {
            return;
        }

        $cloudStorage = AppStorageConfig::query()
            ->where('is_active', true)
            ->whereIn('driver', config('storage_inventory.cloud_drivers', ['s3']))
            ->orderByDesc('priority')
            ->first();

        if ($cloudStorage) {
            $mapping->update([
                'primary_storage_id' => $cloudStorage->id,
                'fallback_storage_ids' => null,
            ]);

            return;
        }

        $mapping->update([
            'fallback_storage_ids' => null,
        ]);
    }

    public function down(): void
    {
        // Intentionally left blank — cloud-only policy should remain.
    }
};
