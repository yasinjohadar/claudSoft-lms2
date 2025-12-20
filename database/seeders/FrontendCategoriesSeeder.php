<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FrontendCourseCategory;

class FrontendCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'تطوير الويب',
                'slug' => 'web-development',
                'description' => 'تعلم تطوير المواقع والتطبيقات الإلكترونية باستخدام أحدث التقنيات والأدوات',
                'icon' => 'fas fa-code',
                'color' => '#667eea',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'تطوير تطبيقات الجوال',
                'slug' => 'mobile-development',
                'description' => 'احترف تطوير تطبيقات iOS و Android باستخدام أحدث الأدوات',
                'icon' => 'fas fa-mobile-alt',
                'color' => '#f093fb',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'برمجة Python',
                'slug' => 'python-programming',
                'description' => 'تعلم لغة البرمجة الأكثر طلباً في سوق العمل',
                'icon' => 'fab fa-python',
                'color' => '#4facfe',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'التصميم الجرافيكي',
                'slug' => 'graphic-design',
                'description' => 'أتقن فنون التصميم الجرافيكي والإبداع البصري',
                'icon' => 'fas fa-palette',
                'color' => '#43e97b',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'تحليل البيانات',
                'slug' => 'data-analysis',
                'description' => 'احترف تحليل البيانات واستخراج الرؤى القيمة',
                'icon' => 'fas fa-chart-line',
                'color' => '#fa709a',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'التسويق الرقمي',
                'slug' => 'digital-marketing',
                'description' => 'تعلم استراتيجيات التسويق الرقمي والنمو السريع',
                'icon' => 'fas fa-bullhorn',
                'color' => '#fee140',
                'is_active' => true,
                'order' => 6,
            ],
            [
                'name' => 'الذكاء الاصطناعي',
                'slug' => 'artificial-intelligence',
                'description' => 'اكتشف عالم الذكاء الاصطناعي والتعلم الآلي',
                'icon' => 'fas fa-brain',
                'color' => '#a8edea',
                'is_active' => true,
                'order' => 7,
            ],
            [
                'name' => 'الأمن السيبراني',
                'slug' => 'cybersecurity',
                'description' => 'تعلم حماية الأنظمة والشبكات من الهجمات الإلكترونية',
                'icon' => 'fas fa-shield-alt',
                'color' => '#ff6a00',
                'is_active' => true,
                'order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            FrontendCourseCategory::create($category);
        }
    }
}
