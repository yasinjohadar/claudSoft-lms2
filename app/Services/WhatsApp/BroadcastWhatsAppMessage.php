<?php

namespace App\Services\WhatsApp;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Support\WapiPhoneNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BroadcastWhatsAppMessage
{
    protected SendWhatsAppMessage $sendService;

    public function __construct(SendWhatsAppMessage $sendService)
    {
        $this->sendService = $sendService;
    }

    /**
     * Get effective phone for user (full_phone or country_code+phone or phone).
     */
    protected function getEffectivePhone(User $user): string
    {
        $phone = $user->full_phone
            ?? (trim(($user->country_code ?? '').($user->phone ?? '')))
            ?: ($user->phone ?? '');

        $phone = preg_replace('/\s+/', '', $phone ?? '');
        if ($phone !== '' && strpos($phone, '+') !== 0) {
            $phone = '+'.ltrim($phone, '0');
        }

        return $phone;
    }

    /**
     * Check if user has a usable phone number (can be normalized to E.164).
     * Relaxed: accept E.164 or any string with 10–15 digits after normalization.
     */
    protected function hasValidPhone(User $user): bool
    {
        return $this->normalizedPhoneDigitsForWapi($user) !== null;
    }

    /**
     * أرقام فقط مناسبة لـ Flaxxa WAPI (E.164 بدون +) أو null إن كان الرقم غير صالح.
     */
    public function normalizedPhoneDigitsForWapi(User $user): ?string
    {
        $phone = $this->getEffectivePhone($user);
        if ($phone === '') {
            return null;
        }
        $normalized = WapiPhoneNormalizer::normalize($phone);
        if (! WapiPhoneNormalizer::isValidE164Digits($normalized)) {
            return null;
        }

        return $normalized;
    }

    /**
     * Get students by criteria.
     * When group is selected: only members of that group (course is ignored).
     * When only course is selected: students enrolled in that course.
     */
    public function getStudentsByCriteria(?int $courseId = null, ?int $groupId = null): Collection
    {
        if ($groupId) {
            Log::channel('whatsapp')->debug('getStudentsByCriteria: filtering by group_id', ['group_id' => $groupId]);

            // إرسال للمجموعة فقط: المستخدمون المنضمون للمجموعة (من جدول course_group_members)
            // لا نشترط دور "student" ولا is_active لأن العضوية في المجموعة تكفي
            $query = User::query()
                ->whereHas('courseGroupMemberships', function ($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                })
                ->where(function ($q) {
                    $q->whereNotNull('phone')->where('phone', '!=', '')
                        ->orWhereNotNull('full_phone')->where('full_phone', '!=', '');
                });

            $beforePhoneFilter = $query->get();
            Log::channel('whatsapp')->debug('getStudentsByCriteria: users in group before phone validation', [
                'group_id' => $groupId,
                'count' => $beforePhoneFilter->count(),
                'user_ids' => $beforePhoneFilter->pluck('id')->toArray(),
            ]);

            $filtered = $beforePhoneFilter->filter(function ($user) {
                $valid = $this->hasValidPhone($user);
                if (! $valid) {
                    Log::channel('whatsapp')->debug('getStudentsByCriteria: user excluded by phone', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'full_phone' => $user->full_phone ?? null,
                        'country_code' => $user->country_code ?? null,
                        'effective' => $this->getEffectivePhone($user),
                    ]);
                }

                return $valid;
            })->values();

            Log::channel('whatsapp')->debug('getStudentsByCriteria: after hasValidPhone filter', [
                'group_id' => $groupId,
                'count' => $filtered->count(),
            ]);

            return $filtered;
        }

        if ($courseId) {
            Log::channel('whatsapp')->debug('getStudentsByCriteria: filtering by course_id', ['course_id' => $courseId]);

            $query = User::role('student')
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereHas('courseEnrollments', function ($q) use ($courseId) {
                    $q->where('course_id', $courseId)
                        ->where('enrollment_status', 'active');
                });

            $beforePhoneFilter = $query->get();
            Log::channel('whatsapp')->debug('getStudentsByCriteria: students in course before phone validation', [
                'course_id' => $courseId,
                'count' => $beforePhoneFilter->count(),
            ]);

            return $beforePhoneFilter->filter(function ($user) {
                return $this->hasValidPhone($user);
            })->values();
        }

        Log::channel('whatsapp')->debug('getStudentsByCriteria: no course_id or group_id, returning empty');

        return collect();
    }

    /**
     * Replace placeholders in message template
     */
    public function replacePlaceholders(
        string $template,
        User $student,
        ?Course $course = null,
        ?CourseGroup $group = null
    ): string {
        $replacements = [
            '{student_name}' => $student->name,
            '{student_email}' => $student->email ?? '',
            '{course_name}' => '', // Default empty
            '{group_name}' => '', // Default empty
        ];

        // Get course from student's enrollment if not provided
        if (! $course && ! $group) {
            // Try to get the first active enrollment's course
            $enrollment = $student->courseEnrollments()
                ->with('course')
                ->where('enrollment_status', 'active')
                ->first();

            if ($enrollment && $enrollment->course) {
                $course = $enrollment->course;
            }
        }

        if ($course) {
            $replacements['{course_name}'] = $course->title;
        }

        // Get group from student's memberships if not provided
        if (! $group) {
            // Try to get first group membership
            $membership = $student->courseGroupMemberships()
                ->with('group')
                ->first();

            if ($membership && $membership->group) {
                $group = $membership->group;
            }
        }

        if ($group) {
            $replacements['{group_name}'] = $group->name;
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }
}
