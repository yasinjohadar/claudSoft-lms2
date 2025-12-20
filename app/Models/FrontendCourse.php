<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FrontendCourse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'category_id',
        'instructor_id',
        'what_you_learn',
        'requirements',
        'level',
        'language',
        'duration',
        'lessons_count',
        'thumbnail',
        'preview_video',
        'cover_image',
        'price',
        'discount_price',
        'is_free',
        'currency',
        'status',
        'is_featured',
        'is_active',
        'published_at',
        'students_count',
        'rating',
        'reviews_count',
        'views_count',
        // Basic SEO
        'meta_title',
        'meta_description',
        'meta_keywords',
        // Open Graph
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        // Twitter Card
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        // Advanced SEO
        'canonical_url',
        'robots',
        'author',
        'schema_markup',
        'focus_keyword',
        'seo_score',
        'reading_time',
        // Other
        'certificate',
        'lifetime_access',
        'downloadable_resources',
        'order',
    ];

    protected $casts = [
        'what_you_learn' => 'array',
        'schema_markup' => 'array',
        'published_at' => 'datetime',
        'is_free' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'certificate' => 'boolean',
        'lifetime_access' => 'boolean',
        'downloadable_resources' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'duration' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(FrontendCourseCategory::class, 'category_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(FrontendReview::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FrontendCourseSection::class, 'course_id')->orderBy('order');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Helper Methods
    public function getEffectivePriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->hasDiscount()) {
            return 0;
        }

        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function updateRating()
    {
        $reviews = $this->reviews()->where('is_active', true)->get();
        $this->reviews_count = $reviews->count();

        if ($this->reviews_count > 0) {
            $this->rating = $reviews->avg('rating');
        } else {
            $this->rating = 0;
        }

        $this->save();
    }

    public function getLevelNameAttribute()
    {
        $levels = [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
        ];

        return $levels[$this->level] ?? 'مبتدئ';
    }

    public function getStatusNameAttribute()
    {
        $statuses = [
            'draft' => 'مسودة',
            'published' => 'منشور',
            'archived' => 'مؤرشف',
        ];

        return $statuses[$this->status] ?? 'مسودة';
    }

    // SEO Helper Methods
    public function getMetaTitleAttribute($value)
    {
        return $value ?? $this->title;
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?? \Str::limit($this->description, 160);
    }

    public function getOgTitleAttribute($value)
    {
        return $value ?? $this->meta_title ?? $this->title;
    }

    public function getOgDescriptionAttribute($value)
    {
        return $value ?? $this->meta_description ?? \Str::limit($this->description, 160);
    }

    public function getOgImageAttribute($value)
    {
        if ($value) {
            // If og_image is set, check if it's a full URL or relative path
            if (strpos($value, 'http') === 0) {
                return $value;
            }
            // Use url() helper which automatically uses current domain
            $url = url('storage/' . ltrim($value, '/'));
            // Fix double slashes in URL
            return str_replace('://', '://', preg_replace('#([^:])//+#', '$1/', $url));
        }
        return $this->thumbnail ? $this->thumbnail_url : asset('frontend/assets/img/default-course.jpg');
    }

    /**
     * Get the thumbnail URL - handles both local and server environments
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return asset('frontend/assets/img/default-course.jpg');
        }

        // Check if it's an external URL
        if (strpos($this->thumbnail, 'http') === 0) {
            return $this->thumbnail;
        }

        // Use url() helper which automatically uses current domain
        // This ensures it works on both local and production
        $url = url('storage/' . ltrim($this->thumbnail, '/'));
        
        // Fix double slashes in URL (e.g., https://domain.com//storage/...)
        return str_replace('://', '://', preg_replace('#([^:])//+#', '$1/', $url));
    }

    public function getTwitterTitleAttribute($value)
    {
        return $value ?? $this->og_title;
    }

    public function getTwitterDescriptionAttribute($value)
    {
        return $value ?? $this->og_description;
    }

    public function getTwitterImageAttribute($value)
    {
        return $value ?? $this->og_image;
    }

    public function getCanonicalUrlAttribute($value)
    {
        return $value ?? route('frontend.courses.show', $this->slug);
    }

    public function getAuthorAttribute($value)
    {
        return $value ?? $this->instructor->name ?? 'المنصة التعليمية';
    }

    /**
     * Generate Course Schema.org JSON-LD
     */
    public function generateSchemaMarkup(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $this->title,
            'description' => $this->description,
            'provider' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'sameAs' => url('/'),
            ],
            'instructor' => [
                '@type' => 'Person',
                'name' => $this->instructor->name ?? 'مدرب معتمد',
            ],
            'offers' => [
                '@type' => 'Offer',
                'category' => $this->is_free ? 'Free' : 'Paid',
                'price' => $this->is_free ? '0' : $this->effective_price,
                'priceCurrency' => $this->currency,
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $this->rating,
                'reviewCount' => $this->reviews_count,
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'image' => $this->thumbnail_url,
            'hasCourseInstance' => [
                '@type' => 'CourseInstance',
                'courseMode' => 'online',
                'courseWorkload' => 'PT' . ($this->duration ?? 0) . 'H',
            ],
            'educationalLevel' => $this->level,
            'inLanguage' => 'ar',
            'totalHistoricalEnrollment' => $this->students_count,
        ];
    }

    /**
     * Calculate reading time based on description length
     */
    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->description));
        $minutes = ceil($wordCount / 200); // Average reading speed 200 words/minute

        return max(1, $minutes);
    }
}
