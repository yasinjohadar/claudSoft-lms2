<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Models\User;
use App\Models\SessionActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSessionController extends Controller
{
    /**
     * Display a listing of all user sessions.
     */
    public function index(Request $request)
    {
        try {
            $query = UserSession::with(['user', 'activities'])
                ->withCount('activities')
                ->latest('started_at');

            // Filter by user
            if ($request->filled('user_id')) {
                $query->byUser($request->user_id);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by device type
            if ($request->filled('device_type')) {
                $query->byDeviceType($request->device_type);
            }

            // Filter by connection type
            if ($request->filled('connection_type')) {
                $query->byConnectionType($request->connection_type);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $dateTo = $request->filled('date_to') 
                    ? Carbon::parse($request->date_to)->endOfDay() 
                    : now()->endOfDay();
                $query->byDateRange($dateFrom, $dateTo);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('session_uuid', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
                });
            }

            $sessions = $query->paginate($request->get('per_page', 20));

            // Statistics
            $stats = [
                'total' => UserSession::count(),
                'active' => UserSession::active()->count(),
                'completed' => UserSession::completed()->count(),
                'avg_duration' => UserSession::whereNotNull('duration_seconds')
                    ->avg('duration_seconds'),
            ];

            // Get filter options
            $users = User::role('student')->orderBy('name')->get();
            $deviceTypes = UserSession::whereNotNull('device_type')
                ->distinct()
                ->pluck('device_type')
                ->filter()
                ->values();

            return view('admin.user-sessions.index', compact(
                'sessions',
                'stats',
                'users',
                'deviceTypes'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'حدث خطأ أثناء تحميل الجلسات: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user session.
     */
    public function show($id)
    {
        try {
            $session = UserSession::with(['user', 'activities' => function($query) {
                $query->orderBy('occurred_at', 'asc');
            }])->findOrFail($id);

            // Activity statistics
            $activityStats = [
                'total' => $session->activities->count(),
                'by_type' => $session->activities->groupBy('activity_type')->map->count(),
                'page_views' => $session->activities->where('activity_type', 'page_view')->count(),
                'unique_pages' => $session->activities->where('activity_type', 'page_view')
                    ->pluck('page_url')
                    ->unique()
                    ->count(),
            ];

            // Timeline data for charts
            $timelineData = $session->activities->map(function($activity) {
                return [
                    'time' => $activity->occurred_at->format('H:i:s'),
                    'type' => $activity->activity_type,
                    'page' => $activity->page_url,
                ];
            });

            return view('admin.user-sessions.show', compact(
                'session',
                'activityStats',
                'timelineData'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.user-sessions.index')
                ->with('error', 'حدث خطأ أثناء تحميل تفاصيل الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * Display all sessions for a specific user.
     */
    public function userSessions($userId, Request $request)
    {
        try {
            $user = User::findOrFail($userId);

            $query = UserSession::byUser($userId)
                ->with('activities')
                ->withCount('activities')
                ->latest('started_at');

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $dateTo = $request->filled('date_to') 
                    ? Carbon::parse($request->date_to)->endOfDay() 
                    : now()->endOfDay();
                $query->byDateRange($dateFrom, $dateTo);
            }

            $sessions = $query->paginate($request->get('per_page', 20));

            // User-specific statistics
            $userStats = [
                'total_sessions' => UserSession::byUser($userId)->count(),
                'total_time' => UserSession::byUser($userId)
                    ->whereNotNull('duration_seconds')
                    ->sum('duration_seconds'),
                'avg_duration' => UserSession::byUser($userId)
                    ->whereNotNull('duration_seconds')
                    ->avg('duration_seconds'),
                'active_sessions' => UserSession::byUser($userId)->active()->count(),
                'devices_used' => UserSession::byUser($userId)
                    ->whereNotNull('device_type')
                    ->distinct()
                    ->pluck('device_type')
                    ->count(),
            ];

            return view('admin.user-sessions.user', compact(
                'user',
                'sessions',
                'userStats'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.user-sessions.index')
                ->with('error', 'حدث خطأ أثناء تحميل جلسات المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * Display active sessions.
     */
    public function activeSessions(Request $request)
    {
        try {
            $query = UserSession::active()
                ->with(['user', 'activities'])
                ->withCount('activities')
                ->latest('started_at');

            // Filter by user
            if ($request->filled('user_id')) {
                $query->byUser($request->user_id);
            }

            $sessions = $query->paginate($request->get('per_page', 20));

            $stats = [
                'total_active' => UserSession::active()->count(),
                'longest_active' => UserSession::active()
                    ->orderBy('started_at', 'asc')
                    ->first(),
            ];

            return view('admin.user-sessions.active', compact('sessions', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.user-sessions.index')
                ->with('error', 'حدث خطأ أثناء تحميل الجلسات النشطة: ' . $e->getMessage());
        }
    }

    /**
     * Display comprehensive statistics.
     */
    public function statistics(Request $request)
    {
        try {
            // Date range filter
            $dateFrom = $request->filled('date_from') 
                ? Carbon::parse($request->date_from)->startOfDay() 
                : now()->subDays(30)->startOfDay();
            $dateTo = $request->filled('date_to') 
                ? Carbon::parse($request->date_to)->endOfDay() 
                : now()->endOfDay();

            $query = UserSession::byDateRange($dateFrom, $dateTo);

            // General statistics
            $stats = [
                'total_sessions' => (clone $query)->count(),
                'active_sessions' => UserSession::active()->count(),
                'completed_sessions' => (clone $query)->completed()->count(),
                'avg_duration' => (clone $query)->whereNotNull('duration_seconds')
                    ->avg('duration_seconds'),
                'total_duration' => (clone $query)->whereNotNull('duration_seconds')
                    ->sum('duration_seconds'),
            ];

            // Device statistics
            $deviceStats = (clone $query)
                ->whereNotNull('device_type')
                ->select('device_type', DB::raw('count(*) as count'))
                ->groupBy('device_type')
                ->orderByDesc('count')
                ->get();

            // Browser statistics
            $browserStats = (clone $query)
                ->whereNotNull('browser')
                ->select('browser', DB::raw('count(*) as count'))
                ->groupBy('browser')
                ->orderByDesc('count')
                ->get();

            // Connection type statistics
            $connectionStats = (clone $query)
                ->whereNotNull('connection_type')
                ->select('connection_type', DB::raw('count(*) as count'))
                ->groupBy('connection_type')
                ->orderByDesc('count')
                ->get();

            // Status distribution
            $statusStats = (clone $query)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            // Sessions over time (daily)
            $sessionsOverTime = (clone $query)
                ->select(DB::raw('DATE(started_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Top users by session count
            $topUsers = (clone $query)
                ->with('user')
                ->select('user_id', DB::raw('count(*) as session_count'))
                ->groupBy('user_id')
                ->orderByDesc('session_count')
                ->limit(10)
                ->get()
                ->load('user');

            return view('admin.user-sessions.statistics', compact(
                'stats',
                'deviceStats',
                'browserStats',
                'connectionStats',
                'statusStats',
                'sessionsOverTime',
                'topUsers',
                'dateFrom',
                'dateTo'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.user-sessions.index')
                ->with('error', 'حدث خطأ أثناء تحميل الإحصائيات: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected sessions.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'session_ids' => 'required|array|min:1',
                'session_ids.*' => 'integer|exists:user_sessions,id',
            ]);

            $count = UserSession::whereIn('id', $request->session_ids)->count();
            UserSession::whereIn('id', $request->session_ids)->delete();

            return back()->with('success', "تم حذف {$count} جلسة بنجاح.");
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف الجلسات: ' . $e->getMessage());
        }
    }

    /**
     * Delete all sessions.
     */
    public function deleteAll()
    {
        try {
            $count = UserSession::count();
            UserSession::truncate();

            return back()->with('success', "تم حذف جميع الجلسات بنجاح. ({$count} جلسة)");
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف جميع الجلسات: ' . $e->getMessage());
        }
    }

    /**
     * Delete completed/old sessions.
     */
    public function deleteCompleted(Request $request)
    {
        try {
            $request->validate([
                'days' => 'nullable|integer|min:0|max:365',
            ]);

            $days = $request->days ?? 0;

            if ($days > 0) {
                $cutoffDate = now()->subDays($days);
                $count = UserSession::where('status', 'completed')
                    ->where('ended_at', '<', $cutoffDate)
                    ->count();
                UserSession::where('status', 'completed')
                    ->where('ended_at', '<', $cutoffDate)
                    ->delete();
            } else {
                $count = UserSession::where('status', 'completed')->count();
                UserSession::where('status', 'completed')->delete();
            }

            $message = $days > 0
                ? "تم حذف {$count} جلسة مكتملة أقدم من {$days} يوم."
                : "تم حذف جميع الجلسات المكتملة بنجاح. ({$count} جلسة)";

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف الجلسات المكتملة: ' . $e->getMessage());
        }
    }

    /**
     * Delete disconnected/timeout sessions.
     */
    public function deleteDisconnected()
    {
        try {
            $count = UserSession::whereIn('status', ['disconnected', 'timeout'])->count();
            UserSession::whereIn('status', ['disconnected', 'timeout'])->delete();

            return back()->with('success', "تم حذف الجلسات المنفصلة والمنتهية بنجاح. ({$count} جلسة)");
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف الجلسات المنفصلة: ' . $e->getMessage());
        }
    }
}
