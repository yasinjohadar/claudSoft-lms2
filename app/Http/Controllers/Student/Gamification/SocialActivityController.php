<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\SocialActivity;
use App\Models\User;
use App\Services\Gamification\SocialActivityService;
use Illuminate\Http\Request;

class SocialActivityController extends Controller
{
    protected SocialActivityService $socialActivityService;

    public function __construct(SocialActivityService $socialActivityService)
    {
        $this->socialActivityService = $socialActivityService;
    }

    /**
     * عرض آخر الأنشطة (News Feed)
     */
    public function feed(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 20);

        $activities = $this->socialActivityService->getFriendsActivities($user, $limit);

        return response()->json([
            'success' => true,
            'activities' => $activities,
        ]);
    }

    /**
     * عرض أنشطتي
     */
    public function myActivities(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 20);

        $activities = $this->socialActivityService->getUserActivities($user, $user, $limit);

        $stats = $this->socialActivityService->getUserSocialStats($user);

        return response()->json([
            'success' => true,
            'activities' => $activities,
            'stats' => $stats,
        ]);
    }

    /**
     * عرض أنشطة مستخدم آخر
     */
    public function userActivities(Request $request, User $targetUser)
    {
        $viewer = $request->user();
        $limit = $request->input('limit', 20);

        $activities = $this->socialActivityService->getUserActivities($targetUser, $viewer, $limit);

        return response()->json([
            'success' => true,
            'activities' => $activities,
        ]);
    }

    /**
     * إعجاب بنشاط
     */
    public function like(Request $request, SocialActivity $activity)
    {
        $user = $request->user();

        $success = $this->socialActivityService->likeActivity($user, $activity);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'لقد أعجبت بهذا النشاط مسبقاً.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم الإعجاب بالنشاط!',
            'activity' => $activity->fresh(),
        ]);
    }

    /**
     * إلغاء الإعجاب
     */
    public function unlike(Request $request, SocialActivity $activity)
    {
        $user = $request->user();

        $success = $this->socialActivityService->unlikeActivity($user, $activity);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'لم تعجب بهذا النشاط.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الإعجاب.',
            'activity' => $activity->fresh(),
        ]);
    }

    /**
     * إضافة تعليق
     */
    public function comment(Request $request, SocialActivity $activity)
    {
        $user = $request->user();

        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $success = $this->socialActivityService->commentOnActivity($user, $activity, $validated['content']);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إضافة التعليق.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التعليق!',
            'activity' => $activity->fresh(['comments.user']),
        ]);
    }

    /**
     * حذف تعليق
     */
    public function deleteComment(Request $request, int $commentId)
    {
        $user = $request->user();

        $success = $this->socialActivityService->deleteComment($user, $commentId);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف التعليق.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التعليق.',
        ]);
    }

    /**
     * مشاركة إنجاز
     */
    public function shareAchievement(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'achievement_id' => 'required|exists:achievements,id',
        ]);

        $activity = $this->socialActivityService->shareAchievement($user, $validated['achievement_id']);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'فشل مشاركة الإنجاز.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم مشاركة إنجازك! 🎉',
            'activity' => $activity,
        ]);
    }

    /**
     * مشاركة شارة
     */
    public function shareBadge(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'badge_id' => 'required|exists:badges,id',
        ]);

        $activity = $this->socialActivityService->shareBadge($user, $validated['badge_id']);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'فشل مشاركة الشارة.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم مشاركة شارتك! 🏅',
            'activity' => $activity,
        ]);
    }

    /**
     * حذف نشاط
     */
    public function destroy(Request $request, SocialActivity $activity)
    {
        $user = $request->user();

        $success = $this->socialActivityService->deleteActivity($user, $activity);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف النشاط.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف النشاط.',
        ]);
    }
}
