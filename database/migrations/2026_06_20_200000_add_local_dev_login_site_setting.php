<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (SiteSetting::where('key', 'local_dev_login_enabled')->exists()) {
            return;
        }

        SiteSetting::create([
            'key' => 'local_dev_login_enabled',
            'value' => '0',
            'description' => 'تفعيل صفحة الدخول السريع للتطوير المحلي (local فقط)',
        ]);
    }

    public function down(): void
    {
        SiteSetting::where('key', 'local_dev_login_enabled')->delete();
    }
};
