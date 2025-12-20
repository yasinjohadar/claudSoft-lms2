<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quizzes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'description',
        'instructions',
        'quiz_type',
        'passing_grade',
        'max_score',
        'time_limit',
        'attempts_allowed',
        'shuffle_questions',
        'shuffle_answers',
        'show_correct_answers',
        'show_correct_answers_after',
        'feedback_mode',
        'allow_review',
        'show_grade_immediately',
        'available_from',
        'due_date',
        'available_until',
        'is_published',
        'is_visible',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'passing_grade' => 'decimal:2',
        'max_score' => 'decimal:2',
        'time_limit' => 'integer',
        'attempts_allowed' => 'integer',
        'shuffle_questions' => 'boolean',
        'shuffle_answers' => 'boolean',
        'show_correct_answers' => 'boolean',
        'allow_review' => 'boolean',
        'show_grade_immediately' => 'boolean',
        'available_from' => 'datetime',
        'due_date' => 'datetime',
        'available_until' => 'datetime',
        'is_published' => 'boolean',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the course that owns the quiz.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the lesson that owns the quiz.
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * Get the user who created the quiz.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the quiz.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the quiz questions for the quiz.
     */
    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('question_order');
    }

    /**
     * Get the attempts for the quiz.
     */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    /**
     * Get the settings for the quiz.
     */
    public function settings()
    {
        return $this->hasOne(QuizSettings::class, 'quiz_id');
    }

    /**
     * Get the analytics for the quiz.
     */
    public function analytics()
    {
        return $this->hasMany(QuizAnalytics::class, 'quiz_id');
    }

    /**
     * Get questions through quiz_questions relationship.
     */
    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'quiz_questions', 'quiz_id', 'question_id')
            ->withPivot('question_order', 'question_grade', 'is_required')
            ->withTimestamps()
            ->orderBy('quiz_questions.question_order');
    }

    /**
     * Get the course module relationship (polymorphic).
     */
    public function courseModule()
    {
        return $this->morphOne(CourseModule::class, 'modulable');
    }

    /**
     * Scope a query to only include published quizzes.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include visible quizzes.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include available quizzes.
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where('is_published', true)
            ->where('is_visible', true)
            ->where(function($q) use ($now) {
                $q->whereNull('available_from')
                  ->orWhere('available_from', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('available_until')
                  ->orWhere('available_until', '>=', $now);
            });
    }

    /**
     * Scope a query by course.
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope a query by lesson.
     */
    public function scopeForLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Scope a query by quiz type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('quiz_type', $type);
    }

    /**
     * Check if quiz is currently available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if (!$this->is_published || !$this->is_visible) {
            return false;
        }

        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if quiz is past due date.
     */
    public function isPastDue(): bool
    {
        return $this->due_date && now()->gt($this->due_date);
    }

    /**
     * Check if quiz has time limit.
     */
    public function hasTimeLimit(): bool
    {
        return $this->time_limit !== null && $this->time_limit > 0;
    }

    /**
     * Check if unlimited attempts are allowed.
     */
    public function hasUnlimitedAttempts(): bool
    {
        return $this->attempts_allowed === null;
    }

    /**
     * Get remaining attempts for a student.
     */
    public function getRemainingAttempts(int $studentId): ?int
    {
        if ($this->hasUnlimitedAttempts()) {
            return null; // Unlimited
        }

        $usedAttempts = $this->attempts()
            ->where('student_id', $studentId)
            ->where('status', '!=', 'abandoned')
            ->count();

        return max(0, $this->attempts_allowed - $usedAttempts);
    }

    /**
     * Check if student can attempt the quiz.
     */
    public function canAttempt(int $studentId): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $remaining = $this->getRemainingAttempts($studentId);

        return $remaining === null || $remaining > 0;
    }

    /**
     * Calculate total score from questions.
     */
    public function calculateMaxScore(): float
    {
        return $this->quizQuestions()
            ->join('question_bank', 'quiz_questions.question_id', '=', 'question_bank.id')
            ->sum(DB::raw('COALESCE(quiz_questions.question_grade, question_bank.default_grade)'));
    }

    /**
     * Get the number of questions in quiz.
     */
    public function getQuestionCount(): int
    {
        return $this->quizQuestions()->count();
    }

    /**
     * Get passing score value.
     */
    public function getPassingScore(): float
    {
        return ($this->max_score * $this->passing_grade) / 100;
    }

    /**
     * Get time limit in minutes.
     */
    public function getTimeLimitInMinutes(): ?int
    {
        return $this->time_limit;
    }

    /**
     * Get time limit in seconds.
     */
    public function getTimeLimitInSeconds(): ?int
    {
        return $this->time_limit ? $this->time_limit * 60 : null;
    }
}
