<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseGroup;
use App\Models\LaravelAiModel;
use App\Models\StudentCourseAiReport;
use App\Models\StudentCourseAiReportBatchItem;
use App\Models\User;
use App\Notifications\StudentCourseAiReportReadyNotification;
use App\Services\Reports\StudentCourseReportDataBuilder;
use App\Services\Reports\StudentCourseReportNarrativeService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateStudentCourseAiReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 240;

    public function __construct(
        public int $studentId,
        public int $courseId,
        public ?int $courseGroupId,
        public int $createdByUserId,
        public string $attemptStrategy,
        public ?int $laravelAiModelId,
        public ?string $since = null,
        public ?int $batchItemId = null,
    ) {}

    public function handle(
        StudentCourseReportDataBuilder $builder,
        StudentCourseReportNarrativeService $narrativeService,
    ): void {
        $item = $this->batchItemId
            ? StudentCourseAiReportBatchItem::query()->with('batch')->find($this->batchItemId)
            : null;

        if ($item) {
            $item->update(['status' => 'processing']);
        }

        $student = User::query()->find($this->studentId);
        $course = Course::query()->find($this->courseId);
        if (! $student || ! $course) {
            $this->finishBatchItem($item, 'failed', null, 'طالب أو كورس غير موجود.', null);

            return;
        }

        if (! $this->isEligible($student, $course)) {
            Log::warning('GenerateStudentCourseAiReportJob skipped: not eligible', [
                'student_id' => $this->studentId,
                'course_id' => $this->courseId,
            ]);
            $this->finishBatchItem($item, 'skipped', null, 'غير مؤهل (تسجيل فعّال أو عضوية المجموعة).', null);

            return;
        }

        $since = $this->since ? Carbon::parse($this->since) : null;
        $facts = $builder->build($student, $course, $this->attemptStrategy, $since);

        $model = $this->resolveModel();
        $actor = User::query()->find($this->createdByUserId);

        $result = $narrativeService->generate($facts, $model, $actor);
        $segments = $this->segmentNarrative($result['narrative']);

        $report = StudentCourseAiReport::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'course_group_id' => $this->courseGroupId,
            'created_by' => $this->createdByUserId,
            'facts' => $facts,
            'narrative' => $result['narrative'],
            'laravel_ai_model_id' => $model->id,
            'meta' => $result['meta'],
        ]);

        $this->finishBatchItem($item, 'succeeded', $report->id, null, $segments);

        $student->notify(new StudentCourseAiReportReadyNotification($report));
    }

    public function failed(?Throwable $exception): void
    {
        if ($this->batchItemId === null) {
            return;
        }

        $item = StudentCourseAiReportBatchItem::query()->with('batch')->find($this->batchItemId);
        if (! $item) {
            return;
        }

        $msg = $exception
            ? mb_substr($exception->getMessage(), 0, 2000)
            : 'فشل تنفيذ المهمة.';

        $item->update([
            'status' => 'failed',
            'error_message' => $msg,
        ]);
        $item->batch?->recalculateAggregates();
    }

    /**
     * @param  array<int, string>|null  $segments
     */
    private function finishBatchItem(
        ?StudentCourseAiReportBatchItem $item,
        string $status,
        ?int $reportId,
        ?string $errorMessage,
        ?array $segments,
    ): void {
        if (! $item) {
            return;
        }

        $data = ['status' => $status];
        if ($reportId !== null) {
            $data['student_course_ai_report_id'] = $reportId;
        }
        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }
        if ($segments !== null) {
            $data['narrative_segments'] = $segments;
        }
        $item->update($data);

        $item->batch?->recalculateAggregates();
    }

    /**
     * @return array<int, string>
     */
    private function segmentNarrative(string $narrative): array
    {
        $narrative = trim($narrative);
        if ($narrative === '') {
            return [];
        }

        $lines = preg_split('/\R+/u', $narrative, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $segments = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $subs = preg_split('/(?<=[.!?؟۔])\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY) ?: [$line];
            foreach ($subs as $s) {
                $t = trim($s);
                if ($t !== '') {
                    $segments[] = $t;
                }
            }
        }

        return array_values($segments);
    }

    private function isEligible(User $student, Course $course): bool
    {
        $enrolled = CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->where('enrollment_status', 'active')
            ->exists();

        if (! $enrolled) {
            return false;
        }

        if ($this->courseGroupId) {
            return CourseGroup::query()
                ->whereKey($this->courseGroupId)
                ->whereHas('students', fn ($q) => $q->where('users.id', $student->id))
                ->exists();
        }

        return true;
    }

    private function resolveModel(): LaravelAiModel
    {
        if ($this->laravelAiModelId) {
            $m = LaravelAiModel::query()
                ->whereKey($this->laravelAiModelId)
                ->where('is_active', true)
                ->first();
            if ($m) {
                return $m;
            }
        }

        $fallback = LaravelAiModel::query()->activeOrdered()->forCapability('reports.student_progress')->first()
            ?? LaravelAiModel::query()->activeOrdered()->forCapability('content.general')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $fallback) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط لتقارير التقدم.');
        }

        return $fallback;
    }
}
