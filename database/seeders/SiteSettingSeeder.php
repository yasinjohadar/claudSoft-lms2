<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الإعداد الافتراضي للتسجيل العام (مفعّل افتراضيًا)
        SiteSetting::setValue(
            'registration_public_enabled',
            true,
            'تفعيل/إيقاف التسجيل العام للزوار (صفحة /register)'
        );

        SiteSetting::setValue(
            'group_registration_terms',
            '',
            'شروط التسجيل العامة المعروضة في نماذج تسجيل المجموعات'
        );
    }
}
