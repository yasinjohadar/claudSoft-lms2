<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseReview;
use App\Models\CourseEnrollment;

class CourseReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🌟 بدء إضافة مراجعات الكورسات...\n\n";

        // Get all published courses
        $courses = Course::published()->get();

        // Get all students using Spatie roles
        $students = User::role('student')->get();

        if ($courses->isEmpty() || $students->isEmpty()) {
            echo "❌ لا توجد كورسات أو طلاب في قاعدة البيانات!\n";
            return;
        }

        $reviewTemplates = [
            [
                'title' => 'كورس ممتاز جداً',
                'reviews' => [
                    'كورس رائع جداً، استفدت منه كثيراً. الشرح واضح ومنظم بشكل احترافي. أنصح الجميع بالتسجيل فيه.',
                    'من أفضل الكورسات التي حضرتها. المحتوى غني ومفيد جداً. المدرب متمكن ويشرح بطريقة سهلة وواضحة.',
                    'كورس شامل ومتكامل. تعلمت الكثير من المهارات العملية. التطبيقات العملية كانت مفيدة جداً.',
                ]
            ],
            [
                'title' => 'كورس جيد',
                'reviews' => [
                    'كورس جيد بشكل عام. بعض المواضيع كانت ممتازة والبعض الآخر يحتاج لمزيد من التفصيل.',
                    'محتوى مفيد لكن كنت أتمنى المزيد من الأمثلة العملية. بشكل عام كورس جيد.',
                    'كورس جيد للمبتدئين. المعلومات أساسية ومفيدة لكن المحترفين قد يجدونه بسيطاً.',
                ]
            ],
            [
                'title' => 'كورس مفيد للمبتدئين',
                'reviews' => [
                    'كورس جيد للمبتدئين. الشرح بسيط وواضح. ساعدني في فهم الأساسيات بشكل جيد.',
                    'بداية جيدة لتعلم الموضوع. المحتوى مناسب للمبتدئين. أتمنى المزيد من التمارين العملية.',
                    'كورس مناسب لمن يبدأ من الصفر. التدرج في الشرح ممتاز. استفدت كثيراً.',
                ]
            ],
            [
                'title' => 'تجربة رائعة',
                'reviews' => [
                    'تجربة تعليمية رائعة! المحتوى منظم بشكل ممتاز والتطبيقات العملية مفيدة جداً.',
                    'كورس احترافي بكل المقاييس. المشاريع العملية كانت تحدياً ممتعاً ومفيداً.',
                    'من أروع الكورسات! تعلمت مهارات جديدة وطبقتها فعلياً في مشاريعي.',
                ]
            ],
            [
                'title' => 'كورس متوسط',
                'reviews' => [
                    'كورس متوسط، بعض الأجزاء ممتازة وبعضها يحتاج تحسين. بشكل عام تجربة جيدة.',
                    'محتوى جيد لكن يحتاج لتحديث بعض المعلومات. الشرح واضح بشكل عام.',
                    'كورس مفيد لكن كنت أتوقع المزيد من العمق في بعض المواضيع.',
                ]
            ],
        ];

        $totalReviews = 0;
        $admins = User::role('admin')->get();

        foreach ($courses->take(10) as $course) {
            echo "📚 إضافة مراجعات لكورس: {$course->title}\n";

            // Get enrolled students for this course
            $enrolledStudents = CourseEnrollment::where('course_id', $course->id)
                ->where('enrollment_status', 'active')
                ->pluck('student_id');

            // If no enrolled students, enroll some random students
            if ($enrolledStudents->isEmpty()) {
                echo "   📝 تسجيل طلاب في الكورس...\n";
                $studentsToEnroll = $students->random(min(10, $students->count()));

                foreach ($studentsToEnroll as $student) {
                    CourseEnrollment::create([
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                        'enrollment_status' => 'active',
                        'enrollment_date' => now()->subDays(rand(1, 90)),
                    ]);
                }

                $enrolledStudents = CourseEnrollment::where('course_id', $course->id)
                    ->where('enrollment_status', 'active')
                    ->pluck('student_id');
            }

            // Add 3-7 reviews per course
            $reviewsCount = rand(3, 7);
            $reviewsAdded = 0;

            foreach ($enrolledStudents->take($reviewsCount) as $studentId) {
                // Random rating (weighted towards higher ratings)
                $rating = $this->getWeightedRating();

                // Select appropriate review template based on rating
                $templateIndex = $rating >= 4 ? 0 : ($rating == 3 ? 1 : 2);
                $template = $reviewTemplates[array_rand($reviewTemplates)];

                $review = CourseReview::create([
                    'course_id' => $course->id,
                    'student_id' => $studentId,
                    'rating' => $rating,
                    'title' => rand(0, 1) ? $template['title'] : null, // 50% chance of having title
                    'review' => $template['reviews'][array_rand($template['reviews'])],
                    'status' => 'approved', // Most reviews are approved
                    'approved_by' => $admins->random()->id ?? 1,
                    'approved_at' => now()->subDays(rand(1, 30)),
                    'helpful_count' => rand(0, 20),
                    'is_featured' => rand(1, 10) > 8, // 20% chance to be featured
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);

                $reviewsAdded++;
            }

            echo "   ✅ تم إضافة {$reviewsAdded} مراجعة\n";
            $totalReviews += $reviewsAdded;
        }

        echo "\n🎉 تم إضافة {$totalReviews} مراجعة بنجاح!\n";
    }

    /**
     * Get weighted random rating (favoring higher ratings)
     */
    private function getWeightedRating(): int
    {
        $weights = [
            5 => 40, // 40% chance
            4 => 30, // 30% chance
            3 => 20, // 20% chance
            2 => 7,  // 7% chance
            1 => 3,  // 3% chance
        ];

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $rating => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $rating;
            }
        }

        return 5;
    }
}
