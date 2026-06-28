<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentationPageLink extends Model
{
    protected $fillable = [
        'documentation_page_id',
        'linkable_type',
        'linkable_id',
        'placement',
        'course_module_id',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function documentationPage(): BelongsTo
    {
        return $this->belongsTo(DocumentationPage::class);
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function courseModule(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeReference($query)
    {
        return $query->where('placement', 'reference');
    }

    public function scopeCurriculum($query)
    {
        return $query->where('placement', 'curriculum');
    }
}
