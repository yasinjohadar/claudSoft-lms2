<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One planned phase (plan/html/css/js) of a staged simulator generation.
 *
 * Rows are created once the plan is known and survive job failures so a paused
 * generation can be resumed without regenerating finished phases.
 */
class SimulatorAiPhase extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    public const STATUS_FAILED = 'failed';

    public const PHASE_PLAN = 'plan';

    public const PHASE_HTML = 'html';

    public const PHASE_CSS = 'css';

    public const PHASE_JS = 'js';

    protected $table = 'simulator_ai_phases';

    protected $fillable = [
        'generation_id',
        'position',
        'phase',
        'label',
        'status',
        'content',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'position' => 'integer',
        'attempts' => 'integer',
    ];

    public function generation(): BelongsTo
    {
        return $this->belongsTo(SimulatorAiGeneration::class, 'generation_id');
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE && trim((string) $this->content) !== '';
    }

    public function markDone(string $content, int $attempts): void
    {
        $this->update([
            'status' => self::STATUS_DONE,
            'content' => $content,
            'attempts' => $attempts,
            'last_error' => null,
        ]);
    }

    public function markFailed(string $error, int $attempts): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'attempts' => $attempts,
            'last_error' => mb_substr($error, 0, 2000),
        ]);
    }
}
