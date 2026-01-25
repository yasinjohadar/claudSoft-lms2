<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    /**
     * عرض قائمة القوالب
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::query();

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب الحالة
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }

        // بحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.email-templates.index', compact('templates'));
    }

    /**
     * عرض فورم إنشاء قالب جديد
     */
    public function create()
    {
        return view('admin.email-templates.create');
    }

    /**
     * حفظ القالب الجديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'type' => 'required|in:registration_welcome,enrollment_confirmation,custom',
            'variables' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template = EmailTemplate::create($validated);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'تم إنشاء القالب بنجاح.');
    }

    /**
     * عرض تفاصيل القالب
     */
    public function show(EmailTemplate $emailTemplate)
    {
        // بيانات تجريبية للمعاينة
        $testData = [
            'student_name' => 'أحمد محمد',
            'student_name_en' => 'Ahmed Mohammed',
            'group_name' => 'برمجة الويب',
            'email' => 'ahmed@example.com',
            'phone' => '+966501234567',
        ];

        $renderedSubject = $emailTemplate->renderSubject($testData);
        $renderedBody = $emailTemplate->render($testData);

        return view('admin.email-templates.show', compact('emailTemplate', 'renderedSubject', 'renderedBody', 'testData'));
    }

    /**
     * عرض فورم تعديل القالب
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.edit', compact('emailTemplate'));
    }

    /**
     * تحديث القالب
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'type' => 'required|in:registration_welcome,enrollment_confirmation,custom',
            'variables' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $emailTemplate->update($validated);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'تم تحديث القالب بنجاح.');
    }

    /**
     * حذف القالب
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        try {
            $emailTemplate->delete();
            return redirect()->route('admin.email-templates.index')
                ->with('success', 'تم حذف القالب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Failed to delete email template', [
                'template_id' => $emailTemplate->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('admin.email-templates.index')
                ->with('error', 'حدث خطأ أثناء حذف القالب.');
        }
    }

    /**
     * معاينة القالب مع بيانات تجريبية
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        // بيانات تجريبية
        $testData = [
            'student_name' => 'أحمد محمد',
            'student_name_en' => 'Ahmed Mohammed',
            'group_name' => 'برمجة الويب',
            'email' => 'ahmed@example.com',
            'phone' => '+966501234567',
        ];

        $renderedSubject = $emailTemplate->renderSubject($testData);
        $renderedBody = $emailTemplate->render($testData);

        return view('admin.email-templates.preview', compact('emailTemplate', 'renderedSubject', 'renderedBody', 'testData'));
    }

    /**
     * نسخ قالب موجود
     */
    public function duplicate(EmailTemplate $emailTemplate)
    {
        try {
            $newTemplate = $emailTemplate->replicate();
            $newTemplate->name = $emailTemplate->name . ' (نسخة)';
            $newTemplate->name_ar = ($emailTemplate->name_ar ?? $emailTemplate->name) . ' (نسخة)';
            $newTemplate->is_active = false; // تعطيل النسخة المنسوخة افتراضياً
            $newTemplate->save();

            return redirect()->route('admin.email-templates.edit', $newTemplate)
                ->with('success', 'تم نسخ القالب بنجاح. يمكنك الآن تعديله.');
        } catch (\Exception $e) {
            Log::error('Failed to duplicate email template', [
                'template_id' => $emailTemplate->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('admin.email-templates.index')
                ->with('error', 'حدث خطأ أثناء نسخ القالب.');
        }
    }

    /**
     * إرسال بريد تجريبي
     */
    public function sendTest(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // بيانات تجريبية
            $testData = [
                'student_name' => 'أحمد محمد',
                'student_name_en' => 'Ahmed Mohammed',
                'group_name' => 'برمجة الويب',
                'email' => $validated['test_email'],
                'phone' => '+966501234567',
            ];

            $subject = $emailTemplate->renderSubject($testData);
            $body = $emailTemplate->render($testData);

            $fromAddress = config('mail.from.address', 'noreply@cloudsoft.edu');
            $fromName = config('mail.from.name', 'كلاودسوفت التعليمية');

            Mail::send([], [], function ($message) use ($validated, $subject, $body, $fromAddress, $fromName) {
                $message->from($fromAddress, $fromName)
                    ->to($validated['test_email'])
                    ->subject('[تجريبي] ' . $subject)
                    ->html($body);
            });

            return back()->with('success', 'تم إرسال البريد التجريبي بنجاح إلى ' . $validated['test_email']);
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'template_id' => $emailTemplate->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء إرسال البريد التجريبي: ' . $e->getMessage());
        }
    }
}
