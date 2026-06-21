<?php

namespace App\Console\Commands;

use App\Models\CourseGroupMember;
use App\Models\CourseGroupMembershipHistory;
use App\Services\CourseGroup\CourseGroupMembershipHistoryService;
use Illuminate\Console\Command;

class BackfillGroupMembershipHistoryCommand extends Command
{
    protected $signature = 'group-membership:backfill-history
                            {--dry-run : عرض ما سيتم إنشاؤه دون تنفيذ}';

    protected $description = 'تعبئة سجل تنقل المجموعات من العضويات الحالية في course_group_members';

    public function handle(CourseGroupMembershipHistoryService $historyService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        CourseGroupMember::query()
            ->with(['student', 'group'])
            ->orderBy('id')
            ->chunkById(200, function ($members) use ($dryRun, $historyService, &$created, &$skipped) {
                foreach ($members as $member) {
                    $hasActiveHistory = CourseGroupMembershipHistory::query()
                        ->where('student_id', $member->student_id)
                        ->where('group_id', $member->group_id)
                        ->whereNull('left_at')
                        ->exists();

                    if ($hasActiveHistory) {
                        $skipped++;

                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Would backfill: student {$member->student_id} → group {$member->group_id}");
                        $created++;

                        continue;
                    }

                    CourseGroupMembershipHistory::create([
                        'student_id' => $member->student_id,
                        'group_id' => $member->group_id,
                        'role' => $member->role,
                        'joined_at' => $member->joined_at ?? $member->created_at ?? now(),
                        'join_reason' => $historyService->defaultReason(
                            CourseGroupMembershipHistory::SOURCE_BACKFILL,
                            'join'
                        ),
                        'source' => CourseGroupMembershipHistory::SOURCE_BACKFILL,
                    ]);

                    $created++;
                }
            });

        $this->info(($dryRun ? '[Dry run] ' : '')."Created: {$created}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
