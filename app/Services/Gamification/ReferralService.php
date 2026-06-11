<?php

namespace App\Services\Gamification;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Str;

class ReferralService
{
    public function __construct(
        protected GamificationService $gamificationService
    ) {}

    public function ensureReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (User::query()->where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);

        return $code;
    }

    public function getReferralLink(User $user): string
    {
        $code = $this->ensureReferralCode($user);

        return route('register', ['ref' => $code]);
    }

    public function resolveReferrer(?string $code): ?User
    {
        if (! $code) {
            return null;
        }

        return User::query()
            ->where('referral_code', strtoupper(trim($code)))
            ->first();
    }

    public function attachReferrer(User $newUser, ?string $code): void
    {
        if (! $code || $newUser->referred_by_user_id) {
            return;
        }

        $referrer = $this->resolveReferrer($code);

        if (! $referrer || $referrer->id === $newUser->id) {
            return;
        }

        $newUser->update(['referred_by_user_id' => $referrer->id]);
    }

    public function awardReferrerForRegistration(User $newUser): void
    {
        if (! $newUser->referred_by_user_id) {
            return;
        }

        $referrer = User::find($newUser->referred_by_user_id);

        if (! $referrer) {
            return;
        }

        $config = config('gamification.points.referral', [
            'points' => 500,
            'xp' => 250,
            'description' => 'دعوة صديق (عند تسجيله)',
        ]);

        $this->gamificationService->awardReward(
            $referrer,
            (int) $config['points'],
            (int) ($config['xp'] ?? 0),
            'referral',
            $config['description'] ?? 'دعوة صديق',
            User::class,
            $newUser->id,
            ['referred_user_name' => $newUser->name]
        );
    }

    public function handleCourseShare(User $user, Course $course): array
    {
        $config = config('gamification.points.course_share', [
            'points' => 15,
            'xp' => 7,
            'description' => 'مشاركة كورس',
        ]);

        return $this->gamificationService->awardReward(
            $user,
            (int) $config['points'],
            (int) ($config['xp'] ?? 0),
            'course_share',
            $config['description'] ?? 'مشاركة كورس',
            Course::class,
            $course->id,
            ['course_title' => $course->title]
        );
    }
}
