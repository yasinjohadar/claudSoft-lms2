<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FrontendReview;
use App\Models\FrontendCourse;

class FrontendReviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some courses for course-specific reviews
        $laravelCourse = FrontendCourse::where('slug', 'laravel-web-development')->first();
        $pythonCourse = FrontendCourse::where('slug', 'python-zero-to-hero')->first();
        $flutterCourse = FrontendCourse::where('slug', 'flutter-for-beginners')->first();
        $photoshopCourse = FrontendCourse::where('slug', 'adobe-photoshop-professional')->first();

        $reviews = [
            // General platform reviews (no course_id, with suggestions)
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'أحمد محمد العلي',
                'student_email' => 'ahmed.ali@example.com',
                'student_position' => 'مطور Full Stack',
                'rating' => 5,
                'review_text' => 'المنصة رائعة جداً! الكورسات ذات جودة عالية والشرح واضح ومفصل. استفدت كثيراً وحصلت على وظيفة بفضل المهارات التي تعلمتها هنا.',
                'suggestion' => 'أتمنى إضافة المزيد من الكورسات المتقدمة في مجال الذكاء الاصطناعي والتعلم الآلي.',
                'is_active' => true,
                'is_featured' => true,
                'order' => 1,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'فاطمة السعيد',
                'student_email' => 'fatima.said@example.com',
                'student_position' => 'مصممة جرافيك',
                'rating' => 5,
                'review_text' => 'أفضل منصة تعليمية عربية جربتها! المحتوى عملي ومباشر، والمدربون محترفون. أنصح بها بشدة لكل من يريد تطوير مهاراته.',
                'suggestion' => 'يا ليت يكون فيه تطبيق للجوال عشان أقدر أتابع الكورسات في أي وقت.',
                'is_active' => true,
                'is_featured' => true,
                'order' => 2,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'خالد إبراهيم',
                'student_email' => 'khaled.ibrahim@example.com',
                'student_position' => 'مدير مشاريع تقنية',
                'rating' => 5,
                'review_text' => 'منصة احترافية بكل المقاييس. الشهادات معتمدة والدعم الفني سريع ومتعاون. تجربة تعليمية متكاملة.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => true,
                'order' => 3,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'نورة الحربي',
                'student_email' => 'noura.alharbi@example.com',
                'student_position' => 'محللة بيانات',
                'rating' => 4,
                'review_text' => 'كورسات ممتازة ومحتوى قيّم. التطبيق العملي والمشاريع ساعدتني كثيراً في فهم المفاهيم.',
                'suggestion' => 'اقترح إضافة منتدى للنقاش بين الطلاب وتبادل الخبرات.',
                'is_active' => true,
                'is_featured' => true,
                'order' => 4,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'عمر القحطاني',
                'student_email' => 'omar.alqahtani@example.com',
                'student_position' => 'مطور تطبيقات جوال',
                'rating' => 5,
                'review_text' => 'الشرح بالعربي كان نقطة تحول بالنسبة لي. أخيراً منصة تفهم احتياجات المتعلم العربي!',
                'suggestion' => 'ممكن تضيفون خاصية التحميل للمحاضرات للمشاهدة بدون إنترنت؟',
                'is_active' => true,
                'is_featured' => true,
                'order' => 5,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'مريم الزهراني',
                'student_email' => 'mariam.alzahrani@example.com',
                'student_position' => 'مسوقة رقمية',
                'rating' => 5,
                'review_text' => 'تجربة رائعة! الكورسات منظمة بشكل ممتاز والمحتوى محدّث دائماً. شكراً للقائمين على المنصة.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => true,
                'order' => 6,
            ],

            // Course-specific reviews
            [
                'frontend_course_id' => $laravelCourse?->id,
                'user_id' => null,
                'student_name' => 'سعد المطيري',
                'student_email' => 'saad.almutairi@example.com',
                'student_position' => 'مطور Laravel',
                'rating' => 5,
                'review_text' => 'أفضل كورس Laravel باللغة العربية! الشرح مفصل جداً والمشاريع العملية ساعدتني في بناء portfolio قوي.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 7,
            ],
            [
                'frontend_course_id' => $pythonCourse?->id,
                'user_id' => null,
                'student_name' => 'ريم العتيبي',
                'student_email' => 'reem.alotaibi@example.com',
                'student_position' => 'مطورة Python',
                'rating' => 5,
                'review_text' => 'كورس Python رهيب! من الصفر وصلت للاحتراف. الأمثلة واقعية والشرح سهل وممتع.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 8,
            ],
            [
                'frontend_course_id' => $flutterCourse?->id,
                'user_id' => null,
                'student_name' => 'يوسف السالم',
                'student_email' => 'yousef.alsalem@example.com',
                'student_position' => 'مطور تطبيقات',
                'rating' => 4,
                'review_text' => 'كورس Flutter ممتاز للمبتدئين. تعلمت كيف أطور تطبيقات حقيقية ونشرتها على Google Play.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 9,
            ],
            [
                'frontend_course_id' => $photoshopCourse?->id,
                'user_id' => null,
                'student_name' => 'هند الدوسري',
                'student_email' => 'hind.aldosari@example.com',
                'student_position' => 'مصممة محتوى',
                'rating' => 5,
                'review_text' => 'كورس Photoshop غيّر حياتي! من هواية إلى احتراف. الآن أعمل كمصممة مستقلة بفضل هذا الكورس.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 10,
            ],
            [
                'frontend_course_id' => $laravelCourse?->id,
                'user_id' => null,
                'student_name' => 'محمد الشهري',
                'student_email' => 'mohammad.alshehri@example.com',
                'student_position' => 'مبرمج ويب',
                'rating' => 5,
                'review_text' => 'المدرب شرحه واضح جداً ويجاوب على كل الأسئلة. Laravel صار سهل معاه!',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 11,
            ],
            [
                'frontend_course_id' => $pythonCourse?->id,
                'user_id' => null,
                'student_name' => 'سارة القرني',
                'student_email' => 'sara.alqarni@example.com',
                'student_position' => 'طالبة علوم حاسب',
                'rating' => 5,
                'review_text' => 'كورس شامل وكامل! ساعدني كثير في دراستي الجامعية وفي مشاريع التخرج.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 12,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'عبدالله النمر',
                'student_email' => 'abdullah.alnamir@example.com',
                'student_position' => 'رائد أعمال تقني',
                'rating' => 4,
                'review_text' => 'المنصة ساعدتني أتعلم المهارات التقنية اللي احتاجها لمشروعي. محتوى عملي ومفيد.',
                'suggestion' => 'ممكن توفرون خصومات للطلاب الجامعيين أو رواد الأعمال الناشئين؟',
                'is_active' => true,
                'is_featured' => false,
                'order' => 13,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'لينا الخطيب',
                'student_email' => 'lina.alkhatib@example.com',
                'student_position' => 'مهندسة برمجيات',
                'rating' => 5,
                'review_text' => 'جودة الإنتاج عالية والمحتوى منظم بشكل ممتاز. تستحق كل التقدير!',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 14,
            ],
            [
                'frontend_course_id' => $flutterCourse?->id,
                'user_id' => null,
                'student_name' => 'طارق الفهد',
                'student_email' => 'tariq.alfahd@example.com',
                'student_position' => 'مطور مستقل',
                'rating' => 5,
                'review_text' => 'Flutter كان حلم بالنسبة لي، والآن صار واقع! شكراً للمنصة على هالكورس الرائع.',
                'suggestion' => null,
                'is_active' => true,
                'is_featured' => false,
                'order' => 15,
            ],
            [
                'frontend_course_id' => null,
                'user_id' => null,
                'student_name' => 'دانة المنصور',
                'student_email' => 'dana.almansour@example.com',
                'student_position' => 'متخصصة UI/UX',
                'rating' => 4,
                'review_text' => 'كورسات التصميم رائعة! تعلمت مهارات جديدة زادت من قيمتي في سوق العمل.',
                'suggestion' => 'يا ليت تضيفون مسار تعليمي كامل لتصميم واجهات المستخدم وتجربة المستخدم.',
                'is_active' => true,
                'is_featured' => false,
                'order' => 16,
            ],
        ];

        foreach ($reviews as $review) {
            FrontendReview::create($review);
        }
    }
}
