<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Services\Gamification\PointsService;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    protected PointsService $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * عرض صفحة النقاط
     */
    public function index()
    {
        $user = auth()->user();

        $totalPoints = $this->pointsService->getTotalPoints($user);
        $availablePoints = $this->pointsService->getAvailablePoints($user);
        $spentPoints = $totalPoints - $availablePoints;

        return view('student.pages.gamification.points', compact(
            'totalPoints',
            'availablePoints',
            'spentPoints'
        ));
    }

    /**
     * عرض سجل النقاط
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        $query = $user->pointsTransactions();

        // فلترة حسب النوع
        if ($request->filled('type')) {
            if ($request->type === 'earned') {
                $query->where('points', '>', 0);
            } elseif ($request->type === 'spent') {
                $query->where('points', '<', 0);
            }
        }

        // فلترة حسب المصدر
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()
            ->paginate(20)
            ->withQueryString();

        // الإحصائيات
        $stats = [
            'total_transactions' => $user->pointsTransactions()->count(),
            'total_earned' => $user->pointsTransactions()->where('points', '>', 0)->sum('points'),
            'total_spent' => abs($user->pointsTransactions()->where('points', '<', 0)->sum('points')),
            'this_month_earned' => $user->pointsTransactions()
                ->where('points', '>', 0)
                ->whereMonth('created_at', now()->month)
                ->sum('points'),
        ];

        // مصادر النقاط المتاحة
        $sources = $user->pointsTransactions()
            ->distinct()
            ->pluck('source');

        return view('student.pages.gamification.points.history', compact(
            'transactions',
            'stats',
            'sources'
        ));
    }

    /**
     * عرض طرق كسب النقاط
     */
    public function howToEarn()
    {
        $pointsConfig = config('gamification.points');
        $streakMultipliers = config('gamification.streak_multipliers');

        return view('student.pages.gamification.points.how-to-earn', compact(
            'pointsConfig',
            'streakMultipliers'
        ));
    }
}
