<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentationAiBatchItem extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    /**
     * Stopped with saved sections and explicitly queued to be continued. Kept
     * distinct from "pending" so the batch driver reuses the existing
     * generation (finishing only the missing sections) instead of starting the
     * topic from scratch.
     */
    public const STATUS_RESUME_QUEUED = 'resume_queued';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    /** Statuses the batch driver may pick up and run. */
    public const RUNNABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_RESUME_QUEUED,
    ];

    /** Statuses that mean work is still owed for this topic. */
    public const OPEN_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_RESUME_QUEUED,
        self::STATUS_RUNNING,
    ];

    /**
     * A manual "release" is allowed only once the worker has been silent this
     * long, so an admin cannot start a second pipeline over a generation whose
     * first one is still alive and writing sections.
     */
    public const MANUAL_RELEASE_MINUTES = 2;

    protected $table = 'documentation_ai_batch_items';

    protected $fillable = [
        'batch_id',
        'position',
        'topic',
        'status',
        'documentation_ai_generation_id',
        'documentation_page_id',
        'error_message',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DocumentationAiBatch::class, 'batch_id');
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(DocumentationAiGeneration::class, 'documentation_ai_generation_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(DocumentationPage::class, 'documentation_page_id');
    }

    /** Stopped, but its finished sections are saved and can be continued. */
    public function isIncomplete(): bool
    {
        return $this->status === self::STATUS_FAILED
            && (bool) $this->generation?->isResumable();
    }

    /** Claimed by a worker that has since gone silent. */
    public function isStuck(): bool
    {
        return $this->status === self::STATUS_RUNNING
            && (bool) $this->generation?->isStale();
    }

    /** Stuck long enough that an admin may force it back to "incomplete" now. */
    public function canRelease(): bool
    {
        if ($this->status !== self::STATUS_RUNNING) {
            return false;
        }

        $generation = $this->generation;
        if (! $generation) {
            return $this->updated_at?->lt(now()->subMinutes(self::MANUAL_RELEASE_MINUTES)) ?? false;
        }

        $last = $generation->heartbeat_at ?? $generation->started_at ?? $generation->updated_at;

        return $last !== null && $last->lt(now()->subMinutes(self::MANUAL_RELEASE_MINUTES));
    }

    public function toStatusPayload(): array
    {
        $generation = $this->generation;

        // Section detail now also matters for a stopped topic — that is exactly
        // the row that has to show "12 of 13 done" next to its resume button.
        // Safe against N+1 because isStaged()/isResumable()/sectionSummary() all
        // read the eager-loaded relation, and every call site loads
        // items.generation.sections.
        $wantsDetail = $generation !== null && in_array($this->status, [
            self::STATUS_RUNNING,
            self::STATUS_RESUME_QUEUED,
            self::STATUS_FAILED,
        ], true);

        $sections = ($wantsDetail && $generation->isStaged())
            ? $generation->sectionSummary()
            : null;

        $sectionsPct = ($sections && $sections['planned'] > 0)
            ? (int) round($sections['done'] / $sections['planned'] * 100)
            : null;

        return [
            'id' => $this->id,
            'position' => (int) $this->position,
            'topic' => $this->topic,
            'status' => $this->status,
            'progress' => $wantsDetail ? (int) $generation->progress : null,
            'stage_label' => $wantsDetail ? $generation->stage_label : null,
            'sections' => $sections,
            'sections_progress' => $sectionsPct,
            'progress_hint' => $wantsDetail ? $generation->progressHint() : null,
            'generation_status' => $generation?->status,
            'partial_available' => $sections !== null && $sections['done'] > 0,
            'page_id' => $this->documentation_page_id,
            'edit_url' => $this->documentation_page_id
                ? route('admin.docs.pages.edit', $this->documentation_page_id)
                : null,
            'error_message' => $this->error_message,
            'resumable' => $this->isIncomplete(),
            'is_incomplete' => $this->isIncomplete(),
            'is_stuck' => $this->isStuck(),
            'can_release' => $this->canRelease(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
