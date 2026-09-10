<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QueueWorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class QueueMonitorController extends Controller
{
    protected array $knownQueues = ['default', 'whatsapp', 'webhooks'];

    public function __construct(protected QueueWorkerService $queueWorkerService) {}

    public function index(Request $request)
    {
        $workerStatus = $this->queueWorkerService->status();
        $queues = $this->queuesBreakdown();

        $stats = [
            'pending_total' => collect($queues)->sum('pending'),
            'completed_today' => DB::table('queue_job_logs')
                ->where('status', 'completed')
                ->whereDate('finished_at', today())
                ->count(),
            'completed_week' => DB::table('queue_job_logs')
                ->where('status', 'completed')
                ->where('finished_at', '>=', now()->subDays(7))
                ->count(),
            'failed_total' => DB::table('failed_jobs')->count(),
        ];

        $pending = DB::table('queue_job_logs')
            ->whereIn('status', ['queued', 'processing'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'pending_page');

        $completed = DB::table('queue_job_logs')
            ->where('status', 'completed')
            ->orderByDesc('finished_at')
            ->paginate(15, ['*'], 'completed_page');

        $failed = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate(15, ['*'], 'failed_page')
            ->through(fn ($row) => $this->decorateFailedJob($row));

        return view('admin.queue-monitor.index', compact(
            'workerStatus',
            'queues',
            'stats',
            'pending',
            'completed',
            'failed'
        ));
    }

    public function data()
    {
        return response()->json([
            'workerStatus' => $this->queueWorkerService->status(),
            'queues' => $this->queuesBreakdown(),
            'stats' => [
                'pending_total' => collect($this->queuesBreakdown())->sum('pending'),
                'completed_today' => DB::table('queue_job_logs')
                    ->where('status', 'completed')
                    ->whereDate('finished_at', today())
                    ->count(),
                'failed_total' => DB::table('failed_jobs')->count(),
            ],
        ]);
    }

    public function retry(string $uuid)
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('success', 'تمت إعادة جدولة المهمة الفاشلة.');
    }

    public function retryAll()
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'تمت إعادة جدولة جميع المهام الفاشلة.');
    }

    public function destroy(string $uuid)
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('success', 'تم حذف المهمة الفاشلة.');
    }

    public function flush()
    {
        Artisan::call('queue:flush');

        return back()->with('success', 'تم حذف جميع المهام الفاشلة.');
    }

    public function workerStatus()
    {
        return response()->json($this->queueWorkerService->status());
    }

    public function workerStart()
    {
        $result = $this->queueWorkerService->start();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function workerStop()
    {
        $result = $this->queueWorkerService->stop();

        return response()->json($result);
    }

    protected function queuesBreakdown(): array
    {
        $queueNames = collect($this->knownQueues)
            ->merge(DB::table('queue_job_logs')->distinct()->pluck('queue'))
            ->unique()
            ->values();

        return $queueNames->map(function (string $queue) {
            $pending = 0;

            try {
                $pending = Queue::connection()->size($queue);
            } catch (\Throwable) {
                // driver may not support size() — leave as 0
            }

            $oldestQueuedAt = DB::table('queue_job_logs')
                ->where('queue', $queue)
                ->whereIn('status', ['queued', 'processing'])
                ->min('queued_at');

            return [
                'name' => $queue,
                'pending' => $pending,
                'oldest_pending_at' => $oldestQueuedAt,
            ];
        })->all();
    }

    protected function decorateFailedJob(object $row): object
    {
        $payload = json_decode($row->payload, true) ?: [];
        $row->job_class = $payload['displayName'] ?? 'غير معروف';
        $row->exception_summary = mb_substr((string) explode("\n", $row->exception)[0], 0, 300);

        return $row;
    }
}
