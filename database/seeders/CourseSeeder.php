<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على التصنيفات
        $categories = CourseCategory::pluck('id', 'slug')->toArray();

        // الحصول على مدرس (instructor)
        $instructor = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$instructor) {
            $instructor = User::first();
        }

        $courses = [
            // تطوير الويب (5 كورسات)
            [
                'category_slug' => 'ttoyr-aloyb',
                'title' => 'تطوير المواقع بـ Laravel من الصفر',
                'code' => 'WEB-LAR-001',
                'short_description' => 'تعلم بناء تطبيقات ويب احترافية باستخدام Laravel 11',
                'description' => 'دورة شاملة تغطي جميع جوانب Laravel من الأساسيات إلى المواضيع المتقدمة. ستتعلم MVC، Eloquent ORM، Authentication، APIs، وأكثر.',
                'level' => 'intermediate',
                'duration_in_hours' => 40,
                'price' => 299.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'ttoyr-aloyb',
                'title' => 'HTML & CSS للمبتدئين',
                'code' => 'WEB-HTML-001',
                'short_description' => 'ابدأ رحلتك في تطوير الويب بتعلم HTML و CSS',
                'description' => 'كورس مثالي للمبتدئين. تعلم بناء صفحات ويب جميلة ومتجاوبة من الصفر.',
                'level' => 'beginner',
                'duration_in_hours' => 20,
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'ttoyr-aloyb',
                'title' => 'JavaScript المتقدم و ES6+',
                'code' => 'WEB-JS-001',
                'short_description' => 'احترف JavaScript الحديث مع ES6 وما بعده',
                'description' => 'تعمق في JavaScript الحديث. تعلم ES6+، Async/Await، Promises، Module، وأحدث الميزات.',
                'level' => 'advanced',
                'duration_in_hours' => 35,
                'price' => 249.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],
            [
                'category_slug' => 'ttoyr-aloyb',
                'title' => 'React.js - بناء تطبيقات تفاعلية',
                'code' => 'WEB-REACT-001',
                'short_description' => 'تعلم React وبناء واجهات مستخدم حديثة',
                'description' => 'دورة متكاملة في React. Hooks، State Management، Redux، Context API، وأفضل الممارسات.',
                'level' => 'intermediate',
                'duration_in_hours' => 45,
                'price' => 349.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'ttoyr-aloyb',
                'title' => 'Vue.js الكامل',
                'code' => 'WEB-VUE-001',
                'short_description' => 'دليل شامل لتطوير تطبيقات Vue.js',
                'description' => 'تعلم Vue 3، Composition API، Vuex، Vue Router، وبناء تطبيقات SPA احترافية.',
                'level' => 'intermediate',
                'duration_in_hours' => 38,
                'price' => 299.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],

            // تطوير تطبيقات الموبايل (5 كورسات)
            [
                'category_slug' => 'ttoyr-ttbykat-almobayl',
                'title' => 'Flutter للمبتدئين',
                'code' => 'MOB-FLT-001',
                'short_description' => 'ابدأ في تطوير تطبيقات الموبايل بـ Flutter',
                'description' => 'تعلم Flutter و Dart من الصفر. بناء تطبيقات Android و iOS بكود واحد.',
                'level' => 'beginner',
                'duration_in_hours' => 30,
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'ttoyr-ttbykat-almobayl',
                'title' => 'React Native المتقدم',
                'code' => 'MOB-RN-001',
                'short_description' => 'طور تطبيقات موبايل احترافية بـ React Native',
                'description' => 'دورة متقدمة في React Native. Navigation، State Management، Native Modules، Performance.',
                'level' => 'advanced',
                'duration_in_hours' => 42,
                'price' => 399.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],
            [
                'category_slug' => 'ttoyr-ttbykat-almobayl',
                'title' => 'تطوير تطبيقات Android بـ Kotlin',
                'code' => 'MOB-KOT-001',
                'short_description' => 'احترف Kotlin لتطوير تطبيقات Android',
                'description' => 'كورس شامل في Kotlin و Android Development. من الأساسيات إلى النشر على Play Store.',
                'level' => 'intermediate',
                'duration_in_hours' => 50,
                'price' => 449.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'ttoyr-ttbykat-almobayl',
                'title' => 'تطبيقات iOS بـ Swift',
                'code' => 'MOB-IOS-001',
                'short_description' => 'تعلم Swift وطور تطبيقات iOS',
                'description' => 'دورة كاملة في Swift و iOS Development. UIKit، SwiftUI، App Store Deployment.',
                'level' => 'intermediate',
                'duration_in_hours' => 48,
                'price' => 449.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],
            [
                'category_slug' => 'ttoyr-ttbykat-almobayl',
                'title' => 'Flutter المتقدم و Firebase',
                'code' => 'MOB-FLT-002',
                'short_description' => 'طور تطبيقات Flutter مع Firebase Backend',
                'description' => 'دمج Flutter مع Firebase. Authentication، Firestore، Cloud Functions، Push Notifications.',
                'level' => 'advanced',
                'duration_in_hours' => 40,
                'price' => 399.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],

            // علم البيانات والذكاء الاصطناعي (3 كورسات)
            [
                'category_slug' => 'aalm-albyanat-oalthkaaa-alastnaaay',
                'title' => 'Python لعلم البيانات',
                'code' => 'AI-PY-001',
                'short_description' => 'تعلم Python وتحليل البيانات',
                'description' => 'كورس شامل في Python للبيانات. NumPy، Pandas، Matplotlib، Seaborn، وتحليل البيانات.',
                'level' => 'beginner',
                'duration_in_hours' => 35,
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'aalm-albyanat-oalthkaaa-alastnaaay',
                'title' => 'التعلم الآلي بـ Python',
                'code' => 'AI-ML-001',
                'short_description' => 'احترف Machine Learning مع Scikit-learn',
                'description' => 'دورة متكاملة في Machine Learning. Supervised، Unsupervised Learning، Neural Networks.',
                'level' => 'advanced',
                'duration_in_hours' => 60,
                'price' => 599.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'aalm-albyanat-oalthkaaa-alastnaaay',
                'title' => 'Deep Learning بـ TensorFlow',
                'code' => 'AI-DL-001',
                'short_description' => 'تعمق في Deep Learning والشبكات العصبية',
                'description' => 'دورة متقدمة في Deep Learning. CNN، RNN، GANs، Transfer Learning مع TensorFlow و Keras.',
                'level' => 'expert',
                'duration_in_hours' => 70,
                'price' => 699.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],

            // الأمن السيبراني (2 كورسات)
            [
                'category_slug' => 'alamn-alsybrany',
                'title' => 'أساسيات الأمن السيبراني',
                'code' => 'SEC-BASE-001',
                'short_description' => 'مدخل إلى عالم الأمن السيبراني',
                'description' => 'تعلم أساسيات الأمن السيبراني، أنواع الهجمات، وطرق الحماية الأساسية.',
                'level' => 'beginner',
                'duration_in_hours' => 25,
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'featured' => false,
            ],
            [
                'category_slug' => 'alamn-alsybrany',
                'title' => 'الهاكينج الأخلاقي المتقدم',
                'code' => 'SEC-ETH-001',
                'short_description' => 'احترف Ethical Hacking واختبار الاختراق',
                'description' => 'دورة شاملة في Penetration Testing، Network Security، Web Security، Kali Linux.',
                'level' => 'advanced',
                'duration_in_hours' => 80,
                'price' => 799.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],

            // قواعد البيانات (2 كورسات)
            [
                'category_slug' => 'koaaad-albyanat',
                'title' => 'MySQL من الصفر للاحتراف',
                'code' => 'DB-SQL-001',
                'short_description' => 'تعلم MySQL وإدارة قواعد البيانات',
                'description' => 'كورس شامل في MySQL. Queries، Joins، Indexing، Optimization، Backup.',
                'level' => 'intermediate',
                'duration_in_hours' => 30,
                'price' => 199.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],
            [
                'category_slug' => 'koaaad-albyanat',
                'title' => 'MongoDB و NoSQL',
                'code' => 'DB-MONGO-001',
                'short_description' => 'احترف قواعد البيانات غير العلائقية',
                'description' => 'تعلم MongoDB، Aggregation، Indexing، Replication، والتكامل مع Node.js.',
                'level' => 'intermediate',
                'duration_in_hours' => 28,
                'price' => 249.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],

            // DevOps والحوسبة السحابية (2 كورسات)
            [
                'category_slug' => 'devops-oalhosb-alshaby',
                'title' => 'Docker و Kubernetes',
                'code' => 'DEV-DOCK-001',
                'short_description' => 'تعلم الحاويات وإدارتها',
                'description' => 'دورة عملية في Docker و Kubernetes. Containerization، Orchestration، CI/CD.',
                'level' => 'intermediate',
                'duration_in_hours' => 35,
                'price' => 349.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],
            [
                'category_slug' => 'devops-oalhosb-alshaby',
                'title' => 'AWS Solutions Architect',
                'code' => 'DEV-AWS-001',
                'short_description' => 'احترف Amazon Web Services',
                'description' => 'دورة شاملة في AWS. EC2، S3، RDS، Lambda، CloudFormation، والتحضير للشهادة.',
                'level' => 'advanced',
                'duration_in_hours' => 50,
                'price' => 499.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => true,
            ],

            // تصميم واجهات المستخدم (1 كورس)
            [
                'category_slug' => 'tsmym-oaghat-almstkhdm',
                'title' => 'UI/UX Design المتكامل',
                'code' => 'DES-UX-001',
                'short_description' => 'تعلم تصميم تجربة المستخدم الاحترافية',
                'description' => 'دورة شاملة في UI/UX. Figma، Adobe XD، Design Thinking، User Research، Prototyping.',
                'level' => 'beginner',
                'duration_in_hours' => 32,
                'price' => 299.00,
                'is_free' => false,
                'is_published' => true,
                'featured' => false,
            ],
        ];

        foreach ($courses as $courseData) {
            // الحصول على category_id من slug
            $categorySlug = $courseData['category_slug'];
            unset($courseData['category_slug']);

            if (!isset($categories[$categorySlug])) {
                $this->command->warn("⚠️  التصنيف '{$categorySlug}' غير موجود، تخطي الكورس: {$courseData['title']}");
                continue;
            }

            $courseData['course_category_id'] = $categories[$categorySlug];
            $courseData['slug'] = Str::slug($courseData['title'], '-');
            $courseData['instructor_id'] = $instructor?->id;
            $courseData['language'] = 'ar';
            $courseData['is_visible'] = true;
            $courseData['enrollment_type'] = 'open';
            $courseData['created_by'] = $instructor?->id;
            $courseData['available_from'] = Carbon::now();
            $courseData['start_date'] = Carbon::now()->addDays(7);

            // التحقق من عدم وجود الكورس مسبقاً
            Course::firstOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );
        }

        $this->command->info('✅ تم إنشاء 20 كورساً تقنياً موزعة على التصنيفات بنجاح!');
        $this->command->info('📊 التوزيع: تطوير الويب (5)، الموبايل (5)، AI (3)، الأمن (2)، قواعد البيانات (2)، DevOps (2)، UI/UX (1)');
    }
}
