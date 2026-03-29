<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 بدء عملية ملء قاعدة البيانات...');
        $this->command->info('');

        // 1. تشغيل seeder الجنسيات
        $this->command->info('📍 المرحلة 1: إضافة الجنسيات العربية');
        $this->call(NationalitySeeder::class);
        $this->command->info('');

        // 2. تشغيل seeder الأدوار والصلاحيات
        $this->command->info('📍 المرحلة 2: إنشاء الأدوار والصلاحيات');
        $this->call(RolePermissionSeeder::class);
        $this->command->info('');

        // 3. تشغيل seeder المستخدمين
        $this->command->info('📍 المرحلة 3: إنشاء المستخدمين التجريبيين');
        $this->call(UserSeeder::class);
        $this->command->info('');

        // 4. تشغيل seeder تصنيفات الكورسات
        $this->command->info('📍 المرحلة 4: إضافة تصنيفات الكورسات التقنية');
        $this->call(CourseCategorySeeder::class);
        $this->command->info('');

        // 5. تشغيل seeder الكورسات
        $this->command->info('📍 المرحلة 5: إضافة 20 كورساً تقنياً');
        $this->call(CourseSeeder::class);
        $this->command->info('');

        // 6. تشغيل seeder الطلاب
        $this->command->info('📍 المرحلة 6: إضافة 20 طالباً');
        $this->call(StudentSeeder::class);
        $this->command->info('');

        // 7. تشغيل seeder الدروس
        $this->command->info('📍 المرحلة 7: إضافة 50 درساً لكورس Laravel');
        $this->call(LessonSeeder::class);
        $this->command->info('');

        // 8. تشغيل seeder الفيديوهات
        $this->command->info('📍 المرحلة 8: إضافة 60 فيديو لكورس HTML & CSS');
        $this->call(VideoSeeder::class);
        $this->command->info('');

        // 9. تشغيل seeder أنواع الأسئلة
        $this->command->info('📍 المرحلة 9: إضافة أنواع الأسئلة');
        $this->call(QuestionTypeSeeder::class);
        $this->command->info('');

        // 10. تشغيل seeder بنك الأسئلة لكورس HTML & CSS
        $this->command->info('📍 المرحلة 10: إضافة 20 سؤالاً لكورس HTML & CSS');
        $this->call(HtmlCssQuestionBankSeeder::class);
        $this->command->info('');

        // ================== نظام الـ Gamification ==================
        $this->command->info('🎮 بدء إعداد نظام الـ Gamification...');
        $this->command->info('');

        // 11. المستويات
        $this->command->info('📍 المرحلة 11: إضافة المستويات');
        $this->call(LevelSeeder::class);
        $this->command->info('');

        // 12. الشارات
        $this->command->info('📍 المرحلة 12: إضافة الشارات');
        $this->call(BadgeSeeder::class);
        $this->command->info('');

        // 13. الإنجازات
        $this->command->info('📍 المرحلة 13: إضافة الإنجازات');
        $this->call(AchievementSeeder::class);
        $this->command->info('');

        // 14. لوحات المتصدرين
        $this->command->info('📍 المرحلة 14: إضافة لوحات المتصدرين');
        $this->call(LeaderboardSeeder::class);
        $this->command->info('');

        // 15. التحديات
        $this->command->info('📍 المرحلة 15: إضافة التحديات');
        $this->call(ChallengeSeeder::class);
        $this->command->info('');

        // 16. فئات المتجر
        $this->command->info('📍 المرحلة 16: إضافة فئات المتجر');
        $this->call(ShopCategorySeeder::class);
        $this->command->info('');

        // 17. عناصر المتجر
        $this->command->info('📍 المرحلة 17: إضافة عناصر المتجر');
        $this->call(ShopItemSeeder::class);
        $this->command->info('');

        // 18. أنواع الإشعارات
        $this->command->info('📍 المرحلة 18: إضافة أنواع الإشعارات');
        $this->call(NotificationTypeSeeder::class);
        $this->command->info('');

        // ================== نظام n8n Webhooks ==================
        $this->command->info('🔗 بدء إعداد نظام n8n Webhooks...');
        $this->command->info('');

        // 19. معالجات Incoming Webhooks
        $this->command->info('📍 المرحلة 19: إضافة معالجات n8n Incoming Webhooks');
        $this->call(N8nIncomingWebhookHandlerSeeder::class);
        $this->command->info('');

        // 20. نقاط النهاية للـ Outgoing Webhooks
        $this->command->info('📍 المرحلة 20: إضافة نقاط نهاية n8n Outgoing Webhooks');
        $this->call(N8nWebhookEndpointSeeder::class);
        $this->command->info('');

        // 21. التوثيق (أقسام + صفحات تجريبية)
        $this->command->info('📍 المرحلة 21: أقسام وصفحات التوثيق التجريبية');
        $this->call(DocumentationSeeder::class);
        $this->command->info('');

        $this->command->info('✨ تم إكمال عملية ملء قاعدة البيانات بنجاح!');
        $this->command->info('📊 الإحصائيات النهائية:');
        $this->command->info('   - 20 كورس تقني');
        $this->command->info('   - 20 طالب');
        $this->command->info('   - 50 درس');
        $this->command->info('   - 60 فيديو');
        $this->command->info('   - 20 سؤال');
        $this->command->info('');
        $this->command->info('🎮 نظام الـ Gamification:');
        $this->command->info('   - 50 مستوى');
        $this->command->info('   - 32 شارة');
        $this->command->info('   - 27 إنجاز');
        $this->command->info('   - 7 لوحات متصدرين');
        $this->command->info('   - 27 تحدي');
        $this->command->info('   - 5 فئات متجر');
        $this->command->info('   - 30 عنصر متجر');
        $this->command->info('   - 17 نوع إشعار');
        $this->command->info('');
        $this->command->info('🔗 نظام n8n Integration:');
        $this->command->info('   - 8 معالجات Incoming Webhooks');
        $this->command->info('   - 5 نقاط نهاية Outgoing Webhooks');
    }
}
