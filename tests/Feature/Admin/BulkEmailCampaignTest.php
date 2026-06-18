<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendBulkEmailRecipientJob;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use App\Models\CourseEnrollment;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulkEmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function studentUser(array $overrides = []): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create(array_merge([
            'email' => 'student'.uniqid().'@example.com',
        ], $overrides));
        $user->assignRole($role);

        return $user;
    }

    private function createTemplate(): EmailTemplate
    {
        return EmailTemplate::create([
            'name' => 'Test Template',
            'name_ar' => 'قالب اختبار',
            'subject' => 'مرحباً {{student_name}}',
            'body' => '<p>أهلاً {{student_name}}، بريدك: {{email}}</p>',
            'type' => EmailTemplate::TYPE_CUSTOM,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_bulk_email_pages(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->get(route('admin.bulk-emails.create'))->assertOk()
            ->assertSee('إرسال بريد جماعي', false);

        $this->actingAs($admin)->get(route('admin.bulk-emails.index'))->assertOk()
            ->assertSee('سجل حملات البريد', false);
    }

    public function test_preview_count_returns_zero_when_individual_without_student(): void
    {
        $admin = $this->adminUser();
        $this->studentUser();

        $resolver = app(\App\Services\BulkEmail\BulkEmailAudienceResolver::class);

        $this->assertSame(0, $resolver->countFromParams(BulkEmailCampaign::AUDIENCE_INDIVIDUAL, []));
    }

    public function test_preview_recipients_returns_student_list(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser(['name' => 'Jane', 'name_ar' => 'جين']);

        $response = $this->actingAs($admin)->postJson(route('admin.bulk-emails.preview-recipients'), [
            'audience_type' => BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
            'student_ids' => [$student->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_count', 1)
            ->assertJsonPath('recipients.0.email', $student->email)
            ->assertJsonPath('recipients.0.name_ar', 'جين');
    }

    public function test_preview_count_returns_student_total(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();

        $response = $this->actingAs($admin)->postJson(route('admin.bulk-emails.preview-count'), [
            'audience_type' => BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
            'student_ids' => [$student->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1);
    }

    public function test_store_creates_campaign_and_dispatches_jobs(): void
    {
        Mail::fake();
        Queue::fake();

        $admin = $this->adminUser();
        $student = $this->studentUser();
        $template = $this->createTemplate();

        $response = $this->actingAs($admin)->post(route('admin.bulk-emails.store'), [
            'content_mode' => BulkEmailCampaign::CONTENT_MODE_TEMPLATE,
            'email_template_id' => $template->id,
            'audience_type' => BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
            'student_ids' => [$student->id],
        ]);

        $campaign = BulkEmailCampaign::first();
        $this->assertNotNull($campaign);

        $response->assertRedirect(route('admin.bulk-emails.show', $campaign));

        $this->assertDatabaseHas('bulk_email_campaigns', [
            'id' => $campaign->id,
            'audience_type' => BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
            'total_recipients' => 1,
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('bulk_email_recipients', [
            'campaign_id' => $campaign->id,
            'user_id' => $student->id,
            'status' => BulkEmailRecipient::STATUS_PENDING,
        ]);

        Queue::assertPushed(SendBulkEmailRecipientJob::class);
    }

    public function test_send_job_marks_recipient_sent_and_sends_mail(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $student = $this->studentUser(['name' => 'John', 'name_ar' => 'جون']);
        $template = $this->createTemplate();

        $campaign = BulkEmailCampaign::create([
            'email_template_id' => $template->id,
            'content_mode' => BulkEmailCampaign::CONTENT_MODE_TEMPLATE,
            'audience_type' => BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
            'student_ids' => [$student->id],
            'total_recipients' => 1,
            'sent_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'status' => BulkEmailCampaign::STATUS_PROCESSING,
            'created_by' => $admin->id,
            'started_at' => now(),
        ]);

        $recipient = BulkEmailRecipient::create([
            'campaign_id' => $campaign->id,
            'user_id' => $student->id,
            'email' => $student->email,
            'status' => BulkEmailRecipient::STATUS_PENDING,
        ]);

        $job = new SendBulkEmailRecipientJob($campaign, $recipient);
        $job->handle(app(\App\Services\BulkEmail\BulkEmailSender::class));

        $recipient->refresh();
        $campaign->refresh();

        $this->assertSame(BulkEmailRecipient::STATUS_SENT, $recipient->status);
        $this->assertSame(1, $campaign->sent_count);
        $this->assertSame(BulkEmailCampaign::STATUS_COMPLETED, $campaign->status);
        $this->assertStringContainsString('جون', $recipient->rendered_subject);

        Mail::assertSentCount(1);
    }

    public function test_recipient_without_email_is_skipped(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $student = $this->studentUser(['email' => null]);

        $campaign = BulkEmailCampaign::create([
            'content_mode' => BulkEmailCampaign::CONTENT_MODE_CUSTOM,
            'subject' => 'Test',
            'body' => '<p>Test</p>',
            'audience_type' => BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
            'student_ids' => [$student->id],
            'total_recipients' => 1,
            'sent_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 1,
            'status' => BulkEmailCampaign::STATUS_PROCESSING,
            'created_by' => $admin->id,
            'started_at' => now(),
        ]);

        $recipient = BulkEmailRecipient::create([
            'campaign_id' => $campaign->id,
            'user_id' => $student->id,
            'email' => null,
            'status' => BulkEmailRecipient::STATUS_SKIPPED,
            'error_message' => 'لا يوجد بريد إلكتروني للطالب.',
        ]);

        $campaign->update([
            'status' => BulkEmailCampaign::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->assertSame(BulkEmailRecipient::STATUS_SKIPPED, $recipient->status);
        Mail::assertNothingSent();
    }

    public function test_preview_count_for_course_audience(): void
    {
        $admin = $this->adminUser();
        $studentOne = $this->studentUser();
        $studentTwo = $this->studentUser();

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

        foreach ([$studentOne, $studentTwo] as $student) {
            CourseEnrollment::create([
                'course_id' => $courseId,
                'student_id' => $student->id,
                'enrollment_status' => 'active',
                'enrollment_date' => now(),
                'completion_percentage' => 0,
                'certificate_issued' => false,
            ]);
        }

        $response = $this->actingAs($admin)->postJson(route('admin.bulk-emails.preview-count'), [
            'audience_type' => BulkEmailCampaign::AUDIENCE_COURSE,
            'course_id' => $courseId,
        ]);

        $response->assertOk()->assertJsonPath('count', 2);
    }

    public function test_show_page_displays_campaign_report(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();

        $campaign = BulkEmailCampaign::create([
            'content_mode' => BulkEmailCampaign::CONTENT_MODE_CUSTOM,
            'subject' => 'Subject',
            'body' => '<p>Body</p>',
            'audience_type' => BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
            'student_ids' => [$student->id],
            'total_recipients' => 1,
            'sent_count' => 1,
            'failed_count' => 0,
            'skipped_count' => 0,
            'status' => BulkEmailCampaign::STATUS_COMPLETED,
            'created_by' => $admin->id,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        BulkEmailRecipient::create([
            'campaign_id' => $campaign->id,
            'user_id' => $student->id,
            'email' => $student->email,
            'status' => BulkEmailRecipient::STATUS_SENT,
            'rendered_subject' => 'Subject',
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.bulk-emails.show', $campaign))
            ->assertOk()
            ->assertSee('تفاصيل المستلمين', false)
            ->assertSee($student->email, false);
    }
}
