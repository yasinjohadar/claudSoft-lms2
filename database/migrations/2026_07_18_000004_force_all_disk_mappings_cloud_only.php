<?php

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $cloudStorage = AppStorageConfig::query()
            ->where('is_active', true)
            ->whereIn('driver', config('storage_inventory.cloud_drivers', ['s3']))
            ->orderByDesc('priority')
            ->first();

        if (! $cloudStorage) {
            return;
        }

        StorageDiskMapping::query()->each(function (StorageDiskMapping $mapping) use ($cloudStorage) {
            $mapping->update([
                'primary_storage_id' => $cloudStorage->id,
                'fallback_storage_ids' => null,
            ]);
        });
    }

    public function down(): void
    {
        // Intentionally left blank — cloud-only policy should remain.
    }
};
