<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupReminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'target_type',
        'target_id',
        'title',
        'message',
        'reminder_type',
        'priority',
        'remind_at',
        'send_email',
        'send_notification',
        'is_active',
        'is_sent',
        'sent_at',
        'recipients_count',
        'read_count',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_notification' => 'boolean',
        'is_active' => 'boolean',
        'is_sent' => 'boolean',
        'remind_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * العلاقة مع المنشئ
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * العلاقة مع الهدف (Polymorphic)
     */
    public function target()
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    /**
     * Scope للتذكيرات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للتذكيرات المرسلة
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * Scope للتذكيرات غير المرسلة
     */
    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Scope حسب النوع
     */
    public function scopeByType($query, $type)
    {
        return $query->where('reminder_type', $type);
    }

    /**
     * Scope حسب الأولوية
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * أنواع التذكيرات
     */
    public static function getReminderTypes()
    {
        return [
            'assignment' => ['name' => 'واجب', 'icon' => '📝', 'color' => 'primary'],
            'exam' => ['name' => 'امتحان', 'icon' => '📋', 'color' => 'danger'],
            'announcement' => ['name' => 'إعلان', 'icon' => '📢', 'color' => 'info'],
            'deadline' => ['name' => 'موعد نهائي', 'icon' => '⏰', 'color' => 'warning'],
            'event' => ['name' => 'حدث', 'icon' => '📅', 'color' => 'success'],
            'meeting' => ['name' => 'اجتماع', 'icon' => '👥', 'color' => 'secondary'],
        ];
    }

    /**
     * مستويات الأولوية
     */
    public static function getPriorities()
    {
        return [
            'low' => ['name' => 'منخفضة', 'icon' => '🟢', 'color' => 'success'],
            'medium' => ['name' => 'متوسطة', 'icon' => '🟡', 'color' => 'warning'],
            'high' => ['name' => 'عالية', 'icon' => '🔴', 'color' => 'danger'],
            'urgent' => ['name' => 'عاجلة', 'icon' => '🚨', 'color' => 'dark'],
        ];
    }

    /**
     * الحصول على المستلمين
     */
    public function getRecipients()
    {
        if ($this->target_type === 'App\Models\Course') {
            return User::whereHas('courseEnrollments', function($query) {
                $query->where('course_id', $this->target_id);
            })->get();
        }

        // يمكن إضافة المزيد من الأنواع هنا
        return collect();
    }
}
