<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImpersonationToken extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * العلاقة مع الأدمن الذي أنشأ Token
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * العلاقة مع المستخدم (الطالب) المراد الدخول إليه
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * إنشاء token جديد
     *
     * @param  int  $adminId
     * @param  int  $userId
     * @param  int  $expiresInMinutes
     * @return self
     */
    public static function createToken(int $adminId, int $userId, int $expiresInMinutes = 60): self
    {
        // حذف Tokens القديمة للمستخدم نفسه من نفس الأدمن
        static::where('admin_id', $adminId)
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->delete();

        return static::create([
            'admin_id' => $adminId,
            'user_id' => $userId,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * البحث عن token صالح
     *
     * @param  string  $token
     * @return self|null
     */
    public static function findValidToken(string $token): ?self
    {
        return static::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * تحديد Token كمستخدم
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * التحقق من صلاحية Token
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return is_null($this->used_at) && $this->expires_at->isFuture();
    }

    /**
     * Scope للتحقق من Tokens الصالحة
     */
    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope للتحقق من Tokens المنتهية
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('used_at')
              ->orWhere('expires_at', '<=', now());
        });
    }

    /**
     * حذف Tokens المنتهية
     *
     * @return int
     */
    public static function deleteExpired(): int
    {
        return static::expired()->delete();
    }
}

