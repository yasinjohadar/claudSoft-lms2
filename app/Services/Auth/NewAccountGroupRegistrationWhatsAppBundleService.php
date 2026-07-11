<?php

namespace App\Services\Auth;

use App\Models\EvolutionInstance;
use App\Models\GroupRegistration;
use App\Models\User;
use App\Services\RegistrationWhatsAppService;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppDeliveryAcceptance;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Support\Facades\Log;

/**
 * New group-registration accounts only: welcome + credentials + password-only
 * from one sticky Evolution instance. Does not alter other WhatsApp paths.
 */
class NewAccountGroupRegistrationWhatsAppBundleService
{
    public function __construct(
        private RegistrationWhatsAppService $registrationWhatsApp,
        private AccountCreatedCredentialDeliveryService $credentialDelivery,
        private SendWhatsAppMessage $whatsAppSender,
        private EvolutionInstanceRotator $evolutionRotator,
        private EvolutionRotatingSendService $rotatingSend,
        private WhatsAppSettingsService $whatsappSettings,
    ) {}

    /**
     * @return array{
     *     email_sent: bool,
     *     whatsapp_sent: bool,
     *     whatsapp_recipient: ?string,
     *     email_error: ?string,
     *     whatsapp_error: ?string,
     *     welcome_sent: bool,
     *     evolution_instance: ?string
     * }
     */
    public function deliver(
        User $user,
        GroupRegistration $registration,
        #[\SensitiveParameter] string $plainPassword,
        bool $sendEmail = true,
        bool $sendWhatsApp = true,
    ): array {
        if (! $sendEmail && ! $sendWhatsApp) {
            return [
                'email_sent' => false,
                'whatsapp_sent' => false,
                'whatsapp_recipient' => null,
                'email_error' => null,
                'whatsapp_error' => null,
                'welcome_sent' => false,
                'evolution_instance' => null,
            ];
        }

        if (! $sendWhatsApp) {
            $result = $this->credentialDelivery->deliver(
                $user,
                $plainPassword,
                AccountCreatedCredentialDeliveryService::CONTEXT_ACCOUNT_CREATED,
                $sendEmail,
                false,
            );

            return $result + [
                'welcome_sent' => false,
                'evolution_instance' => null,
            ];
        }

        $sticky = $this->pickStickyInstance();
        $stickyInstance = $sticky?->instance_name;
        $welcomeSent = false;
        $welcomeError = null;

        if ($stickyInstance !== null) {
            try {
                $welcomeSent = $this->sendWelcomeOnInstance($registration, $stickyInstance);
            } catch (\Throwable $e) {
                $welcomeError = $e->getMessage();
                Log::warning('New-account group registration welcome WhatsApp failed', [
                    'registration_id' => $registration->id,
                    'user_id' => $user->id,
                    'evolution_instance' => $stickyInstance,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $welcomeError = 'لا يوجد instance Evolution متاح لإرسال حزمة الرسائل.';
            Log::warning('New-account WhatsApp bundle skipped sticky pick', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
            ]);
        }

        $credentialResult = $this->credentialDelivery->deliver(
            $user,
            $plainPassword,
            AccountCreatedCredentialDeliveryService::CONTEXT_ACCOUNT_CREATED,
            $sendEmail,
            true,
            null,
            $stickyInstance,
        );

        $anyWhatsAppOk = $welcomeSent || (bool) $credentialResult['whatsapp_sent'];

        if ($anyWhatsAppOk && $sticky !== null) {
            $this->evolutionRotator->markUsed($sticky);
        }

        if ($welcomeSent) {
            $this->registrationWhatsApp->markWelcomeSent($registration);
        }

        $whatsappError = $credentialResult['whatsapp_error'];
        if (! $anyWhatsAppOk && $welcomeError) {
            $whatsappError = trim(implode(' | ', array_filter([
                $welcomeError,
                $credentialResult['whatsapp_error'],
            ])));
        }

        Log::info('New-account group registration WhatsApp bundle finished', [
            'registration_id' => $registration->id,
            'user_id' => $user->id,
            'evolution_instance' => $stickyInstance,
            'welcome_sent' => $welcomeSent,
            'credentials_whatsapp_sent' => $credentialResult['whatsapp_sent'],
            'email_sent' => $credentialResult['email_sent'],
        ]);

        return [
            'email_sent' => (bool) $credentialResult['email_sent'],
            'whatsapp_sent' => $anyWhatsAppOk,
            'whatsapp_recipient' => $credentialResult['whatsapp_recipient'],
            'email_error' => $credentialResult['email_error'],
            'whatsapp_error' => $whatsappError,
            'welcome_sent' => $welcomeSent,
            'evolution_instance' => $stickyInstance,
        ];
    }

    private function pickStickyInstance(): ?EvolutionInstance
    {
        $settings = $this->whatsappSettings->getSettings();

        if (($settings['whatsapp_provider'] ?? '') !== 'evolution') {
            return null;
        }

        if ($this->rotatingSend->isRotationActive() && $this->evolutionRotator->poolCount() > 0) {
            try {
                return $this->evolutionRotator->nextInstance();
            } catch (\Throwable $e) {
                Log::warning('Sticky Evolution pick from pool failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $fallback = $this->rotatingSend->fallbackInstanceName();
        if ($fallback === '') {
            return null;
        }

        return EvolutionInstance::query()
            ->where('instance_name', $fallback)
            ->first()
            ?? new EvolutionInstance(['instance_name' => $fallback]);
    }

    private function sendWelcomeOnInstance(GroupRegistration $registration, string $instanceName): bool
    {
        $message = $this->registrationWhatsApp->buildWelcomeTextForGroup($registration);
        if ($message === null || trim($message) === '') {
            return false;
        }

        $phone = (string) $registration->full_phone;
        if ($phone === '') {
            return false;
        }
        if (! str_starts_with($phone, '+')) {
            $phone = '+'.$phone;
        }

        $sentMessage = $this->whatsAppSender->sendTextSync(
            $phone,
            $message,
            previewUrl: false,
            applySendDelay: false,
            evolutionInstanceName: $instanceName,
        );

        return WhatsAppDeliveryAcceptance::isAccepted($sentMessage);
    }
}
