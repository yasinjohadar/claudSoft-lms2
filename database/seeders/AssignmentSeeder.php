<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a specific course (you can change this ID)
        $course = Course::first();

        if (!$course) {
            $this->command->error('No courses found! Please create courses first.');
            return;
        }

        $this->command->info("Creating assignments for course: {$course->title}");

        // Get course sections
        $sections = CourseSection::where('course_id', $course->id)->get();

        if ($sections->isEmpty()) {
            $this->command->error('No sections found for this course! Please create sections first.');
            return;
        }

        // Get lessons from the course through course_modules
        $lessonModules = CourseModule::where('course_id', $course->id)
            ->where('module_type', 'lesson')
            ->get();

        $lessons = $lessonModules->map(function($module) {
            return Lesson::find($module->modulable_id);
        })->filter();

        // Get admin user as creator
        $admin = User::role('admin')->first();

        if (!$admin) {
            $admin = User::first();
        }

        $assignmentTitles = [
            'واجب تحليل النصوص البرمجية',
            'مشروع تطبيق ويب متكامل',
            'واجب قواعد البيانات',
            'تصميم واجهة مستخدم حديثة',
            'واجب البرمجة الكائنية',
            'مشروع API RESTful',
            'واجب الخوارزميات المتقدمة',
            'تطوير نظام إدارة محتوى',
            'واجب الأمن السيبراني',
            'مشروع التطبيق المحمول',
            'واجب هياكل البيانات',
            'تحليل وتصميم الأنظمة',
            'واجب إدارة المشاريع البرمجية',
            'تطوير تطبيق React',
            'واجب Laravel Framework',
            'مشروع التجارة الإلكترونية',
            'واجب تحسين الأداء',
            'تطبيق Machine Learning',
            'واجب Docker و DevOps',
            'مشروع التخرج النهائي'
        ];

        $descriptions = [
            'واجب شامل يهدف إلى تعزيز المهارات العملية والنظرية',
            'مشروع عملي يتطلب تطبيق جميع المفاهيم المكتسبة',
            'تمرين تطبيقي على المواضيع التي تمت دراستها',
            'واجب يركز على حل المشكلات البرمجية المعقدة',
            'مهمة تطويرية تتطلب التفكير الإبداعي والتحليلي'
        ];

        $instructions = [
            "## المطلوب:\n1. قراءة المتطلبات بعناية\n2. تطبيق أفضل الممارسات البرمجية\n3. اختبار الكود بشكل شامل\n4. كتابة التوثيق المناسب\n\n## معايير التقييم:\n- جودة الكود (30%)\n- الوظائف والميزات (40%)\n- التوثيق (15%)\n- الإبداع (15%)",

            "## إرشادات التنفيذ:\n1. استخدم Git للتحكم في الإصدارات\n2. اتبع معايير الترميز المتفق عليها\n3. قم بتقسيم المشروع إلى مهام صغيرة\n4. اختبر كل جزء قبل الانتقال للتالي\n\n## التسليم:\n- ملف ZIP يحتوي على الكود الكامل\n- ملف README.md شامل\n- لقطات شاشة للتطبيق العامل",

            "## الخطوات المطلوبة:\n1. تحليل المشكلة وفهم المتطلبات\n2. تصميم الحل المناسب\n3. تطبيق الحل وكتابة الكود\n4. اختبار وتصحيح الأخطاء\n5. كتابة تقرير شامل\n\n## ملاحظات هامة:\n- الالتزام بالمواعيد النهائية\n- استخدام لغة عربية سليمة\n- الاستشهاد بالمصادر عند الحاجة",

            "## متطلبات المشروع:\n- استخدام التقنيات المدروسة\n- تطبيق مبادئ التصميم الجيد\n- كتابة كود نظيف وقابل للصيانة\n- إضافة ميزات إبداعية إضافية\n\n## طريقة التقييم:\n- الوظائف الأساسية: 50 نقطة\n- جودة الكود: 25 نقطة\n- التوثيق: 15 نقطة\n- الميزات الإضافية: 10 نقاط"
        ];

        $submissionTypes = ['link', 'file', 'both'];

        // Create 20 assignments
        for ($i = 0; $i < 20; $i++) {
            $dueDate = Carbon::now()->addDays(rand(7, 60));
            $availableFrom = Carbon::now()->subDays(rand(0, 5));
            $lateSubmissionUntil = $dueDate->copy()->addDays(rand(3, 7));

            $submissionType = $submissionTypes[array_rand($submissionTypes)];
            $allowLateSubmission = (bool) rand(0, 1);
            $isPublished = $i < 15; // First 15 are published
            $allowResubmission = (bool) rand(0, 1);

            // Create the assignment
            $assignment = Assignment::create([
                'title' => $assignmentTitles[$i],
                'description' => $descriptions[array_rand($descriptions)],
                'instructions' => $instructions[array_rand($instructions)],
                'course_id' => $course->id,
                'lesson_id' => $lessons->isNotEmpty() ? $lessons->random()->id : null,
                'max_grade' => [50, 75, 100, 100, 100][rand(0, 4)], // Most are 100
                'submission_type' => $submissionType,
                'max_links' => $submissionType === 'file' ? 0 : rand(3, 10),
                'max_files' => $submissionType === 'link' ? 0 : rand(3, 10),
                'max_file_size' => [5120, 10240, 20480, 51200][rand(0, 3)], // 5MB, 10MB, 20MB, 50MB
                'available_from' => $availableFrom,
                'due_date' => $dueDate,
                'late_submission_until' => $allowLateSubmission ? $lateSubmissionUntil : null,
                'allow_late_submission' => $allowLateSubmission,
                'late_penalty_percentage' => $allowLateSubmission ? [10, 20, 25, 30][rand(0, 3)] : 0,
                'allow_resubmission' => $allowResubmission,
                'max_resubmissions' => $allowResubmission ? ([1, 2, 3, null][rand(0, 3)]) : null,
                'resubmit_after_grading_only' => $allowResubmission ? (bool) rand(0, 1) : true,
                'is_published' => $isPublished,
                'is_visible' => true,
                'sort_order' => $i + 1,
                'attachments' => $this->generateRandomAttachments(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);

            // Create course_module for this assignment
            $randomSection = $sections->random();
            $maxSortOrder = CourseModule::where('section_id', $randomSection->id)->max('sort_order') ?? 0;

            CourseModule::create([
                'course_id' => $course->id,
                'section_id' => $randomSection->id,
                'module_type' => 'assignment',
                'modulable_id' => $assignment->id,
                'modulable_type' => Assignment::class,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'sort_order' => $maxSortOrder + 1,
                'is_visible' => $assignment->is_visible,
                'is_required' => (bool) rand(0, 1),
                'is_graded' => true,
                'max_score' => $assignment->max_grade,
                'completion_type' => 'auto',
                'available_from' => $assignment->available_from,
                'available_until' => null,
            ]);

            $this->command->info("✓ Created: {$assignmentTitles[$i]} (Section: {$randomSection->title})");
        }

        $this->command->info("\n✅ Successfully created 20 assignments for course: {$course->title}");
        $this->command->info("   - Published: 15 assignments");
        $this->command->info("   - Draft: 5 assignments");
        $this->command->info("   - All assignments linked to course_modules");
    }

    /**
     * Generate random attachments for assignments
     */
    private function generateRandomAttachments(): ?string
    {
        // 60% chance to have attachments
        if (rand(0, 100) < 40) {
            return null;
        }

        $attachmentCount = rand(1, 3);
        $attachments = [];

        $sampleFiles = [
            'assignment-guidelines.pdf',
            'project-requirements.docx',
            'sample-code.zip',
            'reference-material.pdf',
            'tutorial-video-link.txt',
            'database-schema.sql',
            'api-documentation.pdf'
        ];

        for ($i = 0; $i < $attachmentCount; $i++) {
            $attachments[] = [
                'name' => $sampleFiles[array_rand($sampleFiles)],
                'path' => 'assignments/attachments/' . uniqid() . '_' . $sampleFiles[array_rand($sampleFiles)],
                'size' => rand(100, 5000) . ' KB',
                'type' => ['pdf', 'docx', 'zip', 'txt', 'sql'][rand(0, 4)]
            ];
        }

        return json_encode($attachments);
    }
}
