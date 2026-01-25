<?php

namespace App\Jobs;

use App\Models\GroupRegistration;
use App\Services\RegistrationEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGroupRegistrationEmailJob implements ShouldQueue
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
    public function handle(RegistrationEmailService $emailService): void
    {
        $emailService->sendWelcomeEmailForGroup($this->registration);
    }
}
