<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgrammingLanguage;
use Illuminate\Support\Str;

class ProgrammingLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            // Frontend
            ['name' => 'HTML', 'display_name' => 'HTML', 'description' => 'لغة ترميز النصوص التشعبية', 'category' => 'frontend', 'icon' => 'fab fa-html5', 'color' => '#E34F26'],
            ['name' => 'CSS', 'display_name' => 'CSS', 'description' => 'أوراق الأنماط المتعاقبة', 'category' => 'frontend', 'icon' => 'fab fa-css3-alt', 'color' => '#1572B6'],
            ['name' => 'JavaScript', 'display_name' => 'JavaScript', 'description' => 'لغة البرمجة للويب', 'category' => 'frontend', 'icon' => 'fab fa-js', 'color' => '#B8A000'],
            ['name' => 'TypeScript', 'display_name' => 'TypeScript', 'description' => 'JavaScript مع الأنواع', 'category' => 'frontend', 'icon' => 'fas fa-code', 'color' => '#3178C6'],
            ['name' => 'React', 'display_name' => 'React.js', 'description' => 'مكتبة JavaScript لبناء واجهات المستخدم', 'category' => 'frontend', 'icon' => 'fab fa-react', 'color' => '#61DAFB'],
            ['name' => 'Vue', 'display_name' => 'Vue.js', 'description' => 'إطار عمل JavaScript التقدمي', 'category' => 'frontend', 'icon' => 'fab fa-vuejs', 'color' => '#4FC08D'],
            ['name' => 'Angular', 'display_name' => 'Angular', 'description' => 'منصة لبناء تطبيقات الويب', 'category' => 'frontend', 'icon' => 'fab fa-angular', 'color' => '#DD0031'],
            ['name' => 'Sass', 'display_name' => 'Sass', 'description' => 'معالج CSS المسبق', 'category' => 'frontend', 'icon' => 'fab fa-sass', 'color' => '#CC6699'],
            ['name' => 'Bootstrap', 'display_name' => 'Bootstrap', 'description' => 'إطار عمل CSS', 'category' => 'frontend', 'icon' => 'fab fa-bootstrap', 'color' => '#7952B3'],
            ['name' => 'Tailwind CSS', 'display_name' => 'Tailwind CSS', 'description' => 'إطار عمل CSS Utility-First', 'category' => 'frontend', 'icon' => 'fas fa-wind', 'color' => '#06B6D4'],

            // Backend
            ['name' => 'PHP', 'display_name' => 'PHP', 'description' => 'لغة البرمجة من جانب الخادم', 'category' => 'backend', 'icon' => 'fab fa-php', 'color' => '#777BB4'],
            ['name' => 'Laravel', 'display_name' => 'Laravel', 'description' => 'إطار عمل PHP الحديث', 'category' => 'backend', 'icon' => 'fab fa-laravel', 'color' => '#FF2D20'],
            ['name' => 'Node.js', 'display_name' => 'Node.js', 'description' => 'بيئة تشغيل JavaScript', 'category' => 'backend', 'icon' => 'fab fa-node-js', 'color' => '#339933'],
            ['name' => 'Python', 'display_name' => 'Python', 'description' => 'لغة برمجة عالية المستوى', 'category' => 'backend', 'icon' => 'fab fa-python', 'color' => '#3776AB'],
            ['name' => 'Django', 'display_name' => 'Django', 'description' => 'إطار عمل Python للويب', 'category' => 'backend', 'icon' => 'fas fa-code', 'color' => '#092E20'],
            ['name' => 'Java', 'display_name' => 'Java', 'description' => 'لغة برمجة كائنية التوجه', 'category' => 'backend', 'icon' => 'fab fa-java', 'color' => '#007396'],

            // Mobile
            ['name' => 'Flutter', 'display_name' => 'Flutter', 'description' => 'إطار عمل لبناء تطبيقات متعددة المنصات', 'category' => 'mobile', 'icon' => 'fas fa-mobile-alt', 'color' => '#02569B'],
            ['name' => 'React Native', 'display_name' => 'React Native', 'description' => 'بناء تطبيقات الموبايل باستخدام React', 'category' => 'mobile', 'icon' => 'fab fa-react', 'color' => '#61DAFB'],
            ['name' => 'Kotlin', 'display_name' => 'Kotlin', 'description' => 'لغة برمجة Android الحديثة', 'category' => 'mobile', 'icon' => 'fab fa-android', 'color' => '#0095D5'],
            ['name' => 'Swift', 'display_name' => 'Swift', 'description' => 'لغة برمجة تطبيقات iOS', 'category' => 'mobile', 'icon' => 'fab fa-apple', 'color' => '#FA7343'],

            // Database
            ['name' => 'MySQL', 'display_name' => 'MySQL', 'description' => 'نظام إدارة قواعد البيانات العلائقية', 'category' => 'database', 'icon' => 'fas fa-database', 'color' => '#4479A1'],
            ['name' => 'MongoDB', 'display_name' => 'MongoDB', 'description' => 'قاعدة بيانات NoSQL', 'category' => 'database', 'icon' => 'fas fa-database', 'color' => '#47A248'],
            ['name' => 'PostgreSQL', 'display_name' => 'PostgreSQL', 'description' => 'قاعدة بيانات علائقية متقدمة', 'category' => 'database', 'icon' => 'fas fa-database', 'color' => '#336791'],
            ['name' => 'Redis', 'display_name' => 'Redis', 'description' => 'قاعدة بيانات في الذاكرة', 'category' => 'database', 'icon' => 'fas fa-database', 'color' => '#DC382D'],

            // AI & Data Science
            ['name' => 'TensorFlow', 'display_name' => 'TensorFlow', 'description' => 'مكتبة التعلم الآلي', 'category' => 'ai', 'icon' => 'fas fa-brain', 'color' => '#FF6F00'],
            ['name' => 'PyTorch', 'display_name' => 'PyTorch', 'description' => 'إطار عمل التعلم العميق', 'category' => 'ai', 'icon' => 'fas fa-brain', 'color' => '#EE4C2C'],

            // DevOps
            ['name' => 'Docker', 'display_name' => 'Docker', 'description' => 'منصة الحاويات', 'category' => 'devops', 'icon' => 'fab fa-docker', 'color' => '#2496ED'],
            ['name' => 'Kubernetes', 'display_name' => 'Kubernetes', 'description' => 'نظام إدارة الحاويات', 'category' => 'devops', 'icon' => 'fas fa-server', 'color' => '#326CE5'],
            ['name' => 'AWS', 'display_name' => 'AWS', 'description' => 'خدمات أمازون السحابية', 'category' => 'devops', 'icon' => 'fab fa-aws', 'color' => '#FF9900'],
            ['name' => 'Git', 'display_name' => 'Git', 'description' => 'نظام التحكم بالإصدارات', 'category' => 'devops', 'icon' => 'fab fa-git-alt', 'color' => '#F05032'],

            // Design
            ['name' => 'Figma', 'display_name' => 'Figma', 'description' => 'أداة التصميم التعاوني', 'category' => 'design', 'icon' => 'fab fa-figma', 'color' => '#F24E1E'],
            ['name' => 'UI/UX', 'display_name' => 'UI/UX Design', 'description' => 'تصميم واجهات وتجربة المستخدم', 'category' => 'design', 'icon' => 'fas fa-palette', 'color' => '#6366F1'],
        ];

        $sortOrder = 0;
        foreach ($languages as $language) {
            $language['slug'] = Str::slug($language['name']);
            $language['sort_order'] = $sortOrder++;
            $language['is_active'] = true;

            ProgrammingLanguage::firstOrCreate(
                ['slug' => $language['slug']],
                $language
            );
        }

        $this->command->info('✅ تم إنشاء ' . count($languages) . ' لغة برمجة وتقنية بنجاح!');
    }
}
