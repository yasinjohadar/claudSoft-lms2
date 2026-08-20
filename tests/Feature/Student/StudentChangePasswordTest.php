<?php

namespace Tests\Feature\Student;

use App\Mail\PasswordCredentialsMail;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\Support\PasswordCredentialTestDoubles;
use Tests\TestCase;

class StudentChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    private function studentUser(): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!@#'),
            'country_code' => '+963',
            'phone' => '991234567',
            'full_phone' => '+963991234567',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function enableWhatsAppEvolution(): void
    {
        foreach ([
            'whatsapp_enabled' => '1',
            'whatsapp_provider' => 'evolution',
            'evolution_base_url' => 'https://evolution.test.example',
            'evolution_api_key' => 'test-api-key',
            'evolution_instance_name' => 'main',
        ] as $key => $value) {
            SystemSetting::create(['group' => 'whatsapp', 'key' => $key, 'value' => $value]);
        }
    }

    public function test_change_password_page_is_reachable_on_its_own_url(): void
    {
        $student = $this->studentUser();

        $this->actingAs($student)
            ->get(route('student.profile.password'))
            ->assertOk()
            ->assertSee('تغيير كلمة المرور')
            ->assertSee('توليد اقتراحات لكلمة المرور');
    }

    public function test_student_can_change_password_without_the_current_one(): void
    {
        Mail::fake();

        $mock = Mockery::mock(SendWhatsAppMessage::class);
        $mock->shouldNotReceive('sendTextSync');
        $this->app->instance(SendWhatsAppMessage::class, $mock);

        $student = $this->studentUser();

        $this->actingAs($student)
            ->put(route('student.profile.change-password'), [
                'new_password' => 'BrandNew123!@#',
                'new_password_confirmation' => 'BrandNew123!@#',
            ])
            ->assertRedirect(route('student.profile.index'))
            ->assertSessionHas('success', 'تم تغيير كلمة المرور بنجاح');

        $this->assertTrue(Hash::check('BrandNew123!@#', $student->fresh()->password));
        Mail::assertNothingSent();
    }

    public function test_student_can_request_new_credentials_to_be_sent(): void
    {
        Mail::fake();
        $this->enableWhatsAppEvolution();
        PasswordCredentialTestDoubles::mockNumberResolverPassThrough();
        PasswordCredentialTestDoubles::mockAcceptedWhatsAppSender();

        $student = $this->studentUser();

        $this->actingAs($student)
            ->put(route('student.profile.change-password'), [
                'new_password' => 'BrandNew123!@#',
                'new_password_confirmation' => 'BrandNew123!@#',
                'send_credentials' => '1',
            ])
            ->assertRedirect(route('student.profile.index'));

        Mail::assertSent(PasswordCredentialsMail::class, function (PasswordCredentialsMail $mail) use ($student) {
            return $mail->hasTo($student->email);
        });
    }

    public function test_page_no_longer_asks_for_the_current_password(): void
    {
        $student = $this->studentUser();

        $this->actingAs($student)
            ->get(route('student.profile.password'))
            ->assertOk()
            ->assertDontSee('name="current_password"', false);
    }

    public function test_reusing_the_existing_password_is_rejected(): void
    {
        $student = $this->studentUser();

        $this->actingAs($student)
            ->put(route('student.profile.change-password'), [
                'new_password' => 'OldPass123!@#',
                'new_password_confirmation' => 'OldPass123!@#',
            ])
            ->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('OldPass123!@#', $student->fresh()->password));
    }

    public function test_password_copied_with_bidi_marks_is_sanitized(): void
    {
        $student = $this->studentUser();

        // Password copied out of a WhatsApp credentials message carries LTR override marks.
        $this->actingAs($student)
            ->put(route('student.profile.change-password'), [
                'new_password' => "\u{202D}BrandNew123!@#\u{202C}",
                'new_password_confirmation' => "\u{202D}BrandNew123!@#\u{202C}",
            ])
            ->assertRedirect(route('student.profile.index'));

        $this->assertTrue(Hash::check('BrandNew123!@#', $student->fresh()->password));
    }
}
