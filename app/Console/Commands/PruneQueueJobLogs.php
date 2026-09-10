<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneQueueJobLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-logs:prune {--days= : عدد الأيام المراد الاحتفاظ بها}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'حذف سجلات مراقبة الطابور (queue_job_logs) الأقدم من عدد أيام محدد';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('queue.monitor_logs_retention_days', 14));

        $count = DB::table('queue_job_logs')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("تم حذف {$count} سجل من سجلات مراقبة الطابور (أقدم من {$days} يوم).");

        return Command::SUCCESS;
    }
}
