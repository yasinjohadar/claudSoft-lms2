<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShopItem;
use App\Models\ShopCategory;

class ShopItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على الفئات
        $cosmetics = ShopCategory::where('slug', 'cosmetics')->first();
        $boosters = ShopCategory::where('slug', 'boosters')->first();
        $courseAccess = ShopCategory::where('slug', 'course-access')->first();
        $features = ShopCategory::where('slug', 'features')->first();
        $physicalRewards = ShopCategory::where('slug', 'physical-rewards')->first();

        $items = [
            // ========================================
            // Cosmetics (التخصيص والمظهر)
            // ========================================
            [
                'category_id' => $cosmetics->id,
                'name' => 'أفاتار النجم الذهبي',
                'slug' => 'golden-star-avatar',
                'description' => 'أفاتار نجم ذهبي مميز يظهر إنجازاتك',
                'type' => 'avatar',
                'icon' => '⭐',
                'price_points' => 1000,
                'price_gems' => 50,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'effects' => ['avatar' => 'golden-star.png'],
                'sort_order' => 1,
            ],
            [
                'category_id' => $cosmetics->id,
                'name' => 'أفاتار التاج الملكي',
                'slug' => 'royal-crown-avatar',
                'description' => 'تاج ملكي للمتميزين',
                'type' => 'avatar',
                'icon' => '👑',
                'price_points' => 2500,
                'price_gems' => 100,
                'required_level' => 10,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'effects' => ['avatar' => 'royal-crown.png'],
                'sort_order' => 2,
            ],
            [
                'category_id' => $cosmetics->id,
                'name' => 'أفاتار الصاروخ',
                'slug' => 'rocket-avatar',
                'description' => 'للطلاب الأسرع تقدماً',
                'type' => 'avatar',
                'icon' => '🚀',
                'price_points' => 1500,
                'price_gems' => 75,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'effects' => ['avatar' => 'rocket.png'],
                'sort_order' => 3,
            ],
            [
                'category_id' => $cosmetics->id,
                'name' => 'إطار ذهبي للملف الشخصي',
                'slug' => 'golden-profile-frame',
                'description' => 'إطار ذهبي فاخر لملفك الشخصي',
                'type' => 'profile_frame',
                'icon' => '🖼️',
                'price_points' => 3000,
                'price_gems' => 150,
                'required_level' => 15,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'effects' => ['profile_frame' => 'golden-frame.png'],
                'sort_order' => 4,
            ],
            [
                'category_id' => $cosmetics->id,
                'name' => 'إطار ماسي',
                'slug' => 'diamond-profile-frame',
                'description' => 'إطار ماسي للنخبة فقط',
                'type' => 'profile_frame',
                'icon' => '💎',
                'price_points' => 10000,
                'price_gems' => 500,
                'required_level' => 30,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'effects' => ['profile_frame' => 'diamond-frame.png'],
                'sort_order' => 5,
            ],
            [
                'category_id' => $cosmetics->id,
                'name' => 'ثيم الوضع الداكن الأنيق',
                'slug' => 'elegant-dark-theme',
                'description' => 'ثيم داكن مريح للعين',
                'type' => 'theme',
                'icon' => '🌙',
                'price_points' => 500,
                'price_gems' => 25,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'effects' => ['theme' => 'dark-elegant'],
                'sort_order' => 6,
            ],
            [
                'category_id' => $cosmetics->id,
                'name' => 'ثيم الغابة',
                'slug' => 'forest-theme',
                'description' => 'ثيم أخضر هادئ',
                'type' => 'theme',
                'icon' => '🌲',
                'price_points' => 750,
                'price_gems' => 35,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'effects' => ['theme' => 'forest'],
                'sort_order' => 7,
            ],

            // ========================================
            // Boosters (المعززات)
            // ========================================
            [
                'category_id' => $boosters->id,
                'name' => 'معزز XP (ساعة واحدة)',
                'slug' => 'xp-booster-1h',
                'description' => 'مضاعفة XP بمقدار 1.5x لمدة ساعة',
                'type' => 'xp_booster',
                'icon' => '⚡',
                'price_points' => 500,
                'price_gems' => 25,
                'duration_days' => 0.04167, // 1 hour
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => false,
                'effects' => ['xp_multiplier' => 1.5],
                'sort_order' => 10,
            ],
            [
                'category_id' => $boosters->id,
                'name' => 'معزز XP (يوم كامل)',
                'slug' => 'xp-booster-24h',
                'description' => 'مضاعفة XP بمقدار 1.5x لمدة 24 ساعة',
                'type' => 'xp_booster',
                'icon' => '⚡',
                'price_points' => 2000,
                'price_gems' => 100,
                'duration_days' => 1,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => false,
                'effects' => ['xp_multiplier' => 1.5],
                'sort_order' => 11,
            ],
            [
                'category_id' => $boosters->id,
                'name' => 'معزز XP الفائق (أسبوع)',
                'slug' => 'xp-booster-super-week',
                'description' => 'مضاعفة XP بمقدار 2x لمدة أسبوع كامل',
                'type' => 'xp_booster',
                'icon' => '⚡',
                'price_points' => 10000,
                'price_gems' => 500,
                'duration_days' => 7,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => false,
                'is_featured' => true,
                'effects' => ['xp_multiplier' => 2.0],
                'sort_order' => 12,
            ],
            [
                'category_id' => $boosters->id,
                'name' => 'معزز النقاط (ساعة واحدة)',
                'slug' => 'points-booster-1h',
                'description' => 'مضاعفة النقاط بمقدار 1.5x لمدة ساعة',
                'type' => 'points_booster',
                'icon' => '💰',
                'price_points' => 400,
                'price_gems' => 20,
                'duration_days' => 0.04167,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => false,
                'effects' => ['points_multiplier' => 1.5],
                'sort_order' => 13,
            ],
            [
                'category_id' => $boosters->id,
                'name' => 'معزز النقاط (يوم كامل)',
                'slug' => 'points-booster-24h',
                'description' => 'مضاعفة النقاط بمقدار 1.5x لمدة 24 ساعة',
                'type' => 'points_booster',
                'icon' => '💰',
                'price_points' => 1500,
                'price_gems' => 75,
                'duration_days' => 1,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => false,
                'effects' => ['points_multiplier' => 1.5],
                'sort_order' => 14,
            ],
            [
                'category_id' => $boosters->id,
                'name' => 'حماية السلسلة (مرة واحدة)',
                'slug' => 'streak-protection-single',
                'description' => 'احمي سلسلتك من الانقطاع مرة واحدة',
                'type' => 'streak_protection',
                'icon' => '🛡️',
                'price_points' => 1000,
                'price_gems' => 50,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'effects' => ['streak_protection' => 1],
                'sort_order' => 15,
            ],
            [
                'category_id' => $boosters->id,
                'name' => 'حماية السلسلة (3 مرات)',
                'slug' => 'streak-protection-triple',
                'description' => 'احمي سلسلتك من الانقطاع 3 مرات',
                'type' => 'streak_protection',
                'icon' => '🛡️',
                'price_points' => 2500,
                'price_gems' => 125,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'effects' => ['streak_protection' => 3],
                'is_featured' => true,
                'discount_percentage' => 15,
                'sort_order' => 16,
            ],

            // ========================================
            // Course Access (الوصول للكورسات)
            // ========================================
            [
                'category_id' => $courseAccess->id,
                'name' => 'وصول مبكر لكورس واحد',
                'slug' => 'early-access-single-course',
                'description' => 'افتح أي كورس مميز قبل إطلاقه الرسمي',
                'type' => 'course_unlock',
                'icon' => '🔓',
                'price_points' => 5000,
                'price_gems' => 250,
                'required_level' => 5,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'effects' => ['course_unlock' => 1],
                'sort_order' => 20,
            ],
            [
                'category_id' => $courseAccess->id,
                'name' => 'باقة الوصول الذهبية',
                'slug' => 'golden-access-pack',
                'description' => 'وصول مبكر لـ 3 كورسات مميزة',
                'type' => 'course_unlock',
                'icon' => '🔑',
                'price_points' => 12000,
                'price_gems' => 600,
                'required_level' => 10,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => false,
                'is_consumable' => true,
                'is_featured' => true,
                'discount_percentage' => 20,
                'effects' => ['course_unlock' => 3],
                'sort_order' => 21,
            ],

            // ========================================
            // Features (الميزات الخاصة)
            // ========================================
            [
                'category_id' => $features->id,
                'name' => 'إعادة محاولة اختبار',
                'slug' => 'quiz-retry',
                'description' => 'امنح نفسك فرصة إضافية لإعادة اختبار فاشل',
                'type' => 'quiz_retry',
                'icon' => '🔄',
                'price_points' => 800,
                'price_gems' => 40,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'purchase_limit' => 10,
                'effects' => ['quiz_retry' => 1],
                'sort_order' => 30,
            ],
            [
                'category_id' => $features->id,
                'name' => 'تخطي درس واحد',
                'slug' => 'skip-lesson',
                'description' => 'تخطى درساً صعباً والانتقال للتالي',
                'type' => 'lesson_skip',
                'icon' => '⏭️',
                'price_points' => 1500,
                'price_gems' => 75,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'purchase_limit' => 5,
                'effects' => ['lesson_skip' => 1],
                'sort_order' => 31,
            ],
            [
                'category_id' => $features->id,
                'name' => 'نقاط مكافأة (500)',
                'slug' => 'bonus-points-500',
                'description' => 'احصل على 500 نقطة فوراً',
                'type' => 'bonus_points',
                'icon' => '💎',
                'price_gems' => 25,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'effects' => ['bonus_points' => 500],
                'sort_order' => 32,
            ],
            [
                'category_id' => $features->id,
                'name' => 'أحجار كريمة مكافأة (50)',
                'slug' => 'bonus-gems-50',
                'description' => 'احصل على 50 حجر كريم فوراً',
                'type' => 'bonus_gems',
                'icon' => '💠',
                'price_points' => 2000,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'effects' => ['bonus_gems' => 50],
                'sort_order' => 33,
            ],
            [
                'category_id' => $features->id,
                'name' => 'استعادة السلسلة',
                'slug' => 'restore-streak',
                'description' => 'استعد سلسلتك المفقودة',
                'type' => 'restore_streak',
                'icon' => '🔥',
                'price_points' => 3000,
                'price_gems' => 150,
                'is_available' => true,
                'in_stock' => true,
                'is_stackable' => true,
                'is_consumable' => true,
                'purchase_limit' => 3,
                'effects' => ['restore_streak' => true],
                'sort_order' => 34,
            ],

            // ========================================
            // Physical Rewards (الجوائز الحقيقية)
            // ========================================
            [
                'category_id' => $physicalRewards->id,
                'name' => 'شهادة إنجاز مطبوعة',
                'slug' => 'printed-certificate',
                'description' => 'شهادة إنجاز مطبوعة على ورق فاخر مع ختم رسمي',
                'type' => 'certificate',
                'icon' => '📜',
                'price_points' => 15000,
                'price_gems' => 750,
                'required_level' => 20,
                'is_available' => true,
                'in_stock' => true,
                'stock_quantity' => 50,
                'is_stackable' => false,
                'is_consumable' => true,
                'purchase_limit' => 3,
                'effects' => ['physical_reward' => 'certificate'],
                'sort_order' => 40,
            ],
            [
                'category_id' => $physicalRewards->id,
                'name' => 'قميص المنصة الرسمي',
                'slug' => 'official-tshirt',
                'description' => 'قميص رسمي بشعار المنصة',
                'type' => 'merchandise',
                'icon' => '👕',
                'price_points' => 25000,
                'price_gems' => 1250,
                'required_level' => 30,
                'is_available' => true,
                'in_stock' => true,
                'stock_quantity' => 30,
                'is_stackable' => false,
                'is_consumable' => true,
                'purchase_limit' => 2,
                'is_featured' => true,
                'effects' => ['physical_reward' => 'tshirt'],
                'sort_order' => 41,
            ],
            [
                'category_id' => $physicalRewards->id,
                'name' => 'كوب القهوة المميز',
                'slug' => 'premium-coffee-mug',
                'description' => 'كوب قهوة فاخر مع اقتباس تحفيزي',
                'type' => 'merchandise',
                'icon' => '☕',
                'price_points' => 8000,
                'price_gems' => 400,
                'required_level' => 15,
                'is_available' => true,
                'in_stock' => true,
                'stock_quantity' => 100,
                'is_stackable' => false,
                'is_consumable' => true,
                'purchase_limit' => 3,
                'effects' => ['physical_reward' => 'mug'],
                'sort_order' => 42,
            ],
            [
                'category_id' => $physicalRewards->id,
                'name' => 'دفتر ملاحظات فاخر',
                'slug' => 'premium-notebook',
                'description' => 'دفتر ملاحظات جلدي بشعار المنصة',
                'type' => 'merchandise',
                'icon' => '📓',
                'price_points' => 5000,
                'price_gems' => 250,
                'required_level' => 10,
                'is_available' => true,
                'in_stock' => true,
                'stock_quantity' => 150,
                'is_stackable' => false,
                'is_consumable' => true,
                'effects' => ['physical_reward' => 'notebook'],
                'sort_order' => 43,
            ],
            [
                'category_id' => $physicalRewards->id,
                'name' => 'وسام التميز الذهبي',
                'slug' => 'golden-medal',
                'description' => 'وسام ذهبي حقيقي للمتميزين',
                'type' => 'medal',
                'icon' => '🏅',
                'price_points' => 50000,
                'price_gems' => 2500,
                'required_level' => 50,
                'is_available' => true,
                'in_stock' => true,
                'stock_quantity' => 10,
                'is_stackable' => false,
                'is_consumable' => true,
                'purchase_limit' => 1,
                'is_featured' => true,
                'effects' => ['physical_reward' => 'medal'],
                'sort_order' => 44,
            ],
        ];

        foreach ($items as $item) {
            ShopItem::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }

        $this->command->info('✅ تم إنشاء ' . count($items) . ' عنصر في المتجر بنجاح!');
    }
}
