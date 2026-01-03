<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSetting extends Model
{
    protected $fillable = [
        'gtm_container_id',
        'gtm_enabled',
        'search_console_verification',
        'search_console_enabled',
    ];

    protected $casts = [
        'gtm_enabled' => 'boolean',
        'search_console_enabled' => 'boolean',
    ];

    /**
     * الحصول على إعدادات Google (سجل واحد فقط)
     */
    public static function getSettings()
    {
        return static::first() ?? static::createDefault();
    }

    /**
     * إنشاء إعدادات افتراضية
     */
    public static function createDefault()
    {
        return static::create([
            'gtm_container_id' => null,
            'gtm_enabled' => false,
            'search_console_verification' => null,
            'search_console_enabled' => false,
        ]);
    }
}
