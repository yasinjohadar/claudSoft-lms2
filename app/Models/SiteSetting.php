<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory, LogsModelActivity;

    protected string $activityLogName = 'settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * الحصول على قيمة إعداد معين
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        // تحويل القيمة إلى boolean إذا كانت 'true' أو 'false'
        $value = $setting->value;
        if ($value === 'true' || $value === '1') {
            return true;
        }
        if ($value === 'false' || $value === '0') {
            return false;
        }

        return $value;
    }

    /**
     * تعيين قيمة إعداد معين
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return SiteSetting
     */
    public static function setValue(string $key, $value, ?string $description = null): SiteSetting
    {
        // تحويل boolean إلى string
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } else {
            $value = (string) $value;
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    /**
     * التحقق من تفعيل التسجيل العام
     *
     * @return bool
     */
    public static function isPublicRegistrationEnabled(): bool
    {
        return static::getValue('registration_public_enabled', true);
    }

    public static function isStudentProfileCompletionForced(): bool
    {
        return (bool) static::getValue('force_student_profile_completion', false);
    }

    public static function isLocalDevLoginEnabled(): bool
    {
        return (bool) static::getValue('local_dev_login_enabled', false);
    }

    public static function isProfileCardEnabledForSilver(): bool
    {
        return (bool) static::getValue('profile_card_enabled_silver', false);
    }

    public static function isProfileCardEnabledForGold(): bool
    {
        return (bool) static::getValue('profile_card_enabled_gold', true);
    }
}
