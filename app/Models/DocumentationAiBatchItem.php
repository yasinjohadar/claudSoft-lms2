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
        return [
            'position' => (int) $this->position,
            'topic' => $this->topic,
            'status' => $this->status,
            'page_id' => $this->documentation_page_id,
            'edit_url' => $this->documentation_page_id
                ? route('admin.docs.pages.edit', $this->documentation_page_id)
                : null,
            'error_message' => $this->error_message,
        ];
    }
}
