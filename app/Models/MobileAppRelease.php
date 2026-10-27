<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * إصدار من تطبيق الجوال (أندرويد / iOS) يديره الأدمن.
 * التطبيق يسأل عن أحدث إصدار نشط لمنصته ويقارن رقم البناء.
 */
class MobileAppRelease extends Model
{
    public const PLATFORMS = ['android' => 'أندرويد', 'ios' => 'iOS'];

    public const DISTRIBUTIONS = ['store' => 'المتجر (Google Play / App Store)', 'direct' => 'رابط مباشر (APK)'];

    protected $fillable = [
        'platform',
        'version_name',
        'build_number',
        'release_notes',
        'distribution',
        'download_url',
        'is_forced',
        'is_active',
        'notified_at',
        'notified_count',
        'created_by',
    ];

    protected $casts = [
        'build_number' => 'integer',
        'is_forced' => 'boolean',
        'is_active' => 'boolean',
        'notified_at' => 'datetime',
        'notified_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    public function getDistributionLabelAttribute(): string
    {
        return self::DISTRIBUTIONS[$this->distribution] ?? $this->distribution;
    }

    /**
     * ملاحظات الإصدار كسطور (كل سطر نقطة، وتُحذف علامات القوائم في بدايته).
     *
     * @return list<string>
     */
    public function notesLines(): array
    {
        $lines = preg_split('/\R/u', (string) $this->release_notes) ?: [];
        $lines = array_map(fn ($line) => trim(preg_replace('/^\s*[-•*]\s*/u', '', $line)), $lines);

        return array_values(array_filter($lines, fn ($line) => $line !== ''));
    }
}
