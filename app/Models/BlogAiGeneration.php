<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BlogAiGeneration extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    /** Stopped mid-way with finished sections preserved; can be continued. */
    public const STATUS_PAUSED = 'paused';

    public const OPERATION_GENERATE = 'generate';

    /** A running job without a heartbeat for this long is considered dead. */
    public const STALE_HEARTBEAT_MINUTES = 10;

    protected $table = 'blog_ai_generations';

    protected $fillable = [
        'uuid',
        'user_id',
        'operation',
        'status',
        'progress',
        'stage',
        'stage_label',
        'payload',
        'partial_result',
        'result',
        'error_message',
        'started_at',
        'heartbeat_at',
        'finished_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'partial_result' => 'array',
        'result' => 'array',
        'progress' => 'integer',
        'started_at' => 'datetime',
        'heartbeat_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(BlogAiSection::class, 'generation_id')->orderBy('position');
    }

    public function markRunning(string $stage, string $stageLabel, int $progress): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'stage' => $stage,
            'stage_label' => $stageLabel,
            'progress' => max(0, min(100, $progress)),
            'started_at' => $this->started_at ?? now(),
            'heartbeat_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markProgress(string $stage, string $stageLabel, int $progress, ?array $partial = null): void
    {
        $data = [
            'status' => self::STATUS_RUNNING,
            'stage' => $stage,
            'stage_label' => $stageLabel,
            'progress' => max(0, min(100, $progress)),
            'heartbeat_at' => now(),
        ];
        if ($partial !== null) {
            $data['partial_result'] = array_merge($this->partial_result ?? [], $partial);
        }
        $this->update($data);
    }

    /** Cheap keep-alive between long provider calls. */
    public function touchHeartbeat(): void
    {
        $this->forceFill(['heartbeat_at' => now()])->saveQuietly();
    }

    public function markCompleted(array $result): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'stage' => 'completed',
            'stage_label' => 'اكتمل التوليد',
            'progress' => 100,
            'result' => $result,
            'finished_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Stop without discarding work: finished sections stay in the database and
     * the job can be continued later from the admin UI.
     */
    public function markPaused(string $message): void
    {
        $this->update([
            'status' => self::STATUS_PAUSED,
            'stage' => 'paused',
            'stage_label' => 'متوقف مؤقتاً — بانتظار المتابعة',
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'stage' => 'failed',
            'stage_label' => 'فشل',
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }

    public function isStaged(): bool
    {
        return $this->sections()->exists();
    }

    /**
     * @return array{planned: int, done: int, failed: int, remaining: int, failed_headings: list<string>}
     */
    public function sectionSummary(): array
    {
        /** @var \Illuminate\Support\Collection<int, BlogAiSection> $sections */
        $sections = $this->relationLoaded('sections') ? $this->sections : $this->sections()->get();

        $done = $sections->where('status', BlogAiSection::STATUS_DONE)->count();
        $failed = $sections->where('status', BlogAiSection::STATUS_FAILED);

        return [
            'planned' => $sections->count(),
            'done' => $done,
            'failed' => $failed->count(),
            'remaining' => max(0, $sections->count() - $done),
            'failed_headings' => $failed->pluck('heading')->filter()->values()->all(),
        ];
    }

    public function isResumable(): bool
    {
        if (! in_array($this->status, [self::STATUS_PAUSED, self::STATUS_FAILED], true)) {
            return false;
        }

        return $this->sections()->where('status', '!=', BlogAiSection::STATUS_DONE)->exists();
    }

    /** Running but the worker stopped writing heartbeats (process killed / crashed). */
    public function isStale(): bool
    {
        if ($this->status !== self::STATUS_RUNNING) {
            return false;
        }

        $last = $this->heartbeat_at ?? $this->started_at ?? $this->updated_at;

        return $last !== null && $last->lt(now()->subMinutes(self::STALE_HEARTBEAT_MINUTES));
    }

    public function toStatusPayload(): array
    {
        $summary = $this->isStaged() ? $this->sectionSummary() : null;

        return [
            'uuid' => $this->uuid,
            'operation' => $this->operation,
            'status' => $this->status,
            'progress' => (int) $this->progress,
            'stage' => $this->stage,
            'stage_label' => $this->stage_label,
            'result' => $this->status === self::STATUS_COMPLETED ? $this->result : null,
            'partial_result' => $this->partial_result,
            'error_message' => $this->error_message,
            'sections' => $summary,
            'resumable' => $this->isResumable(),
            'partial_content_available' => $summary !== null && $summary['done'] > 0,
            'queue_hint' => $this->status === self::STATUS_QUEUED
                && config('queue.default') !== 'sync',
        ];
    }
}
