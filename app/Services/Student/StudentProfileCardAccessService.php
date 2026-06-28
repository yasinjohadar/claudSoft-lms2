<?php

namespace App\Services\Student;

use App\Models\SiteSetting;
use App\Models\StudentProfileCard;
use App\Models\User;

class StudentProfileCardAccessService
{
    public function __construct(
        private readonly StudentAccountTierService $tierService
    ) {}

    public function canUseFeature(User $user): bool
    {
        $tier = $this->tierService->resolve($user);

        return match ($tier) {
            'gold' => SiteSetting::isProfileCardEnabledForGold(),
            'silver' => SiteSetting::isProfileCardEnabledForSilver(),
            default => false,
        };
    }

    public function canViewPublicly(StudentProfileCard $card): bool
    {
        if (! $card->is_public || ! $card->admin_enabled) {
            return false;
        }

        $user = $card->user;
        if (! $user || ! $user->is_active) {
            return false;
        }

        return $this->canUseFeature($user);
    }

    public function resolvePublicCard(string $slug): ?StudentProfileCard
    {
        $card = StudentProfileCard::query()
            ->forSlug($slug)
            ->with('user')
            ->first();

        if (! $card || ! $this->canViewPublicly($card)) {
            return null;
        }

        return $card;
    }
}
