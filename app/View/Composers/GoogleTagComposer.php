<?php

namespace App\View\Composers;

use App\Services\Marketing\GoogleDataLayerService;
use App\Models\GoogleSetting;
use Illuminate\View\View;

class GoogleTagComposer
{
    public function __construct(
        protected GoogleDataLayerService $dataLayer
    ) {}

    public function compose(View $view): void
    {
        $settings = GoogleSetting::getSettings();

        $view->with([
            'googleGtmActive' => $settings->isGtmActive(),
            'googleGtmContainerId' => $settings->gtm_container_id,
            'googleSearchConsoleActive' => $settings->isSearchConsoleActive(),
            'googleSearchConsoleVerification' => $settings->search_console_verification,
            'googleDataLayerPageEvents' => $this->dataLayer->getPageEvents(),
            'googleDataLayerFlashEvents' => $this->dataLayer->consumeFlashEvents(),
        ]);
    }
}
