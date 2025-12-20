<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            'سعودي',
            'مصري',
            'إماراتي',
            'كويتي',
            'قطري',
            'بحريني',
            'عماني',
            'أردني',
            'فلسطيني',
            'لبناني',
            'سوري',
            'عراقي',
            'يمني',
            'ليبي',
            'تونسي',
            'جزائري',
            'مغربي',
            'موريتاني',
            'سوداني',
            'صومالي',
            'جيبوتي',
            'قمري',
        ];

        foreach ($nationalities as $nationality) {
            DB::table('nationalities')->insert([
                'name' => $nationality,
            ]);
        }

        $this->command->info('✅ تم إضافة ' . count($nationalities) . ' جنسية عربية بنجاح!');
    }
}
