<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\DB;

class LogQueueJobActivity
{
    public function handleJobQueued(JobQueued $event): void
    {
        $jobClass = match (true) {
            is_object($event->job) => get_class($event->job),
            is_string($event->job) => $event->job,
            default => null,
        };

        if ($jobClass === null) {
            try {
                $jobClass = $event->payload()['displayName'] ?? 'Unknown';
            } catch (\Throwable) {
                $jobClass = 'Unknown';
            }
        }

        DB::table('queue_job_logs')->insert([
            'connection' => $event->connectionName,
            'job_id' => $this->normalizeId($event->id),
            'job_class' => $jobClass,
            'queue' => $event->queue ?: 'default',
            'status' => 'queued',
            'queued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function handleJobProcessing(JobProcessing $event): void
    {
        $id = $this->findLatestLogId($event->connectionName, $this->jobId($event->job), ['queued']);

        if ($id) {
            DB::table('queue_job_logs')->where('id', $id)->update([
                'status' => 'processing',
                'started_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function handleJobProcessed(JobProcessed $event): void
    {
        $id = $this->findLatestLogId($event->connectionName, $this->jobId($event->job), ['processing', 'queued']);

        if (! $id) {
            return;
        }

        $row = DB::table('queue_job_logs')->where('id', $id)->first();
        $durationMs = $row?->started_at
            ? (int) round(now()->diffInMilliseconds($row->started_at, true))
            : null;

        DB::table('queue_job_logs')->where('id', $id)->update([
            'status' => 'completed',
            'finished_at' => now(),
            'duration_ms' => $durationMs,
            'updated_at' => now(),
        ]);
    }

    public function handleJobFailed(JobFailed $event): void
    {
        $message = mb_substr($event->exception->getMessage() ?: get_class($event->exception), 0, 1000);
        $id = $this->findLatestLogId($event->connectionName, $this->jobId($event->job), ['processing', 'queued']);

        if ($id) {
            DB::table('queue_job_logs')->where('id', $id)->update([
                'status' => 'failed',
                'exception' => $message,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        // No matching "queued" row found (e.g. re-dispatched via queue:retry) — log it anyway.
        DB::table('queue_job_logs')->insert([
            'connection' => $event->connectionName,
            'job_id' => $this->jobId($event->job),
            'job_class' => $event->job->resolveName(),
            'queue' => $event->job->getQueue() ?: 'default',
            'status' => 'failed',
            'exception' => $message,
            'finished_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function findLatestLogId(string $connection, ?string $jobId, array $statuses): ?int
    {
        if (! $jobId) {
            return null;
        }

        return DB::table('queue_job_logs')
            ->where('connection', $connection)
            ->where('job_id', $jobId)
            ->whereIn('status', $statuses)
            ->orderByDesc('id')
            ->value('id');
    }

    protected function jobId($job): ?string
    {
        $id = $job->getJobId();

        return $id !== null && $id !== '' ? (string) $id : null;
    }

    protected function normalizeId($id): ?string
    {
        return $id !== null && $id !== '' ? (string) $id : null;
    }
}
