<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (SiteSetting::where('key', 'force_student_profile_completion')->exists()) {
            return;
        }

        SiteSetting::create([
            'key' => 'force_student_profile_completion',
            'value' => '0',
            'description' => 'إجبار الطلاب على إكمال ملفهم الشخصي 100% قبل استخدام المنصة',
        ]);
    }

    public function down(): void
    {
        SiteSetting::where('key', 'force_student_profile_completion')->delete();
    }
};
