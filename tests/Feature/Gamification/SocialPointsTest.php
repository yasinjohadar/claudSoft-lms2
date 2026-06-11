<?php

use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\ReferralService;
use Illuminate\Support\Facades\DB;

test('course share awards points once per course', function () {
    $user = User::factory()->create();
    UserStat::create(['user_id' => $user->id]);

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Cat '.uniqid(),
        'slug' => 'cat-'.uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course '.uniqid(),
        'slug' => 'course-'.uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $course = \App\Models\Course::findOrFail($courseId);
    $service = app(ReferralService::class);

    $first = $service->handleCourseShare($user, $course);
    $second = $service->handleCourseShare($user, $course);

    expect($first['success'])->toBeTrue();
    expect($second['success'])->toBeFalse();
    expect(
        PointsTransaction::where('user_id', $user->id)->where('source', 'course_share')->count()
    )->toBe(1);
});
