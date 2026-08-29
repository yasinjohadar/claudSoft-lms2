<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\GamificationNotification;
use App\Models\GroupNotification;
use Illuminate\Support\Carbon;

class AdminMessageController extends Controller
{
    /**
     * Global archive of every message sent via the per-group notifications feature,
     * aggregated across all groups. Read-only view over existing data — no new table.
     */
    public function index()
    {
        $notifications = GroupNotification::with('group:id,name')
            ->where('is_message', true)
            ->latest()
            ->paginate(20);

        $notifications->getCollection()->transform(function (GroupNotification $notification) {
            $notification->setAttribute('read_count', $notification->readCount());

            return $notification;
        });

        $totalMessages = GroupNotification::where('is_message', true)->count();
        $totalRecipients = (int) GroupNotification::where('is_message', true)->sum('recipients_count');

        $totalCopies = GamificationNotification::messages()->count();
        $readCopies = GamificationNotification::messages()->where('is_read', true)->count();
        $readRate = $totalCopies > 0 ? round(($readCopies / $totalCopies) * 100, 1) : 0;

        $groups = CourseGroup::orderBy('name')->get(['id', 'name']);

        return view('admin.pages.messages.index', [
            'notifications' => $notifications,
            'totalMessages' => $totalMessages,
            'totalRecipients' => $totalRecipients,
            'readRate' => $readRate,
            'groups' => $groups,
        ]);
    }

    /**
     * Lightweight feed for the topbar "الرسائل" dropdown.
     */
    public function latest()
    {
        $items = GroupNotification::with('group:id,name')
            ->where('is_message', true)
            ->latest()
            ->take(5)
            ->get()
            ->map(function (GroupNotification $notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'group_name' => $notification->group?->name ?? '—',
                    'type' => $notification->type,
                    'time_ago' => $notification->created_at->locale('ar')->diffForHumans(),
                    'url' => route('admin.messages.index'),
                ];
            });

        $recentCount = GroupNotification::where('is_message', true)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return response()->json([
            'success' => true,
            'recent_count' => $recentCount,
            'messages' => $items,
        ]);
    }
}
