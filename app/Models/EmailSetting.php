<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailSetting extends Model
{
    protected $fillable = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'is_active',
        'provider',
        'test_results',
        'last_tested_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'test_results' => 'array',
        'last_tested_at' => 'datetime',
        'mail_port' => 'integer',
    ];

    /**
     * Encrypt password before saving
     */
    public function setMailPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['mail_password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when retrieving
     */
    public function getMailPasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get the active email configuration
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Activate this configuration and deactivate others
     */
    public function activate()
    {
        // Deactivate all others
        static::where('id', '!=', $this->id)->update(['is_active' => false]);

        // Activate this one
        $this->update(['is_active' => true]);

        // Update .env configuration
        $this->applyToConfig();
    }

    /**
     * Apply settings to Laravel config
     */
    public function applyToConfig()
    {
        config([
            'mail.default' => $this->mail_mailer,
            'mail.mailers.smtp.host' => $this->mail_host,
            'mail.mailers.smtp.port' => $this->mail_port,
            'mail.mailers.smtp.username' => $this->mail_username,
            'mail.mailers.smtp.password' => $this->mail_password,
            'mail.mailers.smtp.encryption' => $this->mail_encryption,
            'mail.from.address' => $this->mail_from_address,
            'mail.from.name' => $this->mail_from_name,
        ]);
    }

    /**
     * Get predefined provider configurations
     */
    public static function getProviderPresets()
    {
        return [
            'gmail' => [
                'name' => 'Gmail',
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
            ],
            'outlook' => [
                'name' => 'Outlook/Hotmail',
                'mail_host' => 'smtp-mail.outlook.com',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
            ],
            'yahoo' => [
                'name' => 'Yahoo Mail',
                'mail_host' => 'smtp.mail.yahoo.com',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
            ],
            'sendgrid' => [
                'name' => 'SendGrid',
                'mail_host' => 'smtp.sendgrid.net',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
            ],
            'mailgun' => [
                'name' => 'Mailgun',
                'mail_host' => 'smtp.mailgun.org',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
            ],
            'custom' => [
                'name' => 'إعدادات مخصصة',
                'mail_host' => '',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
            ],
        ];
    }
}
