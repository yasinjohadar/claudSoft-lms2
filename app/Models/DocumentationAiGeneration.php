<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocumentationAiGeneration extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const OPERATION_GENERATE = 'generate';

    public const OPERATION_REFINE = 'refine';

    public const OPERATION_ENHANCE = 'enhance';

    protected $table = 'documentation_ai_generations';

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
        'finished_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'partial_result' => 'array',
        'result' => 'array',
        'progress' => 'integer',
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

    public function markRunning(string $stage, string $stageLabel, int $progress): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'stage' => $stage,
            'stage_label' => $stageLabel,
            'progress' => max(0, min(100, $progress)),
            'started_at' => $this->started_at ?? now(),
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
        ];
        if ($partial !== null) {
            $data['partial_result'] = $partial;
        }
        $this->update($data);
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

    public function toStatusPayload(): array
    {
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
            'queue_hint' => $this->status === self::STATUS_QUEUED
                && config('queue.default') !== 'sync',
        ];
    }
}
