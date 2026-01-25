<?php

namespace App\Services;

use App\Models\GroupRegistration;
use App\Models\EmailTemplate;
use App\Models\GroupRegistrationSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RegistrationEmailService
{

    /**
     * إرسال بريد ترحيبي للتسجيل (للمجموعة)
     */
    public function sendWelcomeEmailForGroup(GroupRegistration $registration): bool
    {
        try {
            $group = $registration->group;
            $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();

            // الحصول على القالب
            $template = null;
            if ($settings && $settings->email_template_id) {
                $template = EmailTemplate::find($settings->email_template_id);
            }

            // إذا لم يكن هناك قالب محدد، استخدام القالب الافتراضي
            if (!$template) {
                $template = EmailTemplate::where('type', EmailTemplate::TYPE_REGISTRATION_WELCOME)
                    ->where('is_active', true)
                    ->first();
            }

            // متغيرات القالب
            $variables = [
                'student_name' => $registration->name_ar ?? $registration->name,
                'student_name_en' => $registration->name,
                'group_name' => $group->name,
                'email' => $registration->email,
                'phone' => $registration->phone ?? '',
            ];

            $subject = $template ? $template->renderSubject($variables) : 'مرحباً بك في ' . $group->name;
            $body = $template ? $template->render($variables) : $this->getDefaultEmailBodyForGroup($registration, $group);

            // إرسال البريد
            $fromAddress = config('mail.from.address', 'noreply@cloudsoft.edu');
            $fromName = config('mail.from.name', 'كلاودسوفت التعليمية');
            
            Mail::send([], [], function ($message) use ($registration, $subject, $body, $fromAddress, $fromName) {
                $message->from($fromAddress, $fromName)
                    ->to($registration->email, $registration->name_ar ?? $registration->name)
                    ->subject($subject)
                    ->html($body);
            });

            // تحديث حالة الإرسال
            $registration->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send group registration email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * الحصول على محتوى البريد الافتراضي للمجموعة
     */
    private function getDefaultEmailBodyForGroup(GroupRegistration $registration, $group): string
    {
        $name = $registration->name_ar ?? $registration->name;
        
        return "
        <html>
        <body dir='rtl'>
            <h2>مرحباً {$name}</h2>
            <p>شكراً لك على التسجيل في مجموعة <strong>{$group->name}</strong></p>
            <p>تم استلام طلب تسجيلك بنجاح وسيتم مراجعته قريباً.</p>
            <p>في حالة إنشاء حساب لك، سيتم إرسال تفاصيل تسجيل الدخول إلى هذا البريد الإلكتروني.</p>
            <p>مع تحياتنا،<br>فريق الإدارة</p>
        </body>
        </html>
        ";
    }
}
