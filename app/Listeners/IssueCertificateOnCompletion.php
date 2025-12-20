<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Models\CertificateTemplate;
use App\Services\CertificateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class IssueCertificateOnCompletion implements ShouldQueue
{
    use InteractsWithQueue;

    protected $certificateService;

    /**
     * Create the event listener.
     */
    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Handle the event.
     */
    public function handle(CourseCompleted $event): void
    {
        try {
            $student = $event->user;
            $course = $event->course;

            // الحصول على التسجيل
            $enrollment = $student->enrollments()
                ->where('course_id', $course->id)
                ->first();

            if (!$enrollment) {
                Log::warning('No enrollment found for user', [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                ]);
                return;
            }

            // البحث عن القالب المناسب للإصدار التلقائي
            $template = CertificateTemplate::where('auto_issue', true)
                ->where('is_active', true)
                ->first();

            // إذا لم يوجد قالب تلقائي، استخدم القالب الافتراضي
            if (!$template) {
                $template = CertificateTemplate::where('is_default', true)
                    ->where('is_active', true)
                    ->first();
            }

            // إذا لم يوجد أي قالب، لا نفعل شيء
            if (!$template) {
                Log::warning('No template found for auto-issue certificate', [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                ]);
                return;
            }

            // التحقق من الأهلية
            $eligibility = $template->checkEligibility($enrollment);
            if (!$eligibility['eligible']) {
                Log::info('Student not eligible for certificate', [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'reasons' => $eligibility['reasons'],
                ]);
                return;
            }

            // التحقق من عدم وجود شهادة مسبقاً
            $existingCertificate = $student->certificates()
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->exists();

            if ($existingCertificate) {
                Log::info('Certificate already exists for this student and course', [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                ]);
                return;
            }

            // إصدار الشهادة
            $this->certificateService->issueCertificate(
                $student,
                $course,
                $template,
                $enrollment,
                null // system auto-issue
            );

            Log::info('Certificate auto-issued successfully', [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'template_id' => $template->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to auto-issue certificate', [
                'error' => $e->getMessage(),
                'student_id' => $event->user->id ?? null,
                'course_id' => $event->course->id ?? null,
            ]);

            // لا نرمي الخطأ حتى لا نوقف باقي العمليات
        }
    }
}
