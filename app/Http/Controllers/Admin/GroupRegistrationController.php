<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupRegistration;
use App\Models\CourseGroup;
use App\Jobs\ProcessGroupRegistrationJob;
use App\Jobs\SendGroupRegistrationEmailJob;
use App\Jobs\SendGroupRegistrationWhatsAppJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupRegistrationController extends Controller
{
    /**
     * عرض قائمة التسجيلات
     */
    public function index(Request $request)
    {
        $query = GroupRegistration::with(['group', 'user', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $registrations = $query->paginate(15);
        $groups = CourseGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.group-registrations.index', compact('registrations', 'groups'));
    }

    /**
     * عرض تفاصيل التسجيل
     */
    public function show(GroupRegistration $registration)
    {
        $registration->load(['group', 'user', 'createdBy', 'nationality']);
        return view('admin.group-registrations.show', compact('registration'));
    }

    /**
     * إعادة معالجة تسجيل فاشل أو معلق
     */
    public function reprocess(GroupRegistration $registration)
    {
        if ($registration->isCompleted()) {
            return back()->with('warning', 'هذا التسجيل مكتمل بالفعل ولا يمكن إعادة معالجته.');
        }

        try {
            $registration->update(['status' => 'pending']);
            ProcessGroupRegistrationJob::dispatch($registration);
            return back()->with('success', 'تمت إضافة التسجيل إلى قائمة المعالجة.');
        } catch (\Exception $e) {
            Log::error('Failed to reprocess group registration: ' . $e->getMessage(), ['registration_id' => $registration->id]);
            return back()->with('error', 'حدث خطأ أثناء إعادة معالجة التسجيل.');
        }
    }

    /**
     * إعادة إرسال البريد الإلكتروني
     */
    public function resendEmail(GroupRegistration $registration)
    {
        try {
            SendGroupRegistrationEmailJob::dispatch($registration);
            return back()->with('success', 'تمت إعادة إرسال البريد الإلكتروني الترحيبي.');
        } catch (\Exception $e) {
            Log::error('Failed to resend group registration email: ' . $e->getMessage(), ['registration_id' => $registration->id]);
            return back()->with('error', 'حدث خطأ أثناء إعادة إرسال البريد الإلكتروني.');
        }
    }

    /**
     * إعادة إرسال رسالة الواتساب
     */
    public function resendWhatsApp(GroupRegistration $registration)
    {
        try {
            SendGroupRegistrationWhatsAppJob::dispatch($registration);
            return back()->with('success', 'تمت إعادة إرسال رسالة الواتساب الترحيبية.');
        } catch (\Exception $e) {
            Log::error('Failed to resend group registration WhatsApp: ' . $e->getMessage(), ['registration_id' => $registration->id]);
            return back()->with('error', 'حدث خطأ أثناء إعادة إرسال رسالة الواتساب.');
        }
    }

    /**
     * حذف التسجيل
     */
    public function destroy(GroupRegistration $registration)
    {
        try {
            $registration->delete();
            return back()->with('success', 'تم حذف التسجيل بنجاح.');
        } catch (\Exception $e) {
            Log::error('Failed to delete group registration: ' . $e->getMessage(), ['registration_id' => $registration->id]);
            return back()->with('error', 'حدث خطأ أثناء حذف التسجيل.');
        }
    }
}
