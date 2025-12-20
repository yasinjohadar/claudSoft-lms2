<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\SocialActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SocialActivityService
{
    /**
     * إنشاء نشاط اجتماعي
     */
    public function createActivity(
        User $user,
        string $type,
        string $description,
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?array $metadata = null
    ): ?SocialActivity {
        try {
            $activity = SocialActivity::create([
                'user_id' => $user->id,
                'type' => $type,
                'description' => $description,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'metadata' => $metadata,
                'is_public' => true,
                'created_at' => now(),
            ]);

            Log::info('Social activity created', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'type' => $type,
            ]);

            return $activity;

        } catch (\Exception $e) {
            Log::error('Failed to create social activity', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * إعجاب بنشاط
     */
    public function likeActivity(User $user, SocialActivity $activity): bool
    {
        try {
            // التحقق من عدم الإعجاب مسبقاً
            if ($activity->likes()->where('user_id', $user->id)->exists()) {
                return false;
            }

            $activity->likes()->create([
                'user_id' => $user->id,
            ]);

            $activity->increment('likes_count');

            Log::info('Activity liked', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to like activity', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * إلغاء الإعجاب بنشاط
     */
    public function unlikeActivity(User $user, SocialActivity $activity): bool
    {
        try {
            $like = $activity->likes()->where('user_id', $user->id)->first();

            if (!$like) {
                return false;
            }

            $like->delete();
            $activity->decrement('likes_count');

            Log::info('Activity unliked', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to unlike activity', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * إضافة تعليق على نشاط
     */
    public function commentOnActivity(User $user, SocialActivity $activity, string $content): bool
    {
        try {
            $activity->comments()->create([
                'user_id' => $user->id,
                'content' => $content,
            ]);

            $activity->increment('comments_count');

            Log::info('Comment added to activity', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to comment on activity', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * حذف تعليق
     */
    public function deleteComment(User $user, int $commentId): bool
    {
        try {
            $comment = DB::table('social_activity_comments')
                ->where('id', $commentId)
                ->where('user_id', $user->id)
                ->first();

            if (!$comment) {
                return false;
            }

            DB::table('social_activity_comments')->where('id', $commentId)->delete();

            $activity = SocialActivity::find($comment->social_activity_id);
            if ($activity) {
                $activity->decrement('comments_count');
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete comment', [
                'comment_id' => $commentId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * الحصول على آخر الأنشطة للأصدقاء
     */
    public function getFriendsActivities(User $user, int $limit = 20)
    {
        $friendshipService = app(FriendshipService::class);
        $friendIds = $friendshipService->getFriends($user)->pluck('id');

        return SocialActivity::whereIn('user_id', $friendIds)
            ->where('is_public', true)
            ->with(['user:id,name,email,avatar', 'likes', 'comments.user:id,name,avatar'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function($activity) use ($user) {
                $activity->is_liked_by_me = $activity->likes()
                    ->where('user_id', $user->id)
                    ->exists();
                return $activity;
            });
    }

    /**
     * الحصول على أنشطة مستخدم معين
     */
    public function getUserActivities(User $targetUser, ?User $viewer = null, int $limit = 20)
    {
        $query = SocialActivity::where('user_id', $targetUser->id);

        // إذا كان المشاهد ليس صديق، عرض الأنشطة العامة فقط
        if ($viewer && $viewer->id !== $targetUser->id) {
            $friendshipService = app(FriendshipService::class);
            if (!$friendshipService->areFriends($viewer, $targetUser)) {
                $query->where('is_public', true);
            }
        }

        return $query->with(['user:id,name,email,avatar', 'likes', 'comments.user:id,name,avatar'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function($activity) use ($viewer) {
                if ($viewer) {
                    $activity->is_liked_by_me = $activity->likes()
                        ->where('user_id', $viewer->id)
                        ->exists();
                }
                return $activity;
            });
    }

    /**
     * نشر إنجاز
     */
    public function shareAchievement(User $user, int $achievementId): ?SocialActivity
    {
        $achievement = \App\Models\Achievement::find($achievementId);

        if (!$achievement) {
            return null;
        }

        return $this->createActivity(
            $user,
            'achievement_unlocked',
            "حصل على إنجاز: {$achievement->name}",
            'App\Models\Achievement',
            $achievementId,
            [
                'achievement_name' => $achievement->name,
                'achievement_icon' => $achievement->icon,
                'achievement_tier' => $achievement->tier,
            ]
        );
    }

    /**
     * نشر شارة
     */
    public function shareBadge(User $user, int $badgeId): ?SocialActivity
    {
        $badge = \App\Models\Badge::find($badgeId);

        if (!$badge) {
            return null;
        }

        return $this->createActivity(
            $user,
            'badge_earned',
            "حصل على شارة: {$badge->name}",
            'App\Models\Badge',
            $badgeId,
            [
                'badge_name' => $badge->name,
                'badge_icon' => $badge->icon,
                'badge_rarity' => $badge->rarity,
            ]
        );
    }

    /**
     * نشر مستوى جديد
     */
    public function shareLevelUp(User $user, int $newLevel): ?SocialActivity
    {
        return $this->createActivity(
            $user,
            'level_up',
            "وصل للمستوى {$newLevel}!",
            null,
            null,
            [
                'new_level' => $newLevel,
            ]
        );
    }

    /**
     * نشر إكمال كورس
     */
    public function shareCourseCompletion(User $user, int $courseId): ?SocialActivity
    {
        $course = \App\Models\Course::find($courseId);

        if (!$course) {
            return null;
        }

        return $this->createActivity(
            $user,
            'course_completed',
            "أكمل كورس: {$course->title}",
            'App\Models\Course',
            $courseId,
            [
                'course_title' => $course->title,
            ]
        );
    }

    /**
     * الحصول على إحصائيات الأنشطة الاجتماعية
     */
    public function getUserSocialStats(User $user): array
    {
        $totalActivities = SocialActivity::where('user_id', $user->id)->count();

        $totalLikes = SocialActivity::where('user_id', $user->id)->sum('likes_count');

        $totalComments = SocialActivity::where('user_id', $user->id)->sum('comments_count');

        $activitiesByType = SocialActivity::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $mostLikedActivity = SocialActivity::where('user_id', $user->id)
            ->orderByDesc('likes_count')
            ->first();

        return [
            'total_activities' => $totalActivities,
            'total_likes_received' => $totalLikes,
            'total_comments_received' => $totalComments,
            'activities_by_type' => $activitiesByType,
            'most_liked_activity' => $mostLikedActivity,
        ];
    }

    /**
     * حذف نشاط
     */
    public function deleteActivity(User $user, SocialActivity $activity): bool
    {
        try {
            // التحقق من الملكية
            if ($activity->user_id !== $user->id) {
                return false;
            }

            $activity->delete();

            Log::info('Activity deleted', [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete activity', [
                'activity_id' => $activity->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
