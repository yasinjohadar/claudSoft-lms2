<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Friendship;
use App\Services\Gamification\FriendshipService;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    protected FriendshipService $friendshipService;

    public function __construct(FriendshipService $friendshipService)
    {
        $this->friendshipService = $friendshipService;
    }

    /**
     * عرض قائمة الأصدقاء
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $friends = $this->friendshipService->getFriends($user);

        $stats = $this->friendshipService->getFriendshipStats($user);

        return response()->json([
            'success' => true,
            'friends' => $friends,
            'stats' => $stats,
        ]);
    }

    /**
     * إرسال طلب صداقة
     */
    public function sendRequest(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $friend = User::findOrFail($validated['friend_id']);

        $friendship = $this->friendshipService->sendFriendRequest($user, $friend);

        if (!$friendship) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إرسال الطلب. قد يكون هناك طلب مسبق أو أنتما أصدقاء بالفعل.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الصداقة بنجاح!',
            'friendship' => $friendship,
        ]);
    }

    /**
     * قبول طلب صداقة
     */
    public function acceptRequest(Request $request, Friendship $friendship)
    {
        $user = $request->user();

        $success = $this->friendshipService->acceptFriendRequest($user, $friendship);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل قبول الطلب.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم قبول طلب الصداقة! 🎉',
            'friendship' => $friendship->fresh(['user', 'friend']),
        ]);
    }

    /**
     * رفض طلب صداقة
     */
    public function rejectRequest(Request $request, Friendship $friendship)
    {
        $user = $request->user();

        $success = $this->friendshipService->rejectFriendRequest($user, $friendship);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل رفض الطلب.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم رفض طلب الصداقة.',
        ]);
    }

    /**
     * إلغاء صداقة
     */
    public function unfriend(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $friend = User::findOrFail($validated['friend_id']);

        $success = $this->friendshipService->unfriend($user, $friend);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء الصداقة.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الصداقة.',
        ]);
    }

    /**
     * إلغاء طلب صداقة معلق
     */
    public function cancelRequest(Request $request, Friendship $friendship)
    {
        $user = $request->user();

        $success = $this->friendshipService->cancelFriendRequest($user, $friendship);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء الطلب.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء طلب الصداقة.',
        ]);
    }

    /**
     * عرض طلبات الصداقة الواردة
     */
    public function pendingRequests(Request $request)
    {
        $user = $request->user();

        $requests = $this->friendshipService->getPendingRequests($user);

        return response()->json([
            'success' => true,
            'pending_requests' => $requests,
        ]);
    }

    /**
     * عرض طلبات الصداقة المرسلة
     */
    public function sentRequests(Request $request)
    {
        $user = $request->user();

        $requests = $this->friendshipService->getSentRequests($user);

        return response()->json([
            'success' => true,
            'sent_requests' => $requests,
        ]);
    }

    /**
     * اقتراح أصدقاء
     */
    public function suggestions(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 10);

        $suggestions = $this->friendshipService->suggestFriends($user, $limit);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * البحث عن مستخدمين
     */
    public function search(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $results = $this->friendshipService->searchUsers($user, $validated['query']);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * الحصول على حالة الصداقة مع مستخدم
     */
    public function status(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $otherUser = User::findOrFail($validated['user_id']);

        $status = $this->friendshipService->getFriendshipStatus($user, $otherUser);

        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }
}
