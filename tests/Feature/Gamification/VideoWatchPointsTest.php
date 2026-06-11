<?php

use App\Events\VideoWatched;
use App\Models\CourseModule;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\GamificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

function videoPointsModule(): CourseModule
{
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

    $sectionId = DB::table('course_sections')->insertGetId([
        'course_id' => $courseId,
        'title' => 'Section',
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $moduleId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionId,
        'module_type' => 'video',
        'title' => 'Video Module',
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return CourseModule::findOrFail($moduleId);
}

test('it dispatches VideoWatched when watch percentage meets threshold', function () {
    Event::fake([VideoWatched::class]);

    $user = User::factory()->create();
    $module = videoPointsModule();

    app(GamificationService::class)->dispatchVideoWatchIfEligible($user, $module, 85.0, 850, 1000);

    Event::assertDispatched(VideoWatched::class);
});

test('it does not dispatch VideoWatched below threshold', function () {
    Event::fake([VideoWatched::class]);

    $user = User::factory()->create();
    $module = videoPointsModule();

    app(GamificationService::class)->dispatchVideoWatchIfEligible($user, $module, 50.0, 500, 1000);

    Event::assertNotDispatched(VideoWatched::class);
});

test('handleVideoWatch awards points once per module', function () {
    $user = User::factory()->create();
    UserStat::create(['user_id' => $user->id]);
    $module = videoPointsModule();

    $service = app(GamificationService::class);

    $first = $service->handleVideoWatch($user, $module->id, 90);
    $second = $service->handleVideoWatch($user, $module->id, 95);

    expect($first['success'])->toBeTrue();
    expect($second['success'])->toBeFalse();
    expect($user->fresh()->stats->available_points)->toBeGreaterThan(0);
});
