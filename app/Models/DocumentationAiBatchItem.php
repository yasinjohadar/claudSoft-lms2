<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentationAiBatchItem extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

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

    public function toStatusPayload(): array
    {
        $progress = null;
        $stageLabel = null;
        $sections = null;

        // Only the in-flight item needs live generation detail — completed/failed/
        // pending items don't, so this avoids an extra query per row on every poll.
        if ($this->status === self::STATUS_RUNNING && $this->generation) {
            $progress = (int) $this->generation->progress;
            $stageLabel = $this->generation->stage_label;
            $sections = $this->generation->isStaged() ? $this->generation->sectionSummary() : null;
        }

        return [
            'position' => (int) $this->position,
            'topic' => $this->topic,
            'status' => $this->status,
            'progress' => $progress,
            'stage_label' => $stageLabel,
            'sections' => $sections,
            'page_id' => $this->documentation_page_id,
            'edit_url' => $this->documentation_page_id
                ? route('admin.docs.pages.edit', $this->documentation_page_id)
                : null,
            'error_message' => $this->error_message,
        ];
    }
}
