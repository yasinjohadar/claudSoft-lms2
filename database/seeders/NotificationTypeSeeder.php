<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificationTypes = [
            [
                'type' => 'badge_earned',
                'name' => 'شارة جديدة',
                'description' => 'إشعار عند الحصول على شارة',
                'icon' => '🏅',
                'default_enabled' => true,
            ],
            [
                'type' => 'achievement_unlocked',
                'name' => 'إنجاز جديد',
                'description' => 'إشعار عند إكمال إنجاز',
                'icon' => '🏆',
                'default_enabled' => true,
            ],
            [
                'type' => 'level_up',
                'name' => 'ترقية مستوى',
                'description' => 'إشعار عند الوصول لمستوى جديد',
                'icon' => '⬆️',
                'default_enabled' => true,
            ],
            [
                'type' => 'points_earned',
                'name' => 'نقاط مكتسبة',
                'description' => 'إشعار عند كسب نقاط كبيرة',
                'icon' => '💰',
                'default_enabled' => true,
            ],
            [
                'type' => 'streak_milestone',
                'name' => 'إنجاز سلسلة',
                'description' => 'إشعار عند الوصول لسلسلة مميزة',
                'icon' => '🔥',
                'default_enabled' => true,
            ],
            [
                'type' => 'challenge_completed',
                'name' => 'تحدي مكتمل',
                'description' => 'إشعار عند إكمال تحدي',
                'icon' => '🎯',
                'default_enabled' => true,
            ],
            [
                'type' => 'challenge_expired',
                'name' => 'انتهاء تحدي',
                'description' => 'إشعار عند انتهاء وقت تحدي',
                'icon' => '⏰',
                'default_enabled' => true,
            ],
            [
                'type' => 'leaderboard_rank',
                'name' => 'ترتيب المتصدرين',
                'description' => 'إشعار عند الوصول للمراكز الأولى',
                'icon' => '📊',
                'default_enabled' => true,
            ],
            [
                'type' => 'friend_request',
                'name' => 'طلب صداقة',
                'description' => 'إشعار عند تلقي طلب صداقة',
                'icon' => '👥',
                'default_enabled' => true,
            ],
            [
                'type' => 'friend_accepted',
                'name' => 'قبول صداقة',
                'description' => 'إشعار عند قبول طلب صداقتك',
                'icon' => '🤝',
                'default_enabled' => true,
            ],
            [
                'type' => 'competition_invite',
                'name' => 'دعوة منافسة',
                'description' => 'إشعار عند دعوتك لمنافسة',
                'icon' => '⚔️',
                'default_enabled' => true,
            ],
            [
                'type' => 'competition_won',
                'name' => 'فوز بمنافسة',
                'description' => 'إشعار عند الفوز بمنافسة',
                'icon' => '🥇',
                'default_enabled' => true,
            ],
            [
                'type' => 'competition_ended',
                'name' => 'انتهاء منافسة',
                'description' => 'إشعار عند انتهاء منافسة',
                'icon' => '🏁',
                'default_enabled' => true,
            ],
            [
                'type' => 'shop_purchase',
                'name' => 'شراء من المتجر',
                'description' => 'إشعار تأكيد الشراء',
                'icon' => '🛒',
                'default_enabled' => true,
            ],
            [
                'type' => 'item_expired',
                'name' => 'انتهاء عنصر',
                'description' => 'إشعار عند انتهاء صلاحية عنصر',
                'icon' => '⌛',
                'default_enabled' => true,
            ],
            [
                'type' => 'daily_reminder',
                'name' => 'تذكير يومي',
                'description' => 'تذكير يومي للحفاظ على السلسلة',
                'icon' => '📢',
                'default_enabled' => false,
            ],
            [
                'type' => 'weekly_summary',
                'name' => 'ملخص أسبوعي',
                'description' => 'ملخص إنجازاتك الأسبوعية',
                'icon' => '📋',
                'default_enabled' => true,
            ],
        ];

        foreach ($notificationTypes as $type) {
            DB::table('notification_types')->updateOrInsert(
                ['type' => $type['type']],
                $type
            );
        }

        $this->command->info('✅ تم إنشاء ' . count($notificationTypes) . ' نوع إشعار بنجاح!');
    }
}
