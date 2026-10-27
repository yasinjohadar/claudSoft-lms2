<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\GamificationNotification;
use App\Events\UserNotificationBroadcasted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BadgeEarnedEmail;
use App\Mail\AchievementUnlockedEmail;
use App\Mail\LevelUpEmail;

class NotificationService
{
    /**
     * إرسال إشعار
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $icon = null,
        ?string $actionUrl = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?array $metadata = null
    ): ?GamificationNotification {
        try {
            // التحقق من تفضيلات المستخدم
            if (!$this->shouldSendNotification($user, $type)) {
                return null;
            }

            $notification = GamificationNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'icon' => $icon ?? $this->getDefaultIcon($type),
                'action_url' => $actionUrl,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'metadata' => $metadata,
                'is_read' => false,
            ]);

            Log::info('Notification sent', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'type' => $type,
            ]);

            $this->broadcast($notification);

            return $notification;

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * بث فوري (Reverb) لصندوق الطالب — نفس القناة والحدث المستخدمين في مركز الإشعارات،
     * فيصل للويب وتطبيق الجوال. فشل البث لا يمنع حفظ الإشعار.
     */
    protected function broadcast(GamificationNotification $notification): void
    {
        try {
            event(new UserNotificationBroadcasted($notification->user_id, [
                'notification_id' => null,
                'inbox_id' => $notification->id,
                'event_key' => $notification->type,
                'title' => $notification->title,
                'body' => $notification->message,
                'data' => [
                    'type' => $notification->type,
                    'icon' => $notification->icon,
                    'action_url' => $notification->action_url,
                    'related_type' => $notification->related_type,
                    'related_id' => $notification->related_id,
                ],
            ]));
        } catch (\Throwable $e) {
            Log::warning('Gamification notification broadcast failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * التحقق من تفضيلات الإشعارات
     */
    protected function shouldSendNotification(User $user, string $type): bool
    {
        $preferences = $user->notification_preferences ?? [];

        // إذا لم تكن هناك تفضيلات، إرسال الكل
        if (empty($preferences)) {
            return true;
        }

        return $preferences[$type] ?? true;
    }

    /**
     * التحقق من تفضيلات البريد الإلكتروني
     */
    protected function shouldSendEmail(User $user, string $type): bool
    {
        $emailPreferences = $user->email_preferences ?? [];

        // إذا لم تكن هناك تفضيلات، إرسال الكل
        if (empty($emailPreferences)) {
            return true;
        }

        return $emailPreferences[$type] ?? false;
    }

    /**
     * إرسال بريد إلكتروني
     */
    protected function sendEmail(User $user, $mailable): void
    {
        try {
            if ($user->email) {
                Mail::to($user->email)->send($mailable);

                Log::info('Email sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'mailable' => get_class($mailable),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * الحصول على الأيقونة الافتراضية
     */
    protected function getDefaultIcon(string $type): string
    {
        $icons = [
            'badge_earned' => '🏅',
            'achievement_unlocked' => '🏆',
            'level_up' => '⬆️',
            'points_earned' => '💰',
            'streak_milestone' => '🔥',
            'challenge_completed' => '🎯',
            'challenge_expired' => '⏰',
            'leaderboard_rank' => '📊',
            'friend_request' => '👥',
            'friend_accepted' => '🤝',
            'competition_invite' => '⚔️',
            'competition_won' => '🥇',
            'competition_ended' => '🏁',
            'shop_purchase' => '🛒',
            'item_expired' => '⌛',
            'daily_reminder' => '📢',
            'weekly_summary' => '📋',
        ];

        return $icons[$type] ?? '🔔';
    }

    /**
     * إشعار حصول على شارة
     */
    public function notifyBadgeEarned(User $user, $badge): void
    {
        // إرسال الإشعار الداخلي
        $this->send(
            $user,
            'badge_earned',
            'شارة جديدة! 🏅',
            "حصلت على شارة: {$badge->name}",
            $badge->icon,
            "/student/gamification/badges/{$badge->id}",
            'App\Models\Badge',
            $badge->id,
            ['badge_name' => $badge->name, 'rarity' => $badge->rarity]
        );

        // إرسال البريد الإلكتروني
        if ($this->shouldSendEmail($user, 'badge_earned')) {
            $this->sendEmail($user, new BadgeEarnedEmail($user, $badge));
        }
    }

    /**
     * إشعار إكمال إنجاز
     */
    public function notifyAchievementUnlocked(User $user, $achievement): void
    {
        // إرسال الإشعار الداخلي
        $this->send(
            $user,
            'achievement_unlocked',
            'إنجاز جديد! 🏆',
            "أكملت إنجاز: {$achievement->name}",
            $achievement->icon,
            "/student/gamification/achievements/{$achievement->id}",
            'App\Models\Achievement',
            $achievement->id,
            ['achievement_name' => $achievement->name, 'tier' => $achievement->tier]
        );

        // إرسال البريد الإلكتروني
        if ($this->shouldSendEmail($user, 'achievement_unlocked')) {
            $this->sendEmail($user, new AchievementUnlockedEmail($user, $achievement));
        }
    }

    /**
     * إشعار ترقية المستوى
     */
    public function notifyLevelUp(User $user, int $newLevel): void
    {
        // إرسال الإشعار الداخلي
        $this->send(
            $user,
            'level_up',
            'مستوى جديد! ⬆️',
            "وصلت للمستوى {$newLevel}! استمر في التقدم!",
            '⭐',
            "/student/gamification/levels",
            null,
            null,
            ['new_level' => $newLevel]
        );

        // إرسال البريد الإلكتروني
        if ($this->shouldSendEmail($user, 'level_up')) {
            $this->sendEmail($user, new LevelUpEmail($user, $newLevel));
        }
    }

    /**
     * إشعار كسب نقاط
     */
    public function notifyPointsEarned(User $user, int $points, string $reason): void
    {
        // فقط للمكافآت الكبيرة
        if ($points >= 100) {
            $this->send(
                $user,
                'points_earned',
                "حصلت على {$points} نقطة! 💰",
                $reason,
                '💎',
                "/student/gamification/points"
            );
        }
    }

    /**
     * إشعار إنجاز سلسلة
     */
    public function notifyStreakMilestone(User $user, int $streak): void
    {
        $milestones = [7, 14, 30, 60, 90, 180, 365];

        if (in_array($streak, $milestones)) {
            $this->send(
                $user,
                'streak_milestone',
                "سلسلة رائعة! 🔥",
                "وصلت لسلسلة {$streak} يوم متتالي!",
                '🔥',
                "/student/gamification/streaks",
                null,
                null,
                ['streak_days' => $streak]
            );
        }
    }

    /**
     * إشعار إكمال تحدي
     */
    public function notifyChallengeCompleted(User $user, $challenge): void
    {
        $this->send(
            $user,
            'challenge_completed',
            'تحدي مكتمل! 🎯',
            "أكملت تحدي: {$challenge->name}",
            $challenge->icon,
            "/student/gamification/challenges/{$challenge->id}",
            'App\Models\Challenge',
            $challenge->id
        );
    }

    /**
     * إشعار انتهاء تحدي
     */
    public function notifyChallengeExpired(User $user, $challenge): void
    {
        $this->send(
            $user,
            'challenge_expired',
            'انتهى التحدي ⏰',
            "انتهى الوقت للتحدي: {$challenge->name}",
            '⏰',
            "/student/gamification/challenges",
            'App\Models\Challenge',
            $challenge->id
        );
    }

    /**
     * إشعار ترتيب لوحة المتصدرين
     */
    public function notifyLeaderboardRank(User $user, $leaderboard, int $rank): void
    {
        if ($rank <= 10) {
            $this->send(
                $user,
                'leaderboard_rank',
                "أنت في المراكز الأولى! 📊",
                "ترتيبك #{$rank} في {$leaderboard->name}",
                '🏆',
                "/student/gamification/leaderboards/{$leaderboard->id}"
            );
        }
    }

    /**
     * إشعار طلب صداقة
     */
    public function notifyFriendRequest(User $user, User $sender): void
    {
        $this->send(
            $user,
            'friend_request',
            'طلب صداقة جديد 👥',
            "{$sender->name} يريد إضافتك كصديق",
            '👥',
            "/student/gamification/friends/pending-requests"
        );
    }

    /**
     * إشعار قبول الصداقة
     */
    public function notifyFriendAccepted(User $user, User $friend): void
    {
        $this->send(
            $user,
            'friend_accepted',
            'تم قبول الصداقة! 🤝',
            "{$friend->name} قبل طلب صداقتك",
            '🤝',
            "/student/gamification/friends"
        );
    }

    /**
     * إشعار دعوة منافسة
     */
    public function notifyCompetitionInvite(User $user, $competition, User $creator): void
    {
        $this->send(
            $user,
            'competition_invite',
            'دعوة منافسة! ⚔️',
            "{$creator->name} دعاك للمنافسة في {$competition->name}",
            '⚔️',
            "/student/gamification/competitions/{$competition->id}",
            'App\Models\Competition',
            $competition->id
        );
    }

    /**
     * إشعار الفوز بمنافسة
     */
    public function notifyCompetitionWon(User $user, $competition): void
    {
        $this->send(
            $user,
            'competition_won',
            'فزت بالمنافسة! 🥇',
            "تهانينا! فزت في {$competition->name}",
            '🥇',
            "/student/gamification/competitions/{$competition->id}",
            'App\Models\Competition',
            $competition->id
        );
    }

    /**
     * الحصول على إشعارات المستخدم
     */
    public function getUserNotifications(User $user, bool $unreadOnly = false, int $limit = 50)
    {
        $query = GamificationNotification::where('user_id', $user->id);

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        return $query->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * عدد الإشعارات غير المقروءة
     */
    public function getUnreadCount(User $user): int
    {
        return GamificationNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(GamificationNotification $notification): bool
    {
        return $notification->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * تحديد جميع الإشعارات كمقروءة
     */
    public function markAllAsRead(User $user): int
    {
        return GamificationNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * حذف إشعار
     */
    public function delete(GamificationNotification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * حذف الإشعارات القديمة
     */
    public function deleteOldNotifications(int $daysOld = 30): int
    {
        return GamificationNotification::where('created_at', '<', now()->subDays($daysOld))
            ->where('is_read', true)
            ->delete();
    }

    /**
     * إحصائيات الإشعارات
     */
    public function getNotificationStats(User $user): array
    {
        $total = GamificationNotification::where('user_id', $user->id)->count();
        $unread = GamificationNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $byType = GamificationNotification::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'by_type' => $byType,
        ];
    }
}
