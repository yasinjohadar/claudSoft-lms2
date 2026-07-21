<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\GroupRegistration;
use App\Models\GroupRegistrationSetting;
use App\Models\GroupMembershipRequest;
use App\Models\User;
use App\Rules\UniqueUserFullPhone;
use App\Services\GroupRegistrationReceiptService;
use App\Services\GroupRegistrationService;
use App\Services\Marketing\GoogleDataLayerService;
use App\Services\Marketing\MetaPixelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GroupRegistrationController extends Controller
{
    public function __construct(
        private GroupRegistrationService $registrationService,
        private GroupRegistrationReceiptService $receiptService,
        private MetaPixelService $metaPixel,
        private GoogleDataLayerService $googleDataLayer
    ) {}

    /**
     * عرض فورم التسجيل للمجموعة
     */
    public function create(CourseGroup $group)
    {
        // التحقق من تفعيل التسجيل للمجموعة
        $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();
        
        if (!$settings || !$settings->is_registration_enabled) {
            abort(404, 'التسجيل غير متاح لهذه المجموعة');
        }

        $diplomaName = $settings->diploma_name ?? 'دبلوم البرمجة';
        $this->metaPixel->trackLeadStarted("{$diplomaName} - {$group->name}");

        return view('frontend.group-registration.form', compact('group', 'settings'));
    }

    /**
     * حفظ التسجيل
     */
    public function store(Request $request, CourseGroup $group)
    {
        // التحقق من تفعيل التسجيل
        $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();
        
        if (!$settings || !$settings->is_registration_enabled) {
            return back()->withErrors(['error' => 'التسجيل غير متاح لهذه المجموعة']);
        }

        // التحقق من وجود تسجيل سابق في نفس المجموعة
        $existingRegistration = GroupRegistration::where('email', $request->email)
            ->where('group_id', $group->id)
            ->first();

        // السماح بإعادة التسجيل إذا: طلب انضمام مرفوض، أو تسجيل يتيم بعد حذف المستخدم
        $hasRejectedRequest = false;
        $hasOrphanedRegistration = $existingRegistration && $existingRegistration->user_id === null;

        if ($existingRegistration && $existingRegistration->user_id) {
            $hasRejectedRequest = GroupMembershipRequest::where('group_id', $group->id)
                ->where('student_id', $existingRegistration->user_id)
                ->where('status', 'rejected')
                ->exists();
        }

        $canReregister = $hasRejectedRequest || $hasOrphanedRegistration;

        // Allow same phone when re-registering as an existing user (matched by email).
        $ignoreUserId = User::where('email', $request->email)->value('id');

        // Build validation rules
        $validationRules = [
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                $canReregister
                    ? Rule::unique('group_registrations', 'email')->ignore($existingRegistration->id ?? null)->where(function ($query) use ($group) {
                        return $query->where('group_id', $group->id);
                    })
                    : Rule::unique('group_registrations', 'email')->where(function ($query) use ($group) {
                        return $query->where('group_id', $group->id);
                    }),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                new UniqueUserFullPhone($ignoreUserId ? (int) $ignoreUserId : null),
            ],
            'country_code' => ['required', 'string', 'max:8', \Illuminate\Validation\Rule::in(config('country_codes.allowed_codes'))],
            'commitment_to_training' => 'required|in:yes,no',
            'has_sufficient_time' => 'required|in:yes,no',
            'has_computer' => 'required|in:yes,no',
            'computer_experience_level' => 'required|in:none,beginner,intermediate,good,advanced',
            'programming_experience' => 'required|in:none,beginner,intermediate,expert',
            'education_level' => 'required|string|max:255',
            'interested_in_bootcamp' => 'required|in:yes,no',
            'membership_receipt' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'whatsapp_group_ack' => 'accepted',
        ];
        
        $validationMessages = [
            'name.required' => 'الاسم بالإنجليزية مطلوب',
            'name_ar.required' => 'الاسم بالعربية مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مسجّل مسبقاً في هذه المجموعة',
            'phone.required' => 'رقم الهاتف مطلوب',
            'country_code.required' => 'رمز الدولة مطلوب',
            'commitment_to_training.required' => 'يجب الإجابة على سؤال الالتزام بالتدريب',
            'commitment_to_training.in' => 'قيمة غير صحيحة لسؤال الالتزام بالتدريب',
            'has_sufficient_time.required' => 'يجب الإجابة على سؤال الوقت الكافي',
            'has_sufficient_time.in' => 'قيمة غير صحيحة لسؤال الوقت الكافي',
            'has_computer.required' => 'يجب الإجابة على سؤال امتلاك الحاسوب',
            'has_computer.in' => 'قيمة غير صحيحة لسؤال امتلاك الحاسوب',
            'computer_experience_level.required' => 'يجب اختيار مستوى خبرتك بالحاسوب',
            'computer_experience_level.in' => 'قيمة غير صحيحة لمستوى خبرة الحاسوب',
            'programming_experience.required' => 'يجب اختيار مستوى خبرتك بالبرمجة',
            'programming_experience.in' => 'قيمة غير صحيحة لمستوى خبرة البرمجة',
            'education_level.required' => 'آخر مرحلة دراسية مطلوبة',
            'education_level.max' => 'آخر مرحلة دراسية يجب أن تكون أقل من 255 حرف',
            'interested_in_bootcamp.required' => 'يجب الإجابة على سؤال الاهتمام بالمعسكر التدريبي',
            'interested_in_bootcamp.in' => 'قيمة غير صحيحة لسؤال الاهتمام بالمعسكر التدريبي',
            'membership_receipt.required' => 'يرجى رفع إثبات الشخصية (هوية، جواز، بطاقة جامعة، أو شهادة سواقة)',
            'membership_receipt.file' => 'ملف إثبات الشخصية المرفوع غير صالح',
            'membership_receipt.mimes' => 'يجب أن يكون إثبات الشخصية صورة (JPG أو PNG أو WEBP) أو ملف PDF',
            'membership_receipt.max' => 'يجب ألا يتجاوز حجم ملف إثبات الشخصية 10 ميجابايت',
            'whatsapp_group_ack.accepted' => 'يجب الموافقة على الانضمام إلى مجموعة الواتساب قبل إرسال الطلب',
        ];

        $validated = $request->validate($validationRules, $validationMessages);
        unset($validated['whatsapp_group_ack']);

        // إزالة الصفر من بداية رقم الهاتف إن أدخله الطالب
        if (!empty($validated['phone']) && str_starts_with(trim($validated['phone']), '0')) {
            $validated['phone'] = preg_replace('/^0/', '', trim($validated['phone']), 1);
        }

        try {
            $validated['group_id'] = $group->id;
            $validated['membership_receipt_path'] = $this->receiptService->store(
                $request->file('membership_receipt'),
                (int) $group->id
            );
            $validated['membership_receipt_disk'] = GroupRegistrationReceiptService::DISK;
            unset($validated['membership_receipt']);

            // استبدال التسجيل اليتيم/المرفوض بدل ترك صفوف مكررة
            if ($canReregister && $existingRegistration) {
                $existingRegistration->delete();
            }

            $registration = $this->registrationService->createRegistration($validated);

            $diplomaName = $settings->diploma_name ?? 'دبلوم البرمجة';
            $this->metaPixel->trackLeadWithCapi(
                $request,
                "{$diplomaName} - {$group->name}",
                $validated['email'],
                ($validated['country_code'] ?? '') . ($validated['phone'] ?? ''),
                $validated['name'],
                $validated['name_ar']
            );

            $this->googleDataLayer->trackGenerateLead("{$diplomaName} - {$group->name}");

            return redirect()->route('frontend.group-registration.success', $registration)
                ->with('success', 'تم إرسال طلب التسجيل بنجاح. سيتم مراجعته قريباً.');

        } catch (\Exception $e) {
            Log::error('Failed to create group registration', [
                'group_id' => $group->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->withErrors(['error' => 'حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.']);
        }
    }

    /**
     * صفحة النجاح
     */
    public function success(GroupRegistration $registration)
    {
        $settings = GroupRegistrationSetting::where('group_id', $registration->group_id)->first();
        return view('frontend.group-registration.success', compact('registration', 'settings'));
    }
}
