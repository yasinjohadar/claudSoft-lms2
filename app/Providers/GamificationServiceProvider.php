<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

// Services
use App\Services\Gamification\PointsService;
use App\Services\Gamification\XPService;
use App\Services\Gamification\LevelService;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\AchievementService;
use App\Services\Gamification\LeaderboardService;
use App\Services\Gamification\ChallengeService;
use App\Services\Gamification\ShopService;
use App\Services\Gamification\InventoryService;
use App\Services\Gamification\BoosterService;
use App\Services\Gamification\FriendshipService;
use App\Services\Gamification\CompetitionService;
use App\Services\Gamification\SocialActivityService;
use App\Services\Gamification\NotificationService;
use App\Services\Gamification\AnalyticsService;

// Events
use App\Events\Gamification\BadgeEarned;
use App\Events\Gamification\AchievementUnlocked;
use App\Events\Gamification\LevelUp;
use App\Events\Gamification\PointsEarned;
use App\Events\Gamification\XPEarned;
use App\Events\Gamification\StreakUpdated;
use App\Events\Gamification\ChallengeCompleted;
use App\Events\Gamification\LeaderboardRankChanged;

// Listeners
use App\Listeners\Gamification\SendNotificationListener;
use App\Listeners\Gamification\CheckBadgesListener;
use App\Listeners\Gamification\CheckAchievementsListener;
use App\Listeners\Gamification\UpdateLeaderboardListener;
use App\Listeners\Gamification\UpdateChallengeProgressListener;
use App\Listeners\Gamification\SocialActivityListener;
use App\Listeners\Gamification\UpdateCompetitionListener;

class GamificationServiceProvider extends ServiceProvider
{
    /**
     * تسجيل الخدمات
     */
    public function register(): void
    {
        // تسجيل الخدمات كـ Singletons للأداء
        $this->app->singleton(PointsService::class);
        $this->app->singleton(XPService::class);
        $this->app->singleton(LevelService::class);
        $this->app->singleton(BadgeService::class);
        $this->app->singleton(AchievementService::class);
        $this->app->singleton(LeaderboardService::class);
        $this->app->singleton(ChallengeService::class);
        $this->app->singleton(ShopService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(BoosterService::class);
        $this->app->singleton(FriendshipService::class);
        $this->app->singleton(CompetitionService::class);
        $this->app->singleton(SocialActivityService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(AnalyticsService::class);
    }

    /**
     * تشغيل الخدمات
     */
    public function boot(): void
    {
        $this->registerEvents();
    }

    /**
     * تسجيل الأحداث والـ Listeners
     */
    protected function registerEvents(): void
    {
        // عند كسب نقاط
        Event::listen(PointsEarned::class, [
            SendNotificationListener::class,
            CheckBadgesListener::class,
            CheckAchievementsListener::class,
            UpdateLeaderboardListener::class,
            UpdateChallengeProgressListener::class,
            UpdateCompetitionListener::class,
        ]);

        // عند كسب XP
        Event::listen(XPEarned::class, [
            CheckBadgesListener::class,
            CheckAchievementsListener::class,
            UpdateLeaderboardListener::class,
            UpdateChallengeProgressListener::class,
            UpdateCompetitionListener::class,
        ]);

        // عند الترقية لمستوى جديد
        Event::listen(LevelUp::class, [
            SendNotificationListener::class,
            CheckBadgesListener::class,
            CheckAchievementsListener::class,
            SocialActivityListener::class,
        ]);

        // عند الحصول على شارة
        Event::listen(BadgeEarned::class, [
            SendNotificationListener::class,
            CheckAchievementsListener::class,
            SocialActivityListener::class,
        ]);

        // عند إكمال إنجاز
        Event::listen(AchievementUnlocked::class, [
            SendNotificationListener::class,
            SocialActivityListener::class,
        ]);

        // عند تحديث السلسلة
        Event::listen(StreakUpdated::class, [
            SendNotificationListener::class,
            CheckBadgesListener::class,
            CheckAchievementsListener::class,
            UpdateChallengeProgressListener::class,
        ]);

        // عند إكمال تحدي
        Event::listen(ChallengeCompleted::class, [
            SendNotificationListener::class,
            SocialActivityListener::class,
        ]);

        // عند تغيير الترتيب في لوحة المتصدرين
        Event::listen(LeaderboardRankChanged::class, [
            SendNotificationListener::class,
        ]);

        // ربط الأحداث الأساسية مع Listeners
        Event::listen(\App\Events\QuizCompleted::class, \App\Listeners\Gamification\QuizCompletedListener::class);
        Event::listen(\App\Events\LessonCompleted::class, \App\Listeners\Gamification\LessonCompletedListener::class);
        Event::listen(\App\Events\AssignmentSubmitted::class, \App\Listeners\Gamification\AssignmentSubmittedListener::class);
        Event::listen(\App\Events\CourseCompleted::class, \App\Listeners\Gamification\CourseCompletedListener::class);
        Event::listen(\App\Events\VideoWatched::class, \App\Listeners\Gamification\VideoWatchedListener::class);
        Event::listen(\Illuminate\Auth\Events\Login::class, \App\Listeners\Gamification\UserLoginListener::class);
    }
}
