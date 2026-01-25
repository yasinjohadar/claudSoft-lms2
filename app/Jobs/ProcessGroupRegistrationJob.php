<?php

namespace App\Jobs;

use App\Models\GroupRegistration;
use App\Services\GroupRegistrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessGroupRegistrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GroupRegistration $registration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GroupRegistrationService $registrationService): void
    {
        $registrationService->processRegistration($this->registration);
    }
}
