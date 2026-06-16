<?php

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $localStorage = AppStorageConfig::where('driver', 'local')
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->first();

        if (! $localStorage) {
            return;
        }

        if (StorageDiskMapping::where('disk_name', 'gift_images')->exists()) {
            return;
        }

        StorageDiskMapping::create([
            'disk_name' => 'gift_images',
            'label' => 'Gift Images Storage',
            'primary_storage_id' => $localStorage->id,
            'fallback_storage_ids' => null,
            'file_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        StorageDiskMapping::where('disk_name', 'gift_images')
            ->where('label', 'Gift Images Storage')
            ->delete();
    }
};
