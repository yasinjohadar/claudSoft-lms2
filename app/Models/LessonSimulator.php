<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LessonSimulator extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'draft' => 'مسودة',
        'published' => 'منشور',
        'archived' => 'مؤرشف',
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'topic_key',
        'spec_json',
        'spec_version',
        'render_mode',
        'simulator_archetype',
        'bundle_path',
        'status',
        'languages',
        'ai_generation_meta',
        'created_by',
    ];

    protected $casts = [
        'spec_json' => 'array',
        'languages' => 'array',
        'ai_generation_meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $simulator) {
            if (empty($simulator->slug)) {
                $simulator->slug = static::uniqueSlug($simulator->title);
            }
        });

        static::deleting(function (self $simulator) {
            if ($simulator->isHtmlBundle()) {
                app(\App\Services\Simulator\SimulatorBundleStorage::class)->delete($simulator->slug);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'simulator';
        $slug = $base;
        $i = 1;

        while (static::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_lesson_simulator')
            ->withTimestamps();
    }

    public function courseModules(): MorphMany
    {
        return $this->morphMany(CourseModule::class, 'modulable');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isHtmlBundle(): bool
    {
        return ($this->render_mode ?? config('simulator.default_render_mode', 'html_bundle')) === 'html_bundle';
    }

    public function hasPlayableContent(): bool
    {
        if ($this->isHtmlBundle()) {
            return ! empty($this->bundle_path) && app(\App\Services\Simulator\SimulatorBundleStorage::class)->exists($this->slug);
        }

        return count($this->spec_json['sections'] ?? []) > 0;
    }

    public function playerUrl(?CourseModule $module = null): string
    {
        $params = ['slug' => $this->slug];
        if ($module) {
            $params['module'] = $module->id;
        }

        return route('frontend.simulator.show', $params);
    }

    public function playUrl(?CourseModule $module = null): string
    {
        $params = ['slug' => $this->slug];
        if ($module) {
            $params['module'] = $module->id;
        }

        return route('frontend.simulator.play', $params);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
