<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompetitionService
{
    /**
     * إنشاء منافسة بين أصدقاء
     */
    public function createCompetition(
        User $creator,
        array $participantIds,
        string $type,
        Carbon $endsAt,
        ?int $targetValue = null
    ): ?Competition {
        try {
            return DB::transaction(function () use ($creator, $participantIds, $type, $endsAt, $targetValue) {
                // إنشاء المنافسة
                $competition = Competition::create([
                    'creator_id' => $creator->id,
                    'name' => $this->generateCompetitionName($type),
                    'type' => $type,
                    'target_value' => $targetValue,
                    'starts_at' => now(),
                    'ends_at' => $endsAt,
                    'status' => 'active',
                ]);

                // إضافة المنشئ كمشارك
                CompetitionParticipant::create([
                    'competition_id' => $competition->id,
                    'user_id' => $creator->id,
                    'current_value' => 0,
                    'rank' => 1,
                    'joined_at' => now(),
                ]);

                // إضافة المشاركين
                foreach ($participantIds as $userId) {
                    if ($userId !== $creator->id) {
                        CompetitionParticipant::create([
                            'competition_id' => $competition->id,
                            'user_id' => $userId,
                            'current_value' => 0,
                            'rank' => 1,
                            'joined_at' => now(),
                        ]);
                    }
                }

                Log::info('Competition created', [
                    'competition_id' => $competition->id,
                    'creator_id' => $creator->id,
                    'type' => $type,
                ]);

                return $competition;
            });

        } catch (\Exception $e) {
            Log::error('Failed to create competition', [
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * توليد اسم للمنافسة
     */
    protected function generateCompetitionName(string $type): string
    {
        $names = [
            'points' => 'منافسة النقاط',
            'xp' => 'منافسة الخبرة',
            'lessons' => 'منافسة الدروس',
            'quizzes' => 'منافسة الاختبارات',
            'streak' => 'منافسة السلسلة',
        ];

        return $names[$type] ?? 'منافسة';
    }

    /**
     * تحديث تقدم مشارك
     */
    public function updateParticipantProgress(
        User $user,
        Competition $competition,
        int $value
    ): bool {
        try {
            $participant = CompetitionParticipant::where('competition_id', $competition->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$participant) {
                return false;
            }

            // التحقق من أن المنافسة نشطة
            if ($competition->status !== 'active' || $competition->ends_at < now()) {
                return false;
            }

            $participant->update([
                'current_value' => $value,
            ]);

            // تحديث الترتيب
            $this->updateRankings($competition);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update participant progress', [
                'user_id' => $user->id,
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * تحديث ترتيب المشاركين
     */
    protected function updateRankings(Competition $competition): void
    {
        $participants = CompetitionParticipant::where('competition_id', $competition->id)
            ->orderByDesc('current_value')
            ->get();

        $rank = 1;
        $previousValue = null;
        $actualRank = 1;

        foreach ($participants as $participant) {
            if ($previousValue !== null && $participant->current_value < $previousValue) {
                $rank = $actualRank;
            }

            $participant->update(['rank' => $rank]);

            $previousValue = $participant->current_value;
            $actualRank++;
        }
    }

    /**
     * إنهاء منافسة
     */
    public function endCompetition(Competition $competition): bool
    {
        try {
            if ($competition->status === 'completed') {
                return false;
            }

            $competition->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // تحديد الفائز
            $winner = CompetitionParticipant::where('competition_id', $competition->id)
                ->orderBy('rank')
                ->orderByDesc('current_value')
                ->first();

            if ($winner) {
                $winner->update(['is_winner' => true]);

                // منح مكافأة للفائز
                $this->awardWinner($winner->user, $competition);
            }

            Log::info('Competition ended', [
                'competition_id' => $competition->id,
                'winner_id' => $winner?->user_id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to end competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * منح مكافأة للفائز
     */
    protected function awardWinner(User $winner, Competition $competition): void
    {
        $rewards = config('gamification.competition.rewards', [
            'points' => 500,
            'xp' => 250,
            'gems' => 50,
        ]);

        // منح النقاط
        if (isset($rewards['points'])) {
            $pointsService = app(PointsService::class);
            $pointsService->awardPoints(
                $winner,
                $rewards['points'],
                'competition_won',
                "الفوز في {$competition->name}",
                'App\Models\Competition',
                $competition->id
            );
        }

        // منح XP
        if (isset($rewards['xp'])) {
            $levelService = app(LevelService::class);
            $levelService->awardXP(
                $winner,
                $rewards['xp'],
                'competition_won',
                "الفوز في {$competition->name}"
            );
        }

        // منح الأحجار الكريمة
        if (isset($rewards['gems'])) {
            $winner->stats->increment('available_gems', $rewards['gems']);
        }

        // تحديث إحصائيات
        $winner->stats->increment('competitions_won');
    }

    /**
     * فحص وإنهاء المنافسات المنتهية
     */
    public function checkExpiredCompetitions(): int
    {
        $expired = Competition::where('status', 'active')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($expired as $competition) {
            $this->endCompetition($competition);
        }

        return $expired->count();
    }

    /**
     * الحصول على منافسات المستخدم النشطة
     */
    public function getUserActiveCompetitions(User $user)
    {
        return Competition::where('status', 'active')
            ->where('ends_at', '>', now())
            ->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['participants.user:id,name,email,avatar', 'creator:id,name'])
            ->latest('created_at')
            ->get();
    }

    /**
     * الحصول على منافسات المستخدم المكتملة
     */
    public function getUserCompletedCompetitions(User $user)
    {
        return Competition::where('status', 'completed')
            ->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['participants.user:id,name,email,avatar', 'creator:id,name'])
            ->latest('completed_at')
            ->get();
    }

    /**
     * الحصول على تفاصيل مشاركة المستخدم
     */
    public function getUserParticipation(User $user, Competition $competition): ?CompetitionParticipant
    {
        return CompetitionParticipant::where('competition_id', $competition->id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * إحصائيات المنافسات للمستخدم
     */
    public function getUserCompetitionStats(User $user): array
    {
        $totalParticipated = CompetitionParticipant::where('user_id', $user->id)->count();

        $totalWon = CompetitionParticipant::where('user_id', $user->id)
            ->where('is_winner', true)
            ->count();

        $activeCompetitions = Competition::where('status', 'active')
            ->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        $winRate = $totalParticipated > 0
            ? round(($totalWon / $totalParticipated) * 100, 2)
            : 0;

        $topRanks = CompetitionParticipant::where('user_id', $user->id)
            ->whereHas('competition', function($q) {
                $q->where('status', 'completed');
            })
            ->selectRaw('rank, COUNT(*) as count')
            ->groupBy('rank')
            ->orderBy('rank')
            ->get()
            ->pluck('count', 'rank');

        return [
            'total_participated' => $totalParticipated,
            'total_won' => $totalWon,
            'active_competitions' => $activeCompetitions,
            'win_rate' => $winRate,
            'top_ranks' => $topRanks,
        ];
    }

    /**
     * مغادرة منافسة
     */
    public function leaveCompetition(User $user, Competition $competition): bool
    {
        try {
            // لا يمكن المغادرة إذا كنت المنشئ
            if ($competition->creator_id === $user->id) {
                return false;
            }

            // لا يمكن المغادرة إذا انتهت المنافسة
            if ($competition->status === 'completed') {
                return false;
            }

            $participant = CompetitionParticipant::where('competition_id', $competition->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$participant) {
                return false;
            }

            $participant->delete();

            // تحديث الترتيب
            $this->updateRankings($competition);

            Log::info('User left competition', [
                'user_id' => $user->id,
                'competition_id' => $competition->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to leave competition', [
                'user_id' => $user->id,
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * حذف منافسة (للمنشئ فقط)
     */
    public function deleteCompetition(User $user, Competition $competition): bool
    {
        try {
            // التحقق من أن المستخدم هو المنشئ
            if ($competition->creator_id !== $user->id) {
                return false;
            }

            // لا يمكن حذف منافسة مكتملة
            if ($competition->status === 'completed') {
                return false;
            }

            $competition->delete();

            Log::info('Competition deleted', [
                'competition_id' => $competition->id,
                'creator_id' => $user->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
