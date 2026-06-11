<?php

use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\ReferralService;

test('it awards referrer when referred user registers', function () {
    $referrer = User::factory()->create();
    UserStat::create(['user_id' => $referrer->id]);

    $referred = User::factory()->create(['referred_by_user_id' => $referrer->id]);

    app(ReferralService::class)->awardReferrerForRegistration($referred);

    expect(
        PointsTransaction::where('user_id', $referrer->id)
            ->where('source', 'referral')
            ->where('related_id', $referred->id)
            ->exists()
    )->toBeTrue();
});

test('it generates unique referral codes', function () {
    $user = User::factory()->create();

    $service = app(ReferralService::class);
    $code = $service->ensureReferralCode($user);

    expect($code)->not->toBeEmpty();
    expect($user->fresh()->referral_code)->toBe($code);
});

test('it attaches referrer from referral code', function () {
    $referrer = User::factory()->create(['referral_code' => 'TESTREF1']);
    $newUser = User::factory()->create();

    app(ReferralService::class)->attachReferrer($newUser, 'TESTREF1');

    expect($newUser->fresh()->referred_by_user_id)->toBe($referrer->id);
});
