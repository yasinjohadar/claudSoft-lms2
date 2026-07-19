<?php

namespace App\Services;

use App\Models\GroupRegistration;
use App\Models\User;
use App\Models\CourseGroup;
use App\Models\GroupRegistrationSetting;
use App\Models\GroupMembershipRequest;
use App\Services\Auth\AccountCreatedCredentialDeliveryService;
use App\Services\Auth\NewAccountGroupRegistrationWhatsAppBundleService;
use App\Support\InternationalPhoneDigits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupRegistrationService
{
    /**
     * إنشاء تسجيل جديد للمجموعة
     */
    public function createRegistration(array $data, ?int $createdBy = null): GroupRegistration
    {
        // حساب full_phone تلقائياً
        if (isset($data['country_code']) && isset($data['phone'])) {
            $data['full_phone'] = $this->formatFullPhone($data['country_code'], $data['phone']);
        }

        // الحصول على إعدادات المجموعة
        $group = CourseGroup::findOrFail($data['group_id']);
        $settings = GroupRegistrationSetting::firstOrCreate(
            ['group_id' => $group->id],
            $this->getDefaultSettings()
        );

        if ($createdBy) {
            $data['created_by'] = $createdBy;
        }

        $registration = GroupRegistration::create($data);

        // معالجة التسجيل مباشرة (بدون Queue) لضمان التنفيذ الفوري
        // يمكن تغيير هذا لاستخدام Queue إذا كان مفعلاً
        try {
            $this->processRegistration($registration);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to process registration immediately: ' . $e->getMessage());
            // في حالة الفشل، نرسل Job للمحاولة لاحقاً
            \App\Jobs\ProcessGroupRegistrationJob::dispatch($registration);
        }

        return $registration;
    }

    /**
     * معالجة التسجيل (إنشاء حساب + انضمام للمجموعة)
     */
    public function processRegistration(GroupRegistration $registration): void
    {
        $plainPassword = null;
        $userCreated = false;

        DB::beginTransaction();
        try {
            $registration->markAsProcessing();

            $group = $registration->group;
            $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();

            if (!$settings) {
                $settings = GroupRegistrationSetting::create([
                    'group_id' => $group->id,
                    ...$this->getDefaultSettings(),
                ]);
            }

            // 1. التحقق أولاً من وجود المستخدم مسبقاً (من خلال الإيميل)
            $existingUser = User::where('email', $registration->email)->first();
            $user = null;

            if ($existingUser) {
                // المستخدم موجود مسبقاً
                $user = $existingUser;
                $registration->update(['user_id' => $user->id]);
                // لا يتم إنشاء مستخدم جديد
            } else {
                // المستخدم غير موجود - إنشاء حساب جديد إذا كان auto_create_user = true
                if ($settings->auto_create_user) {
                    $created = $this->createUser($registration);
                    $user = $created['user'];
                    $plainPassword = $created['plain_password'];
                    $userCreated = $plainPassword !== null;
                    $registration->update([
                        'user_id' => $user->id,
                        'user_created' => true,
                    ]);
                }
            }

            // 2. إضافة المستخدم للمجموعة أو إنشاء طلب انضمام
            if ($user && $group) {
                if ($settings->shouldAutoApproveMembership()) {
                    // إضافة مباشرة للمجموعة
                    $this->addUserToGroup($user, $group);
                } else {
                    // إنشاء طلب انضمام بحالة pending
                    $this->createMembershipRequest($user, $group, $registration);
                }
            }

            $registration->markAsCompleted();

            DB::commit();

            // 3. إرسال الإشعارات بعد نجاح المعاملة
            if ($userCreated && $plainPassword !== null && $user) {
                $this->deliverNewAccountCredentials(
                    $user,
                    $plainPassword,
                    (bool) $settings->send_welcome_email,
                    (bool) $settings->send_welcome_whatsapp,
                    $registration,
                );
            } else {
                $this->sendExistingUserWelcome($registration, $settings);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process group registration', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $registration->markAsFailed($e->getMessage());
        }
    }

    /**
     * إرسال بيانات الدخول للحساب الجديد عبر الإيميل/الواتساب.
     * عند تفعيل الواتساب: ترحيب + بيانات دخول + كلمة مرور من نفس رقم Evolution.
     */
    private function deliverNewAccountCredentials(
        User $user,
        #[\SensitiveParameter] string $plainPassword,
        bool $sendEmail,
        bool $sendWhatsApp,
        GroupRegistration $registration,
    ): void {
        if (! $sendEmail && ! $sendWhatsApp) {
            return;
        }

        try {
            if ($sendWhatsApp) {
                $result = app(NewAccountGroupRegistrationWhatsAppBundleService::class)->deliver(
                    $user,
                    $registration,
                    $plainPassword,
                    $sendEmail,
                    true,
                );
            } else {
                $result = app(AccountCreatedCredentialDeliveryService::class)->deliver(
                    $user,
                    $plainPassword,
                    AccountCreatedCredentialDeliveryService::CONTEXT_ACCOUNT_CREATED,
                    $sendEmail,
                    false,
                );
            }

            $updates = [];
            if ($result['email_sent']) {
                $updates['email_sent'] = true;
                $updates['email_sent_at'] = now();
            }
            if ($result['whatsapp_sent']) {
                $updates['whatsapp_sent'] = true;
                $updates['whatsapp_sent_at'] = now();
                $updates['whatsapp_error'] = null;
            } elseif (! empty($result['whatsapp_error'])) {
                $updates['whatsapp_error'] = $result['whatsapp_error'];
            }
            if ($updates !== []) {
                $registration->update($updates);
            }
        } catch (\Exception $e) {
            Log::error('Failed to deliver account credentials for group registration', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ترحيب للمستخدم الموجود مسبقاً (بدون كلمة مرور).
     */
    private function sendExistingUserWelcome(GroupRegistration $registration, GroupRegistrationSetting $settings): void
    {
        if ($settings->send_welcome_email) {
            try {
                $emailService = app(RegistrationEmailService::class);
                $sent = $emailService->sendWelcomeEmailForGroup($registration);

                if (! $sent) {
                    \App\Jobs\SendGroupRegistrationEmailJob::dispatch($registration);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
                try {
                    \App\Jobs\SendGroupRegistrationEmailJob::dispatch($registration);
                } catch (\Exception $jobException) {
                    Log::error('Failed to dispatch email job', [
                        'registration_id' => $registration->id,
                        'error' => $jobException->getMessage(),
                    ]);
                }
            }
        }

        if ($settings->send_welcome_whatsapp) {
            try {
                $whatsAppService = app(RegistrationWhatsAppService::class);
                $sent = $whatsAppService->sendWelcomeWhatsAppForGroup($registration);

                if (! $sent) {
                    \App\Jobs\SendGroupRegistrationWhatsAppJob::dispatch($registration);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send WhatsApp message', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
                try {
                    \App\Jobs\SendGroupRegistrationWhatsAppJob::dispatch($registration);
                } catch (\Exception $jobException) {
                    Log::error('Failed to dispatch WhatsApp job', [
                        'registration_id' => $registration->id,
                        'error' => $jobException->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * إنشاء حساب مستخدم من التسجيل
     *
     * @return array{user: User, plain_password: string|null}
     */
    public function createUser(GroupRegistration $registration): array
    {
        // التحقق من عدم وجود مستخدم بنفس البريد
        $existingUser = User::where('email', $registration->email)->first();
        if ($existingUser) {
            return ['user' => $existingUser, 'plain_password' => null];
        }

        $phoneDigits = InternationalPhoneDigits::fromCountryAndLocal(
            (string) ($registration->country_code ?? ''),
            (string) ($registration->phone ?? '')
        );

        if ($phoneDigits === null && ! empty($registration->full_phone)) {
            $phoneDigits = InternationalPhoneDigits::fromInput((string) $registration->full_phone);
        }

        if ($phoneDigits !== null && User::fullPhoneDigitsTaken($phoneDigits)) {
            throw new \RuntimeException('رقم الهاتف مستخدم بالفعل لحساب آخر.');
        }

        $settings = GroupRegistrationSetting::where('group_id', $registration->group_id)->first();
        $plainPassword = $settings
            ? $settings->resolveNewAccountPassword()
            : app(AccountCreatedCredentialDeliveryService::class)->generateSecurePassword();

        $userData = [
            'name' => $registration->name,
            'name_ar' => $registration->name_ar,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'country_code' => $registration->country_code,
            'full_phone' => $registration->full_phone,
            'password' => $plainPassword, // hashed cast on User
            'is_active' => true,
        ];

        $user = User::create($userData);
        $user->assignRole('student');
        $user->assignStudentSerial();

        return ['user' => $user, 'plain_password' => $plainPassword];
    }

    /**
     * إضافة مستخدم لمجموعة
     */
    private function addUserToGroup(User $user, CourseGroup $group): void
    {
        if ($group->hasMember($user)) {
            return;
        }

        $group->addMember($user, 'member', [
            'source' => \App\Models\CourseGroupMembershipHistory::SOURCE_SYSTEM,
            'reason' => 'تسجيل عام مع موافقة تلقائية',
        ]);
    }

    /**
     * تنسيق رقم الهاتف الكامل
     */
    private function formatFullPhone(?string $countryCode, ?string $phone): ?string
    {
        if (!$countryCode || !$phone) {
            return null;
        }

        // إزالة أي رموز أو مسافات
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // إزالة الصفر من بداية رقم الهاتف إن وُجد
        if (str_starts_with($phone, '0')) {
            $phone = preg_replace('/^0/', '', $phone, 1);
        }
        $countryCode = preg_replace('/[^0-9+]/', '', $countryCode);

        // إزالة + من country_code إذا كان موجوداً
        $countryCode = ltrim($countryCode, '+');

        return $countryCode . $phone;
    }

    /**
     * إنشاء طلب انضمام للمجموعة
     */
    private function createMembershipRequest(User $user, CourseGroup $group, GroupRegistration $registration): void
    {
        // التحقق من عدم وجود طلب pending مسبقاً
        $existingRequest = GroupMembershipRequest::where('group_id', $group->id)
            ->where('student_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            // طلب موجود مسبقاً، لا ننشئ طلب جديد
            return;
        }

        // إنشاء طلب انضمام جديد
        GroupMembershipRequest::create([
            'group_id' => $group->id,
            'student_id' => $user->id,
            'status' => 'pending',
            'message' => $registration->notes ?? 'طلب انضمام من خلال فورم التسجيل',
            'request_date' => now(),
        ]);
    }

    /**
     * الحصول على الإعدادات الافتراضية
     */
    private function getDefaultSettings(): array
    {
        return [
            'is_registration_enabled' => true,
            'auto_create_user' => true,
            'auto_approve_membership' => false, // افتراضياً: طلب انضمام يحتاج موافقة
            'hide_courses_until_membership_approved' => false,
            'send_welcome_email' => true,
            'send_welcome_whatsapp' => false,
            'require_email_verification' => false,
        ];
    }
}
