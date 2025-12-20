<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FriendshipService
{
    /**
     * إرسال طلب صداقة
     */
    public function sendFriendRequest(User $sender, User $recipient): ?Friendship
    {
        try {
            // التحقق من عدم إرسال طلب لنفسه
            if ($sender->id === $recipient->id) {
                Log::warning('User tried to send friend request to self', [
                    'user_id' => $sender->id,
                ]);
                return null;
            }

            // التحقق من عدم وجود طلب مسبق
            $existing = Friendship::where(function($q) use ($sender, $recipient) {
                $q->where('user_id', $sender->id)
                  ->where('friend_id', $recipient->id);
            })->orWhere(function($q) use ($sender, $recipient) {
                $q->where('user_id', $recipient->id)
                  ->where('friend_id', $sender->id);
            })->first();

            if ($existing) {
                Log::warning('Friendship already exists', [
                    'sender_id' => $sender->id,
                    'recipient_id' => $recipient->id,
                    'status' => $existing->status,
                ]);
                return null;
            }

            // إنشاء طلب الصداقة
            $friendship = Friendship::create([
                'user_id' => $sender->id,
                'friend_id' => $recipient->id,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            Log::info('Friend request sent', [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
            ]);

            return $friendship;

        } catch (\Exception $e) {
            Log::error('Failed to send friend request', [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * قبول طلب صداقة
     */
    public function acceptFriendRequest(User $user, Friendship $friendship): bool
    {
        try {
            // التحقق من أن المستخدم هو المستلم
            if ($friendship->friend_id !== $user->id) {
                Log::warning('User not authorized to accept request', [
                    'user_id' => $user->id,
                    'friendship_id' => $friendship->id,
                ]);
                return false;
            }

            // التحقق من حالة الطلب
            if ($friendship->status !== 'pending') {
                Log::warning('Friendship not pending', [
                    'friendship_id' => $friendship->id,
                    'status' => $friendship->status,
                ]);
                return false;
            }

            // قبول الطلب
            $friendship->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // تحديث إحصائيات المستخدمين
            $user->stats->increment('total_friends');
            $friendship->user->stats->increment('total_friends');

            Log::info('Friend request accepted', [
                'friendship_id' => $friendship->id,
                'user_id' => $user->id,
                'friend_id' => $friendship->user_id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to accept friend request', [
                'friendship_id' => $friendship->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * رفض طلب صداقة
     */
    public function rejectFriendRequest(User $user, Friendship $friendship): bool
    {
        try {
            // التحقق من أن المستخدم هو المستلم
            if ($friendship->friend_id !== $user->id) {
                return false;
            }

            // رفض الطلب
            $friendship->update([
                'status' => 'rejected',
            ]);

            Log::info('Friend request rejected', [
                'friendship_id' => $friendship->id,
                'user_id' => $user->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to reject friend request', [
                'friendship_id' => $friendship->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * إلغاء صداقة
     */
    public function unfriend(User $user, User $friend): bool
    {
        try {
            $friendship = Friendship::where(function($q) use ($user, $friend) {
                $q->where('user_id', $user->id)
                  ->where('friend_id', $friend->id);
            })->orWhere(function($q) use ($user, $friend) {
                $q->where('user_id', $friend->id)
                  ->where('friend_id', $user->id);
            })->where('status', 'accepted')
              ->first();

            if (!$friendship) {
                return false;
            }

            $friendship->delete();

            // تحديث الإحصائيات
            $user->stats->decrement('total_friends');
            $friend->stats->decrement('total_friends');

            Log::info('Friendship removed', [
                'user_id' => $user->id,
                'friend_id' => $friend->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to unfriend', [
                'user_id' => $user->id,
                'friend_id' => $friend->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * إلغاء طلب صداقة معلق
     */
    public function cancelFriendRequest(User $user, Friendship $friendship): bool
    {
        try {
            // التحقق من أن المستخدم هو المرسل
            if ($friendship->user_id !== $user->id) {
                return false;
            }

            // التحقق من حالة الطلب
            if ($friendship->status !== 'pending') {
                return false;
            }

            $friendship->delete();

            Log::info('Friend request cancelled', [
                'friendship_id' => $friendship->id,
                'user_id' => $user->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to cancel friend request', [
                'friendship_id' => $friendship->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * الحصول على قائمة الأصدقاء
     */
    public function getFriends(User $user)
    {
        $friendIds = Friendship::where('status', 'accepted')
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('friend_id', $user->id);
            })
            ->get()
            ->map(function($friendship) use ($user) {
                return $friendship->user_id === $user->id
                    ? $friendship->friend_id
                    : $friendship->user_id;
            });

        return User::whereIn('id', $friendIds)
            ->where('is_active', true)
            ->select('id', 'name', 'email', 'avatar', 'created_at')
            ->with('stats:user_id,current_level,total_points,total_badges')
            ->get();
    }

    /**
     * الحصول على طلبات الصداقة الواردة
     */
    public function getPendingRequests(User $user)
    {
        return Friendship::where('friend_id', $user->id)
            ->where('status', 'pending')
            ->with('user:id,name,email,avatar')
            ->latest('requested_at')
            ->get();
    }

    /**
     * الحصول على طلبات الصداقة المرسلة
     */
    public function getSentRequests(User $user)
    {
        return Friendship::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('friend:id,name,email,avatar')
            ->latest('requested_at')
            ->get();
    }

    /**
     * التحقق من وجود صداقة
     */
    public function areFriends(User $user1, User $user2): bool
    {
        return Friendship::where('status', 'accepted')
            ->where(function($q) use ($user1, $user2) {
                $q->where(function($q2) use ($user1, $user2) {
                    $q2->where('user_id', $user1->id)
                       ->where('friend_id', $user2->id);
                })->orWhere(function($q2) use ($user1, $user2) {
                    $q2->where('user_id', $user2->id)
                       ->where('friend_id', $user1->id);
                });
            })
            ->exists();
    }

    /**
     * الحصول على حالة الصداقة
     */
    public function getFriendshipStatus(User $user, User $otherUser): string
    {
        if ($user->id === $otherUser->id) {
            return 'self';
        }

        $friendship = Friendship::where(function($q) use ($user, $otherUser) {
            $q->where(function($q2) use ($user, $otherUser) {
                $q2->where('user_id', $user->id)
                   ->where('friend_id', $otherUser->id);
            })->orWhere(function($q2) use ($user, $otherUser) {
                $q2->where('user_id', $otherUser->id)
                   ->where('friend_id', $user->id);
            });
        })->first();

        if (!$friendship) {
            return 'none';
        }

        if ($friendship->status === 'accepted') {
            return 'friends';
        }

        if ($friendship->status === 'pending') {
            if ($friendship->user_id === $user->id) {
                return 'request_sent';
            } else {
                return 'request_received';
            }
        }

        return $friendship->status;
    }

    /**
     * اقتراح أصدقاء
     */
    public function suggestFriends(User $user, int $limit = 10)
    {
        // الحصول على أصدقاء الأصدقاء
        $currentFriendIds = $this->getFriends($user)->pluck('id');

        $friendsOfFriends = Friendship::where('status', 'accepted')
            ->whereIn('user_id', $currentFriendIds)
            ->orWhereIn('friend_id', $currentFriendIds)
            ->get()
            ->map(function($friendship) use ($currentFriendIds) {
                return $currentFriendIds->contains($friendship->user_id)
                    ? $friendship->friend_id
                    : $friendship->user_id;
            })
            ->filter(fn($id) => $id !== $user->id && !$currentFriendIds->contains($id))
            ->unique();

        // الحصول على الطلاب بنفس المستوى
        $sameLevelUsers = User::where('role', 'student')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $currentFriendIds)
            ->whereHas('stats', function($q) use ($user) {
                $q->whereBetween('current_level', [
                    $user->stats->current_level - 5,
                    $user->stats->current_level + 5
                ]);
            })
            ->pluck('id');

        // دمج الاقتراحات
        $suggestedIds = $friendsOfFriends->merge($sameLevelUsers)
            ->unique()
            ->take($limit * 2);

        return User::whereIn('id', $suggestedIds)
            ->select('id', 'name', 'email', 'avatar')
            ->with('stats:user_id,current_level,total_points,total_badges')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * إحصائيات الصداقة
     */
    public function getFriendshipStats(User $user): array
    {
        $totalFriends = $this->getFriends($user)->count();
        $pendingRequests = $this->getPendingRequests($user)->count();
        $sentRequests = $this->getSentRequests($user)->count();

        return [
            'total_friends' => $totalFriends,
            'pending_requests' => $pendingRequests,
            'sent_requests' => $sentRequests,
        ];
    }

    /**
     * البحث عن مستخدمين للإضافة
     */
    public function searchUsers(User $currentUser, string $query, int $limit = 20)
    {
        $currentFriendIds = $this->getFriends($currentUser)->pluck('id');

        return User::where('role', 'student')
            ->where('is_active', true)
            ->where('id', '!=', $currentUser->id)
            ->whereNotIn('id', $currentFriendIds)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email', 'avatar')
            ->with('stats:user_id,current_level,total_points,total_badges')
            ->limit($limit)
            ->get();
    }
}
