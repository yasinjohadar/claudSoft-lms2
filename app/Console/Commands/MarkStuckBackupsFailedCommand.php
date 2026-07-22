<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;

class MarkStuckBackupsFailedCommand extends Command
{
    protected $signature = 'backups:mark-stuck-failed
                            {--minutes= : Minutes after which a running backup is considered stuck (default from config)}';

    protected $description = 'Mark backups stuck in running status as failed';

    public function handle(): int
    {
        $minutes = (int) ($this->option('minutes') ?: config('backup.stuck_running_minutes', 120));
        $cutoff = now()->subMinutes($minutes);

        $stuck = Backup::query()
            ->where('status', 'running')
            ->where(function ($q) use ($cutoff) {
                $q->where('started_at', '<=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('started_at')->where('created_at', '<=', $cutoff);
                    });
            })
            ->get();

        if ($stuck->isEmpty()) {
            $this->info('No stuck running backups found.');

            return self::SUCCESS;
        }

        foreach ($stuck as $backup) {
            $backup->update([
                'status' => 'failed',
                'stage' => 'failed',
                'completed_at' => now(),
                'error_message' => "Marked as failed: stuck in running longer than {$minutes} minutes",
            ]);
            $this->line("Marked backup #{$backup->id} as failed");
        }

        $this->info("Marked {$stuck->count()} backup(s) as failed.");

        return self::SUCCESS;
    }
}
