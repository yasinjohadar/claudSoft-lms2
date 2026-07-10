<?php

use App\Models\Badge;
use App\Models\CourseEnrollment;
use App\Models\CourseGroupMember;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStat;
use App\Services\Gamification\BadgeDistributionReportService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

function badgeReportAdmin(): User
{
    $role = Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

function badgeReportStudent(array $overrides = []): User
{
    $role = Role::findOrCreate('student', 'web');
    $student = User::factory()->create($overrides);
    $student->assignRole($role);
    UserStat::create(['user_id' => $student->id]);

    return $student;
}

function badgeReportCourseId(): int
{
    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Cat ' . uniqid(),
        'slug' => 'cat-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course ' . uniqid(),
        'slug' => 'course-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function badgeReportCreateBadge(array $overrides = []): Badge
{
    return Badge::create(array_merge([
        'name' => 'Report Badge ' . uniqid(),
        'slug' => 'report-badge-' . uniqid(),
        'description' => 'Test badge',
        'type' => 'progress',
        'rarity' => 'common',
        'criteria' => ['lessons_completed' => 1],
        'points_value' => 10,
        'is_active' => true,
        'is_visible' => true,
        'is_hidden' => false,
        'sort_order' => 0,
    ], $overrides));
}

test('admin can access badge distribution report page', function () {
    $response = $this->actingAs(badgeReportAdmin())
        ->get(route('admin.gamification.badges.reports.distribution'));

    $response->assertSuccessful();
    $response->assertSee('توزيع الشارات', false);
});

test('admin can access badge students report page', function () {
    $response = $this->actingAs(badgeReportAdmin())
        ->get(route('admin.gamification.badges.reports.students'));

    $response->assertSuccessful();
    $response->assertSee('شارات الطلاب', false);
});

test('distribution report filters by course enrollment and calculates award rate', function () {
    $admin = badgeReportAdmin();
    $inCourse = badgeReportStudent();
    $outside = badgeReportStudent();
    $courseId = badgeReportCourseId();

    CourseEnrollment::create([
        'course_id' => $courseId,
        'student_id' => $inCourse->id,
        'enrollment_date' => now(),
        'status' => 'active',
    ]);

    $badge = badgeReportCreateBadge();

    UserBadge::create([
        'user_id' => $inCourse->id,
        'badge_id' => $badge->id,
        'awarded_at' => now(),
    ]);

    $service = app(BadgeDistributionReportService::class);
    $stats = $service->buildScopeStats($courseId, 0);
    $badges = $service->paginateBadgeDistribution($courseId, 0, null, $badge->name);

    expect($stats['total_students'])->toBe(1);
    expect($stats['total_awards'])->toBe(1);

    $row = $badges->getCollection()->firstWhere('id', $badge->id);
    expect($row)->not->toBeNull();
    expect($row->earners_count)->toBe(1);
    expect($row->award_rate)->toBe(100.0);

    $response = $this->actingAs($admin)
        ->getJson(route('admin.gamification.badges.reports.distribution', [
            'course_id' => $courseId,
            'q' => $badge->name,
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertSuccessful();
    $response->assertJsonStructure(['stats', 'table', 'pagination', 'total', 'group_options']);
    $response->assertJsonPath('total', 1);
});

test('students report returns earned badges and progress in detail modal', function () {
    $admin = badgeReportAdmin();
    $student = badgeReportStudent(['name' => 'Scoped Student']);
    $courseId = badgeReportCourseId();

    CourseEnrollment::create([
        'course_id' => $courseId,
        'student_id' => $student->id,
        'enrollment_date' => now(),
        'status' => 'active',
    ]);

    $earnedBadge = badgeReportCreateBadge(['name' => 'Earned Scope Badge']);
    $progressBadge = badgeReportCreateBadge([
        'name' => 'Progress Scope Badge',
        'criteria' => ['lessons_completed' => 100],
    ]);

    UserBadge::create([
        'user_id' => $student->id,
        'badge_id' => $earnedBadge->id,
        'awarded_at' => now(),
    ]);

    UserStat::where('user_id', $student->id)->update(['lessons_completed' => 50]);

    $service = app(BadgeDistributionReportService::class);
    $students = $service->paginateStudentsReport($courseId, 0, 'Scoped Student');
    $row = $students->getCollection()->first();

    expect($row)->not->toBeNull();
    expect($row->earned_count)->toBe(1);
    expect($row->completion_rate)->toBeGreaterThan(0);

    $detail = $service->buildStudentDetail($student, $courseId, 0);
    expect($detail['earned'])->toHaveCount(1);
    expect($detail['in_progress']->pluck('badge.id'))->toContain($progressBadge->id);

    $response = $this->actingAs($admin)
        ->get(route('admin.gamification.badges.reports.students.detail', [
            'user' => $student->id,
            'course_id' => $courseId,
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertSuccessful();
    $response->assertSee('Earned Scope Badge', false);
    $response->assertSee('Progress Scope Badge', false);
});

test('statistics overview redirects to distribution report', function () {
    $response = $this->actingAs(badgeReportAdmin())
        ->get(route('admin.gamification.badges.statistics'));

    $response->assertRedirect(route('admin.gamification.badges.reports.distribution'));
});

test('course groups endpoint returns groups for selected course', function () {
    $admin = badgeReportAdmin();
    $courseId = badgeReportCourseId();

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group ' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_group_courses')->insert([
        'course_id' => $courseId,
        'group_id' => $groupId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('admin.gamification.badges.course-groups', ['course_id' => $courseId]));

    $response->assertSuccessful();
    expect(collect($response->json())->pluck('id'))->toContain($groupId);
});
