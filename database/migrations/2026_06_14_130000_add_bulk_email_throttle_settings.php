<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaults = [
            'base_delay_seconds' => ['value' => '3', 'type' => 'integer'],
            'random_delay_enabled' => ['value' => '1', 'type' => 'boolean'],
            'min_jitter_seconds' => ['value' => '1', 'type' => 'integer'],
            'max_jitter_seconds' => ['value' => '4', 'type' => 'integer'],
            'batch_size' => ['value' => '10', 'type' => 'integer'],
            'batch_pause_seconds' => ['value' => '45', 'type' => 'integer'],
            'max_recipients_per_campaign' => ['value' => '500', 'type' => 'integer'],
            'daily_send_limit' => ['value' => '0', 'type' => 'integer'],
        ];

        foreach ($defaults as $key => $config) {
            if (! SystemSetting::byKey($key)->ofGroup('bulk_email')->exists()) {
                SystemSetting::set(
                    $key,
                    $config['value'],
                    $config['type'],
                    'bulk_email'
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'base_delay_seconds',
            'random_delay_enabled',
            'min_jitter_seconds',
            'max_jitter_seconds',
            'batch_size',
            'batch_pause_seconds',
            'max_recipients_per_campaign',
            'daily_send_limit',
        ];

        foreach ($keys as $key) {
            SystemSetting::byKey($key)->ofGroup('bulk_email')->delete();
        }
    }
};
