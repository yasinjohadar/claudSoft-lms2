<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    private function getNotificationCategories(): array
    {
        return [
            'gamification' => [
                'name' => 'التحفيز والإنجازات',
                'icon' => 'fe-zap',
                'color' => 'warning',
            ],
            'social' => [
                'name' => 'التواصل الاجتماعي',
                'icon' => 'fe-users',
                'color' => 'info',
            ],
            'competitions' => [
                'name' => 'المنافسات',
                'icon' => 'fe-target',
                'color' => 'danger',
            ],
        ];
    }

    /**
     * الحصول على أنواع الإشعارات المتاحة
     */
    private function getNotificationTypes(): array
    {
        return [
            'badge_earned' => [
                'name' => 'حصلت على شارة جديدة',
                'icon' => 'fe-award',
                'color' => 'warning',
                'category' => 'gamification',
                'description' => 'عند الحصول على شارة جديدة',
            ],
            'achievement_unlocked' => [
                'name' => 'إنجاز جديد',
                'icon' => 'fe-star',
                'color' => 'success',
                'category' => 'gamification',
                'description' => 'عند إكمال إنجاز جديد',
            ],
            'level_up' => [
                'name' => 'ترقية المستوى',
                'icon' => 'fe-trending-up',
                'color' => 'primary',
                'category' => 'gamification',
                'description' => 'عند الوصول لمستوى جديد',
            ],
            'points_earned' => [
                'name' => 'نقاط جديدة',
                'icon' => 'fe-dollar-sign',
                'color' => 'teal',
                'category' => 'gamification',
                'description' => 'عند كسب نقاط كبيرة (100+)',
            ],
            'streak_milestone' => [
                'name' => 'إنجاز سلسلة',
                'icon' => 'fe-activity',
                'color' => 'orange',
                'category' => 'gamification',
                'description' => 'عند الوصول لسلسلة أيام متتالية',
            ],
            'challenge_completed' => [
                'name' => 'إكمال تحدي',
                'icon' => 'fe-check-circle',
                'color' => 'success',
                'category' => 'gamification',
                'description' => 'عند إكمال تحدي',
            ],
            'challenge_expired' => [
                'name' => 'انتهاء تحدي',
                'icon' => 'fe-clock',
                'color' => 'secondary',
                'category' => 'gamification',
                'description' => 'عند انتهاء وقت التحدي',
            ],
            'leaderboard_rank' => [
                'name' => 'ترتيب المتصدرين',
                'icon' => 'fe-bar-chart-2',
                'color' => 'info',
                'category' => 'gamification',
                'description' => 'عند دخولك ضمن أفضل 10',
            ],
            'friend_request' => [
                'name' => 'طلب صداقة',
                'icon' => 'fe-user-plus',
                'color' => 'info',
                'category' => 'social',
                'description' => 'عند استلام طلب صداقة',
            ],
            'friend_accepted' => [
                'name' => 'قبول الصداقة',
                'icon' => 'fe-user-check',
                'color' => 'success',
                'category' => 'social',
                'description' => 'عند قبول طلب صداقتك',
            ],
            'competition_invite' => [
                'name' => 'دعوة منافسة',
                'icon' => 'fe-send',
                'color' => 'primary',
                'category' => 'competitions',
                'description' => 'عند دعوتك لمنافسة',
            ],
            'competition_won' => [
                'name' => 'الفوز بمنافسة',
                'icon' => 'fe-award',
                'color' => 'danger',
                'category' => 'competitions',
                'description' => 'عند الفوز بمنافسة',
            ],
        ];
    }

    /**
     * عرض صفحة إعدادات الإشعارات
     */
    public function index()
    {
        $user = auth()->user();
        $notificationTypes = $this->getNotificationTypes();
        $categories = $this->getNotificationCategories();

        $notificationPrefs = $user->notification_preferences ?? [];
        $emailPrefs = $user->email_preferences ?? [];

        $stats = [
            'total' => count($notificationTypes),
            'internal_enabled' => collect($notificationTypes)->keys()->filter(
                fn ($key) => (bool) ($notificationPrefs[$key] ?? true)
            )->count(),
            'email_enabled' => collect($notificationTypes)->keys()->filter(
                fn ($key) => (bool) ($emailPrefs[$key] ?? false)
            )->count(),
        ];

        return view('student.settings.notifications', compact('notificationTypes', 'categories', 'stats'));
    }

    /**
     * تحديث تفضيلات الإشعارات
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'notification_preferences' => 'nullable|array',
            'email_preferences' => 'nullable|array',
        ]);

        // الحصول على أنواع الإشعارات المتاحة
        $notificationTypesArray = $this->getNotificationTypes();
        $notificationTypes = array_keys($notificationTypesArray);

        // تهيئة تفضيلات الإشعارات الداخلية بجميع القيم كـ false
        $notificationPreferences = [];
        foreach ($notificationTypes as $type) {
            $notificationPreferences[$type] = false;
        }
        
        // تحديث القيم التي تم إرسالها (المفعّلة فقط)
        if (isset($validated['notification_preferences']) && is_array($validated['notification_preferences'])) {
            foreach ($validated['notification_preferences'] as $type => $enabled) {
                if (in_array($type, $notificationTypes)) {
                    $notificationPreferences[$type] = (bool) $enabled;
                }
            }
        }

        // تهيئة تفضيلات البريد الإلكتروني بجميع القيم كـ false
        $emailPreferences = [];
        foreach ($notificationTypes as $type) {
            $emailPreferences[$type] = false;
        }
        
        // تحديث القيم التي تم إرسالها (المفعّلة فقط)
        if (isset($validated['email_preferences']) && is_array($validated['email_preferences'])) {
            foreach ($validated['email_preferences'] as $type => $enabled) {
                if (in_array($type, $notificationTypes)) {
                    $emailPreferences[$type] = (bool) $enabled;
                }
            }
        }

        $user->update([
            'notification_preferences' => $notificationPreferences,
            'email_preferences' => $emailPreferences,
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم حفظ إعدادات الإشعارات بنجاح');
    }
}
