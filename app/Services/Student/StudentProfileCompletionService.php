<?php

namespace App\Services\Student;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StudentProfileCompletionService
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_WEB_ROUTES = [
        'student.profile.edit',
        'student.profile.update',
        'student.profile.upload-photo',
        'student.profile.delete-photo',
        'student.profile.password',
        'student.profile.change-password',
        'logout',
        'admin.stop-impersonate',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_API_ROUTES = [
        'api.student.profile.show',
        'api.student.profile.update',
        'api.student.nationalities',
        'api.student.logout',
    ];

    public function isEnforcementEnabled(): bool
    {
        return SiteSetting::isStudentProfileCompletionForced();
    }

    public function isComplete(User $user): bool
    {
        return $user->profile_completion_percentage >= 100;
    }

    public function shouldBypass(Request $request, ?User $user): bool
    {
        if (! $user) {
            return true;
        }

        if (Session::has('impersonate')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return true;
        }

        return false;
    }

    public function isAllowedRoute(?string $routeName, bool $isApi = false): bool
    {
        if ($routeName === null) {
            return false;
        }

        $allowed = $isApi ? self::ALLOWED_API_ROUTES : self::ALLOWED_WEB_ROUTES;

        return in_array($routeName, $allowed, true);
    }

    public function getRedirectMessage(): string
    {
        return 'هذه الصفحة مخصّصة لإكمال ملفك الشخصي. أكمل جميع البيانات ثم اضغط «حفظ وإكمال الملف» للدخول إلى المنصة.';
    }

    /**
     * @return array<string, mixed>
     */
    public function buildApiBlockPayload(User $user): array
    {
        $data = $user->profile_completion_data;

        return [
            'message' => $this->getRedirectMessage(),
            'profile_completion_required' => true,
            'completion' => [
                'percentage' => $data['percentage'],
                'completed' => $data['completed'],
                'total' => $data['total'],
                'missing_count' => $data['missing_count'],
                'missing_fields' => $data['missing_fields'],
            ],
        ];
    }

    public function isLockedFor(User $user): bool
    {
        return $this->isEnforcementEnabled()
            && ! $this->isComplete($user);
    }
}
