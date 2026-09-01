<?php

namespace App\Services\Marketing;

use App\Models\MetaPixelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class MetaPixelService
{
    protected MetaPixelSetting $settings;

    /** @var array<int, array<string, mixed>> */
    protected array $pageEvents = [];

    public function __construct(
        protected MetaConversionsApiClient $capiClient
    ) {
        $this->settings = MetaPixelSetting::getSettings();
    }

    public function settings(): MetaPixelSetting
    {
        return $this->settings;
    }

    public function isActive(): bool
    {
        return $this->settings->hasValidPixel();
    }

    public function getPixelId(): ?string
    {
        return $this->settings->pixel_id;
    }

    public function isEventEnabled(string $eventName): bool
    {
        return $this->isActive() && $this->settings->isEventEnabled($eventName);
    }

    public static function generateEventId(): string
    {
        return (string) Str::uuid();
    }

    public function pushPageEvent(string $eventName, array $customData = [], ?string $eventId = null): void
    {
        if (! $this->isEventEnabled($eventName)) {
            return;
        }

        $this->pageEvents[] = [
            'event' => $eventName,
            'event_id' => $eventId ?? self::generateEventId(),
            'data' => $this->normalizeCustomData($customData),
        ];
    }

    public function flashBrowserEvent(string $eventName, array $customData = [], ?string $eventId = null): void
    {
        if (! $this->isEventEnabled($eventName)) {
            return;
        }

        $key = config('meta_pixel.session_flash_key', 'meta_pixel_flash_events');
        $events = Session::get($key, []);

        $events[] = [
            'event' => $eventName,
            'event_id' => $eventId ?? self::generateEventId(),
            'data' => $this->normalizeCustomData($customData),
        ];

        Session::flash($key, $events);
    }

    /** @return array<int, array<string, mixed>> */
    public function getPageEvents(): array
    {
        return $this->pageEvents;
    }

    /** @return array<int, array<string, mixed>> */
    public function consumeFlashEvents(): array
    {
        $key = config('meta_pixel.session_flash_key', 'meta_pixel_flash_events');

        return Session::pull($key, []);
    }

    public function trackViewContent(
        string $contentName,
        string $contentCategory,
        ?string $contentId = null,
        ?float $value = null,
        ?string $currency = null
    ): void {
        $data = [
            'content_name' => $contentName,
            'content_category' => $contentCategory,
        ];

        if ($contentId) {
            $data['content_ids'] = [$contentId];
        }

        if ($value !== null) {
            $data['value'] = $value;
            $data['currency'] = $currency ?? config('meta_pixel.default_currency', 'SAR');
        }

        $this->pushPageEvent('ViewContent', $data);
    }

    public function trackSearch(string $searchString): void
    {
        if (blank($searchString)) {
            return;
        }

        $this->pushPageEvent('Search', [
            'search_string' => $searchString,
        ]);
    }

    public function trackLeadStarted(string $contentName, ?string $contentCategory = 'diploma_registration'): void
    {
        $this->pushPageEvent('LeadStarted', [
            'content_name' => $contentName,
            'content_category' => $contentCategory,
        ]);
    }

    public function trackLeadWithCapi(
        Request $request,
        string $contentName,
        ?string $email = null,
        ?string $phone = null,
        ?string $firstName = null,
        ?string $lastName = null
    ): string {
        $eventId = self::generateEventId();

        if (! $this->isEventEnabled('Lead')) {
            return $eventId;
        }

        $customData = [
            'content_name' => $contentName,
            'content_category' => 'diploma_registration',
        ];

        $this->flashBrowserEvent('Lead', $customData, $eventId);

        if ($this->settings->hasValidCapi()) {
            $this->capiClient->send($this->settings, [
                $this->buildServerEventPayload($request, 'Lead', $eventId, $customData, $email, $phone, $firstName, $lastName),
            ]);
        }

        return $eventId;
    }

    public function trackContactWithCapi(
        Request $request,
        string $subject,
        ?string $email = null,
        ?string $phone = null,
        ?string $firstName = null
    ): string {
        $eventId = self::generateEventId();

        if (! $this->isEventEnabled('Contact')) {
            return $eventId;
        }

        $customData = [
            'content_name' => $subject,
            'content_category' => 'contact_form',
        ];

        $this->flashBrowserEvent('Contact', $customData, $eventId);

        if ($this->settings->hasValidCapi()) {
            $this->capiClient->send($this->settings, [
                $this->buildServerEventPayload($request, 'Contact', $eventId, $customData, $email, $phone, $firstName),
            ]);
        }

        return $eventId;
    }

    public function sendTestEvent(string $eventName = 'Lead', ?Request $request = null): array
    {
        $eventId = self::generateEventId();

        $payload = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => url('/'),
            'custom_data' => [
                'content_name' => 'Meta Pixel Test Event',
                'content_category' => 'test',
            ],
        ];

        if ($request) {
            $userData = $this->buildUserData($request);

            if ($userData !== []) {
                $payload['user_data'] = $userData;
            }
        }

        return $this->capiClient->send($this->settings, [$payload]);
    }

    protected function buildServerEventPayload(
        Request $request,
        string $eventName,
        string $eventId,
        array $customData,
        ?string $email = null,
        ?string $phone = null,
        ?string $firstName = null,
        ?string $lastName = null
    ): array {
        $payload = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => $request->fullUrl(),
            'custom_data' => $customData,
            'user_data' => $this->buildUserData($request, $email, $phone, $firstName, $lastName),
        ];

        return array_filter($payload, fn ($value) => $value !== null && $value !== []);
    }

    protected function buildUserData(
        Request $request,
        ?string $email = null,
        ?string $phone = null,
        ?string $firstName = null,
        ?string $lastName = null
    ): array {
        $userData = array_filter([
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbc' => $request->cookie('_fbc'),
            'fbp' => $request->cookie('_fbp'),
        ]);

        if ($email) {
            $userData['em'] = [$this->hashValue($email)];
        }

        if ($phone) {
            $normalized = preg_replace('/\D+/', '', $phone) ?: $phone;
            $userData['ph'] = [$this->hashValue($normalized)];
        }

        if ($firstName) {
            $userData['fn'] = [$this->hashValue($firstName)];
        }

        if ($lastName) {
            $userData['ln'] = [$this->hashValue($lastName)];
        }

        return $userData;
    }

    protected function hashValue(string $value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }

    protected function normalizeCustomData(array $customData): array
    {
        return array_filter($customData, fn ($value) => $value !== null && $value !== '');
    }
}
