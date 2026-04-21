<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WapiAutomationRule extends Model
{
    protected $fillable = [
        'event_key',
        'is_active',
        'priority',
        'sort_order',
        'wapi_template_id',
        'template_name',
        'language',
        'course_id',
        'group_id',
        'module_id',
        'lesson_id',
        'header_variables',
        'body_variables',
        'cooldown_seconds',
        'dedupe_template',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'header_variables' => 'array',
            'body_variables' => 'array',
            'priority' => 'integer',
            'sort_order' => 'integer',
            'cooldown_seconds' => 'integer',
        ];
    }

    public function wapiTemplate(): BelongsTo
    {
        return $this->belongsTo(WapiTemplate::class, 'wapi_template_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function specificityScore(): int
    {
        $n = 0;
        if ($this->course_id !== null) {
            $n++;
        }
        if ($this->group_id !== null) {
            $n++;
        }
        if ($this->module_id !== null) {
            $n++;
        }
        if ($this->lesson_id !== null) {
            $n++;
        }

        return $n;
    }

    public function effectiveTemplateName(): ?string
    {
        if ($this->template_name) {
            return $this->template_name;
        }
        $t = $this->relationLoaded('wapiTemplate') ? $this->wapiTemplate : $this->wapiTemplate()->first();

        return $t?->name;
    }

    public function effectiveLanguage(): ?string
    {
        if ($this->language) {
            return $this->language;
        }
        $t = $this->relationLoaded('wapiTemplate') ? $this->wapiTemplate : $this->wapiTemplate()->first();

        return $t?->language;
    }
}
