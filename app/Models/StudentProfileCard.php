<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfileCard extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'job_title',
        'bio',
        'social_links',
        'theme',
        'is_public',
        'admin_enabled',
        'qr_enabled',
        'qr_code_path',
    ];

    protected $casts = [
        'social_links' => 'array',
        'theme' => 'array',
        'is_public' => 'boolean',
        'admin_enabled' => 'boolean',
        'qr_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('is_public', true)->where('admin_enabled', true);
    }

    public function scopeForSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function getPublicUrlAttribute(): string
    {
        return route('frontend.profile-card.show', $this->slug);
    }

    public function enabledSocialLinks(): array
    {
        $links = collect($this->social_links ?? [])
            ->filter(fn ($link) => ! empty($link['enabled']) && ! empty($link['url']))
            ->sortBy(fn ($link) => $link['sort_order'] ?? 0)
            ->values()
            ->all();

        return $links;
    }

    public function resolvedTheme(): array
    {
        $defaults = config('profile-card.defaults.theme', []);
        $theme = is_array($this->theme) ? $this->theme : [];

        return array_merge($defaults, $theme);
    }
}
