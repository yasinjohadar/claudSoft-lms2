<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(#[\SensitiveParameter] string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        
        $userName = $notifiable->name_ar ?? $notifiable->name ?? 'عزيزي المستخدم';
        $appUrl = config('app.url');
        $logoUrl = $appUrl . '/assets/logo/logo.png';

        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور - أكاديمية كلاودسوفت')
            ->view('emails.reset-password', [
                'url' => $url,
                'userName' => $userName,
                'expireMinutes' => $expireMinutes,
                'logoUrl' => $logoUrl,
                'appUrl' => $appUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
