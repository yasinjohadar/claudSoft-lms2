<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupRegistrationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'diploma_name',
        'is_registration_enabled',
        'auto_create_user',
        'use_fixed_registration_password',
        'fixed_registration_password',
        'auto_approve_membership',
        'hide_courses_until_membership_approved',
        'send_welcome_email',
        'send_welcome_whatsapp',
        'whatsapp_delivery_mode',
        'email_template_id',
        'whatsapp_template_id',
        'wapi_template_id',
        'wapi_template_language',
        'wapi_body_variables',
        'whatsapp_template',
        'whatsapp_group_link',
        'telegram_group_link',
        'telegram_chat_id',
        'telegram_template_id',
        'send_welcome_telegram',
        'require_email_verification',
        'extra',
    ];

    protected $casts = [
        'is_registration_enabled' => 'boolean',
        'auto_create_user' => 'boolean',
        'use_fixed_registration_password' => 'boolean',
        'auto_approve_membership' => 'boolean',
        'hide_courses_until_membership_approved' => 'boolean',
        'send_welcome_email' => 'boolean',
        'send_welcome_whatsapp' => 'boolean',
        'send_welcome_telegram' => 'boolean',
        'require_email_verification' => 'boolean',
        'wapi_body_variables' => 'array',
        'extra' => 'array',
    ];

    protected $hidden = [
        'fixed_registration_password',
    ];

    // Relationships

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function whatsappTemplate(): BelongsTo
    {
        return $this->belongsTo(WhatsAppMessageTemplate::class, 'whatsapp_template_id');
    }

    public function telegramTemplate(): BelongsTo
    {
        return $this->belongsTo(TelegramMessageTemplate::class, 'telegram_template_id');
    }

    public function wapiTemplate(): BelongsTo
    {
        return $this->belongsTo(WapiTemplate::class, 'wapi_template_id');
    }

    public function usesFlaxxaTemplate(): bool
    {
        return ($this->whatsapp_delivery_mode ?? 'evolution_text') === 'flaxxa_template'
            && ! empty($this->wapi_template_id);
    }

    // Helper Methods

    public function isRegistrationEnabled(): bool
    {
        return $this->is_registration_enabled;
    }

    public function shouldAutoCreateUser(): bool
    {
        return $this->auto_create_user;
    }

    /**
     * Password for newly created accounts in this group.
     * Fixed password when enabled; otherwise a secure random password.
     */
    public function resolveNewAccountPassword(): string
    {
        if ($this->use_fixed_registration_password) {
            $fixed = trim((string) ($this->fixed_registration_password ?? ''));
            if ($fixed !== '') {
                return $fixed;
            }
        }

        return app(\App\Services\Auth\AccountCreatedCredentialDeliveryService::class)->generateSecurePassword();
    }

    public function shouldSendWelcomeEmail(): bool
    {
        return $this->send_welcome_email;
    }

    public function shouldSendWelcomeWhatsApp(): bool
    {
        return $this->send_welcome_whatsapp;
    }

    public function shouldAutoApproveMembership(): bool
    {
        return $this->auto_approve_membership;
    }

    public function shouldHideCoursesUntilMembershipApproved(): bool
    {
        return (bool) $this->hide_courses_until_membership_approved;
    }
}
