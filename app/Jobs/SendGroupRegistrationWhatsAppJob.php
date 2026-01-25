<?php

namespace App\Jobs;

use App\Models\GroupRegistration;
use App\Services\RegistrationWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGroupRegistrationWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GroupRegistration $registration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RegistrationWhatsAppService $whatsAppService): void
    {
        $whatsAppService->sendWelcomeWhatsAppForGroup($this->registration);
    }
}
