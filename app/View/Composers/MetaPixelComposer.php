<?php

namespace App\View\Composers;

use App\Services\Marketing\MetaPixelService;
use Illuminate\View\View;

class MetaPixelComposer
{
    public function __construct(
        protected MetaPixelService $metaPixel
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'metaPixelActive' => $this->metaPixel->isActive(),
            'metaPixelId' => $this->metaPixel->getPixelId(),
            'metaPixelPageEvents' => $this->metaPixel->getPageEvents(),
            'metaPixelFlashEvents' => $this->metaPixel->consumeFlashEvents(),
            'metaPixelTrackPageView' => $this->metaPixel->isEventEnabled('PageView'),
        ]);
    }
}
