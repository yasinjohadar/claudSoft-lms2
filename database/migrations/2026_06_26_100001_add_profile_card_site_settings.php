<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! SiteSetting::where('key', 'profile_card_enabled_silver')->exists()) {
            SiteSetting::create([
                'key' => 'profile_card_enabled_silver',
                'value' => '0',
                'description' => 'تفعيل بطاقة الطالب التعريفية للحسابات الفضية',
            ]);
        }

        if (! SiteSetting::where('key', 'profile_card_enabled_gold')->exists()) {
            SiteSetting::create([
                'key' => 'profile_card_enabled_gold',
                'value' => '1',
                'description' => 'تفعيل بطاقة الطالب التعريفية للحسابات الذهبية',
            ]);
        }
    }

    public function down(): void
    {
        SiteSetting::whereIn('key', [
            'profile_card_enabled_silver',
            'profile_card_enabled_gold',
        ])->delete();
    }
};
