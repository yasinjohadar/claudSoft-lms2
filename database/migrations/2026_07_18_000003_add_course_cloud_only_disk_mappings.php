<?php

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * @param  array<string, string>  $disks
     */
    protected function ensureCloudOnlyMapping(array $disks): void
    {
        $cloudStorage = AppStorageConfig::query()
            ->where('is_active', true)
            ->whereIn('driver', config('storage_inventory.cloud_drivers', ['s3']))
            ->orderByDesc('priority')
            ->first();

        $localStorage = AppStorageConfig::query()
            ->where('driver', 'local')
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->first();

        if (! $cloudStorage && ! $localStorage) {
            return;
        }

        foreach ($disks as $diskName => $label) {
            $mapping = StorageDiskMapping::where('disk_name', $diskName)->first();

            if ($mapping) {
                if ($cloudStorage) {
                    $mapping->update([
                        'primary_storage_id' => $cloudStorage->id,
                        'fallback_storage_ids' => null,
                    ]);
                } else {
                    $mapping->update([
                        'fallback_storage_ids' => null,
                    ]);
                }

                continue;
            }

            StorageDiskMapping::create([
                'disk_name' => $diskName,
                'label' => $label,
                'primary_storage_id' => ($cloudStorage ?? $localStorage)->id,
                'fallback_storage_ids' => null,
                'file_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
                'is_active' => true,
            ]);
        }
    }

    public function up(): void
    {
        $this->ensureCloudOnlyMapping([
            'course_images' => 'Course Images Storage',
            'course_thumbnails' => 'Course Thumbnails Storage',
        ]);
    }

    public function down(): void
    {
        // Intentionally left blank — cloud-only policy should remain.
    }
};
