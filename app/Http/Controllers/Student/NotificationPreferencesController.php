<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    /**
     * عرض صفحة إعدادات الإشعارات
     */
    public function index()
    {
        $user = auth()->user();

        // أنواع الإشعارات المتاحة
        $notificationTypes = [
            'badge_earned' => [
                'name' => 'حصلت على شارة جديدة',
                'icon' => '🏅',
                'description' => 'عند الحصول على شارة جديدة',
            ],
            'achievement_unlocked' => [
                'name' => 'إنجاز جديد',
                'icon' => '🏆',
                'description' => 'عند إكمال إنجاز جديد',
            ],
            'level_up' => [
                'name' => 'ترقية المستوى',
                'icon' => '⬆️',
                'description' => 'عند الوصول لمستوى جديد',
            ],
            'points_earned' => [
                'name' => 'نقاط جديدة',
                'icon' => '💰',
                'description' => 'عند كسب نقاط كبيرة (100+)',
            ],
            'streak_milestone' => [
                'name' => 'إنجاز سلسلة',
                'icon' => '🔥',
                'description' => 'عند الوصول لسلسلة أيام متتالية',
            ],
            'challenge_completed' => [
                'name' => 'إكمال تحدي',
                'icon' => '🎯',
                'description' => 'عند إكمال تحدي',
            ],
            'challenge_expired' => [
                'name' => 'انتهاء تحدي',
                'icon' => '⏰',
                'description' => 'عند انتهاء وقت التحدي',
            ],
            'leaderboard_rank' => [
                'name' => 'ترتيب المتصدرين',
                'icon' => '📊',
                'description' => 'عند دخولك ضمن أفضل 10',
            ],
            'friend_request' => [
                'name' => 'طلب صداقة',
                'icon' => '👥',
                'description' => 'عند استلام طلب صداقة',
            ],
            'friend_accepted' => [
                'name' => 'قبول الصداقة',
                'icon' => '🤝',
                'description' => 'عند قبول طلب صداقتك',
            ],
            'competition_invite' => [
                'name' => 'دعوة منافسة',
                'icon' => '⚔️',
                'description' => 'عند دعوتك لمنافسة',
            ],
            'competition_won' => [
                'name' => 'الفوز بمنافسة',
                'icon' => '🥇',
                'description' => 'عند الفوز بمنافسة',
            ],
        ];

        return view('student.settings.notifications', compact('notificationTypes'));
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

        // تحديث تفضيلات الإشعارات الداخلية
        $notificationPreferences = [];
        if (isset($validated['notification_preferences'])) {
            foreach ($validated['notification_preferences'] as $type => $enabled) {
                $notificationPreferences[$type] = (bool) $enabled;
            }
        }

        // تحديث تفضيلات البريد الإلكتروني
        $emailPreferences = [];
        if (isset($validated['email_preferences'])) {
            foreach ($validated['email_preferences'] as $type => $enabled) {
                $emailPreferences[$type] = (bool) $enabled;
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
