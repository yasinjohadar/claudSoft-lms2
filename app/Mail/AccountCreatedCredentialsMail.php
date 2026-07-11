<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Auth\AccountCreatedMessageRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCreatedCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        #[\SensitiveParameter] public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        $renderer = app(AccountCreatedMessageRenderer::class);

        return new Envelope(
            subject: $renderer->renderEmailSubject(),
        );
    }

    public function content(): Content
    {
        $renderer = app(AccountCreatedMessageRenderer::class);
        $variables = $renderer->credentialVariables($this->user, $this->plainPassword);
        $userName = $variables['student_name_ar'];
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

        return new Content(
            view: 'emails.reset-password',
            with: [
                'userName' => $userName,
                'logoUrl' => $logoUrl,
                'appUrl' => $appUrl,
                'customBodyHtml' => $renderer->renderCredentialEmailBodyHtml($this->user, $this->plainPassword),
            ],
        );
    }
}
