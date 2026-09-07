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

    public function toStatusPayload(): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'total' => (int) $this->total,
            'completed' => (int) $this->completed,
            'failed' => (int) $this->failed,
            'finished' => $this->isFinished(),
            'items' => $this->items->map(fn (DocumentationAiBatchItem $item) => $item->toStatusPayload())->all(),
        ];
    }
}
