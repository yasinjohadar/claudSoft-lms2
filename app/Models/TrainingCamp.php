<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TrainingCamp extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'training_camps';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'category_id',
        'price',
        'start_date',
        'end_date',
        'duration_days',
        'instructor_name',
        'location',
        'max_participants',
        'current_participants',
        'is_active',
        'is_featured',
        'require_payment_receipt',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_days' => 'integer',
        'max_participants' => 'integer',
        'current_participants' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'require_payment_receipt' => 'boolean',
        'order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the category that the training camp belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    /**
     * Get the enrollments for the training camp.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CampEnrollment::class, 'camp_id');
    }

    /**
     * Audience targets (course + group) that control student visibility.
     */
    public function targets(): HasMany
    {
        return $this->hasMany(TrainingCampTarget::class)->orderBy('id');
    }

    /**
     * Get the invoice items for the training camp.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'camp_enrollment_id');
    }

    /**
     * Scope a query to only include active camps.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Camps visible to a student: must have targets and student must be
     * a member of at least one targeted group.
     */
    public function scopeVisibleToStudent(Builder $query, int $studentId): Builder
    {
        $memberGroupIds = DB::table('course_group_members')
            ->where('student_id', $studentId)
            ->pluck('group_id');

        if ($memberGroupIds->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereHas('targets', function (Builder $targetQuery) use ($memberGroupIds) {
            $targetQuery->whereIn('group_id', $memberGroupIds);
        });
    }

    public function isVisibleToStudent(int $studentId): bool
    {
        $targets = $this->relationLoaded('targets')
            ? $this->targets
            : $this->targets()->get();

        if ($targets->isEmpty()) {
            return false;
        }

        $memberGroupIds = DB::table('course_group_members')
            ->where('student_id', $studentId)
            ->pluck('group_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($memberGroupIds === []) {
            return false;
        }

        foreach ($targets as $target) {
            if (in_array((int) $target->group_id, $memberGroupIds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Audience rows for admin forms: one row per course with group_ids[].
     *
     * @return array<int, array{course_id: int, group_ids: array<int, int>}>
     */
    public function audienceRowsForForm(): array
    {
        $targets = $this->relationLoaded('targets')
            ? $this->targets
            : $this->targets()->get();

        if ($targets->isEmpty()) {
            return [];
        }

        return $targets
            ->groupBy('course_id')
            ->map(function ($rows, $courseId) {
                return [
                    'course_id' => (int) $courseId,
                    'group_ids' => $rows->pluck('group_id')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Scope a query to only include featured camps.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include camps that haven't started yet.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include camps that are currently running.
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include camps that have ended.
     */
    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Scope a query to only include camps with available seats.
     */
    public function scopeAvailable($query)
    {
        return $query->whereColumn('current_participants', '<', 'max_participants')
                     ->orWhereNull('max_participants');
    }

    /**
     * Check if the camp is full.
     */
    public function isFull(): bool
    {
        if (is_null($this->max_participants)) {
            return false;
        }

        return $this->current_participants >= $this->max_participants;
    }

    /**
     * Check if the camp has available seats.
     */
    public function hasAvailableSeats(): bool
    {
        if (is_null($this->max_participants)) {
            return true; // No limit means always available
        }

        return $this->current_participants < $this->max_participants;
    }

    /**
     * Get the number of available seats.
     */
    public function availableSeats(): ?int
    {
        if (is_null($this->max_participants)) {
            return null;
        }

        return max(0, $this->max_participants - $this->current_participants);
    }

    /**
     * Check if the camp has started.
     */
    public function hasStarted(): bool
    {
        return $this->start_date->isPast();
    }

    /**
     * Check if the camp has ended.
     */
    public function hasEnded(): bool
    {
        return $this->end_date->isPast();
    }

    /**
     * Check if the camp is currently ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->start_date->isPast() && $this->end_date->isFuture();
    }
}
