<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'color',
        'is_pinned',
        'is_favorite',
        'is_archived',
        'reminder_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_favorite' => 'boolean',
        'is_archived' => 'boolean',
        'reminder_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope للملاحظات المثبتة
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope للملاحظات المفضلة
     */
    public function scopeFavorite($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope للملاحظات غير المؤرشفة
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope للملاحظات المؤرشفة
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope حسب التصنيف
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * التصنيفات المتاحة
     */
    public static function getCategories()
    {
        return [
            'personal' => ['name' => 'شخصي', 'icon' => '📝', 'color' => '#10b981'],
            'study' => ['name' => 'دراسي', 'icon' => '📚', 'color' => '#3b82f6'],
            'work' => ['name' => 'عمل', 'icon' => '💼', 'color' => '#f59e0b'],
            'important' => ['name' => 'مهم', 'icon' => '⭐', 'color' => '#ef4444'],
            'todo' => ['name' => 'قوائم المهام', 'icon' => '✅', 'color' => '#8b5cf6'],
            'ideas' => ['name' => 'أفكار', 'icon' => '💡', 'color' => '#ec4899'],
        ];
    }
}
