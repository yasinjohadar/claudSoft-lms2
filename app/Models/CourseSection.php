<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'section_type',
        'description',
        'is_visible',
        'is_locked',
        'show_unavailable',
        'unlock_conditions',
        'available_from',
        'available_until',
        'sort_order',
        'order_index',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_locked' => 'boolean',
        'show_unavailable' => 'boolean',
        'unlock_conditions' => 'array',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    // Relationships

    /**
     * Get the course that owns the section.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the modules for the section.
     */
    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'section_id')->orderBy('sort_order');
    }

    /**
     * Get the access restrictions for the section.
     */
    public function accessRestrictions()
    {
        return $this->hasMany(SectionAccessRestriction::class, 'section_id');
    }

    /**
     * Get the completion records for the section.
     */
    public function completions()
    {
        return $this->hasMany(SectionCompletion::class, 'section_id');
    }

    /**
     * Get the questions linked to this section.
     */
    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'course_section_questions', 'course_section_id', 'question_id')
            ->withPivot(['question_order', 'question_grade', 'is_required', 'settings'])
            ->orderBy('course_section_questions.question_order')
            ->withTimestamps();
    }

    /**
     * Get the user who created the section.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the section.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    /**
     * Scope a query to only include visible sections.
     */
    public function scopeVisible($query)
    {
        return $query->where('course_sections.is_visible', true);
    }

    /**
     * Scope a query to only include unlocked sections.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Scope a query to only include available sections based on dates.
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('available_until')->orWhere('available_until', '>=', $now);
        });
    }

    // Helper Methods

    /**
     * Check if the section is available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $this->available_from > $now) {
            return false;
        }

        if ($this->available_until && $this->available_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Check if the section is unlocked for a student.
     */
    public function isUnlockedFor(User $student): bool
    {
        if (!$this->is_locked) {
            return true;
        }

        // Check unlock conditions
        if (!$this->unlock_conditions) {
            return false;
        }

        // TODO: Implement unlock conditions logic
        // For now, return true if no conditions or locked is false
        return !$this->is_locked;
    }

    public const SECTION_TYPES = [
        'video',
        'quiz',
        'lesson',
        'simulator',
        'assignment',
        'default',
    ];

    /**
     * @return array<string, array{key: string, icon: string, label: string, tone: string}>
     */
    public static function visualPresets(): array
    {
        return [
            'video' => [
                'key' => 'video',
                'icon' => 'fe-play-circle',
                'label' => 'دروس فيديو',
                'tone' => 'video',
            ],
            'quiz' => [
                'key' => 'quiz',
                'icon' => 'fe-help-circle',
                'label' => 'اختبارات',
                'tone' => 'quiz',
            ],
            'lesson' => [
                'key' => 'lesson',
                'icon' => 'fe-file-text',
                'label' => 'شروحات نصية',
                'tone' => 'lesson',
            ],
            'simulator' => [
                'key' => 'simulator',
                'icon' => 'fe-cpu',
                'label' => 'محاكاة تنفيذ',
                'tone' => 'simulator',
            ],
            'assignment' => [
                'key' => 'assignment',
                'icon' => 'fe-award',
                'label' => 'واجبات وتحديات',
                'tone' => 'assignment',
            ],
            'default' => [
                'key' => 'default',
                'icon' => 'fe-layers',
                'label' => 'عام',
                'tone' => 'default',
            ],
        ];
    }

    /**
     * Visual icon/tone for admin UI — uses saved section_type first,
     * then title keywords, then majority of module types.
     *
     * @return array{key: string, icon: string, label: string, tone: string}
     */
    public function visualPresentation(): array
    {
        $presets = self::visualPresets();
        $savedType = trim((string) ($this->section_type ?? ''));

        if ($savedType !== '' && isset($presets[$savedType])) {
            return $presets[$savedType];
        }

        $fromTitle = $this->inferVisualKeyFromTitle((string) $this->title);

        if ($fromTitle !== null) {
            return $presets[$fromTitle];
        }

        $fromModules = $this->inferVisualKeyFromModules();

        return $presets[$fromModules] ?? $presets['default'];
    }

    protected function inferVisualKeyFromTitle(string $title): ?string
    {
        $t = mb_strtolower(trim($title));

        if ($t === '') {
            return null;
        }

        // Order matters: more specific phrases first.
        $rules = [
            'simulator' => ['محاكاة', 'محاكيات', 'تنفيذ', 'simulator'],
            'quiz' => ['اختبار', 'اختبارات', 'إختبار', 'إختبارات', 'امتحان', 'امتحانات', 'quiz'],
            'assignment' => ['واجب', 'واجبات', 'تحدي', 'تحديات', 'مشروع', 'مشاريع', 'assignment', 'challenge'],
            'video' => ['دروس شرح', 'دروس الفيديو', 'فيديو', 'فيديوهات', 'شرح مرئي', 'video'],
            'lesson' => ['شروحات', 'شرح نص', 'نصي', 'نصية', 'ملحق', 'ملحقات', 'روابط', 'مقال', 'توثيق', 'lesson', 'resource'],
        ];

        foreach ($rules as $key => $needles) {
            foreach ($needles as $needle) {
                if (mb_strpos($t, mb_strtolower($needle)) !== false) {
                    return $key;
                }
            }
        }

        return null;
    }

    protected function inferVisualKeyFromModules(): string
    {
        if (! $this->relationLoaded('modules')) {
            $this->load('modules:id,section_id,module_type');
        }

        $counts = $this->modules
            ->pluck('module_type')
            ->filter()
            ->countBy()
            ->all();

        if ($counts === []) {
            return 'default';
        }

        arsort($counts);
        $top = (string) array_key_first($counts);

        return match ($top) {
            'video' => 'video',
            'quiz', 'question' => 'quiz',
            'lesson', 'documentation', 'resource' => 'lesson',
            'simulator' => 'simulator',
            'assignment', 'challenge', 'programming_challenge' => 'assignment',
            default => 'default',
        };
    }
}
