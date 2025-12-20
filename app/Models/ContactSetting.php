<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = [
        'page_title',
        'page_subtitle',
        'address_title',
        'address_text',
        'address_icon',
        'phone_title',
        'phone_numbers',
        'phone_icon',
        'email_title',
        'email_addresses',
        'email_icon',
        'map_embed_url',
        'show_map',
        'social_title',
        'social_subtitle',
        'social_links',
        'working_hours_title',
        'working_hours',
        'show_working_hours',
        'form_title',
        'form_subtitle',
        'form_enabled',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'email_addresses' => 'array',
        'social_links' => 'array',
        'working_hours' => 'array',
        'show_map' => 'boolean',
        'show_working_hours' => 'boolean',
        'form_enabled' => 'boolean',
    ];

    /**
     * الحصول على إعدادات الاتصال (سجل واحد فقط)
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
            'page_title' => 'اتصل بنا',
            'page_subtitle' => 'نحن هنا للإجابة على استفساراتك ومساعدتك',
            'address_title' => 'العنوان',
            'address_text' => 'المملكة العربية السعودية<br>الرياض - حي النخيل',
            'address_icon' => 'fa-location-dot',
            'phone_title' => 'الهاتف',
            'phone_numbers' => [
                ['number' => '+966551966588', 'label' => ''],
                ['number' => '+966551966588', 'label' => ''],
            ],
            'phone_icon' => 'fa-phone',
            'email_title' => 'البريد الإلكتروني',
            'email_addresses' => [
                ['email' => 'info@claudsoft.com', 'label' => ''],
                ['email' => 'support@claudsoft.com', 'label' => ''],
            ],
            'email_icon' => 'fa-envelope',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.564766853959!2d46.67291257603705!3d24.71353557809859!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2f03890d489399%3A0xba974d1c98e79fd5!2sRiyadh%20Saudi%20Arabia!5e0!3m2!1sen!2s!4v1698765432109!5m2!1sen!2s',
            'show_map' => true,
            'social_title' => 'تابعنا على',
            'social_subtitle' => 'كن على اطلاع بأحدث الكورسات والعروض',
            'social_links' => [
                ['platform' => 'facebook', 'url' => '#', 'icon' => 'fa-facebook-f', 'label' => 'فيسبوك', 'enabled' => true],
                ['platform' => 'twitter', 'url' => '#', 'icon' => 'fa-x-twitter', 'label' => 'تويتر', 'enabled' => true],
                ['platform' => 'instagram', 'url' => '#', 'icon' => 'fa-instagram', 'label' => 'إنستجرام', 'enabled' => true],
                ['platform' => 'youtube', 'url' => '#', 'icon' => 'fa-youtube', 'label' => 'يوتيوب', 'enabled' => true],
                ['platform' => 'whatsapp', 'url' => '#', 'icon' => 'fa-whatsapp', 'label' => 'واتساب', 'enabled' => true],
                ['platform' => 'telegram', 'url' => '#', 'icon' => 'fa-telegram', 'label' => 'تيليجرام', 'enabled' => true],
            ],
            'working_hours_title' => 'ساعات العمل',
            'working_hours' => [
                ['day' => 'السبت - الخميس', 'time' => '9:00 ص - 6:00 م'],
                ['day' => 'الجمعة', 'time' => 'مغلق'],
            ],
            'show_working_hours' => true,
            'form_title' => 'أرسل لنا رسالة',
            'form_subtitle' => 'املأ النموذج أدناه وسنتواصل معك في أقرب وقت',
            'form_enabled' => true,
        ]);
    }
}
