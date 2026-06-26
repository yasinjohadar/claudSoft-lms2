<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\GroupRegistration;
use App\Models\GroupRegistrationSetting;
use App\Models\GroupMembershipRequest;
use App\Models\User;
use App\Services\GroupRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GroupRegistrationController extends Controller
{
    public function __construct(
        private GroupRegistrationService $registrationService
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
        
        // التحقق من وجود طلب مرفوض للسماح بإعادة التسجيل
        $hasRejectedRequest = false;
        if ($existingRegistration && $existingRegistration->user_id) {
            $hasRejectedRequest = GroupMembershipRequest::where('group_id', $group->id)
                ->where('student_id', $existingRegistration->user_id)
                ->where('status', 'rejected')
                ->exists();
        }

        // Build validation rules
        $validationRules = [
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                $hasRejectedRequest 
                    ? Rule::unique('group_registrations', 'email')->ignore($existingRegistration->id ?? null)->where(function ($query) use ($group) {
                        return $query->where('group_id', $group->id);
                    })
                    : Rule::unique('group_registrations', 'email')->where(function ($query) use ($group) {
                        return $query->where('group_id', $group->id);
                    }),
            ],
            'phone' => 'required|string|max:20',
            'country_code' => ['required', 'string', 'max:8', \Illuminate\Validation\Rule::in(config('country_codes.allowed_codes'))],
            'commitment_to_training' => 'required|in:yes,no',
            'has_sufficient_time' => 'required|in:yes,no',
            'has_computer' => 'required|in:yes,no',
            'computer_experience_level' => 'required|in:none,beginner,intermediate,good,advanced',
            'programming_experience' => 'required|in:none,beginner,intermediate,expert',
            'education_level' => 'required|string|max:255',
            'interested_in_bootcamp' => 'required|in:yes,no',
        ];
        
        $validationMessages = [
            'name.required' => 'الاسم بالإنجليزية مطلوب',
            'name_ar.required' => 'الاسم بالعربية مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل',
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
        ];

        $validated = $request->validate($validationRules, $validationMessages);

        // إزالة الصفر من بداية رقم الهاتف إن أدخله الطالب
        if (!empty($validated['phone']) && str_starts_with(trim($validated['phone']), '0')) {
            $validated['phone'] = preg_replace('/^0/', '', trim($validated['phone']), 1);
        }

        try {
            $validated['group_id'] = $group->id;

            $registration = $this->registrationService->createRegistration($validated);

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
