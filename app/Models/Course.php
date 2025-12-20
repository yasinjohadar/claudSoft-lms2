<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_category_id',
        'title',
        'slug',
        'code',
        'description',
        'short_description',
        'image',
        'instructor_id',
        'level',
        'language',
        'duration_in_hours',
        'price',
        'is_free',
        'is_published',
        'is_visible',
        'featured',
        'enrollment_type',
        'auto_enroll',
        'max_students',
        'start_date',
        'end_date',
        'available_from',
        'available_until',
        'enrollment_start_date',
        'enrollment_end_date',
        'completion_criteria',
        'sort_order',
        'meta_keywords',
        'meta_description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_visible' => 'boolean',
        'featured' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'passing_percentage' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'enrollment_start_date' => 'datetime',
        'enrollment_end_date' => 'datetime',
    ];

    // Relationships

    /**
     * Get the category that owns the course.
     */
    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    /**
     * Get the sections for the course.
     */
    public function sections()
    {
        return $this->hasMany(CourseSection::class, 'course_id', 'id');
    }

    /**
     * Get all modules through sections.
     */
    public function modules()
    {
        return $this->hasManyThrough(
            CourseModule::class,
            CourseSection::class,
            'course_id',      // Foreign key on course_sections table
            'section_id',     // Foreign key on course_modules table
            'id',             // Local key on courses table
            'id'              // Local key on course_sections table
        );
    }

    /**
     * Get the enrollments for the course.
     */
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get the students enrolled in the course.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'student_id')
                    ->withPivot(['enrollment_status', 'completion_percentage', 'enrolled_at', 'completed_at'])
                    ->withTimestamps();
    }

    /**
     * Get the main instructor for the course.
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the instructors for the course.
     */
    public function instructors()
    {
        return $this->belongsToMany(User::class, 'course_instructors', 'course_id', 'instructor_id')
                    ->withPivot(['role', 'permissions'])
                    ->withTimestamps();
    }

    /**
     * Get the groups for the course.
     */
    public function groups()
    {
        return $this->hasMany(CourseGroup::class);
    }

    /**
     * Get the access restrictions for the course.
     */
    public function accessRestrictions()
    {
        return $this->hasMany(CourseAccessRestriction::class);
    }

    /**
     * Get the user who created the course.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the course.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the reviews for the course.
     */
    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    /**
     * Get only approved reviews for the course.
     */
    public function approvedReviews()
    {
        return $this->hasMany(CourseReview::class)->approved();
    }

    // Scopes

    /**
     * Scope a query to only include published courses.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include visible courses.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include featured courses.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include available courses based on dates.
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('available_until')->orWhere('available_until', '>=', $now);
        });
    }

    /**
     * Scope a query to only include courses with open enrollment.
     */
    public function scopeEnrollmentOpen($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('enrollment_start_date')->orWhere('enrollment_start_date', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('enrollment_end_date')->orWhere('enrollment_end_date', '>=', $now);
        });
    }

    // Helper Methods

    /**
     * Check if the course is available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $this->available_from > $now) {
            return false;
        }

        if ($this->available_until && $this->available_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Check if enrollment is open.
     */
    public function isEnrollmentOpen(): bool
    {
        $now = now();

        if ($this->enrollment_start_date && $this->enrollment_start_date > $now) {
            return false;
        }

        if ($this->enrollment_end_date && $this->enrollment_end_date < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get the final price after discount.
     */
    public function getFinalPrice(): float
    {
        return $this->discount_price ?? $this->price ?? 0;
    }

    /**
     * Check if the course has a discount.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }

    /**
     * Get the discount percentage.
     */
    public function getDiscountPercentage(): ?int
    {
        if (!$this->hasDiscount() || !$this->price) {
            return null;
        }

        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    /**
     * Get total enrolled students count.
     */
    public function getEnrolledStudentsCount(): int
    {
        return $this->enrollments()->where('enrollment_status', 'active')->count();
    }

    /**
     * Check if the course is full.
     */
    public function isFull(): bool
    {
        if (!$this->max_students) {
            return false;
        }

        return $this->getEnrolledStudentsCount() >= $this->max_students;
    }

    /**
     * Get average rating for the course.
     */
    public function getAverageRating(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Get total approved reviews count.
     */
    public function getReviewsCount(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Get rating distribution (count per rating).
     */
    public function getRatingDistribution(): array
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->approvedReviews()->where('rating', $i)->count();
        }
        return $distribution;
    }
}
