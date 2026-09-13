<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One planned section of a staged documentation generation.
 *
 * Rows are created once from the outline and survive job failures so a paused
 * generation can be resumed without regenerating finished sections.
 */
class DocumentationAiSection extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    public const STATUS_FAILED = 'failed';

    protected $table = 'documentation_ai_sections';

    protected $fillable = [
        'generation_id',
        'position',
        'heading',
        'brief',
        'status',
        'html',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'position' => 'integer',
        'attempts' => 'integer',
    ];

    public function generation(): BelongsTo
    {
        return $this->belongsTo(DocumentationAiGeneration::class, 'generation_id');
    }

    public function isDone(): bool
    {
        if ($this->status !== self::STATUS_DONE) {
            return false;
        }

        // Callers that poll this relation every few seconds load the row without
        // its LONGTEXT body and project LENGTH(html) instead, so the emptiness
        // check works off that projection when the body itself is absent.
        if (! array_key_exists('html', $this->attributes)
            && array_key_exists('html_length', $this->attributes)
        ) {
            return (int) $this->attributes['html_length'] > 0;
        }

        return trim((string) $this->html) !== '';
    }

    public function markDone(string $html, int $attempts): void
    {
        $this->update([
            'status' => self::STATUS_DONE,
            'html' => $html,
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
