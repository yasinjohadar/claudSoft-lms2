<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\GroupRegistrationSetting;
use App\Models\EmailTemplate;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Http\Request;

class GroupRegistrationSettingController extends Controller
{
    /**
     * عرض إعدادات التسجيل للمجموعة
     */
    public function index(CourseGroup $group)
    {
        $settings = GroupRegistrationSetting::firstOrCreate(
            ['group_id' => $group->id],
            [
                'is_registration_enabled' => true,
                'auto_create_user' => true,
                'auto_approve_membership' => false,
                'send_welcome_email' => true,
                'send_welcome_whatsapp' => false,
                'require_email_verification' => false,
            ]
        );

        $emailTemplates = EmailTemplate::active()->get();
        $whatsappTemplates = WhatsAppMessageTemplate::active()
            ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
            ->orderBy('name')
            ->get();

        return view('admin.group-registration-settings.index', compact('group', 'settings', 'emailTemplates', 'whatsappTemplates'));
    }

    /**
     * تحديث إعدادات التسجيل
     */
    public function update(Request $request, CourseGroup $group)
    {
        // معالجة checkboxes الرئيسية قبل الـ validation
        // تحويل "on" إلى true والقيمة غير الموجودة إلى false
        $booleanFields = [
            'is_registration_enabled',
            'auto_create_user',
            'auto_approve_membership',
            'send_welcome_email',
            'send_welcome_whatsapp',
            'require_email_verification',
        ];
        
        foreach ($booleanFields as $field) {
            $value = $request->input($field);
            // إذا كان الحقل موجود وقيمته "on" أو true أو "1" أو 1، نضعه true
            // وإلا نضعه false
            $request->merge([
                $field => ($value === 'on' || $value === true || $value === '1' || $value === 1)
            ]);
        }

        $validated = $request->validate([
            'diploma_name' => 'nullable|string|max:255',
            'is_registration_enabled' => 'boolean',
            'auto_create_user' => 'boolean',
            'auto_approve_membership' => 'boolean',
            'send_welcome_email' => 'boolean',
            'send_welcome_whatsapp' => 'boolean',
            'email_template_id' => 'nullable|exists:email_templates,id',
            'whatsapp_template_id' => 'nullable|exists:whatsapp_message_templates,id',
            'whatsapp_group_link' => 'nullable|url|max:500',
            'require_email_verification' => 'boolean',
        ]);

        $settings = GroupRegistrationSetting::updateOrCreate(
            ['group_id' => $group->id],
            $validated
        );

        return back()->with('success', 'تم تحديث الإعدادات بنجاح.');
    }
}
