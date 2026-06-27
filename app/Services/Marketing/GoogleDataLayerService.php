<?php

namespace App\Services\Marketing;

use App\Models\GoogleSetting;
use Illuminate\Support\Facades\Session;

class GoogleDataLayerService
{
    protected GoogleSetting $settings;

    /** @var array<int, array<string, mixed>> */
    protected array $pageEvents = [];

    public function __construct()
    {
        $this->settings = GoogleSetting::getSettings();
    }

    public function isActive(): bool
    {
        return $this->settings->isGtmActive();
    }

    public function pushPageEvent(string $event, array $data = []): void
    {
        if (! $this->isActive()) {
            return;
        }

        $this->pageEvents[] = [
            'event' => $event,
            'data' => $data,
        ];
    }

    public function flashEvent(string $event, array $data = []): void
    {
        if (! $this->isActive()) {
            return;
        }

        $key = config('google_marketing.session_flash_key', 'google_datalayer_flash_events');
        $events = Session::get($key, []);

        $events[] = [
            'event' => $event,
            'data' => $data,
        ];

        Session::flash($key, $events);
    }

    public function trackGenerateLead(string $contentName): void
    {
        $this->flashEvent('generate_lead', [
            'lead_source' => 'diploma_registration',
            'content_name' => $contentName,
        ]);
    }

    public function trackContact(string $subject = ''): void
    {
        $data = ['form_name' => 'contact'];

        if ($subject !== '') {
            $data['content_name'] = $subject;
        }

        $this->flashEvent('contact', $data);
    }

    /** @return array<int, array<string, mixed>> */
    public function getPageEvents(): array
    {
        return $this->pageEvents;
    }

    /** @return array<int, array<string, mixed>> */
    public function consumeFlashEvents(): array
    {
        $key = config('google_marketing.session_flash_key', 'google_datalayer_flash_events');

        return Session::pull($key, []);
    }
}
