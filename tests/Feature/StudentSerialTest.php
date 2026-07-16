<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentSerialTest extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'claudsoft_platform');
        $app['config']->set('activitylog.enabled', false);

        return $app;
    }

    public function test_generate_student_serial_uses_year_and_five_digits(): void
    {
        $year = (int) date('Y');
        $serial = User::generateStudentSerial($year);

        $this->assertMatchesRegularExpression('/^STD-' . $year . '-\d{5}$/', $serial);
    }

    public function test_generate_student_serial_increments_within_year(): void
    {
        $year = 2099;
        $prefix = 'STD-' . $year . '-';

        User::factory()->create([
            'student_id' => $prefix . '00007',
            'email' => 'serial-seed-' . uniqid() . '@example.com',
        ]);

        $next = User::generateStudentSerial($year);

        $this->assertSame($prefix . '00008', $next);
    }

    public function test_assign_student_serial_sets_value_when_empty(): void
    {
        $user = User::factory()->create([
            'student_id' => null,
            'email' => 'serial-assign-' . uniqid() . '@example.com',
        ]);

        $user->assignStudentSerial();
        $user->refresh();

        $this->assertNotNull($user->student_id);
        $this->assertMatchesRegularExpression('/^STD-\d{4}-\d{5}$/', $user->student_id);
    }

    public function test_assign_student_serial_is_idempotent(): void
    {
        $user = User::factory()->create([
            'student_id' => 'STD-2026-00999',
            'email' => 'serial-idempotent-' . uniqid() . '@example.com',
        ]);

        $user->assignStudentSerial();
        $user->refresh();

        $this->assertSame('STD-2026-00999', $user->student_id);
    }

    public function test_registration_path_assigns_student_serial(): void
    {
        $email = 'serial-register-' . uniqid() . '@example.com';

        $user = User::create([
            'name' => 'Serial Test Student',
            'email' => $email,
            'password' => 'Password123!',
        ]);

        $user->assignStudentSerial();
        $user->refresh();

        $this->assertNotNull($user->student_id);
        $this->assertMatchesRegularExpression('/^STD-\d{4}-\d{5}$/', $user->student_id);
    }

    public function test_backfill_skips_inactive_users(): void
    {
        $active = User::factory()->create([
            'student_id' => null,
            'is_active' => true,
            'email' => 'serial-active-' . uniqid() . '@example.com',
        ]);
        $inactive = User::factory()->create([
            'student_id' => null,
            'is_active' => false,
            'email' => 'serial-inactive-' . uniqid() . '@example.com',
        ]);

        $this->artisan('users:backfill-student-serials')
            ->assertSuccessful();

        $active->refresh();
        $inactive->refresh();

        $this->assertNotNull($active->student_id);
        $this->assertNull($inactive->student_id);
    }

    public function test_activating_user_assigns_student_serial_when_missing(): void
    {
        $user = User::factory()->create([
            'student_id' => null,
            'is_active' => false,
            'email' => 'serial-reactivate-' . uniqid() . '@example.com',
        ]);

        $user->is_active = true;
        $user->save();
        $user->refresh();

        $this->assertNotNull($user->student_id);
        $this->assertMatchesRegularExpression('/^STD-\d{4}-\d{5}$/', $user->student_id);
    }

    public function test_activating_user_keeps_existing_student_serial(): void
    {
        $user = User::factory()->create([
            'student_id' => 'STD-2024-00042',
            'is_active' => false,
            'email' => 'serial-keep-' . uniqid() . '@example.com',
        ]);

        $user->is_active = true;
        $user->save();
        $user->refresh();

        $this->assertSame('STD-2024-00042', $user->student_id);
    }
}
