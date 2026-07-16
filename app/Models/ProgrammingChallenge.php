<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

class ProgrammingChallenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'instructions',
        'challenge_type',
        'grading_mode',
        'difficulty',
        'max_score',
        'time_limit_seconds',
        'attempts_allowed',
        'allow_resubmit',
        'is_published',
        'is_standalone',
        'course_id',
        'target_group_id',
        'starter_layout',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'time_limit_seconds' => 'integer',
        'attempts_allowed' => 'integer',
        'allow_resubmit' => 'boolean',
        'is_published' => 'boolean',
        'is_standalone' => 'boolean',
        'course_id' => 'integer',
        'target_group_id' => 'integer',
        'starter_layout' => 'array',
        'settings' => 'array',
    ];

    public function courseModules(): MorphMany
    {
        return $this->morphMany(CourseModule::class, 'modulable');
    }

    public function module(): MorphOne
    {
        return $this->morphOne(CourseModule::class, 'modulable');
    }

    /**
     * @deprecated Prefer targets(); kept for legacy display/sync.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @deprecated Prefer targets(); kept for legacy display/sync.
     */
    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'target_group_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeTarget::class)->orderBy('id');
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(ProgrammingLanguage::class, 'programming_challenge_language')
            ->withPivot(['is_default', 'editor_tab_label', 'sort_order'])
            ->withTimestamps()
            ->orderBy('programming_challenge_language.sort_order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeFile::class);
    }

    public function testCases(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeTestCase::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeAttempt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isRestrictedToCourse(): bool
    {
        return $this->targets()->exists() || $this->course_id !== null;
    }

    public function isRestrictedToGroup(): bool
    {
        return $this->targets()->whereNotNull('group_id')->exists()
            || $this->target_group_id !== null;
    }

    public function hasAudienceTargets(): bool
    {
        if ($this->relationLoaded('targets')) {
            return $this->targets->isNotEmpty();
        }

        return $this->targets()->exists();
    }

    public function isVisibleToStudent(int $studentId): bool
    {
        $targets = $this->relationLoaded('targets')
            ? $this->targets
            : $this->targets()->get();

        if ($targets->isEmpty()) {
            return true;
        }

        $enrolledCourseIds = CourseEnrollment::query()
            ->where('student_id', $studentId)
            ->where('enrollment_status', 'active')
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($enrolledCourseIds === []) {
            return false;
        }

        $memberGroupIds = DB::table('course_group_members')
            ->where('student_id', $studentId)
            ->pluck('group_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($targets as $target) {
            $courseId = (int) $target->course_id;
            if (! in_array($courseId, $enrolledCourseIds, true)) {
                continue;
            }

            if ($target->group_id === null) {
                return true;
            }

            if (in_array((int) $target->group_id, $memberGroupIds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Challenges visible in the student library:
     * - public standalone (no audience targets), or
     * - standalone with at least one matching course/group target.
     */
    public function scopeVisibleToStudent(Builder $query, int $studentId): Builder
    {
        $enrolledCourseIds = CourseEnrollment::query()
            ->where('student_id', $studentId)
            ->where('enrollment_status', 'active')
            ->pluck('course_id');

        $memberGroupIds = DB::table('course_group_members')
            ->where('student_id', $studentId)
            ->pluck('group_id');

        return $query->where('is_standalone', true)
            ->where(function (Builder $outer) use ($enrolledCourseIds, $memberGroupIds) {
                $outer->whereDoesntHave('targets')
                    ->orWhereHas('targets', function (Builder $targetQuery) use ($enrolledCourseIds, $memberGroupIds) {
                        $targetQuery->whereIn('course_id', $enrolledCourseIds)
                            ->where(function (Builder $groupQuery) use ($memberGroupIds) {
                                $groupQuery->whereNull('group_id')
                                    ->orWhereIn('group_id', $memberGroupIds);
                            });
                    });
            });
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeStandalone($query)
    {
        return $query->where('is_standalone', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('challenge_type', $type);
    }

    public function studentAttempts(int $studentId)
    {
        return $this->attempts()->where('user_id', $studentId)->orderByDesc('attempt_number');
    }

    public function canStudentAttempt(int $studentId): bool
    {
        if ($this->attempts_allowed === null) {
            return true;
        }

        $completedCount = $this->studentAttempts($studentId)
            ->whereIn('status', ['submitted', 'graded'])
            ->count();

        return $completedCount < $this->attempts_allowed;
    }

    public function isWebSandbox(): bool
    {
        return $this->challenge_type === 'web_sandbox';
    }

    public function isCodeRunner(): bool
    {
        return $this->challenge_type === 'code_runner';
    }

    public function getDefaultSettings(): array
    {
        return array_merge([
            'show_console' => true,
            'auto_save_interval' => 30,
            'show_test_results' => false,
        ], $this->settings ?? []);
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
                $groupIds = $rows->pluck('group_id')
                    ->filter(fn ($id) => $id !== null)
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                return [
                    'course_id' => (int) $courseId,
                    'group_ids' => $groupIds,
                ];
            })
            ->values()
            ->all();
    }
}
