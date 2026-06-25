<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgrammingLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'description',
        'category',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'monaco_language_id',
        'execution_mode',
        'runtime_slug',
        'file_extension',
        'default_filename',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get questions for this programming language
     */
    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'question_programming_language', 'programming_language_id', 'question_id')
            ->withTimestamps();
    }

    /**
     * Get programming challenges that use this language
     */
    public function challenges()
    {
        return $this->belongsToMany(ProgrammingChallenge::class, 'programming_challenge_language')
            ->withPivot(['is_default', 'editor_tab_label', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * Scope for active languages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for languages that can run in the IDE
     */
    public function scopeRunnable($query)
    {
        return $query->whereIn('execution_mode', ['client_web', 'server']);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get questions count
     */
    public function getQuestionsCountAttribute()
    {
        return $this->questions()->count();
    }
}
