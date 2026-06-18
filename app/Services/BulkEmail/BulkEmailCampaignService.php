<?php

namespace App\Services\BulkEmail;

use App\Jobs\SendBulkEmailRecipientJob;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkEmailCampaignService
{
    public function __construct(
        private BulkEmailAudienceResolver $audienceResolver,
        private BulkEmailSettingsService $settingsService,
        private BulkEmailThrottleService $throttleService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createAndDispatch(array $data): BulkEmailCampaign
    {
        $studentIds = $data['student_ids'] ?? [];
        if (! is_array($studentIds)) {
            $studentIds = [];
        }

        $users = $this->audienceResolver->resolveFromParams(
            $data['audience_type'],
            $studentIds,
            isset($data['course_id']) ? (int) $data['course_id'] : null,
            isset($data['group_id']) ? (int) $data['group_id'] : null
        );

        if ($users->isEmpty()) {
            throw new \InvalidArgumentException('لم يتم العثور على مستلمين مطابقين للمعايير المحددة.');
        }

        $deliverableCount = $users->filter(fn (User $user) => trim((string) ($user->email ?? '')) !== '')->count();

        $this->settingsService->assertCampaignLimits($deliverableCount);

        return DB::transaction(function () use ($data, $studentIds, $users) {
            $campaign = BulkEmailCampaign::create([
                'email_setting_id' => $data['email_setting_id'] ?? null,
                'email_template_id' => $data['email_template_id'] ?? null,
                'content_mode' => $data['content_mode'],
                'subject' => $data['subject'] ?? null,
                'body' => $data['body'] ?? null,
                'audience_type' => $data['audience_type'],
                'course_id' => $data['course_id'] ?? null,
                'group_id' => $data['group_id'] ?? null,
                'student_ids' => ! empty($studentIds) ? array_values($studentIds) : null,
                'total_recipients' => $users->count(),
                'sent_count' => 0,
                'failed_count' => 0,
                'skipped_count' => 0,
                'status' => BulkEmailCampaign::STATUS_PENDING,
                'created_by' => Auth::id(),
                'started_at' => now(),
            ]);

            $delayIndex = 0;
            $pendingJobs = 0;

            foreach ($users as $user) {
                $email = trim((string) ($user->email ?? ''));
                $isSkipped = $email === '';

                $recipient = BulkEmailRecipient::create([
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'email' => $email ?: null,
                    'status' => $isSkipped
                        ? BulkEmailRecipient::STATUS_SKIPPED
                        : BulkEmailRecipient::STATUS_PENDING,
                    'error_message' => $isSkipped ? 'لا يوجد بريد إلكتروني للطالب.' : null,
                ]);

                if ($isSkipped) {
                    $campaign->increment('skipped_count');
                    continue;
                }

                SendBulkEmailRecipientJob::dispatch($campaign, $recipient)
                    ->delay(now()->addSeconds($this->throttleService->cumulativeDelayForIndex($delayIndex)));

                $delayIndex++;
                $pendingJobs++;
            }

            if ($pendingJobs === 0) {
                $campaign->update([
                    'status' => BulkEmailCampaign::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            } else {
                $campaign->update(['status' => BulkEmailCampaign::STATUS_PROCESSING]);
            }

            return $campaign->fresh(['course', 'group', 'emailTemplate', 'emailSetting', 'creator']);
        });
    }

    public function retryFailed(BulkEmailCampaign $campaign): int
    {
        $failedRecipients = $campaign->recipients()
            ->where('status', BulkEmailRecipient::STATUS_FAILED)
            ->get();

        if ($failedRecipients->isEmpty()) {
            return 0;
        }

        $campaign->update([
            'status' => BulkEmailCampaign::STATUS_PROCESSING,
            'completed_at' => null,
            'failed_count' => max(0, $campaign->failed_count - $failedRecipients->count()),
        ]);

        $delayIndex = 0;

        foreach ($failedRecipients as $recipient) {
            $recipient->update([
                'status' => BulkEmailRecipient::STATUS_PENDING,
                'error_message' => null,
                'sent_at' => null,
                'rendered_subject' => null,
            ]);

            SendBulkEmailRecipientJob::dispatch($campaign, $recipient)
                ->delay(now()->addSeconds($this->throttleService->cumulativeDelayForIndex($delayIndex)));

            $delayIndex++;
        }

        return $failedRecipients->count();
    }
}
