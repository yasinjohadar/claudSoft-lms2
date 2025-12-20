<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Video;
use App\Models\CourseModule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // البحث عن كورس HTML & CSS
        $course = Course::where('code', 'WEB-HTML-001')->first();

        if (!$course) {
            $this->command->error('⚠️  كورس HTML & CSS غير موجود! يرجى تشغيل CourseSeeder أولاً.');
            return;
        }

        $this->command->info("🎥 إضافة الأقسام والفيديوهات لكورس: {$course->title}");

        // قائمة معرفات فيديوهات يوتيوب تعليمية حقيقية
        $youtubeIds = [
            'UB1O30fR-EE', 'qz0aGYrrlhU', 'pQN-pnXPaVg', 'OXGznpKZ_sA', 'UB1O30fR-EE',
            'qz0aGYrrlhU', 'pQN-pnXPaVg', 'OXGznpKZ_sA', 'UB1O30fR-EE', 'qz0aGYrrlhU',
            'pQN-pnXPaVg', 'OXGznpKZ_sA', 'UB1O30fR-EE', 'qz0aGYrrlhU', 'pQN-pnXPaVg',
            'OXGznpKZ_sA', 'UB1O30fR-EE', 'qz0aGYrrlhU', 'pQN-pnXPaVg', 'OXGznpKZ_sA',
        ];

        // تعريف الأقسام الخمسة مع الفيديوهات
        $sectionsWithVideos = [
            [
                'section' => [
                    'title' => 'مقدمة في HTML',
                    'description' => 'تعلم أساسيات HTML من الصفر',
                    'sort_order' => 1,
                ],
                'videos' => [
                    ['title' => 'ما هو HTML؟', 'description' => 'مقدمة شاملة عن HTML ولماذا نستخدمه', 'duration' => 10],
                    ['title' => 'إعداد بيئة العمل', 'description' => 'تثبيت محرر النصوص والمتصفح', 'duration' => 8],
                    ['title' => 'هيكل صفحة HTML الأساسي', 'description' => 'فهم البنية الأساسية لصفحة HTML', 'duration' => 12],
                    ['title' => 'العناصر والوسوم', 'description' => 'تعلم كيفية استخدام الوسوم في HTML', 'duration' => 15],
                    ['title' => 'وسوم العناوين H1-H6', 'description' => 'استخدام عناوين HTML بشكل صحيح', 'duration' => 10],
                    ['title' => 'الفقرات والنصوص', 'description' => 'تنسيق النصوص باستخدام P, BR, HR', 'duration' => 12],
                    ['title' => 'التنسيقات النصية', 'description' => 'استخدام Strong, Em, Mark, Del', 'duration' => 14],
                    ['title' => 'القوائم المرتبة وغير المرتبة', 'description' => 'إنشاء القوائم باستخدام UL, OL, LI', 'duration' => 16],
                    ['title' => 'الروابط', 'description' => 'إضافة روابط داخلية وخارجية', 'duration' => 13],
                    ['title' => 'الصور', 'description' => 'إدراج الصور وتنسيقها', 'duration' => 11],
                    ['title' => 'الجداول', 'description' => 'إنشاء جداول HTML احترافية', 'duration' => 18],
                    ['title' => 'مشروع عملي: صفحة شخصية', 'description' => 'بناء أول صفحة HTML كاملة', 'duration' => 25],
                ]
            ],
            [
                'section' => [
                    'title' => 'HTML المتقدم والنماذج',
                    'description' => 'تعمق في عناصر HTML والنماذج التفاعلية',
                    'sort_order' => 2,
                ],
                'videos' => [
                    ['title' => 'النماذج Forms', 'description' => 'مقدمة في نماذج HTML', 'duration' => 12],
                    ['title' => 'حقول الإدخال Input Types', 'description' => 'أنواع حقول الإدخال المختلفة', 'duration' => 16],
                    ['title' => 'Textarea و Select', 'description' => 'حقول النصوص الطويلة والقوائم المنسدلة', 'duration' => 14],
                    ['title' => 'Radio و Checkbox', 'description' => 'أزرار الاختيار ومربعات الاختيار', 'duration' => 13],
                    ['title' => 'الأزرار Buttons', 'description' => 'أنواع الأزرار واستخداماتها', 'duration' => 10],
                    ['title' => 'التحقق من البيانات', 'description' => 'استخدام HTML5 Validation', 'duration' => 15],
                    ['title' => 'Semantic HTML', 'description' => 'استخدام العناصر الدلالية', 'duration' => 17],
                    ['title' => 'Header, Nav, Footer', 'description' => 'بناء هيكل الصفحة الدلالي', 'duration' => 14],
                    ['title' => 'Article, Section, Aside', 'description' => 'تنظيم المحتوى بشكل دلالي', 'duration' => 16],
                    ['title' => 'الوسائط المتعددة', 'description' => 'إضافة الفيديو والصوت', 'duration' => 12],
                    ['title' => 'Iframe و Embed', 'description' => 'تضمين محتوى خارجي', 'duration' => 11],
                    ['title' => 'مشروع عملي: نموذج تسجيل', 'description' => 'بناء نموذج تسجيل كامل', 'duration' => 22],
                ]
            ],
            [
                'section' => [
                    'title' => 'أساسيات CSS',
                    'description' => 'تعلم تنسيق صفحات الويب باستخدام CSS',
                    'sort_order' => 3,
                ],
                'videos' => [
                    ['title' => 'ما هو CSS؟', 'description' => 'مقدمة في CSS وأهميته', 'duration' => 10],
                    ['title' => 'طرق إضافة CSS', 'description' => 'Inline, Internal, External CSS', 'duration' => 12],
                    ['title' => 'المحددات Selectors', 'description' => 'أنواع المحددات في CSS', 'duration' => 18],
                    ['title' => 'الألوان Colors', 'description' => 'استخدام الألوان بطرق مختلفة', 'duration' => 14],
                    ['title' => 'الخلفيات Backgrounds', 'description' => 'تنسيق خلفيات العناصر', 'duration' => 16],
                    ['title' => 'الحدود Borders', 'description' => 'إضافة حدود للعناصر', 'duration' => 13],
                    ['title' => 'الهوامش Margins', 'description' => 'التحكم بالمسافات الخارجية', 'duration' => 15],
                    ['title' => 'الحشو Padding', 'description' => 'التحكم بالمسافات الداخلية', 'duration' => 14],
                    ['title' => 'الأبعاد Width & Height', 'description' => 'تحديد أبعاد العناصر', 'duration' => 12],
                    ['title' => 'Box Model', 'description' => 'فهم نموذج الصندوق في CSS', 'duration' => 17],
                    ['title' => 'تنسيق النصوص', 'description' => 'Font, Text-align, Line-height', 'duration' => 16],
                    ['title' => 'مشروع عملي: بطاقة شخصية', 'description' => 'تصميم بطاقة احترافية', 'duration' => 20],
                ]
            ],
            [
                'section' => [
                    'title' => 'CSS المتقدم والتخطيط',
                    'description' => 'تعلم تقنيات التخطيط المتقدمة',
                    'sort_order' => 4,
                ],
                'videos' => [
                    ['title' => 'Display Property', 'description' => 'فهم خاصية العرض', 'duration' => 14],
                    ['title' => 'Position Property', 'description' => 'تحديد موضع العناصر', 'duration' => 18],
                    ['title' => 'Float و Clear', 'description' => 'إنشاء تخطيطات باستخدام Float', 'duration' => 16],
                    ['title' => 'Flexbox - المقدمة', 'description' => 'أساسيات Flexbox', 'duration' => 20],
                    ['title' => 'Flexbox - التطبيق', 'description' => 'أمثلة عملية على Flexbox', 'duration' => 22],
                    ['title' => 'Grid Layout - المقدمة', 'description' => 'مقدمة في CSS Grid', 'duration' => 19],
                    ['title' => 'Grid Layout - التطبيق', 'description' => 'بناء تخطيطات معقدة بـ Grid', 'duration' => 24],
                    ['title' => 'Responsive Design', 'description' => 'أساسيات التصميم المتجاوب', 'duration' => 17],
                    ['title' => 'Media Queries', 'description' => 'استخدام Media Queries', 'duration' => 20],
                    ['title' => 'Mobile First Approach', 'description' => 'منهجية Mobile First', 'duration' => 15],
                    ['title' => 'CSS Variables', 'description' => 'استخدام المتغيرات في CSS', 'duration' => 13],
                    ['title' => 'مشروع عملي: صفحة هبوط متجاوبة', 'description' => 'بناء Landing Page كامل', 'duration' => 30],
                ]
            ],
            [
                'section' => [
                    'title' => 'مشاريع عملية ونصائح احترافية',
                    'description' => 'بناء مشاريع كاملة وتعلم أفضل الممارسات',
                    'sort_order' => 5,
                ],
                'videos' => [
                    ['title' => 'Transitions', 'description' => 'إضافة حركات انتقالية', 'duration' => 14],
                    ['title' => 'Animations', 'description' => 'إنشاء رسوم متحركة', 'duration' => 18],
                    ['title' => 'Transform', 'description' => 'تحويل وتدوير العناصر', 'duration' => 16],
                    ['title' => 'Pseudo Classes', 'description' => 'استخدام Hover, Focus, Active', 'duration' => 15],
                    ['title' => 'Pseudo Elements', 'description' => 'Before, After وتطبيقاتها', 'duration' => 17],
                    ['title' => 'Box Shadow و Text Shadow', 'description' => 'إضافة ظلال احترافية', 'duration' => 13],
                    ['title' => 'Gradients', 'description' => 'إنشاء تدرجات لونية', 'duration' => 14],
                    ['title' => 'CSS Best Practices', 'description' => 'أفضل ممارسات كتابة CSS', 'duration' => 16],
                    ['title' => 'مشروع 1: موقع شخصي كامل', 'description' => 'بناء Portfolio Website', 'duration' => 35],
                    ['title' => 'مشروع 2: صفحة مطعم', 'description' => 'تصميم موقع مطعم متجاوب', 'duration' => 32],
                    ['title' => 'مشروع 3: صفحة تسجيل دخول', 'description' => 'تصميم Login Page احترافي', 'duration' => 28],
                    ['title' => 'المشروع النهائي: موقع متكامل', 'description' => 'بناء موقع كامل من الصفر', 'duration' => 45],
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            $videoCount = 0;
            $youtubeIndex = 0;

            foreach ($sectionsWithVideos as $data) {
                // إنشاء القسم
                $section = CourseSection::create([
                    'course_id' => $course->id,
                    'title' => $data['section']['title'],
                    'description' => $data['section']['description'],
                    'sort_order' => $data['section']['sort_order'],
                    'is_visible' => true,
                ]);

                $this->command->info("✅ تم إنشاء القسم: {$section->title}");

                // إنشاء الفيديوهات
                foreach ($data['videos'] as $index => $videoData) {
                    // استخدام معرف يوتيوب بشكل دوري
                    $youtubeId = $youtubeIds[$youtubeIndex % count($youtubeIds)];
                    $youtubeIndex++;

                    // إنشاء الفيديو
                    $video = Video::create([
                        'title' => $videoData['title'],
                        'description' => $videoData['description'],
                        'video_url' => 'https://www.youtube.com/watch?v=' . $youtubeId,
                        'video_type' => 'youtube',
                        'duration' => $videoData['duration'] * 60, // تحويل الدقائق إلى ثواني
                        'sort_order' => $index + 1,
                        'is_published' => true,
                        'is_visible' => true,
                        'allow_download' => false,
                        'processing_status' => 'completed',
                    ]);

                    // ربط الفيديو بالقسم عبر course_modules
                    CourseModule::create([
                        'course_id' => $course->id,
                        'section_id' => $section->id,
                        'module_type' => 'video',
                        'modulable_id' => $video->id,
                        'modulable_type' => Video::class,
                        'title' => $video->title,
                        'description' => $video->description,
                        'sort_order' => $index + 1,
                        'is_visible' => true,
                        'is_required' => false,
                        'is_graded' => false,
                        'completion_type' => 'auto',
                    ]);

                    $videoCount++;
                }

                $this->command->info("   🎬 تم إضافة " . count($data['videos']) . " فيديو للقسم");
            }

            DB::commit();

            $this->command->info('');
            $this->command->info("🎉 تم إنشاء {$videoCount} فيديو موزعة على 5 أقسام بنجاح!");
            $this->command->info("📚 الكورس: {$course->title}");
            $this->command->info("🎥 جميع الفيديوهات من YouTube");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ حدث خطأ: {$e->getMessage()}");
        }
    }
}
