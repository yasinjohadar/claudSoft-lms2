<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGift extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_GRANTED = 'granted';

    public const STATUS_REVOKED = 'revoked';

    public const CONTENT_UPLOAD = 'upload';

    public const CONTENT_EXTERNAL = 'external';

    public const TARGET_TYPES = [
        'single',
        'multiple',
        'group',
        'course',
        'course_group',
    ];

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'content_mode',
        'preview_url',
        'preview_file_path',
        'preview_file_name',
        'preview_mime_type',
        'download_url',
        'download_file_path',
        'download_file_name',
        'download_mime_type',
        'download_file_size',
        'target_type',
        'target_payload',
        'status',
        'granted_at',
        'granted_by',
        'last_regranted_at',
        'created_by',
    ];

    protected $casts = [
        'target_payload' => 'array',
        'granted_at' => 'datetime',
        'last_regranted_at' => 'datetime',
        'download_file_size' => 'integer',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(StudentGiftRecipient::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isGranted(): bool
    {
        return $this->status === self::STATUS_GRANTED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isUploadMode(): bool
    {
        return $this->content_mode === self::CONTENT_UPLOAD;
    }

    public function isExternalMode(): bool
    {
        return $this->content_mode === self::CONTENT_EXTERNAL;
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return resolve_storage_image_url(
            ['gift_images', 'public'],
            $this->image_path,
            asset('frontend/assets/images/placeholder.jpg')
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_GRANTED => 'ممنوحة',
            self::STATUS_REVOKED => 'ملغاة',
            default => 'مسودة',
        };
    }

    public function getTargetTypeLabelAttribute(): string
    {
        return match ($this->target_type) {
            'single' => 'طالب واحد',
            'multiple' => 'عدة طلاب',
            'group' => 'مجموعة',
            'course' => 'كورس',
            'course_group' => 'كورس + مجموعة',
            default => '—',
        };
    }
}
