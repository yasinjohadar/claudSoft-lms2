<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
      use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_ar', // الاسم بالعربي
        'email',
        'phone',
        'country_code', // رمز الدولة
        'full_phone', // الرقم الكامل بصيغة دولية
        'national_id',
        'nationality_id',
        'password',
        'is_active',
        'is_profile_public',
        'is_connected',
        'avatar',
        'student_id',
        'last_login_at',
        'last_login_ip',
        'last_device_type',
        'date_of_birth',
        'gender',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_profile_public' => 'boolean',
            'notification_preferences' => 'array',
            'email_preferences' => 'array',
        ];
    }

     public function sessions()
    {
        return $this->hasMany(\App\Models\Session::class, 'user_id');
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    /**
     * Get all course enrollments for this user (student).
     */
    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'student_id');
    }

    /**
     * Alias for courseEnrollments() for backward compatibility.
     */
    public function enrollments()
    {
        return $this->courseEnrollments();
    }

    /**
     * Get all courses this user is enrolled in.
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'student_id', 'course_id')
            ->withPivot([
                'enrollment_date',
                'enrollment_status',
                'completion_percentage',
                'last_accessed',
                'enrolled_by'
            ])
            ->withTimestamps();
    }

    // ========================================
    // Gamification System Relationships
    // ========================================

    /**
     * Get user's gamification statistics
     */
    public function stats()
    {
        return $this->hasOne(UserStat::class);
    }

    /**
     * Get all badges this user has earned
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot([
                'awarded_at',
                'reason',
                'related_type',
                'related_id',
                'progress',
                'is_seen',
                'is_featured',
                'points_awarded',
                'metadata'
            ])
            ->withTimestamps();
    }

    /**
     * Get user badge records
     */
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get all achievements this user is working on or has completed
     */
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot([
                'current_value',
                'target_value',
                'progress_percentage',
                'status',
                'started_at',
                'completed_at',
                'claimed_at',
                'related_type',
                'related_id',
                'progress_data',
                'points_claimed',
                'xp_claimed',
                'is_notified'
            ])
            ->withTimestamps();
    }

    /**
     * Get user achievement records
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Get all points transactions for this user
     */
    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class);
    }

    /**
     * Get user's daily streak record
     */
    public function dailyStreak()
    {
        return $this->hasOne(DailyStreak::class);
    }

    /**
     * Get all challenges this user is participating in
     */
    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'user_challenges')
            ->withPivot([
                'current_value',
                'target_value',
                'progress_percentage',
                'status',
                'joined_at',
                'started_at',
                'completed_at',
                'expires_at',
                'points_earned',
                'xp_earned',
                'rewards_claimed',
                'progress_data',
                'team_id',
                'is_team_leader',
                'is_notified',
                'attempts_count'
            ])
            ->withTimestamps();
    }

    /**
     * Get user challenge records
     */
    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    /**
     * Get all rewards this user has purchased
     */
    public function rewards()
    {
        return $this->belongsToMany(RewardCatalog::class, 'user_rewards', 'user_id', 'reward_id')
            ->withPivot([
                'purchased_at',
                'points_spent',
                'status',
                'delivery_code',
                'delivery_details',
                'delivered_at',
                'claimed_at',
                'expires_at',
                'is_expired',
                'transaction_id',
                'approved_by',
                'approved_at',
                'admin_notes',
                'metadata'
            ])
            ->withTimestamps();
    }

    /**
     * Get user reward records
     */
    public function userRewards()
    {
        return $this->hasMany(UserReward::class);
    }

    /**
     * Get all leaderboard entries for this user
     */
    public function leaderboardEntries()
    {
        return $this->hasMany(LeaderboardEntry::class);
    }

    /**
     * Get user's current experience level
     */
    public function experienceLevel()
    {
        return $this->belongsTo(ExperienceLevel::class, 'current_level', 'level');
    }

    /**
     * Get all notifications for this user
     */
    public function gamificationNotifications()
    {
        return $this->hasMany(GamificationNotification::class);
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(array $preferences): bool
    {
        return $this->update(['notification_preferences' => $preferences]);
    }

    // ========================================
    // Phone & WhatsApp Methods
    // ========================================

    /**
     * Get WhatsApp formatted phone number
     */
    public function getWhatsappNumberAttribute(): ?string
    {
        if (!$this->full_phone) {
            return null;
        }

        // إرجاع الرقم بصيغة WhatsApp (بدون + أو مسافات)
        return preg_replace('/[^0-9]/', '', $this->full_phone);
    }

    /**
     * Get WhatsApp URL
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        $number = $this->whatsapp_number;

        if (!$number) {
            return null;
        }

        return "https://wa.me/{$number}";
    }

    /**
     * Set full phone when country code or phone changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            // تحديث full_phone تلقائياً عند حفظ country_code أو phone
            if ($user->country_code && $user->phone) {
                $user->full_phone = $user->country_code . $user->phone;
            }
        });
    }
}