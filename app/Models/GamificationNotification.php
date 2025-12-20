<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class GamificationNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'action_url',
        'related_type',
        'related_id',
        'metadata',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة المتعددة الأشكال مع الكيان المرتبط
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: الإشعارات غير المقروءة
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: الإشعارات المقروءة
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: تصفية حسب النوع
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: الإشعارات الحديثة
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Accessor: الوقت منذ الإرسال
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->locale('ar')->diffForHumans();
    }

    /**
     * Accessor: الأيقونة بصيغة HTML
     */
    public function getIconHtmlAttribute(): string
    {
        if (!$this->icon) {
            return '🔔';
        }

        // إذا كانت الأيقونة emoji
        if (mb_strlen($this->icon) <= 4) {
            return $this->icon;
        }

        // إذا كانت الأيقونة font awesome class
        return '<i class="' . $this->icon . '"></i>';
    }

    /**
     * تحديد الإشعار كمقروء
     */
    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * تحديد الإشعار كغير مقروء
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}
