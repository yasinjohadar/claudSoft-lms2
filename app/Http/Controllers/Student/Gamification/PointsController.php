<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Services\Gamification\PointEarningCatalog;
use App\Services\Gamification\PointsService;
use App\Services\Gamification\ReferralService;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct(
        protected PointsService $pointsService,
        protected PointEarningCatalog $earningCatalog,
        protected ReferralService $referralService
    ) {}

    public function index()
    {
        $user = auth()->user();

        $totalPoints = $this->pointsService->getTotalPoints($user);
        $availablePoints = $this->pointsService->getAvailablePoints($user);
        $spentPoints = $user->stats?->spent_points ?? ($totalPoints - $availablePoints);

        $monthlyEarned = (int) $user->pointsTransactions()
            ->where('points', '>', 0)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('points');

        $recentTransactions = $this->pointsService->getPointsHistory($user, 5);
        $earningMethods = $this->earningCatalog->getEarningMethods();
        $referralLink = $this->referralService->getReferralLink($user);

        return view('student.pages.gamification.points', compact(
            'totalPoints',
            'availablePoints',
            'spentPoints',
            'monthlyEarned',
            'recentTransactions',
            'earningMethods',
            'referralLink'
        ));
    }

    public function history(Request $request)
    {
        $user = auth()->user();

        $query = $user->pointsTransactions();

        if ($request->filled('type')) {
            if (in_array($request->type, ['earn', 'spend', 'bonus', 'penalty', 'refund', 'adjustment'], true)) {
                $query->where('type', $request->type);
            } elseif ($request->type === 'earned') {
                $query->where('points', '>', 0);
            } elseif ($request->type === 'spent') {
                $query->where('points', '<', 0);
            }
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_transactions' => $user->pointsTransactions()->count(),
            'total_earned' => $user->pointsTransactions()->where('points', '>', 0)->sum('points'),
            'total_spent' => abs($user->pointsTransactions()->where('points', '<', 0)->sum('points')),
            'this_month_earned' => $user->pointsTransactions()
                ->where('points', '>', 0)
                ->whereMonth('created_at', now()->month)
                ->sum('points'),
        ];

        $sources = $user->pointsTransactions()
            ->distinct()
            ->pluck('source')
            ->filter()
            ->mapWithKeys(fn (string $source) => [
                $source => $this->earningCatalog->getSourceLabel($source),
            ])
            ->sort()
            ->all();

        return view('student.pages.gamification.points.history', compact(
            'transactions',
            'stats',
            'sources'
        ));
    }

    public function howToEarn()
    {
        $earningByCategory = $this->earningCatalog->getEarningMethodsByCategory();
        $streakMultipliers = $this->earningCatalog->getStreakMultipliers();
        $streakMilestones = $this->earningCatalog->getStreakMilestones();
        $referralLink = $this->referralService->getReferralLink(auth()->user());

        return view('student.pages.gamification.points.how-to-earn', compact(
            'earningByCategory',
            'streakMultipliers',
            'streakMilestones',
            'referralLink'
        ));
    }
}
