<?php

use App\Events\Gamification\PointsEarned;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\PointsService;
use Illuminate\Support\Facades\Event;

test('awardPoints dispatches PointsEarned with correct payload', function () {
    Event::fake([PointsEarned::class]);

    $user = User::factory()->create();
    UserStat::create(['user_id' => $user->id]);

    app(PointsService::class)->awardPoints(
        $user,
        100,
        'quiz_completion',
        'اختبار',
        'App\Models\Quiz',
        5
    );

    Event::assertDispatched(PointsEarned::class, function (PointsEarned $event) use ($user) {
        return $event->user->id === $user->id
            && $event->points === 100
            && $event->reason === 'quiz_completion'
            && $event->relatedId === 5;
    });
});
