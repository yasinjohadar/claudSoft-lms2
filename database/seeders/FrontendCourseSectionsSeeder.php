<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FrontendCourse;
use App\Models\FrontendCourseSection;
use App\Models\FrontendCourseLesson;

class FrontendCourseSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $courses = FrontendCourse::all();

        foreach ($courses as $course) {
            // Create 3-5 sections per course
            $sectionsCount = rand(3, 5);

            for ($i = 1; $i <= $sectionsCount; $i++) {
                $section = FrontendCourseSection::create([
                    'course_id' => $course->id,
                    'title' => $this->getSectionTitle($course->title, $i),
                    'description' => 'في هذا المحور سوف نتعلم الأساسيات والمفاهيم المهمة التي تساعدك على فهم الموضوع بشكل أفضل.',
                    'order' => $i,
                    'is_active' => true,
                ]);

                // Create 4-8 lessons per section
                $lessonsCount = rand(4, 8);

                for ($j = 1; $j <= $lessonsCount; $j++) {
                    FrontendCourseLesson::create([
                        'section_id' => $section->id,
                        'title' => $this->getLessonTitle($j),
                        'description' => 'شرح تفصيلي للموضوع مع أمثلة عملية.',
                        'order' => $j,
                        'type' => $this->getRandomType(),
                        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'duration' => rand(5, 45), // Minutes
                        'is_active' => true,
                        'is_free' => $j <= 2 ? true : false, // First 2 lessons are free preview
                    ]);
                }

                // Update section lesson count and duration
                $section->updateLessonsCount();
                $section->duration = $section->calculateDuration();
                $section->save();
            }

            // Update course total lessons count
            $totalLessons = $course->sections()->withCount('lessons')->get()->sum('lessons_count');
            $course->lessons_count = $totalLessons;

            // Update course total duration
            $totalDuration = $course->sections()->sum('duration');
            $course->duration = $totalDuration;

            $course->save();
        }

        $this->command->info('تم إنشاء المحاور والدروس بنجاح!');
    }

    private function getSectionTitle($courseTitle, $sectionNumber): string
    {
        $titles = [
            1 => [
                'المقدمة والبدايات',
                'الأساسيات والمفاهيم',
                'البداية الصحيحة',
                'المقدمة التمهيدية',
            ],
            2 => [
                'المستوى المتوسط',
                'التعمق في الموضوع',
                'المفاهيم المتقدمة',
                'بناء الأساس القوي',
            ],
            3 => [
                'التطبيقات العملية',
                'المشاريع الواقعية',
                'الممارسة والتدريب',
                'التطبيق العملي',
            ],
            4 => [
                'المستوى المتقدم',
                'تقنيات متقدمة',
                'الاحتراف والإتقان',
                'المواضيع المتقدمة',
            ],
            5 => [
                'الخاتمة والمراجعة',
                'المشروع النهائي',
                'الخلاصة والتقييم',
                'الختام والتوصيات',
            ],
        ];

        $sectionTitles = $titles[$sectionNumber] ?? ['محور ' . $sectionNumber];
        return $sectionTitles[array_rand($sectionTitles)];
    }

    private function getLessonTitle($lessonNumber): string
    {
        $titles = [
            'مقدمة عن الموضوع',
            'فهم الأساسيات',
            'الخطوات العملية',
            'أمثلة تطبيقية',
            'حل المشاكل الشائعة',
            'أفضل الممارسات',
            'نصائح وإرشادات',
            'التحديات والحلول',
            'المشروع العملي',
            'المراجعة والتقييم',
            'الأخطاء الشائعة',
            'تقنيات متقدمة',
        ];

        return $titles[array_rand($titles)] . ' - الجزء ' . $lessonNumber;
    }

    private function getRandomType(): string
    {
        $types = ['video', 'video', 'video', 'text', 'quiz']; // More videos
        return $types[array_rand($types)];
    }
}
