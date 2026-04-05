<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCourseAiReportBatch extends Model
{
    protected $table = 'student_course_ai_report_batches';

    protected $fillable = [
        'course_id',
        'created_by',
        'attempt_strategy',
        'since',
        'laravel_ai_model_id',
        'scope',
        'course_group_id',
        'status',
        'items_total',
        'items_succeeded',
        'items_failed',
        'items_skipped',
        'finished_at',
    ];

    protected $casts = [
        'since' => 'date',
        'finished_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courseGroup(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'course_group_id');
    }

    public function laravelAiModel(): BelongsTo
    {
        return $this->belongsTo(LaravelAiModel::class, 'laravel_ai_model_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StudentCourseAiReportBatchItem::class, 'batch_id');
    }

    public function recalculateAggregates(): void
    {
        $byStatus = $this->items()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $queued = (int) ($byStatus['queued'] ?? 0);
        $processing = (int) ($byStatus['processing'] ?? 0);
        $this->items_succeeded = (int) ($byStatus['succeeded'] ?? 0);
        $this->items_failed = (int) ($byStatus['failed'] ?? 0);
        $this->items_skipped = (int) ($byStatus['skipped'] ?? 0);
        $this->items_total = $this->items()->count();

        $pending = $queued + $processing;
        if ($pending === 0 && $this->items_total > 0) {
            $this->status = $this->items_failed > 0 ? 'partial_failed' : 'completed';
            $this->finished_at = $this->finished_at ?? now();
        }

        $this->save();
    }

    /** وصف نطاق الإرسال للواجهة (تجنّب بادئة scope* في Eloquent). */
    public function humanScopeSummary(): string
    {
        if ($this->scope === 'single_group') {
            return $this->courseGroup?->name ?? 'مجموعة';
        }

        return 'كل مجموعات الكورس';
    }
}
