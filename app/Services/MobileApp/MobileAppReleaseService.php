<?php

namespace App\Services\MobileApp;

use App\Models\MobileAppRelease;
use App\Models\User;
use App\Services\Notifications\NotificationHubService;
use Illuminate\Support\Facades\Log;

class MobileAppReleaseService
{
    /** نوع الإشعار الذي يتعرّف عليه التطبيق ليعرض نافذة التحديث. */
    public const NOTIFICATION_TYPE = 'app_update';

    public function __construct(protected NotificationHubService $hub) {}

    /**
     * حالة التحديث لجهاز على [$platform] برقم بناء [$build].
     * - الأحدث: أعلى رقم بناء نشط.
     * - إجباري: إذا كان أي إصدار نشط أحدث من الجهاز مُعلَّماً كإجباري
     *   (حتى لو تخطّى الطالب إصدارات بينها).
     */
    public function check(string $platform, int $build): array
    {
        $latest = MobileAppRelease::active()->forPlatform($platform)->orderByDesc('build_number')->first();

        if (! $latest) {
            return [
                'update_available' => false,
                'force_update' => false,
                'latest' => null,
            ];
        }

        $available = $latest->build_number > $build;
        $forced = $available && MobileAppRelease::active()
            ->forPlatform($platform)
            ->where('build_number', '>', $build)
            ->where('is_forced', true)
            ->exists();

        return [
            'update_available' => $available,
            'force_update' => $forced,
            'latest' => [
                'id' => $latest->id,
                'version_name' => $latest->version_name,
                'build_number' => $latest->build_number,
                'release_notes' => $latest->notesLines(),
                'distribution' => $latest->distribution,
                'download_url' => $latest->download_url,
                'released_at' => $latest->created_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * يرسل إشعار "تحديث جديد" (صندوق + فوري + FCM) للطلاب.
     * [$appUsersOnly]: فقط من سجّلوا الدخول من التطبيق (لديهم توكن mobile:*).
     */
    public function notifyStudents(MobileAppRelease $release, bool $appUsersOnly = true): int
    {
        $students = User::role('student')
            ->where('users.is_active', true)
            ->when($appUsersOnly, fn ($q) => $q->whereHas('tokens', fn ($t) => $t->where('name', 'like', 'mobile:%')))
            ->select('users.*');

        $title = "تحديث جديد للتطبيق ({$release->version_name})";
        $body = $release->is_forced
            ? 'يتوفّر إصدار جديد مطلوب لمتابعة استخدام التطبيق. حدّث الآن.'
            : 'يتوفّر إصدار جديد من تطبيق ClaudSoft بتحسينات وميزات جديدة. حدّث الآن.';

        $sent = 0;
        $students->chunkById(200, function ($chunk) use ($release, $title, $body, &$sent) {
            foreach ($chunk as $student) {
                try {
                    $result = $this->hub->sendToUser($student, 'admin.custom', [
                        'title' => $title,
                        'body' => $body,
                        'type' => self::NOTIFICATION_TYPE,
                        'icon' => '📲',
                        'action_url' => null,
                        'release_id' => $release->id,
                        'platform' => $release->platform,
                        'build_number' => $release->build_number,
                        'sent_by' => auth()->user()?->name,
                        'sent_by_id' => auth()->id(),
                    ], ['database', 'realtime', 'fcm']);

                    if (! empty($result['channels'])) {
                        $sent++;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Mobile app release notification failed', [
                        'release_id' => $release->id,
                        'user_id' => $student->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }, 'users.id', 'id');

        $release->forceFill(['notified_at' => now(), 'notified_count' => $sent])->save();

        return $sent;
    }
}
