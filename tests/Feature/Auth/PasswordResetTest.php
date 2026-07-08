<?php

use App\Mail\PasswordCredentialsMail;
use App\Models\User;
use App\Services\Auth\PasswordCredentialDeliveryService;
use App\Services\Auth\PasswordResetMessageRenderer;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\Support\PasswordCredentialTestDoubles;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('forgot password sends credential email and whatsapp', function () {
    Mail::fake();
    PasswordCredentialTestDoubles::mockNumberResolverPassThrough();

    $user = User::factory()->create([
        'country_code' => '+963',
        'phone' => '991234567',
        'full_phone' => '+963991234567',
    ]);
    $oldHash = $user->password;

    PasswordCredentialTestDoubles::mockAcceptedWhatsAppSender(function (string $to, string $text) use ($user) {
        return $to === '+963991234567' && str_contains($text, $user->email);
    });

    $response = $this->post('/forgot-password', [
        'channel' => 'email',
        'email' => $user->email,
    ]);

    $response->assertSessionHasNoErrors();

    Mail::assertSent(PasswordCredentialsMail::class, function (PasswordCredentialsMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    $user->refresh();
    expect($user->password)->not->toBe($oldHash);
});

test('reset password screen can be rendered', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->get('/reset-password/'.$token.'?email='.urlencode($user->email));

    $response->assertStatus(200);
});

test('password can be reset with valid token and credentials are sent', function () {
    Mail::fake();
    PasswordCredentialTestDoubles::mockNumberResolverPassThrough();
    PasswordCredentialTestDoubles::mockAcceptedWhatsAppSender();

    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));

    Mail::assertSent(PasswordCredentialsMail::class, function (PasswordCredentialsMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('credential renderer replaces placeholders without expiry values', function () {
    $user = new User([
        'name' => 'Yasin',
        'name_ar' => 'ياسين محمد جوخدار',
        'email' => 'student@example.com',
    ]);

    $renderer = app(PasswordResetMessageRenderer::class);
    $message = $renderer->renderCredentialWhatsApp($user, 'SecretPass123!');

    expect($message)
        ->toContain('ياسين محمد جوخدار')
        ->toContain('SecretPass123!')
        ->toContain('student@example.com')
        ->not->toContain('{password}')
        ->not->toContain('صلاحية الرابط')
        ->not->toContain('---');
});

test('credential delivery service generates secure password', function () {
    $service = app(PasswordCredentialDeliveryService::class);
    $password = $service->generateSecurePassword();

    expect(strlen($password))->toBeGreaterThanOrEqual(16);
});
