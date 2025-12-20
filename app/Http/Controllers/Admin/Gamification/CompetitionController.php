<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Services\Gamification\CompetitionService;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    protected CompetitionService $competitionService;

    public function __construct(CompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
    }

    /**
     * عرض قائمة المنافسات
     */
    public function index(Request $request)
    {
        $query = Competition::with(['creator:id,name,email', 'participants']);

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $competitions = $query->latest('created_at')
            ->paginate(20);

        // إحصائيات
        $stats = [
            'total_competitions' => Competition::count(),
            'active_competitions' => Competition::where('status', 'active')->count(),
            'completed_competitions' => Competition::where('status', 'completed')->count(),
            'by_type' => Competition::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
        ];

        return response()->json([
            'success' => true,
            'competitions' => $competitions,
            'stats' => $stats,
        ]);
    }

    /**
     * عرض تفاصيل منافسة
     */
    public function show(Competition $competition)
    {
        $competition->load([
            'creator:id,name,email,avatar',
            'participants.user:id,name,email,avatar'
        ]);

        return response()->json([
            'success' => true,
            'competition' => $competition,
        ]);
    }

    /**
     * إنهاء منافسة يدوياً
     */
    public function end(Competition $competition)
    {
        $success = $this->competitionService->endCompetition($competition);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إنهاء المنافسة.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء المنافسة بنجاح!',
            'competition' => $competition->fresh(['participants']),
        ]);
    }

    /**
     * حذف منافسة
     */
    public function destroy(Competition $competition)
    {
        $competition->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنافسة بنجاح!',
        ]);
    }

    /**
     * إحصائيات المنافسات
     */
    public function statistics()
    {
        $totalCompetitions = Competition::count();
        $activeCompetitions = Competition::where('status', 'active')->count();
        $completedCompetitions = Competition::where('status', 'completed')->count();

        $competitionsByType = Competition::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $averageParticipants = Competition::withCount('participants')
            ->avg('participants_count');

        $mostActiveCreators = Competition::selectRaw('creator_id, COUNT(*) as count')
            ->groupBy('creator_id')
            ->with('creator:id,name,email')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_competitions' => $totalCompetitions,
                'active_competitions' => $activeCompetitions,
                'completed_competitions' => $completedCompetitions,
                'competitions_by_type' => $competitionsByType,
                'average_participants' => round($averageParticipants, 2),
                'most_active_creators' => $mostActiveCreators,
            ],
        ]);
    }
}
