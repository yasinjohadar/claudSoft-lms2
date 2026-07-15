<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BackfillStudentSerials extends Command
{
    protected $signature = 'users:backfill-student-serials
                            {--dry-run : عرض ما سيتم توليده دون حفظ}';

    protected $description = 'توليد أرقام تسلسلية STD-YYYY-NNNNN للطلاب الحاليين الذين لا يملكون student_id';

    /**
     * Roles that should never receive a student serial during backfill.
     *
     * @var list<string>
     */
    private const EXCLUDED_ROLES = [
        'admin',
        'super-admin',
        'super_admin',
        'instructor',
        'teacher',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $assigned = 0;
        $skipped = 0;

        /** @var array<int, int> $nextByYear */
        $nextByYear = [];

        User::query()
            ->where(function ($query) {
                $query->whereNull('student_id')->orWhere('student_id', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($dryRun, &$assigned, &$skipped, &$nextByYear) {
                foreach ($users as $user) {
                    if ($user->hasAnyRole(self::EXCLUDED_ROLES)) {
                        $skipped++;

                        continue;
                    }

                    $year = $user->created_at ? (int) $user->created_at->format('Y') : (int) date('Y');

                    if ($dryRun) {
                        if (! isset($nextByYear[$year])) {
                            $preview = User::generateStudentSerial($year);
                            $nextByYear[$year] = (int) substr($preview, -5);
                        }

                        $serial = 'STD-'.$year.'-'.str_pad((string) $nextByYear[$year], 5, '0', STR_PAD_LEFT);
                        $this->line("Would assign {$serial} to user #{$user->id} ({$user->email})");
                        $nextByYear[$year]++;
                        $assigned++;

                        continue;
                    }

                    $user->assignStudentSerial($year);
                    $assigned++;
                }
            });

        $prefix = $dryRun ? '[Dry run] ' : '';
        $this->info("{$prefix}Assigned: {$assigned}, Skipped (non-student roles): {$skipped}");

        return self::SUCCESS;
    }
}
