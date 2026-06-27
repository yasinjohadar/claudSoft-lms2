<?php

namespace App\Console\Commands;

use App\Services\Marketing\MetaPixelService;
use Illuminate\Console\Command;

class MetaPixelTestCommand extends Command
{
    protected $signature = 'meta-pixel:test {--event=Lead : Event name to send via CAPI}';

    protected $description = 'Send a test event to Meta Conversions API';

    public function handle(MetaPixelService $metaPixel): int
    {
        if (! $metaPixel->settings()->hasValidCapi()) {
            $this->error('Conversions API غير مفعّل. راجع إعدادات Facebook Pixel في الأدمن.');

            return self::FAILURE;
        }

        $event = (string) $this->option('event');
        $result = $metaPixel->sendTestEvent($event);

        if ($result['success']) {
            $this->info($result['message']);
            if (! empty($result['response'])) {
                $this->line(json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return self::SUCCESS;
        }

        $this->error($result['message']);

        return self::FAILURE;
    }
}
