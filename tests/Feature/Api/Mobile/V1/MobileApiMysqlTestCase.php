<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseEnrollment;
use App\Models\Nationality;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * migrations تستخدم صيغة MySQL فقط، لذا تعمل هذه الاختبارات على قاعدة MySQL المحلية داخل transactions.
 */
abstract class MobileApiMysqlTestCase extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        $app = parent::createApplication();

        // قاعدة MySQL المحددة في .env (phpunit.xml يفرض sqlite).
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', env('MOBILE_TEST_DB_DATABASE', 'cloudsoft_platform'));
        $app['config']->set('activitylog.enabled', false);

        return $app;
    }

    protected function student(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true], $overrides));
        $user->assignRole(Role::findOrCreate('student', 'web'));

        return $user;
    }

    /**
     * طالب بملف شخصي مكتمل (لتجاوز قفل إكمال الملف عند تفعيله).
     */
    protected function completeStudent(array $overrides = []): User
    {
        return $this->student(array_merge([
            'name_ar' => 'طالب تجريبي',
            'full_phone' => '+966512345678',
            'date_of_birth' => '2000-01-01',
            'gender' => 'male',
            'nationality_id' => Nationality::query()->value('id'),
            'city' => 'الرياض',
            'address' => 'حي تجريبي',
        ], $overrides));
    }

    protected function course(array $overrides = []): Course
    {
        $category = CourseCategory::query()->first() ?? CourseCategory::create([
            'name' => 'Mobile Test '.Str::random(6),
            'slug' => 'mobile-test-'.Str::random(6),
        ]);

        return Course::create(array_merge([
            'course_category_id' => $category->id,
            'title' => 'Mobile Course '.Str::random(6),
            'slug' => 'mobile-course-'.Str::random(10),
            'is_published' => true,
            'is_visible' => true,
        ], $overrides));
    }

    protected function enroll(User $student, Course $course, string $status = 'active'): CourseEnrollment
    {
        return CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'enrollment_date' => now(),
            'enrollment_status' => $status,
            'completion_percentage' => $status === 'completed' ? 100 : 40,
        ]);
    }
}
