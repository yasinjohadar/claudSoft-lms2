<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\CourseModule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // البحث عن كورس Laravel
        $course = Course::where('code', 'WEB-LAR-001')->first();

        if (!$course) {
            $this->command->error('⚠️  كورس Laravel غير موجود! يرجى تشغيل CourseSeeder أولاً.');
            return;
        }

        $this->command->info("📚 إضافة الأقسام والدروس لكورس: {$course->title}");

        // تعريف الأقسام الخمسة مع الدروس
        $sectionsWithLessons = [
            [
                'section' => [
                    'title' => 'المقدمة وأساسيات Laravel',
                    'description' => 'تعرف على Laravel وكيفية البدء في استخدامه',
                    'sort_order' => 1,
                ],
                'lessons' => [
                    ['title' => 'مرحباً بك في Laravel', 'content' => 'نظرة عامة على Laravel وما يمكنك بناؤه باستخدامه', 'duration' => 15],
                    ['title' => 'تثبيت Laravel', 'content' => 'تعلم كيفية تثبيت Laravel باستخدام Composer', 'duration' => 20],
                    ['title' => 'هيكل المشروع', 'content' => 'فهم بنية مشروع Laravel وأهم المجلدات', 'duration' => 25],
                    ['title' => 'ملف التكوين .env', 'content' => 'كيفية استخدام ملف البيئة لتخزين الإعدادات', 'duration' => 15],
                    ['title' => 'Artisan CLI', 'content' => 'التعرف على أوامر Artisan الأساسية', 'duration' => 20],
                    ['title' => 'Routing الأساسي', 'content' => 'إنشاء أول Route في Laravel', 'duration' => 30],
                    ['title' => 'Views و Blade', 'content' => 'إنشاء واجهات المستخدم باستخدام Blade', 'duration' => 35],
                    ['title' => 'تمرير البيانات للـ Views', 'content' => 'كيفية إرسال البيانات من Controller إلى View', 'duration' => 25],
                    ['title' => 'Blade Directives', 'content' => 'استخدام @if، @foreach، @include وغيرها', 'duration' => 30],
                    ['title' => 'مشروع عملي: صفحة ترحيب', 'content' => 'بناء صفحة ترحيب ديناميكية', 'duration' => 40],
                ]
            ],
            [
                'section' => [
                    'title' => 'Controllers و MVC Pattern',
                    'description' => 'فهم نمط MVC وكيفية استخدام Controllers',
                    'sort_order' => 2,
                ],
                'lessons' => [
                    ['title' => 'ما هو MVC Pattern', 'content' => 'فهم نمط Model-View-Controller', 'duration' => 20],
                    ['title' => 'إنشاء Controller', 'content' => 'كيفية إنشاء Controller باستخدام Artisan', 'duration' => 15],
                    ['title' => 'Controller Methods', 'content' => 'كتابة الدوال داخل Controller', 'duration' => 25],
                    ['title' => 'Resource Controllers', 'content' => 'استخدام Resource Controllers للـ CRUD', 'duration' => 30],
                    ['title' => 'Route Model Binding', 'content' => 'ربط الـ Routes مع الـ Models تلقائياً', 'duration' => 25],
                    ['title' => 'Request Validation', 'content' => 'التحقق من صحة البيانات المدخلة', 'duration' => 35],
                    ['title' => 'Form Requests', 'content' => 'إنشاء Form Request Classes للتحقق', 'duration' => 30],
                    ['title' => 'Middleware الأساسي', 'content' => 'فهم واستخدام Middleware', 'duration' => 25],
                    ['title' => 'إنشاء Custom Middleware', 'content' => 'كتابة Middleware خاص بك', 'duration' => 30],
                    ['title' => 'مشروع عملي: نظام مدونة بسيط', 'content' => 'بناء صفحات CRUD للمقالات', 'duration' => 45],
                ]
            ],
            [
                'section' => [
                    'title' => 'Eloquent ORM وقواعد البيانات',
                    'description' => 'العمل مع قواعد البيانات باستخدام Eloquent',
                    'sort_order' => 3,
                ],
                'lessons' => [
                    ['title' => 'مقدمة في Eloquent ORM', 'content' => 'ما هو ORM وكيف يعمل في Laravel', 'duration' => 20],
                    ['title' => 'إنشاء Migrations', 'content' => 'بناء جداول قاعدة البيانات باستخدام Migrations', 'duration' => 30],
                    ['title' => 'إنشاء Models', 'content' => 'كيفية إنشاء Eloquent Models', 'duration' => 20],
                    ['title' => 'CRUD العمليات الأساسية', 'content' => 'Create, Read, Update, Delete باستخدام Eloquent', 'duration' => 35],
                    ['title' => 'Query Builder', 'content' => 'بناء استعلامات معقدة باستخدام Query Builder', 'duration' => 30],
                    ['title' => 'Eloquent Relationships - One to One', 'content' => 'العلاقات الفردية بين الجداول', 'duration' => 25],
                    ['title' => 'Eloquent Relationships - One to Many', 'content' => 'علاقة واحد لمتعدد', 'duration' => 30],
                    ['title' => 'Eloquent Relationships - Many to Many', 'content' => 'العلاقات المتعددة', 'duration' => 35],
                    ['title' => 'Seeders و Factories', 'content' => 'ملء قاعدة البيانات ببيانات تجريبية', 'duration' => 25],
                    ['title' => 'مشروع عملي: نظام مكتبة', 'content' => 'بناء علاقات بين الكتب والمؤلفين', 'duration' => 50],
                ]
            ],
            [
                'section' => [
                    'title' => 'Authentication و Authorization',
                    'description' => 'نظام المصادقة والصلاحيات في Laravel',
                    'sort_order' => 4,
                ],
                'lessons' => [
                    ['title' => 'مقدمة في Authentication', 'content' => 'فهم نظام المصادقة في Laravel', 'duration' => 15],
                    ['title' => 'Laravel Breeze', 'content' => 'تثبيت واستخدام Laravel Breeze', 'duration' => 30],
                    ['title' => 'صفحات التسجيل والدخول', 'content' => 'تخصيص صفحات Authentication', 'duration' => 25],
                    ['title' => 'Guards و Providers', 'content' => 'فهم آلية عمل Guards', 'duration' => 20],
                    ['title' => 'Gates و Policies', 'content' => 'تحديد الصلاحيات باستخدام Gates', 'duration' => 30],
                    ['title' => 'Role-Based Access Control', 'content' => 'بناء نظام أدوار وصلاحيات', 'duration' => 35],
                    ['title' => 'Email Verification', 'content' => 'تفعيل البريد الإلكتروني', 'duration' => 25],
                    ['title' => 'Password Reset', 'content' => 'نظام استعادة كلمة المرور', 'duration' => 30],
                    ['title' => 'Two-Factor Authentication', 'content' => 'إضافة المصادقة الثنائية', 'duration' => 35],
                    ['title' => 'مشروع عملي: نظام عضويات كامل', 'content' => 'بناء نظام تسجيل متكامل', 'duration' => 45],
                ]
            ],
            [
                'section' => [
                    'title' => 'مواضيع متقدمة و APIs',
                    'description' => 'بناء APIs ومواضيع Laravel المتقدمة',
                    'sort_order' => 5,
                ],
                'lessons' => [
                    ['title' => 'مقدمة في RESTful APIs', 'content' => 'فهم مفهوم REST APIs', 'duration' => 20],
                    ['title' => 'إنشاء API Routes', 'content' => 'تعريف Routes للـ API', 'duration' => 25],
                    ['title' => 'API Resources', 'content' => 'تنسيق استجابة API باستخدام Resources', 'duration' => 30],
                    ['title' => 'API Authentication - Sanctum', 'content' => 'حماية API باستخدام Laravel Sanctum', 'duration' => 35],
                    ['title' => 'Rate Limiting', 'content' => 'تحديد عدد الطلبات للـ API', 'duration' => 20],
                    ['title' => 'File Uploads', 'content' => 'رفع الملفات والصور', 'duration' => 30],
                    ['title' => 'Queue و Jobs', 'content' => 'معالجة المهام في الخلفية', 'duration' => 35],
                    ['title' => 'Events و Listeners', 'content' => 'نظام الأحداث في Laravel', 'duration' => 25],
                    ['title' => 'Laravel Notifications', 'content' => 'إرسال الإشعارات للمستخدمين', 'duration' => 30],
                    ['title' => 'مشروع نهائي: تطبيق متكامل', 'content' => 'بناء API كامل مع Frontend', 'duration' => 60],
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            $lessonCount = 0;

            foreach ($sectionsWithLessons as $data) {
                // إنشاء القسم
                $section = CourseSection::create([
                    'course_id' => $course->id,
                    'title' => $data['section']['title'],
                    'description' => $data['section']['description'],
                    'sort_order' => $data['section']['sort_order'],
                    'is_visible' => true,
                ]);

                $this->command->info("✅ تم إنشاء القسم: {$section->title}");

                // إنشاء الدروس
                foreach ($data['lessons'] as $index => $lessonData) {
                    // إنشاء الدرس
                    $lesson = Lesson::create([
                        'title' => $lessonData['title'],
                        'description' => $lessonData['content'],
                        'content' => '<p>' . $lessonData['content'] . '</p><p>المدة: ' . $lessonData['duration'] . ' دقيقة</p>',
                        'reading_time' => $lessonData['duration'],
                        'sort_order' => $index + 1,
                        'is_published' => true,
                        'is_visible' => true,
                        'allow_comments' => true,
                    ]);

                    // ربط الدرس بالقسم عبر course_modules
                    CourseModule::create([
                        'course_id' => $course->id,
                        'section_id' => $section->id,
                        'module_type' => 'lesson',
                        'modulable_id' => $lesson->id,
                        'modulable_type' => Lesson::class,
                        'title' => $lesson->title,
                        'description' => $lesson->content,
                        'sort_order' => $index + 1,
                        'is_visible' => true,
                        'is_required' => false,
                        'is_graded' => false,
                        'completion_type' => 'manual',
                    ]);

                    $lessonCount++;
                }

                $this->command->info("   📖 تم إضافة " . count($data['lessons']) . " درس للقسم");
            }

            DB::commit();

            $this->command->info('');
            $this->command->info("🎉 تم إنشاء {$lessonCount} درساً موزعة على 5 أقسام بنجاح!");
            $this->command->info("📚 الكورس: {$course->title}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ حدث خطأ: {$e->getMessage()}");
        }
    }
}
