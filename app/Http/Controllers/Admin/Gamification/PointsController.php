<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gamification\PointTransaction as PointsTransaction;
use App\Services\Gamification\PointsService;
use App\Services\Gamification\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PointsController extends Controller
{
    protected PointsService $pointsService;
    protected GamificationService $gamificationService;

    public function __construct(
        PointsService $pointsService,
        GamificationService $gamificationService
    ) {
        $this->pointsService = $pointsService;
        $this->gamificationService = $gamificationService;
    }

    /**
     * عرض قائمة معاملات النقاط
     */
    public function index(Request $request)
    {
        $query = PointsTransaction::with(['user:id,name,email']);

        // فلترة حسب المستخدم
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // فلترة حسب المصدر
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // فلترة حسب النوع (ربح/صرف)
        if ($request->filled('type')) {
            if ($request->type === 'earned') {
                $query->where('points', '>', 0);
            } elseif ($request->type === 'spent') {
                $query->where('points', '<', 0);
            }
        }

        // فلترة حسب التاريخ
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->latest()
            ->paginate(50)
            ->withQueryString();

        // إحصائيات
        $stats = [
            'total_transactions' => PointsTransaction::count(),
            'total_points_awarded' => PointsTransaction::where('points', '>', 0)->sum('points'),
            'total_points_spent' => abs(PointsTransaction::where('points', '<', 0)->sum('points')),
            'today_transactions' => PointsTransaction::whereDate('created_at', today())->count(),
        ];

        return view('admin.pages.gamification.points.index', compact('transactions', 'stats'));
    }

    /**
     * عرض معاملات نقاط مستخدم محدد
     */
    public function userTransactions(User $user)
    {
        $transactions = $user->pointsTransactions()
            ->latest()
            ->paginate(30);

        $stats = [
            'total_points' => $this->pointsService->getTotalPoints($user),
            'available_points' => $this->pointsService->getAvailablePoints($user),
            'total_earned' => $user->pointsTransactions()->where('points', '>', 0)->sum('points'),
            'total_spent' => abs($user->pointsTransactions()->where('points', '<', 0)->sum('points')),
        ];

        return view('admin.pages.gamification.points.user-transactions', compact('user', 'transactions', 'stats'));
    }

    /**
     * عرض صفحة منح نقاط يدوياً
     */
    public function create()
    {
        $users = User::whereHas('roles', function($q) {
                $q->where('name', 'student');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.pages.gamification.points.create', compact('users'));
    }

    /**
     * منح نقاط يدوياً
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:-100000|max:100000|not_in:0',
            'reason' => 'required|string|max:500',
        ], [
            'user_id.required' => 'يجب اختيار المستخدم',
            'user_id.exists' => 'المستخدم غير موجود',
            'points.required' => 'يجب إدخال عدد النقاط',
            'points.integer' => 'النقاط يجب أن تكون رقم صحيح',
            'points.not_in' => 'لا يمكن أن تكون النقاط صفر',
            'reason.required' => 'يجب إدخال السبب',
            'reason.max' => 'السبب طويل جداً',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::findOrFail($request->user_id);
        $points = (int) $request->points;
        $reason = $request->reason;

        try {
            if ($points > 0) {
                // منح نقاط
                $transaction = $this->pointsService->awardBonus(
                    $user,
                    $points,
                    $reason,
                    auth()->user()
                );
            } else {
                // خصم نقاط
                $transaction = $this->pointsService->deductPoints(
                    $user,
                    abs($points),
                    'admin_adjustment',
                    $reason,
                    null,
                    null,
                    auth()->id()
                );
            }

            if ($transaction) {
                return redirect()
                    ->route('admin.pages.gamification.points.index')
                    ->with('success', 'تم تعديل نقاط المستخدم بنجاح');
            } else {
                return redirect()
                    ->back()
                    ->with('error', 'فشل في تعديل النقاط')
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف معاملة نقاط (إلغاء)
     */
    public function destroy(PointsTransaction $transaction)
    {
        try {
            // لا يمكن حذف المعاملة مباشرة، يجب إنشاء معاملة عكسية
            $user = $transaction->user;
            $reversePoints = -$transaction->points;

            $this->pointsService->awardBonus(
                $user,
                $reversePoints,
                "إلغاء معاملة رقم {$transaction->id}",
                auth()->user()
            );

            return redirect()
                ->back()
                ->with('success', 'تم إلغاء المعاملة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * عرض تقرير النقاط
     */
    public function report(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        // أكثر المصادر منحاً للنقاط
        $topSources = PointsTransaction::where('points', '>', 0)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('source, COUNT(*) as count, SUM(points) as total_points')
            ->groupBy('source')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        // أكثر المستخدمين كسباً للنقاط
        $topEarners = User::whereHas('stats')
            ->with('stats')
            ->withCount(['pointsTransactions as earned_points' => function ($query) use ($startDate) {
                $query->where('points', '>', 0)
                    ->where('created_at', '>=', $startDate)
                    ->selectRaw('COALESCE(SUM(points), 0)');
            }])
            ->orderByDesc('earned_points')
            ->limit(20)
            ->get();

        // رسم بياني للنقاط اليومية
        $dailyPoints = PointsTransaction::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as earned')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.pages.gamification.points.report', compact(
            'topSources',
            'topEarners',
            'dailyPoints',
            'period'
        ));
    }

    /**
     * إعادة حساب نقاط مستخدم
     */
    public function recalculate(User $user)
    {
        try {
            $success = $this->gamificationService->recalculateStats($user);

            if ($success) {
                return redirect()
                    ->back()
                    ->with('success', 'تم إعادة حساب الإحصائيات بنجاح');
            } else {
                return redirect()
                    ->back()
                    ->with('error', 'فشل في إعادة الحساب');
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }
}
