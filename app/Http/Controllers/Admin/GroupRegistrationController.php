<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupRegistration;
use App\Models\CourseGroup;
use App\Models\Nationality;
use App\Jobs\ProcessGroupRegistrationJob;
use App\Jobs\SendGroupRegistrationEmailJob;
use App\Jobs\SendGroupRegistrationWhatsAppJob;
use App\Services\GroupRegistrationReceiptService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupRegistrationController extends Controller
{
    /**
     * عرض قائمة التسجيلات
     */
    public function index(Request $request)
    {
        $query = $this->buildRegistrationsQuery($request);
        $registrations = $query->paginate(15)->withQueryString();
        $stats = $this->computeRegistrationStats($request);

        $groups = CourseGroup::where('is_active', true)->orderBy('name')->get();
        $nationalities = Nationality::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.group-registrations._table', compact('registrations'))->render(),
                'stats_html' => view('admin.group-registrations.partials.stats', compact('stats'))->render(),
                'count' => $registrations->total(),
            ]);
        }

        return view('admin.group-registrations.index', compact(
            'registrations',
            'groups',
            'nationalities',
            'stats'
        ));
    }

    private function buildRegistrationsQuery(Request $request)
    {
        $query = GroupRegistration::with(['group.courses', 'user', 'createdBy', 'nationality'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('full_phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('has_computer')) {
            $query->where('has_computer', $request->has_computer);
        }

        if ($request->filled('user_created')) {
            $query->where('user_created', $request->user_created === '1');
        }

        if ($request->filled('email_sent')) {
            $query->where('email_sent', $request->email_sent === '1');
        }

        if ($request->filled('whatsapp_status')) {
            match ($request->whatsapp_status) {
                'sent' => $query->where('whatsapp_sent', true),
                'not_sent' => $query->where('whatsapp_sent', false)->whereNull('whatsapp_error'),
                'failed' => $query->whereNotNull('whatsapp_error'),
                default => null,
            };
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('nationality_id')) {
            $query->where('nationality_id', $request->nationality_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    private function computeRegistrationStats(Request $request): array
    {
        $base = $this->buildRegistrationsQuery($request);

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', GroupRegistration::STATUS_PENDING)->count(),
            'processing' => (clone $base)->where('status', GroupRegistration::STATUS_PROCESSING)->count(),
            'completed' => (clone $base)->where('status', GroupRegistration::STATUS_COMPLETED)->count(),
            'failed' => (clone $base)->where('status', GroupRegistration::STATUS_FAILED)->count(),
            'user_created' => (clone $base)->where('user_created', true)->count(),
            'email_sent' => (clone $base)->where('email_sent', true)->count(),
            'whatsapp_sent' => (clone $base)->where('whatsapp_sent', true)->count(),
            'whatsapp_failed' => (clone $base)->whereNotNull('whatsapp_error')->count(),
        ];
    }

    /**
     * تقرير رسائل الواتساب المرسلة عند التسجيل في المجموعات
     */
    public function whatsappReport(Request $request)
    {
        $query = GroupRegistration::with(['group', 'nationality'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('whatsapp_status')) {
            if ($request->whatsapp_status === 'sent') {
                $query->where('whatsapp_sent', true);
            } elseif ($request->whatsapp_status === 'not_sent') {
                $query->where('whatsapp_sent', false);
            } elseif ($request->whatsapp_status === 'failed') {
                $query->whereNotNull('whatsapp_error');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('full_phone', 'like', "%{$search}%");
            });
        }

        $registrations = $query->paginate(20)->withQueryString();
        $groups = CourseGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.group-registrations.whatsapp-report', compact('registrations', 'groups'));
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
     * عرض أو تنزيل وصل الانتساب من التخزين الخاص عبر رابط إداري مؤقت.
     */
    public function receipt(
        Request $request,
        GroupRegistration $registration,
        GroupRegistrationReceiptService $receiptService
    ): Response {
        abort_unless($registration->membership_receipt_path, 404);

        $receipt = $receiptService->retrieve(
            $registration->membership_receipt_path,
            $registration->membership_receipt_disk
        );

        abort_if($receipt === null, 404, 'تعذر العثور على وصل الانتساب.');

        $extension = strtolower(pathinfo($registration->membership_receipt_path, PATHINFO_EXTENSION));
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)
            ? $extension
            : 'bin';
        $filename = "membership-receipt-{$registration->id}.{$extension}";
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($receipt['content'], 200, [
            'Content-Type' => $receipt['mime_type'] ?: 'application/octet-stream',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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
            if ($registration->user_created && $registration->user) {
                $result = app(\App\Services\Auth\AccountCreatedCredentialDeliveryService::class)
                    ->resetAndDeliver($registration->user, sendEmail: true, sendWhatsApp: false);

                if ($result['email_sent']) {
                    $registration->update([
                        'email_sent' => true,
                        'email_sent_at' => now(),
                    ]);

                    return back()->with('success', 'تمت إعادة تعيين كلمة المرور وإرسال بيانات الدخول عبر البريد.');
                }

                return back()->with('error', $result['email_error'] ?: 'تعذّر إرسال بيانات الدخول عبر البريد.');
            }

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
            if ($registration->user_created && $registration->user) {
                $result = app(\App\Services\Auth\AccountCreatedCredentialDeliveryService::class)
                    ->resetAndDeliver($registration->user, sendEmail: false, sendWhatsApp: true);

                if ($result['whatsapp_sent']) {
                    $registration->update([
                        'whatsapp_sent' => true,
                        'whatsapp_sent_at' => now(),
                    ]);

                    return back()->with('success', 'تمت إعادة تعيين كلمة المرور وإرسال بيانات الدخول عبر الواتساب.');
                }

                return back()->with('error', $result['whatsapp_error'] ?: 'تعذّر إرسال بيانات الدخول عبر الواتساب.');
            }

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
