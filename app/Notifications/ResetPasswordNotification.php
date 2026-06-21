<?php

namespace App\Notifications;

use App\Services\Auth\PasswordResetMessageRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        $renderer = app(PasswordResetMessageRenderer::class);
        $variables = $renderer->variables($notifiable, $url, $expireMinutes);

        $userName = $variables['user_name'];
        $appUrl = rtrim(config('app.url'), '/');

        $logoPath = public_path('assets/logo/logo.png');
        $logoUrl = null;

        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $imageBase64 = base64_encode($imageData);
            $imageInfo = getimagesize($logoPath);
            $mimeType = $imageInfo['mime'] ?? 'image/png';
            $logoUrl = 'data:'.$mimeType.';base64,'.$imageBase64;
        } else {
            $logoUrl = $appUrl.'/assets/logo/logo.png';
        }

        $customBodyHtml = $renderer->renderEmailBodyHtml($notifiable, $url, $expireMinutes);

        return (new MailMessage)
            ->subject($renderer->renderEmailSubject())
            ->view('emails.reset-password', [
                'url' => $url,
                'userName' => $userName,
                'expireMinutes' => $expireMinutes,
                'expireAt' => $variables['expire_at'],
                'logoUrl' => $logoUrl,
                'appUrl' => $appUrl,
                'customBodyHtml' => $customBodyHtml,
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
