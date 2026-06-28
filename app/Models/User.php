<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Notifications\ResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;
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
        'phone_verified_at',
        'national_id',
        'nationality_id',
        'password',
        'is_active',
        'is_profile_public',
        'is_connected',
        'avatar',
        'photo',
        'student_id',
        'last_login_at',
        'last_login_ip',
        'last_device_type',
        'date_of_birth',
        'gender',
        'address',
        'city', // المدينة
        'notification_preferences',
        'email_preferences',
        'referral_code',
        'referred_by_user_id',
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
            'phone_verified_at' => 'datetime',
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

    /**
     * Administrative notes attached to this user (e.g. deactivation reasons).
     */
    public function adminNotes()
    {
        return $this->hasMany(UserAdminNote::class, 'user_id')->orderByDesc('occurred_on')->orderByDesc('id');
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    /**
     * Build student profile completion metrics used across student views.
     *
     * @return array{
     *     percentage:int,
     *     completed:int,
     *     total:int,
     *     missing_count:int,
     *     missing_fields:array<int,string>
     * }
     */
    public function getProfileCompletionDataAttribute(): array
    {
        $requiredFields = [
            'name_ar' => 'الاسم بالعربية',
            'name' => 'الاسم بالإنجليزية',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'date_of_birth' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'nationality_id' => 'الدولة',
            'city' => 'المدينة',
            'address' => 'العنوان',
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $label) {
            if (!$this->isProfileFieldCompleted($field)) {
                $missingFields[] = $label;
            }
        }

        $total = count($requiredFields);
        $missingCount = count($missingFields);
        $completed = $total - $missingCount;
        $percentage = (int) round(($completed / max($total, 1)) * 100);

        return [
            'percentage' => $percentage,
            'completed' => $completed,
            'total' => $total,
            'missing_count' => $missingCount,
            'missing_fields' => $missingFields,
        ];
    }

    public function getProfileCompletionPercentageAttribute(): int
    {
        return $this->profile_completion_data['percentage'];
    }

    private function isProfileFieldCompleted(string $field): bool
    {
        if ($field === 'phone') {
            $hasCountryAndPhone = !empty($this->country_code) && !empty($this->phone);
            $hasFullPhone = !empty(trim((string) ($this->full_phone ?? '')));

            return $hasCountryAndPhone || $hasFullPhone;
        }

        $value = $this->{$field} ?? null;

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return !is_null($value);
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

    /**
     * Get all course group memberships for this user.
     */
    public function courseGroupMemberships()
    {
        return $this->hasMany(CourseGroupMember::class, 'student_id');
    }

    // ========================================
    // Student Profile Card
    // ========================================

    public function profileCard()
    {
        return $this->hasOne(StudentProfileCard::class);
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
     * Device tokens used for mobile push notifications (FCM/APNs).
     */
    public function notificationDeviceTokens(): HasMany
    {
        return $this->hasMany(NotificationDeviceToken::class);
    }

    /**
     * Per-event preferences for notification channels.
     */
    public function notificationHubPreferences(): HasMany
    {
        return $this->hasMany(NotificationUserPreference::class);
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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // ========================================
    // Impersonation Methods
    // ========================================

    /**
     * Check if current user is being impersonated.
     *
     * @return bool
     */
    public function isImpersonating(): bool
    {
        return \Illuminate\Support\Facades\Session::has('impersonate');
    }

    /**
     * Get the original admin user who is impersonating.
     *
     * @return \App\Models\User|null
     */
    public function getOriginalUser(): ?User
    {
        if (!$this->isImpersonating()) {
            return null;
        }

        $impersonateData = \Illuminate\Support\Facades\Session::get('impersonate');
        return self::find($impersonateData['original_user_id'] ?? null);
    }

    /**
     * Check if user can impersonate other users.
     *
     * @return bool
     */
    public function canImpersonate(): bool
    {
        return $this->hasRole('admin');
    }
}