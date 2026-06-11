<?php

namespace App\Listeners\Gamification;

use App\Services\Gamification\ReferralService;
use Illuminate\Auth\Events\Registered;

class AwardReferralPointsListener
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user) {
            return;
        }

        $this->referralService->ensureReferralCode($user);
        $this->referralService->awardReferrerForRegistration($user);
    }
}
