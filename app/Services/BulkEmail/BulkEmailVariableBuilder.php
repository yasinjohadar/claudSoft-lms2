<?php

namespace App\Services\BulkEmail;

use App\Models\BulkEmailCampaign;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;

class BulkEmailVariableBuilder
{
    public function build(User $user, ?Course $course = null, ?CourseGroup $group = null): array
    {
        $phone = $user->full_phone
            ?? trim(($user->country_code ?? '').($user->phone ?? ''))
            ?: ($user->phone ?? '');

        return [
            'student_name' => $user->name_ar ?? $user->name ?? '',
            'student_name_en' => $user->name ?? '',
            'email' => $user->email ?? '',
            'phone' => $phone,
            'group_name' => $group?->name ?? '',
            'course_name' => $course?->title ?? '',
        ];
    }

    public function buildForCampaign(User $user, BulkEmailCampaign $campaign): array
    {
        return $this->build(
            $user,
            $campaign->relationLoaded('course') ? $campaign->course : $campaign->course()->first(),
            $campaign->relationLoaded('group') ? $campaign->group : $campaign->group()->first()
        );
    }

    public function renderSubject(string $subject, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', (string) $value, $subject);
        }

        return $subject;
    }

    public function renderBody(string $body, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $body = str_replace('{{'.$key.'}}', (string) $value, $body);
        }

        return $body;
    }
}
