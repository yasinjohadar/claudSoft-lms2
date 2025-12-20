<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseCategory;
use Illuminate\Support\Str;

class CourseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'تطوير الويب',
                'description' => 'تعلم تطوير المواقع والتطبيقات الإلكترونية باستخدام أحدث التقنيات مثل HTML, CSS, JavaScript, PHP, Laravel, React وغيرها',
                'icon' => 'fas fa-code',
                'color' => '#3b82f6', // Blue
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'تطوير تطبيقات الموبايل',
                'description' => 'دورات متخصصة في تطوير تطبيقات Android و iOS باستخدام Flutter, React Native, Kotlin, Swift',
                'icon' => 'fas fa-mobile-alt',
                'color' => '#10b981', // Green
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'علم البيانات والذكاء الاصطناعي',
                'description' => 'استكشف عالم تحليل البيانات، التعلم الآلي، الذكاء الاصطناعي، والشبكات العصبية باستخدام Python, TensorFlow, PyTorch',
                'icon' => 'fas fa-brain',
                'color' => '#8b5cf6', // Purple
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'الأمن السيبراني',
                'description' => 'تعلم أساسيات وتقنيات الأمن السيبراني، الاختبار الأمني، حماية الشبكات، والهاكينج الأخلاقي',
                'icon' => 'fas fa-shield-alt',
                'color' => '#ef4444', // Red
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'قواعد البيانات',
                'description' => 'احتراف إدارة وتصميم قواعد البيانات باستخدام MySQL, PostgreSQL, MongoDB, Redis',
                'icon' => 'fas fa-database',
                'color' => '#f59e0b', // Orange
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'البرمجة العامة',
                'description' => 'تعلم لغات البرمجة الأساسية مثل Python, Java, C++, JavaScript من الصفر حتى الاحتراف',
                'icon' => 'fas fa-laptop-code',
                'color' => '#06b6d4', // Cyan
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'DevOps والحوسبة السحابية',
                'description' => 'تعلم Docker, Kubernetes, CI/CD, AWS, Azure, Google Cloud وأدوات DevOps الحديثة',
                'icon' => 'fas fa-cloud',
                'color' => '#14b8a6', // Teal
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'تصميم واجهات المستخدم',
                'description' => 'دورات UI/UX، تصميم الجرافيك، Adobe XD, Figma، وتصميم تجربة المستخدم الاحترافية',
                'icon' => 'fas fa-palette',
                'color' => '#ec4899', // Pink
                'order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'هندسة البرمجيات',
                'description' => 'تعلم مبادئ هندسة البرمجيات، Design Patterns, Clean Code, SOLID Principles, Architecture',
                'icon' => 'fas fa-project-diagram',
                'color' => '#6366f1', // Indigo
                'order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'تطوير الألعاب',
                'description' => 'برمج ألعابك الخاصة باستخدام Unity, Unreal Engine, Godot وتعلم C#, C++ لتطوير الألعاب',
                'icon' => 'fas fa-gamepad',
                'color' => '#f97316', // Deep Orange
                'order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            // إنشاء slug تلقائي من الاسم
            $category['slug'] = Str::slug($category['name'], '-');

            // التحقق من عدم وجود التصنيف مسبقاً
            CourseCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✅ تم إنشاء 10 تصنيفات للكورسات التقنية والبرمجية بنجاح!');
    }
}
