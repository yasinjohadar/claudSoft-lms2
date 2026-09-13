<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DocumentationAiBatch extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'documentation_ai_batches';

    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'total',
        'completed',
        'failed',
        'settings',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'total' => 'integer',
        'completed' => 'integer',
        'failed' => 'integer',
        'started_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(DocumentationAiBatchItem::class, 'batch_id')->orderBy('position');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_COMPLETED_WITH_ERRORS,
            self::STATUS_CANCELLED,
        ], true);
    }

    /**
     * Counts taken from the item rows, which are the authority. The completed/
     * failed columns are a denormalised cache that several code paths used to
     * increment by hand — easy to double-count once a reaper can also close an
     * item, so nothing decides anything from them any more.
     *
     * @return array{completed:int, failed:int, running:int, pending:int, resume_queued:int, skipped:int}
     */
    public function itemStatusCounts(): array
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get(['id', 'batch_id', 'status']);

        $countOf = fn (string $status) => $items->where('status', $status)->count();

        return [
            'completed' => $countOf(DocumentationAiBatchItem::STATUS_COMPLETED),
            'failed' => $countOf(DocumentationAiBatchItem::STATUS_FAILED),
            'running' => $countOf(DocumentationAiBatchItem::STATUS_RUNNING),
            'pending' => $countOf(DocumentationAiBatchItem::STATUS_PENDING),
            'resume_queued' => $countOf(DocumentationAiBatchItem::STATUS_RESUME_QUEUED),
            'skipped' => $countOf(DocumentationAiBatchItem::STATUS_SKIPPED),
        ];
    }

    /** Re-sync the cache columns from the item rows. Idempotent by construction. */
    public function recountFromItems(): void
    {
        $counts = $this->itemStatusCounts();

        $this->forceFill([
            'completed' => $counts['completed'],
            'failed' => $counts['failed'],
        ])->saveQuietly();
    }

    /** Nothing is in flight and nothing more is owed. */
    public function hasOpenWork(): bool
    {
        $counts = $this->itemStatusCounts();

        return $counts['running'] > 0 || $counts['pending'] > 0 || $counts['resume_queued'] > 0;
    }

    /**
     * Unfinished, nothing running, yet topics are still waiting — the queue
     * worker is almost certainly down, which no reaper should paper over by
     * re-dispatching blindly.
     */
    public function isStalled(): bool
    {
        if ($this->isFinished()) {
            return false;
        }

        $counts = $this->itemStatusCounts();
        if ($counts['running'] > 0) {
            return false;
        }

        if ($counts['pending'] === 0 && $counts['resume_queued'] === 0) {
            return false;
        }

        return $this->updated_at?->lt(
            now()->subMinutes(DocumentationAiGeneration::STALE_HEARTBEAT_MINUTES)
        ) ?? false;
    }

    public function toStatusPayload(): array
    {
        $counts = $this->itemStatusCounts();
        $items = $this->items->map(fn (DocumentationAiBatchItem $item) => $item->toStatusPayload())->all();

        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'total' => (int) $this->total,
            'completed' => $counts['completed'],
            'failed' => $counts['failed'],
            'incomplete' => count(array_filter($items, fn (array $i) => $i['is_incomplete'])),
            'running' => $counts['running'],
            'resume_queued' => $counts['resume_queued'],
            'any_running' => $counts['running'] > 0 || $counts['resume_queued'] > 0,
            'finished' => $this->isFinished(),
            'stalled' => $this->isStalled(),
            'created_at' => $this->created_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'items' => $items,
        ];
    }
}
