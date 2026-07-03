<?php

namespace App\Services\Telegram;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BroadcastTelegramMessage
{
    public function __construct(
        private TelegramSettingsService $settingsService,
    ) {}

    public function getStudentsByCriteria(?int $courseId = null, ?int $groupId = null): Collection
    {
        if ($groupId) {
            $query = User::query()
                ->whereNotNull('telegram_chat_id')
                ->where('telegram_chat_id', '!=', '')
                ->whereHas('courseGroupMemberships', fn ($q) => $q->where('group_id', $groupId));
        } elseif ($courseId) {
            $query = User::query()
                ->whereNotNull('telegram_chat_id')
                ->where('telegram_chat_id', '!=', '')
                ->whereHas('courseEnrollments', fn ($q) => $q->where('course_id', $courseId)->where('enrollment_status', 'active'))
                ->role('student');
        } else {
            return collect();
        }

        $students = $query->orderBy('name')->get();

        Log::channel('single')->debug('Telegram broadcast audience', [
            'course_id' => $courseId,
            'group_id' => $groupId,
            'count' => $students->count(),
        ]);

        return $students;
    }

    public function replacePlaceholders(
        string $message,
        User $student,
        ?Course $course = null,
        ?CourseGroup $group = null
    ): string {
        $replacements = [
            'student_name' => $student->name_ar ?? $student->name ?? '',
            'student_email' => $student->email ?? '',
            'email' => $student->email ?? '',
            'course_name' => $course?->title ?? $course?->name ?? '',
            'group_name' => $group?->name ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $message = str_replace(['{{'.$key.'}}', '{'.$key.'}'], (string) $value, $message);
        }

        return $message;
    }

    public function countEligible(?int $courseId, ?int $groupId): int
    {
        return $this->getStudentsByCriteria($courseId, $groupId)->count();
    }
}
