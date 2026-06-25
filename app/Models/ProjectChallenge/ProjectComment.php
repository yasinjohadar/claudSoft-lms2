<?php

namespace App\Models\ProjectChallenge;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_showcase_id',
        'user_id',
        'parent_id',
        'body',
        'is_edited',
        'is_hidden',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function showcase(): BelongsTo
    {
        return $this->belongsTo(ProjectShowcase::class, 'project_showcase_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ProjectCommentLike::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }
}
