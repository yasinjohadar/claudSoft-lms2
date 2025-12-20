<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StudentWork extends Model
{
    use SoftDeletes;

    protected $table = 'student_works';

    protected $fillable = [
        'student_id', 'course_id', 'title', 'slug', 'category', 'description',
        'tags', 'attachments', 'gallery', 'image', 'video_url', 'website_url',
        'github_url', 'demo_url', 'technologies', 'completion_date', 'views_count',
        'likes_count', 'rating', 'admin_feedback', 'status', 'approved_by',
        'approved_at', 'is_active', 'is_featured', 'order',
    ];

    protected $casts = [
        'tags' => 'array',
        'attachments' => 'array',
        'gallery' => 'array',
        'completion_date' => 'date',
        'approved_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'order' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'rating' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($work) {
            if (empty($work->slug)) {
                $work->slug = Str::slug($work->title) . '-' . Str::random(6);
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('technologies', 'like', "%{$term}%");
        });
    }

    public static function getCategories()
    {
        return [
            'project' => ['name' => 'مشروع', 'icon' => 'ri-code-box-line', 'color' => 'primary'],
            'assignment' => ['name' => 'واجب', 'icon' => 'ri-file-text-line', 'color' => 'success'],
            'creative' => ['name' => 'عمل إبداعي', 'icon' => 'ri-palette-line', 'color' => 'warning'],
            'research' => ['name' => 'بحث', 'icon' => 'ri-search-line', 'color' => 'info'],
            'other' => ['name' => 'أخرى', 'icon' => 'ri-folder-line', 'color' => 'secondary'],
        ];
    }

    public static function getStatuses()
    {
        return [
            'draft' => ['name' => 'مسودة', 'color' => 'secondary', 'icon' => 'ri-draft-line'],
            'pending' => ['name' => 'قيد المراجعة', 'color' => 'warning', 'icon' => 'ri-time-line'],
            'approved' => ['name' => 'معتمد', 'color' => 'success', 'icon' => 'ri-checkbox-circle-line'],
            'rejected' => ['name' => 'مرفوض', 'color' => 'danger', 'icon' => 'ri-close-circle-line'],
        ];
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('assets/images/default-work.png');
    }
}
