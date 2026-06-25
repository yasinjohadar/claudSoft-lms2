<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
}
