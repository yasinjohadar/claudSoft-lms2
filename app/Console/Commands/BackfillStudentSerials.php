<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BackfillStudentSerials extends Command
{
    protected $signature = 'users:backfill-student-serials
                            {--dry-run : عرض ما سيتم توليده دون حفظ}
                            {--include-inactive : تضمين الحسابات غير النشطة أيضاً}';

    protected $description = 'توليد أرقام تسلسلية STD-YYYY-NNNNN للطلاب النشطين الذين لا يملكون student_id';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $includeInactive = (bool) $this->option('include-inactive');
        $assigned = 0;
        $skippedRoles = 0;
        $skippedInactive = 0;

        /** @var array<int, int> $nextByYear */
        $nextByYear = [];

        User::query()
            ->where(function ($query) {
                $query->whereNull('student_id')->orWhere('student_id', '');
            })
            ->when(! $includeInactive, function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($dryRun, &$assigned, &$skippedRoles, &$nextByYear) {
                foreach ($users as $user) {
                    if ($user->hasAnyRole(User::studentSerialExcludedRoles())) {
                        $skippedRoles++;

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

        if (! $includeInactive) {
            $skippedInactive = User::query()
                ->where(function ($query) {
                    $query->whereNull('student_id')->orWhere('student_id', '');
                })
                ->where('is_active', false)
                ->count();
        }

        $prefix = $dryRun ? '[Dry run] ' : '';
        $this->info("{$prefix}Assigned: {$assigned}, Skipped roles: {$skippedRoles}, Skipped inactive: {$skippedInactive}");

        return self::SUCCESS;
    }
}
